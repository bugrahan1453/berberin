<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\NotificationSetting;
use App\Models\QueueStatus;
use App\Models\Service;
use App\Models\Shop;
use App\Models\ShopGallery;
use App\Models\ShopSetting;
use App\Models\Staff;
use App\Models\StaffShift;
use App\Models\Review;
use App\Services\QrService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PanelController extends Controller
{
    private function shop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    // ─── Randevular ───────────────────────────────────────────────
    public function appointments(Request $request)
    {
        $shop = $this->shop();
        $date = $request->date ?? Carbon::today()->toDateString();
        $status = $request->status;

        $query = Appointment::with(['user:id,name,phone', 'service:id,name,price', 'staff:id,name', 'seat:id,name'])
            ->where('shop_id', $shop->id)
            ->where('date', $date);

        if ($status) $query->where('status', $status);

        $appointments = $query->orderBy('time')->get();
        $staff = $shop->staff()->where('is_active', true)->get(['id', 'name']);
        $services = $shop->services()->where('is_active', true)->get(['id', 'name', 'price', 'duration_min']);
        $seats = $shop->seats()->get(['id', 'name']);

        return view('panel.appointments.index', compact('appointments', 'date', 'status', 'staff', 'services', 'seats', 'shop'));
    }

    // ─── Koltuklar ────────────────────────────────────────────────
    public function seats()
    {
        $shop = $this->shop();
        $seats = $shop->seats()->with(['assignedStaff:id,name', 'currentAppointment.user:id,name'])->get();
        $staff = $shop->staff()->where('is_active', true)->get(['id', 'name']);

        return view('panel.seats.index', compact('seats', 'staff', 'shop'));
    }

    public function storeSeat(Request $request)
    {
        $shop = $this->shop();
        $shop->seats()->create([
            'name' => $request->name,
            'assigned_staff_id' => $request->assigned_staff_id ?: null,
            'is_vip' => $request->boolean('is_vip'),
        ]);
        $shop->increment('total_seats');
        return back()->with('success', 'Koltuk eklendi.');
    }

    public function updateSeatStatus(Request $request, $id)
    {
        $shop = $this->shop();
        $seat = $shop->seats()->findOrFail($id);
        $data = ['status' => $request->status];
        if ($request->status === 'empty') { $data['current_appointment_id'] = null; $data['busy_since'] = null; }
        if ($request->status === 'busy') $data['busy_since'] = now();
        $seat->update($data);
        return back()->with('success', 'Koltuk durumu güncellendi.');
    }

    public function toggleShopFull()
    {
        $shop = $this->shop();
        $shop->update(['is_full' => !$shop->is_full]);
        return back()->with('success', $shop->is_full ? 'Dükkan dolu olarak işaretlendi.' : 'Dükkan müsait olarak işaretlendi.');
    }

    // ─── Personel ─────────────────────────────────────────────────
    public function staff()
    {
        $shop = $this->shop();
        $staff = $shop->staff()->get();

        return view('panel.staff.index', compact('staff', 'shop'));
    }

    public function staffShifts(Request $request, $id)
    {
        $shop = $this->shop();
        $staffMember = $shop->staff()->findOrFail($id);
        $weekStart = Carbon::parse($request->week_start ?? Carbon::now()->startOfWeek())->startOfWeek();
        $shifts = StaffShift::where('staff_id', $staffMember->id)
            ->whereBetween('date', [$weekStart, $weekStart->copy()->addDays(6)])
            ->get()->keyBy(fn($s) => $s->date->format('Y-m-d'));

        return view('panel.staff.shifts', compact('staffMember', 'shifts', 'weekStart', 'shop'));
    }

    // ─── Hizmetler ────────────────────────────────────────────────
    public function services()
    {
        $shop = $this->shop();
        $services = $shop->services()->orderBy('sort_order')->get();

        return view('panel.services.index', compact('services', 'shop'));
    }

    // ─── Galeri ───────────────────────────────────────────────────
    public function gallery()
    {
        $shop = $this->shop();
        $gallery = $shop->gallery()->orderBy('sort_order')->get();

        return view('panel.gallery.index', compact('gallery', 'shop'));
    }

    // ─── Yorumlar ─────────────────────────────────────────────────
    public function reviews()
    {
        $shop = $this->shop();
        $reviews = Review::with('user:id,name,avatar')
            ->where('shop_id', $shop->id)
            ->latest()
            ->paginate(20);

        return view('panel.reviews.index', compact('reviews', 'shop'));
    }

    // ─── Raporlar ─────────────────────────────────────────────────
    public function reports(Request $request)
    {
        $shop = $this->shop();

        if ($shop->subscription_plan === 'starter') {
            return view('panel.reports.locked', compact('shop'));
        }

        $period = $request->period ?? 'weekly';
        [$startDate, $endDate] = match ($period) {
            'daily' => [Carbon::today(), Carbon::today()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
        };

        $appointments = Appointment::where('shop_id', $shop->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $totalRevenue = $appointments->where('status', 'completed')->sum('price');
        $completedCount = $appointments->where('status', 'completed')->count();
        $noShowCount = $appointments->where('status', 'no_show')->count();
        $totalCount = $appointments->count();
        $noShowRate = $totalCount > 0 ? round($noShowCount / $totalCount * 100, 1) : 0;

        return view('panel.reports.index', compact('shop', 'period', 'totalRevenue', 'completedCount', 'noShowCount', 'totalCount', 'noShowRate'));
    }

    // ─── Ayarlar ──────────────────────────────────────────────────
    public function settings()
    {
        $shop = $this->shop()->load(['settings', 'notificationSettings', 'hours']);
        $settings = $shop->settings ?? ShopSetting::create(['shop_id' => $shop->id]);
        $notifSettings = $shop->notificationSettings ?? NotificationSetting::create(['shop_id' => $shop->id]);

        return view('panel.settings.index', compact('shop', 'settings', 'notifSettings'));
    }

    public function settingsUpdate(Request $request)
    {
        $shop = $this->shop();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender_filter' => ['required', 'in:male,female,both'],
            'slot_interval' => ['integer', 'in:15,30,45,60'],
            'min_advance_hours' => ['integer', 'min:0'],
            'max_advance_days' => ['integer', 'min:1', 'max:90'],
            'cancel_hours' => ['integer', 'min:0'],
            'auto_approve' => ['boolean'],
            'walkin_enabled' => ['boolean'],
            'deposit_required' => ['boolean'],
            'deposit_amount' => ['nullable', 'numeric'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
        }

        $shop->update($request->only(['name', 'address', 'city', 'district', 'phone', 'email', 'instagram', 'tiktok', 'gender_filter']));

        // Slug güncelle (ad değişirse)
        if ($shop->wasChanged('name')) {
            $shop->update(['slug' => Str::slug($request->name) . '-' . rand(1000, 9999)]);
        }

        ShopSetting::updateOrCreate(['shop_id' => $shop->id], $request->only([
            'slot_interval', 'min_advance_hours', 'max_advance_days',
            'cancel_hours', 'walkin_enabled', 'deposit_required', 'deposit_amount',
        ]) + [
            'auto_approve' => $request->boolean('auto_approve'),
            'walkin_enabled' => $request->boolean('walkin_enabled'),
            'deposit_required' => $request->boolean('deposit_required'),
        ]);

        // Çalışma saatleri kaydet
        if ($request->has('hours')) {
            foreach ($request->hours as $day => $hour) {
                \App\Models\ShopHour::updateOrCreate(
                    ['shop_id' => $shop->id, 'day_of_week' => $day],
                    [
                        'is_closed' => isset($hour['is_closed']),
                        'open_time' => $hour['open_time'] ?? '09:00',
                        'close_time' => $hour['close_time'] ?? '18:00',
                        'break_start' => $hour['break_start'] ?? null,
                        'break_end' => $hour['break_end'] ?? null,
                    ]
                );
            }
        }

        return back()->with('success', 'Ayarlar kaydedildi.');
    }

    public function notificationSettingsUpdate(Request $request)
    {
        $shop = $this->shop();

        NotificationSetting::updateOrCreate(['shop_id' => $shop->id], [
            'morning_summary_enabled' => $request->boolean('morning_summary_enabled'),
            'morning_summary_time' => $request->morning_summary_time ?? '08:00',
            'evening_summary_enabled' => $request->boolean('evening_summary_enabled'),
            'weekly_report_enabled' => $request->boolean('weekly_report_enabled'),
            'sms_enabled' => $request->boolean('sms_enabled'),
            'reminder_hours' => $request->reminder_hours ?? 2,
            'no_show_auto_mark_minutes' => $request->no_show_auto_mark_minutes ?? 30,
            'review_notification_enabled' => $request->boolean('review_notification_enabled'),
            'campaign_notification_enabled' => $request->boolean('campaign_notification_enabled'),
        ]);

        return back()->with('success', 'Bildirim ayarları kaydedildi.');
    }

    // ─── Randevu oluştur (web form) ───────────────────────────────
    public function storeAppointment(Request $request)
    {
        $shop = $this->shop();
        $validator = Validator::make($request->all(), [
            'service_id' => ['required', 'exists:services,id'],
            'date' => ['required', 'date'],
            'time' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $service = Service::findOrFail($request->service_id);
        $endTime = date('H:i', strtotime($request->time) + ($service->duration_min * 60));

        Appointment::create([
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'staff_id' => $request->staff_id ?: null,
            'seat_id' => $request->seat_id ?: null,
            'date' => $request->date,
            'time' => $request->time,
            'end_time' => $endTime,
            'price' => $service->price,
            'source' => $request->source ?? 'walkin',
            'status' => 'confirmed',
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'confirmed_at' => now(),
        ]);

        return redirect()->route('panel.appointments', ['date' => $request->date])->with('success', 'Randevu oluşturuldu.');
    }

    // ─── Randevu durum güncelle (web form) ────────────────────────
    public function updateAppointmentStatus(Request $request)
    {
        $shop = $this->shop();
        $appt = Appointment::where('shop_id', $shop->id)->findOrFail($request->appointment_id);

        $data = ['status' => $request->new_status];
        if ($request->new_status === 'completed') $data['completed_at'] = now();
        if ($request->new_status === 'cancelled') { $data['cancelled_at'] = now(); $data['cancelled_by'] = 'shop'; }
        if ($request->new_status === 'in_progress') $data['started_at'] = now();

        $appt->update($data);

        return back()->with('success', 'Randevu güncellendi.');
    }

    // ─── QR Kod ───────────────────────────────────────────────────
    public function qrCode()
    {
        $shop = $this->shop();
        $qrUrl = app(QrService::class)->generate($shop);
        $shopUrl = url('/s/' . $shop->slug);

        return view('panel.settings.qr', compact('shop', 'qrUrl', 'shopUrl'));
    }

    // ─── Personel CRUD ────────────────────────────────────────────
    public function storeStaff(Request $request)
    {
        $shop = $this->shop();
        $shop->staff()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => $request->role ?? 'staff',
            'commission_rate' => $request->commission_rate ?? 0,
            'specialties' => $request->specialties ? explode(',', $request->specialties) : [],
            'is_active' => true,
        ]);
        return back()->with('success', 'Personel eklendi.');
    }

    public function toggleStaff($id)
    {
        $shop = $this->shop();
        $staffMember = $shop->staff()->findOrFail($id);
        $staffMember->update(['is_active' => !$staffMember->is_active]);
        return back()->with('success', 'Personel durumu güncellendi.');
    }

    public function deleteStaff($id)
    {
        $shop = $this->shop();
        $shop->staff()->findOrFail($id)->delete();
        return back()->with('success', 'Personel silindi.');
    }

    public function saveStaffShifts(Request $request, $id)
    {
        $shop = $this->shop();
        $staffMember = $shop->staff()->findOrFail($id);

        foreach ($request->shifts ?? [] as $date => $shift) {
            StaffShift::updateOrCreate(
                ['staff_id' => $staffMember->id, 'date' => $date],
                [
                    'start_time' => $shift['start_time'] ?? '09:00',
                    'end_time' => $shift['end_time'] ?? '18:00',
                    'break_start' => $shift['break_start'] ?? null,
                    'break_end' => $shift['break_end'] ?? null,
                    'is_off' => isset($shift['is_off']),
                ]
            );
        }
        return back()->with('success', 'Vardiyalar kaydedildi.');
    }

    // ─── Hizmet CRUD ──────────────────────────────────────────────
    public function storeService(Request $request)
    {
        $shop = $this->shop();
        $shop->services()->create([
            'name' => $request->name,
            'price' => $request->price,
            'duration_min' => $request->duration_min ?? 30,
            'category' => $request->category,
            'gender' => $request->gender ?? 'both',
            'is_active' => true,
        ]);
        return back()->with('success', 'Hizmet eklendi.');
    }

    public function updateService(Request $request, $id)
    {
        $shop = $this->shop();
        $service = $shop->services()->findOrFail($id);
        $service->update($request->only(['name', 'price', 'duration_min', 'category', 'gender']));
        return back()->with('success', 'Hizmet güncellendi.');
    }

    public function toggleService($id)
    {
        $shop = $this->shop();
        $service = $shop->services()->findOrFail($id);
        $service->update(['is_active' => !$service->is_active]);
        return back()->with('success', 'Hizmet durumu güncellendi.');
    }

    public function deleteService($id)
    {
        $shop = $this->shop();
        $shop->services()->findOrFail($id)->delete();
        return back()->with('success', 'Hizmet silindi.');
    }

    // ─── Galeri CRUD ──────────────────────────────────────────────
    public function storeGallery(Request $request)
    {
        $shop = $this->shop();
        $shop->gallery()->create([
            'image_url' => $request->image_url,
            'caption' => $request->caption,
        ]);
        return back()->with('success', 'Fotoğraf eklendi.');
    }

    public function deleteGallery($id)
    {
        $shop = $this->shop();
        $shop->gallery()->findOrFail($id)->delete();
        return back()->with('success', 'Fotoğraf silindi.');
    }

    // ─── Yorum yanıtla ────────────────────────────────────────────
    public function replyReview(Request $request, $id)
    {
        $shop = $this->shop();
        $review = Review::where('shop_id', $shop->id)->findOrFail($id);
        $review->update(['reply' => $request->reply, 'replied_at' => now()]);
        return back()->with('success', 'Yanıt kaydedildi.');
    }
}
