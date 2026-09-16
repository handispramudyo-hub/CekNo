# Kebijakan Privasi

Berlaku untuk platform CekNO (NUMTAG) — aplikasi mobile, aplikasi web, dan layanan terkait. Dokumen ini menjelaskan data apa yang kami proses, bagaimana kami memprosesnya, dan hak Anda.

## 1. Ringkasan

CekNO adalah platform identifikasi & reputasi nomor telepon berbasis kontribusi komunitas. Kami menganut prinsip **data minimization**: hanya data yang benar-benar diperlukan yang diproses, dengan persetujuan Anda, dan tanpa akses ke isi komunikasi pribadi.

## 2. Data yang Kami Proses

### Wajib (akun)
- Nama, email, dan kata sandi (dienkripsi/hash) untuk membuat dan mengamankan akun.
- Nomor telepon Anda (opsional) sebagai identitas akun.

### Kontribusi yang Anda berikan (opsional, atas izin Anda)
- **Nametag / label** untuk nomor telepon yang Anda kontribusikan, beserta kategori (personal, bisnis, sales, layanan, spam, penipuan, lainnya).
- Laporan, ulasan/review, dan usulan label untuk nomor tertentu.
- Menghubungkan data tersebut ke nomor telepon yang dinormalisasi (kanonik `+62…`).

### Data dari kontak di perangkat Anda (HANYA dengan izin eksplisit)
- Bila Anda mengizinkan akses kontak pada aplikasi mobile, kami membaca nomor telepon dan **nama yang Anda simpan** untuk dijadikan usulan nametag. Ini bersifat **otomatis**, sesuai pilihan Anda.
- Data tersebut dikirim sebagai **usulan kontribusi berstatus pending** dan melalui proses moderasi sebelum dapat terlihat oleh publik.
- Kami **tidak pernah** mengakses:
  - isi SMS,
  - isi WhatsApp/pesan seluler,
  - isi panggilan telepon,
  - foto kontak,
  - alamat kontak,
  - email kontak bila tidak diperlukan.

## 3. Consent & Versi Consent

- Sebelum akses kontak/pengiriman kontribusi, Anda menyetujui kebijakan ini secara eksplisit.
- Setiap persetujuan dicatat: versi kebijakan (`consent_version`), waktu, beserta metadata (IP, user agent) di tabel `consents`.
- Setiap kontribusi membawa `consent_version` yang berlaku saat itu.
- Anda boleh **menarik (withdraw)** kontribusi kapan saja — kontribusi berubah menjadi `withdrawn` dan tidak lagi tampil.

## 4. Status Kontribusi & Cara Data Tampil

- Kontribusi selalu dimulai sebagai `pending` dan **dimoderasi** sebelum tampil publik.
- Di publik, nametag tampil sebagai agregat: **"label — N kontribusi komunitas"**. Label adalah opini kontributor, **bukan identitas resmi pemilik nomor**.
- Kami tidak menampilkan identitas legal pemilik nomor.

## 5. Data ML (Riset/Pengembangan)

- Model machine learning dilatih pada **data sintetis** untuk pengembangan/testing, diberi tanda jelas **SYNTHETIC — BUKAN DATA PRODUKSI**, kecuali dinyatakan lain.
- Prediksi risiko adalah estimasi berbasis data komunitas, bukan pernyataan faktual.

## 6. Penyimpanan & Keamanan

- Data disimpan di server (MySQL) dengan akses terbatas; kata sandi di-hash; token autentikasi disimpan dengan aman (Sanctum).
- Komunikasi dienkripsi (HTTPS) pada produksi.
- Akses kontak Anda tidak disimpan secara mentah; hanya hasil kontribusi yang dinormalisasi yang diproses.

## 7. Hak Anda

- **Opt-out / Hapus akun**: Anda dapat menonaktifkan atau menghapus akun; kontribusi Anda menarik/dihapus sesuai kebijakan.
- **Tarik kontribusi**: hapus kontribusi individual (status `withdrawn`).
- **Akses**: meminta salinan data pribadi Anda.
- **Koreksi/Pembatasan**: perbaiki data akun (nama, email, telepon).

## 8. Retensi

- Kontribusi yang `pending` yang tidak dimoderasi dapat dihapus setelah jangka waktu tertentu.
- Log audit disimpan untuk kepatuhan & keamanan.

## 9. Kontak

Untuk pertanyaan privasi, hubungi admin platform melalui email yang tertera pada aplikasi.

*Dokumen ini adalah v1. Versi baru akan diperbarui di halaman `/privacy`.*