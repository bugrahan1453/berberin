<?php
/**
 * BERBERiN — Web Kurulum Sihirbazı
 * Tarayıcıdan aç, bilgileri gir, kur.
 * Kurulum tamamlanınca bu dosyayı sil!
 */

define('ROOT', dirname(__DIR__));
define('STEP', $_POST['step'] ?? $_GET['step'] ?? 1);

// ------- YARDIMCI FONKSİYONLAR -------

function envPath(): string { return ROOT . '/.env'; }

function readEnv(): array {
    if (!file_exists(envPath())) return [];
    $lines = file(envPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $env[trim($key)] = trim($val);
    }
    return $env;
}

function writeEnv(array $data): void {
    $example = ROOT . '/.env.example';
    $content = file_exists($example) ? file_get_contents($example) : '';

    foreach ($data as $key => $value) {
        $value = str_contains($value, ' ') ? '"' . $value . '"' : $value;
        if (preg_match('/^' . preg_quote($key, '/') . '=/m', $content)) {
            $content = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $key . '=' . $value, $content);
        } else {
            $content .= "\n" . $key . '=' . $value;
        }
    }

    file_put_contents(envPath(), $content);
}

function runArtisan(string $cmd): array {
    $php = PHP_BINARY;
    $artisan = ROOT . '/artisan';
    $output = [];
    $code = 0;
    exec("\"$php\" \"$artisan\" $cmd 2>&1", $output, $code);
    return ['output' => implode("\n", $output), 'success' => $code === 0];
}

function testDb(string $host, string $db, string $user, string $pass): bool {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function checks(): array {
    return [
        'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
        'PDO MySQL'  => extension_loaded('pdo_mysql'),
        'OpenSSL'    => extension_loaded('openssl'),
        'Mbstring'   => extension_loaded('mbstring'),
        'Tokenizer'  => extension_loaded('tokenizer'),
        'XML'        => extension_loaded('xml'),
        'Ctype'      => extension_loaded('ctype'),
        'storage/ yazılabilir' => is_writable(ROOT . '/storage'),
        'bootstrap/cache/ yazılabilir' => is_writable(ROOT . '/bootstrap/cache'),
        'composer yüklü (vendor/)' => is_dir(ROOT . '/vendor'),
    ];
}

// ------- FORM GÖNDERILDI Mİ -------

$message = '';
$success = false;
$step = (int) STEP;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($step === 2) {
        // Veritabanı bağlantı testi + .env yaz
        $host = trim($_POST['db_host'] ?? 'localhost');
        $db   = trim($_POST['db_name'] ?? '');
        $user = trim($_POST['db_user'] ?? '');
        $pass = trim($_POST['db_pass'] ?? '');
        $url  = rtrim(trim($_POST['app_url'] ?? ''), '/');

        if (!$db || !$user) {
            $message = 'error:Veritabanı adı ve kullanıcı adı zorunlu!';
        } elseif (!testDb($host, $db, $user, $pass)) {
            $message = 'error:Veritabanına bağlanılamadı. Bilgileri kontrol et.';
        } else {
            writeEnv([
                'APP_NAME'  => 'BERBERiN',
                'APP_ENV'   => 'production',
                'APP_DEBUG' => 'false',
                'APP_URL'   => $url,
                'DB_CONNECTION' => 'mysql',
                'DB_HOST'   => $host,
                'DB_PORT'   => '3306',
                'DB_DATABASE' => $db,
                'DB_USERNAME' => $user,
                'DB_PASSWORD' => $pass,
                'QUEUE_CONNECTION' => 'sync',
                'SESSION_DRIVER'   => 'file',
                'CACHE_STORE'      => 'file',
            ]);
            $step = 3;
            $message = 'success:Veritabanı bağlantısı başarılı, .env yazıldı.';
        }
    }

    if ($step === 3 && isset($_POST['do_install'])) {
        // APP_KEY üret
        $keyResult  = runArtisan('key:generate --force');
        // Migration
        $migResult  = runArtisan('migrate --force');
        // Cache
        $cfgResult  = runArtisan('config:cache');
        $rtResult   = runArtisan('route:cache');
        // Storage link
        $slResult   = runArtisan('storage:link');

        $allOk = $keyResult['success'] && $migResult['success'];

        $log = implode("\n\n", [
            "=== APP KEY ===\n"    . $keyResult['output'],
            "=== MİGRATION ===\n" . $migResult['output'],
            "=== CONFIG CACHE ===\n" . $cfgResult['output'],
            "=== ROUTE CACHE ===\n"  . $rtResult['output'],
            "=== STORAGE LINK ===\n" . $slResult['output'],
        ]);

        if ($allOk) {
            $step = 4;
            $message = 'success:Kurulum tamamlandı!';
            $success = true;
        } else {
            $message = 'error:Kurulum sırasında hata oluştu. Logları incele.';
        }
        // Logu dosyaya yaz
        file_put_contents(ROOT . '/storage/logs/install.log', $log);
    }
}

$checks = checks();
$allChecksOk = !in_array(false, $checks, true);

// ------- MESAJ PARSE -------
$msgType = '';
$msgText = '';
if ($message) {
    [$msgType, $msgText] = explode(':', $message, 2);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>BERBERiN Kurulum</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#0A0A0F;color:#e5e5e5;font-family:system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
  .card{background:#13131A;border:1px solid #2a2a3a;border-radius:16px;width:100%;max-width:560px;padding:36px}
  h1{color:#FF6B35;font-size:22px;font-weight:800;margin-bottom:4px}
  .sub{color:#6b7280;font-size:13px;margin-bottom:28px}
  .step-bar{display:flex;gap:8px;margin-bottom:28px}
  .step-bar span{flex:1;height:4px;border-radius:4px;background:#1A1A24}
  .step-bar span.done{background:#FF6B35}
  label{display:block;font-size:12px;color:#9ca3af;margin-bottom:5px;margin-top:16px;font-weight:500;text-transform:uppercase;letter-spacing:.5px}
  input[type=text],input[type=password],input[type=url]{width:100%;background:#1A1A24;border:1px solid #2a2a3a;border-radius:8px;padding:10px 12px;color:#e5e5e5;font-size:14px;outline:none;transition:border .2s}
  input:focus{border-color:#FF6B35}
  .btn{display:block;width:100%;margin-top:24px;background:#FF6B35;color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:700;cursor:pointer;transition:opacity .2s}
  .btn:hover{opacity:.85}
  .alert{padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:16px}
  .alert.error{background:#2d1515;border:1px solid #7f1d1d;color:#fca5a5}
  .alert.success{background:#0d2d1f;border:1px solid #166534;color:#86efac}
  .check-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #1A1A24;font-size:13px}
  .check-row:last-child{border:none}
  .ok{color:#2DD4A0;font-weight:700}
  .fail{color:#EF4444;font-weight:700}
  pre{background:#0A0A0F;border:1px solid #2a2a3a;border-radius:8px;padding:12px;font-size:11px;color:#9ca3af;overflow:auto;max-height:200px;margin-top:16px;white-space:pre-wrap}
  .done-icon{font-size:48px;text-align:center;margin-bottom:12px}
  .done-title{text-align:center;font-size:20px;font-weight:800;color:#2DD4A0;margin-bottom:8px}
  .done-sub{text-align:center;color:#6b7280;font-size:13px}
  .warn{background:#2d200a;border:1px solid #92400e;color:#fcd34d;padding:12px 14px;border-radius:10px;font-size:12px;margin-top:16px}
</style>
</head>
<body>
<div class="card">
  <h1>✂️ BERBERiN</h1>
  <p class="sub">Web Kurulum Sihirbazı</p>

  <div class="step-bar">
    <span class="<?= $step >= 1 ? 'done' : '' ?>"></span>
    <span class="<?= $step >= 2 ? 'done' : '' ?>"></span>
    <span class="<?= $step >= 3 ? 'done' : '' ?>"></span>
    <span class="<?= $step >= 4 ? 'done' : '' ?>"></span>
  </div>

  <?php if ($msgText): ?>
  <div class="alert <?= $msgType ?>">
    <?= htmlspecialchars($msgText) ?>
  </div>
  <?php endif; ?>

  <?php if ($step === 1): ?>
  <!-- ADIM 1: Gereksinimler -->
  <h2 style="font-size:15px;margin-bottom:16px">Adım 1 — Sistem Gereksinimleri</h2>
  <?php foreach ($checks as $label => $ok): ?>
  <div class="check-row">
    <span><?= $label ?></span>
    <span class="<?= $ok ? 'ok' : 'fail' ?>"><?= $ok ? '✓ OK' : '✗ EKSİK' ?></span>
  </div>
  <?php endforeach; ?>
  <?php if ($allChecksOk): ?>
  <form method="post">
    <input type="hidden" name="step" value="2">
    <button class="btn" type="submit">Devam Et →</button>
  </form>
  <?php else: ?>
  <div class="alert error" style="margin-top:16px">Eksik gereksinimler var. Hosting firmanla iletişime geç.</div>
  <?php endif; ?>

  <?php elseif ($step === 2): ?>
  <!-- ADIM 2: Veritabanı -->
  <h2 style="font-size:15px;margin-bottom:4px">Adım 2 — Veritabanı Bilgileri</h2>
  <p style="font-size:12px;color:#6b7280;margin-bottom:16px">cPanel → MySQL Databases'ten oluşturduğun bilgileri gir.</p>
  <form method="post">
    <input type="hidden" name="step" value="2">
    <label>Veritabanı Sunucusu</label>
    <input type="text" name="db_host" value="localhost" required>
    <label>Veritabanı Adı</label>
    <input type="text" name="db_name" placeholder="örn: kullanici_berberin" required>
    <label>Veritabanı Kullanıcısı</label>
    <input type="text" name="db_user" placeholder="örn: kullanici_admin" required>
    <label>Veritabanı Şifresi</label>
    <input type="password" name="db_pass" placeholder="••••••••">
    <label>Site URL (https dahil, sonda / olmadan)</label>
    <input type="text" name="app_url" placeholder="https://api.berberin.com" required>
    <button class="btn" type="submit">Bağlantıyı Test Et →</button>
  </form>

  <?php elseif ($step === 3): ?>
  <!-- ADIM 3: Kurulum -->
  <h2 style="font-size:15px;margin-bottom:8px">Adım 3 — Kurulumu Başlat</h2>
  <p style="font-size:13px;color:#6b7280;margin-bottom:20px">
    Veritabanı bağlandı. Aşağıdaki butona basınca şunlar yapılacak:<br><br>
    • APP_KEY üretilecek<br>
    • Tablolar oluşturulacak (migrate)<br>
    • Config ve route cache alınacak<br>
    • Storage klasörü linklecek
  </p>
  <form method="post">
    <input type="hidden" name="step" value="3">
    <input type="hidden" name="do_install" value="1">
    <button class="btn" type="submit">🚀 Kurulumu Başlat</button>
  </form>

  <?php elseif ($step === 4): ?>
  <!-- ADIM 4: Tamamlandı -->
  <div class="done-icon">🎉</div>
  <div class="done-title">Kurulum Tamamlandı!</div>
  <p class="done-sub">BERBERiN API başarıyla kuruldu.</p>
  <div class="warn" style="margin-top:20px">
    ⚠️ <strong>GÜVENLİK:</strong> Bu <code>install.php</code> dosyasını hemen sil!<br>
    cPanel Dosya Yöneticisi → <code>public_html/install.php</code> → Sil
  </div>
  <pre><?= file_exists(ROOT . '/storage/logs/install.log') ? htmlspecialchars(file_get_contents(ROOT . '/storage/logs/install.log')) : '' ?></pre>
  <?php endif; ?>
</div>
</body>
</html>
