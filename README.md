# آی‌کارز (iBatri)

Persian RTL car-battery marketplace app + VIN body report.

- `app-dark.html` — main app (v۱.۱۴.۱۲۹)
- `icon.png` + `manifest.json` — PWA assets
- `api.php` + `schema.sql` + `admin/` — PHP/MySQL backend (Vin reports: `?action=vin` / `?action=vinAdd`)
- `ibatri-v1.14.xxx.apk` — latest signed Android build only

گزارش VIN در اپلیکیشن هیچ داده‌ای نمی‌سازد؛ فقط از جدول `vin_reports`
(ثبت کاربران سایت/ابزار تشخیص رنگ) خوانده می‌شود.
