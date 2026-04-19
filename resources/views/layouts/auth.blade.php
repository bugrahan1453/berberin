<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Giriş') — BERBERiN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#0A0A0F; min-height:100vh; display:flex; align-items:center; justify-content:center;">

<div style="width:100%; max-width:420px; padding:1.5rem;">
    <!-- Logo -->
    <div style="text-align:center; margin-bottom:2rem;">
        <div style="font-family:'Outfit',sans-serif; font-size:2rem; font-weight:700; color:#FF6B35;">✂ BERBERiN</div>
        <div style="font-size:0.8rem; color:#8B8B9E; margin-top:4px;">Dükkan Yönetim Paneli</div>
    </div>

    <div class="card">
        @yield('content')
    </div>
</div>

</body>
</html>
