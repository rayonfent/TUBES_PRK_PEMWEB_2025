# Daftar anggota
1. Rayhan Danar Abiyyuendra	(2315061098)
2. Tri Septiani	(2315061036)
3. Muhammad Aqsha Fadilah Jailani	(2315061127)
4. Raed Basheer Abbas Hasan Al-Zuraiqi (2315061133)
  
# Judul & summary project
## Astral Psychologist - Website Konsultasi Mental Health dengan Fitur Chat Real-Time Konselor

Platform konsultasi kesehatan mental yang dirancang untuk memberikan akses kepada pengguna untuk berkonsultasi dengan konselor profesional melalui sistem chat real-time. Sistem ini mencocokkan pengguna dengan konselor berdasarkan jawaban survei dan penilaian kepribadian untuk memastikan gaya komunikasi yang paling sesuai. Fitur utama dari proyek ini mencakup registrasi pengguna, login, pencocokan konselor, pesan real-time, dan pengelolaan langganan.

## Fitur Utama 
### 1. Registrasi Pengguna dan Login:
- Pengguna dapat mendaftar dengan mengisi formulir berisi nama, email, dan kata sandi.
- Setelah registrasi berhasil, pengguna akan diarahkan untuk mengisi survei preferensi.


# Cara menjalankan aplikasi


# Algoritma Aplikasi

# Algoritma Sistem Astral Psychologist
## 1. Algoritma Registrasi Pengguna
1.	Pengguna mengisi formulir registrasi berisi nama, email, dan kata sandi.
2.	Sistem memvalidasi kelengkapan data.
3.	Sistem melakukan pengecekan apakah alamat email sudah terdaftar di basis data.
4.	Jika email belum terdaftar:
    - Sistem melakukan hashing terhadap kata sandi menggunakan password_hash().
    - Sistem menyimpan data pengguna ke tabel users.
    - Jika pengguna mengunggah foto profil:
-	Sistem memindahkan file ke direktori penyimpanan.
-	Sistem menyimpan nama file foto ke basis data.
5.	Jika email sudah terdaftar, sistem mengembalikan pesan kesalahan.
6.	Setelah registrasi berhasil, sistem mengarahkan pengguna ke halaman survei preferensi.
## 2. Algoritma Login Pengguna
1.	Pengguna memasukkan email dan kata sandi.
2.	Sistem mengambil data pengguna berdasarkan email.
3.	Jika email ditemukan:
-	Sistem memverifikasi kata sandi dengan password_verify().
-	Jika valid: sistem membuat sesi dan mengarahkan pengguna ke dashboard.
4.	Jika email tidak ditemukan atau kata sandi salah, sistem menampilkan pesan kesalahan.
## 3. Algoritma Survei Preferensi
1.	Survei terdiri dari empat pertanyaan dengan dua pilihan (nilai 1 atau 2).
2.	Langkah proses:
-	Sistem menerima nilai q1, q2, q3, q4.
-	Sistem memvalidasi seluruh pertanyaan telah dijawab.
-	Sistem menyimpan jawaban survei ke tabel user_survey berserta timestamp.
-	Sistem mengarahkan pengguna ke halaman hasil kecocokan.
## 4. Algoritma Penentuan Tipe Komunikasi Pengguna
### 4.1 Penentuan Gaya Komunikasi (Direct vs Gentle)
-	Variabel: q1, q2, q3.
-	Nilai 1 = Direct
-	Nilai 2 = Gentle

Rumus:
-	direct_score = jumlah(q1, q2, q3 == 1)
-	gentle_score = jumlah(q1, q2, q3 == 2)

Klasifikasi:
-	direct_score ≥ 2 → Direct Communicator
-	gentle_score ≥ 2 → Empathic Communicator
-	Jika seimbang → Balanced Communicator
  
### 4.2 Penentuan Tipe Emosional (Logical vs Emotional)
-	Berdasarkan q4:
    -	q4 = 1 → Logical Thinker
    -	q4 = 2 → Emotional Feeler
      
### 4.3 Penentuan Tipe Kepribadian Final
-	Direct + Logical → Analytical Realist
-	Direct + Emotional → Straightforward Feeler
-	Gentle + Logical → Calm Rationalist
-	Gentle + Emotional → Empathic Listener
-	Jika tidak dominan → Adaptive Communicator
## 5. Algoritma Matching Konselor
### 5.1 Atribut Konselor
-	communication_style
    -	1 = direct
    -	2 = gentle
-	approach_style
    -	1 = logical
    -	2 = emotional
### 5.2 Perhitungan Skor Kecocokan
Rumus:
- score = 0
- Jika q1 == communication_style → score + 1
- Jika q2 == communication_style → score + 1
- Jika q3 == approach_style      → score + 1
- Jika q4 == approach_style      → score + 1
### 5.3 Sorting dan Rekomendasi
-	Sistem menghitung skor seluruh konselor.
-	Sistem mengurutkan dari skor tertinggi.
-	Sistem menampilkan 1–3 konselor terbaik.
## 6. Algoritma Dashboard Pengguna
-	Mengambil profil pengguna dari tabel users.
-	Mengambil survei terakhir dari user_survey.
-	Menampilkan ringkasan:
    -	Gaya komunikasi
    -	Pendekatan emosional
    -	Grafik horizontal kecenderungan
-	Mengambil riwayat sesi (sessions).
-	Mengambil status pembayaran (payments).
-	Menampilkan tombol aksi:
    -	Melanjutkan sesi aktif
    -	Mengisi survei ulang
    -	Mencari konselor
    -	Mengelola pembayaran
## 7. Algoritma Sistem Chat (High-Level)
-	Saat pengguna memilih konselor, sistem membuat atau melanjutkan sesi (sessions).
-	Pesan disimpan ke tabel messages:
    -	session_id
    -	sender_type
    -	message_text
    -	timestamp
•	Sistem menampilkan pesan secara live via:
    -	Polling interval, atau
    -	WebSocket (opsional)
•	Sistem menandai pesan sebagai seen ketika chat dibuka.
## 8. Algoritma Sistem Trial dan Langganan
•	Pengguna mendapatkan free trial 1 hari saat memulai sesi pertama.
•	Setelah trial berakhir, pengguna harus berlangganan.
### Langkah-langkah:
1.	Saat sesi pertama dimulai:
    -	Sistem membuat data pembayaran dengan status trial dan expires_at = now + 1 day.
2.	Saat pengguna membuka halaman chat:
    -	Sistem memeriksa status trial/berlangganan.
3.	Jika trial habis:
    -	Pengguna diarahkan ke halaman pembayaran.

