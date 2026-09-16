import { Link } from 'react-router-dom'

export function PrivacyPage() {
  return (
    <article className="mx-auto max-w-2xl pt-4">
      <Link to="/profile" className="text-sm font-semibold text-primary">
        ← Kembali
      </Link>
      <h1 className="mt-3 text-2xl font-extrabold tracking-tight">Kebijakan Privasi</h1>
      <p className="mt-1 text-sm text-on-surface-variant">Terakhir diperbarui: September 2026</p>

      <div className="mt-5 grid gap-5 text-sm leading-relaxed">
        <section>
          <h2 className="font-bold">1. Data yang Dikumpulkan</h2>
          <p className="mt-1 text-on-surface-variant">
            Kami mengumpulkan data akun (nama, email, telepon opsional), kontribusi label kontak (nomor & label), laporan,
            ulasan, dan log penelusuran tanpa identitas (guest).
          </p>
        </section>
        <section>
          <h2 className="font-bold">2. Penggunaan Data</h2>
          <p className="mt-1 text-on-surface-variant">
            Data digunakan untuk menghitung skor reputasi nomor (XGBoost, IndoBERT, komunitas, aturan) dan menampilkan
            informasi publik nomor. Kontribusi kontak tidak menampilkan identitas pengirimnya kepada publik.
          </p>
        </section>
        <section>
          <h2 className="font-bold">3. Persetujuan (Consent)</h2>
          <p className="mt-1 text-on-surface-variant">
            Kontribusi label buku kontak hanya dilakukan setelah Anda menyetujui versi persetujuan (consent version) saat
            ini. Anda dapat menarik kembali (withdraw) kontribusi kapan saja dari halaman profil.
          </p>
        </section>
        <section>
          <h2 className="font-bold">4. Penyimpanan & Keamanan</h2>
          <p className="mt-1 text-on-surface-variant">
            Data disimpan secara terenkripsi, akses dibatasi, dan kata sandi di-hash. Riwayat dan laporan ditinjau oleh
            moderator untuk menjaga kualitas komunitas.
          </p>
        </section>
        <section>
          <h2 className="font-bold">5. Hak Anda</h2>
          <p className="mt-1 text-on-surface-variant">
            Anda dapat mengakses, memperbarui, atau menghapus data akun serta menarik persetujuan kapan saja.
          </p>
        </section>
      </div>
    </article>
  )
}