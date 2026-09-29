# HADIRIN — Vercel + Supabase

Laravel 12, Blade, PHP runtime komunitas `vercel-php@0.9.0`. Paket ini menggantikan konfigurasi Docker/Render. Database dan foto tetap di Supabase. Sumber runtime: https://github.com/vercel-community/php

## Status pengujian
Struktur rute, referensi view, JSON, dan JavaScript diperiksa secara statis. PHP/Composer dan deploy Vercel belum dapat dijalankan di workspace penyusun. Build production pertama adalah verifikasi kompatibilitas runtime dan database, bukan keberhasilan yang sudah dibuktikan.

## 1. Supabase
Gunakan proyek yang sudah dibuat. Buat bucket `attendance-photos` privat melalui SQL Editor:

```sql
insert into storage.buckets (id,name,public)
values ('attendance-photos','attendance-photos',false)
on conflict (id) do update set public=false;
```

Ambil parameter dari Connect → Session pooler: host, port, user, database. DB_PASSWORD adalah password database, tidak perlu percent-encoding karena variabel terpisah. Gunakan legacy JWT service_role key untuk Storage. Jangan gunakan anon/publishable key untuk variabel SUPABASE_SERVICE_ROLE_KEY. Migrasi mengaktifkan RLS pada tabel aplikasi; akses melalui server Laravel tetap menggunakan akun PostgreSQL yang sesuai.

## 2. GitHub
Buat repository baru, misalnya `hadirin-vercel`. Unggah ISI folder ini ke root repo. Di halaman utama harus tampak `vercel.json`, `composer.json`, `artisan`, dan folder `api`, `scripts`, `app`, `public`, `bootstrap`, `config`, `database`, `resources`, `routes`, `storage`. Jangan unggah .env atau credentials. Buat file .gitkeep pada bootstrap/cache jika unggahan web mengabaikan folder kosong. Hindari memakai repo lama dengan Dockerfile atau public/index.php.

## 3. Vercel
Pilih akun personal / Hobby, Add New → Project → Import repo. Framework Preset: Other. Root Directory: root repo. Output Directory: public (sudah dalam vercel.json). Biarkan Build Command dan Install Command tanpa override; runtime PHP komunitas memasang Composer dan memanggil hook `vercel`. Gunakan Node.js 22.x pada Project Settings jika perlu.

## 4. Environment Variables
Isi semua variabel berikut pada scope PRODUCTION. Jangan beri preview deployment akses database production. Nilai contoh tanpa GANTI tidak berisi secret.

```dotenv
APP_NAME=HADIRIN
APP_ENV=production
APP_DEBUG=false
APP_KEY=GANTI_BASE64_KEY_BARU
APP_URL=https://hadirin-vercel.vercel.app
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
SESSION_SECURE_COOKIE=true
DB_CONNECTION=pgsql
DB_HOST=GANTI_HOST_SESSION_POOLER
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=GANTI_USER_SESSION_POOLER
DB_PASSWORD=GANTI_PASSWORD_DATABASE
DB_SSLMODE=require
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
SUPABASE_URL=GANTI_PROJECT_URL
SUPABASE_SERVICE_ROLE_KEY=GANTI_LEGACY_SERVICE_ROLE_KEY
SUPABASE_BUCKET=attendance-photos
ADMIN_NAME=Administrator
ADMIN_EMAIL=GANTI_EMAIL_ADMIN
ADMIN_PASSWORD=GANTI_PASSWORD_ADMIN_MIN_10_KARAKTER
```

Isi nilai ke field Value tanpa tanda kutip. Sesuaikan APP_URL setelah tahu domain sebenarnya dan Redeploy bila berubah. Jangan menetapkan VERCEL_ENV sendiri; Vercel menyediakannya. Aktifkan opsi “Automatically expose System Environment Variables” di Project Settings → Environment Variables bila dinonaktifkan. Script database hanya berjalan ketika VERCEL_ENV=production.

Buat APP_KEY dari Windows PowerShell tanpa memasang PHP:

```powershell
$bytes = New-Object byte[] 32
$rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
$rng.GetBytes($bytes)
"base64:" + [Convert]::ToBase64String($bytes)
$rng.Dispose()
```

Gunakan kunci baru jika pernah dibagikan. Setelah aplikasi digunakan, jangan mengganti APP_KEY rutin karena sesi akan invalid.

## 5. Deploy
Klik Deploy. Hook Composer `vercel` menjalankan migrasi dan seeder pada BUILD production. Build gagal jika kredensial penting kosong atau database tak terhubung. Preview tidak menjalankan migrasi. Migrasi tidak berjalan di tiap request. Paket ini tidak menyediakan endpoint publik untuk setup database.

Seeder hanya membuat admin jika email belum ada; perubahan ADMIN_PASSWORD pada environment tidak otomatis mengganti sandi akun lama.

Setelah Ready, buka /login, masuk sebagai admin, atur lokasi pada /admin/locations, buat pengguna pada /admin/users. Uji selfie/GPS pada ponsel dengan URL HTTPS. Cek riwayat, foto pemilik dan admin, filter serta CSV. Uji penolakan presensi kedua pada tanggal sama dan penolakan akses user ke admin.

## 6. Cara kerja serverless
`api/index.php` adalah satu fungsi. Kompilasi Blade dan manifest runtime dibuat pada /tmp/hadirin. Session dan cache disimpan PostgreSQL. Foto tersimpan pada bucket privat, dibaca melalui rute berotorisasi. Hanya CSS/JS berada dalam public; source PHP tidak diletakkan dalam output statis. Bootstrap mempercayai reverse proxy untuk URL HTTPS Vercel. Batas foto JPEG 3 MB untuk menyesuaikan batas request; permintaan dengan foto mendekati batas platform bisa ditolak sebelum Laravel.

## 7. Troubleshooting
- Missing production environment variable: isi variabel bernama itu dan redeploy.
- Could not find driver: runtime gagal memuat pdo_pgsql; kirim log, jangan ganti database sembarangan.
- No application encryption key: APP_KEY harus `base64:` diikuti hasil acak 32 byte.
- Relation users/sessions does not exist: cek hook vercel pada Build Logs dan VERCEL_ENV production.
- Foto gagal: periksa bucket privat, URL, service_role key.
- Halaman login 500: periksa Runtime Logs. Jangan set APP_DEBUG=true pada URL publik.
- CSS/JS 404: pastikan outputDirectory=public dan style.css/attendance.js ikut diunggah.

## Kemampuan dan batasan
Presensi satu kali sehari, status tepat waktu/terlambat/di luar area, kebijakan lokasi, login, manajemen pengguna, riwayat, foto privat, dan CSV. Selfie adalah bukti foto; tidak ada face recognition/liveness. Koordinat browser dapat dimanipulasi. Tidak ada reset email, presensi pulang, atau grafik historis. Runtime PHP merupakan proyek komunitas, sehingga dukungannya berbeda dari runtime resmi Vercel.
