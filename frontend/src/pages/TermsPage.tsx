import { Link } from 'react-router-dom'

export function TermsPage() {
  return (
    <article className="mx-auto max-w-2xl pt-4">
      <Link to="/profile" className="text-sm font-semibold text-primary">
        ← Kembali
      </Link>
      <h1 className="mt-3 text-2xl font-extrabold tracking-tight">Ketentuan Layanan</h1>
      <p className="mt-1 text-sm text-on-surface-variant">Terakhir diperbarui: September 2026</p>

      <div className="mt-5 grid gap-5 text-sm leading-relaxed">
        <section>
          <h2 className="font-bold">1. Layanan</h2>
          <p className="mt-1 text-on-surface-variant">
            CekNO adalah platform identifikasi & reputasi nomor telepon berbasis komunitas. Skor risiko bersifat indikatif,
            bukan keputusan hukum resmi.
          </p>
        </section>
        <section>
          <h2 className="font-bold">2. Kontribusi Pengguna</h2>
          <p className="mt-1 text-on-surface-variant">
            Setiap laporan, ulasan, label, dan kontribusi kontak melalui moderasi sebelum tampil publik. Konten yang
            melanggar (pencemaran, spam, data pribadi) dapat ditolak dan akun dapat ditangguhkan.
          </p>
        </section>
        <section>
          <h2 className="font-bold">3. Perilaku yang Dilarang</h2>
          <p className="mt-1 text-on-surface-variant">
            Dilarang menyebarkan kebencian, mengunggah data pribadi orang lain, menyalahgunakan otomasi berlebih, atau
            memanipulasi skor nomor.
          </p>
        </section>
        <section>
          <h2 className="font-bold">4. Batasan Tanggung Jawab</h2>
          <p className="mt-1 text-on-surface-variant">
            Kami tidak menjamin keakuratan mutlak data komunitas. Penggunaan informasi di luar tanggung jawab kami.
          </p>
        </section>
        <section>
          <h2 className="font-bold">5. Perubahan Ketentuan</h2>
          <p className="mt-1 text-on-surface-variant">Ketentuan dapat diperbarui; perubahan besar akan diumumkan melalui pembaruan versi persetujuan.</p>
        </section>
      </div>
    </article>
  )
}