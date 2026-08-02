<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $faqs = [
      [
        "question" => "Kapan periode pendaftaran murid baru dibuka?",
        "answer" =>
          "Periode pendaftaran murid baru biasanya dibuka pada bulan Januari hingga Juni setiap tahun ajaran. Informasi detail mengenai jadwal pendaftaran akan diumumkan melalui website dan media sosial resmi sekolah.",
        "order" => 1,
      ],
      [
        "question" => "Apa saja persyaratan dokumen yang harus disiapkan?",
        "answer" =>
          "Dokumen yang diperlukan meliputi: akta kelahiran, kartu keluarga, rapor terakhir (untuk siswa pindahan), pas foto terbaru ukuran 3x4, serta fotokopi KTP orang tua/wali. Dokumen lengkap dapat diunggah melalui portal SPMB setelah registrasi.",
        "order" => 2,
      ],
      [
        "question" => "Bagaimana proses seleksi calon murid baru?",
        "answer" =>
          "Proses seleksi meliputi tes akademik dasar, wawancara siswa, dan wawancara orang tua/wali. Tes dirancang untuk mengenal potensi dan kesiapan belajar calon siswa, bukan untuk menyaring secara ketat.",
        "order" => 3,
      ],
      [
        "question" => "Apakah ada program beasiswa yang tersedia?",
        "answer" =>
          "Ya, Stella Gracia School menyediakan beberapa jenis beasiswa bagi siswa berprestasi, baik di bidang akademik maupun non-akademik. Informasi lebih lanjut mengenai beasiswa dapat ditanyakan langsung kepada bagian administrasi sekolah.",
        "order" => 4,
      ],
      [
        "question" => "Berapa biaya pendaftaran dan uang sekolah?",
        "answer" =>
          "Informasi biaya pendaftaran dan uang sekolah dapat diperoleh dengan menghubungi bagian administrasi sekolah melalui email info@stellagracia.sch.id atau datang langsung ke sekretariat sekolah pada jam kerja.",
        "order" => 5,
      ],
      [
        "question" => "Bagaimana cara memantau status pendaftaran?",
        "answer" =>
          "Setelah melakukan registrasi, Anda dapat memantau status pendaftaran secara real-time melalui portal SPMB dengan login menggunakan akun yang telah didaftarkan. Notifikasi juga akan dikirimkan melalui email.",
        "order" => 6,
      ],
    ];

    foreach ($faqs as $faq) {
      \App\Models\Faq::create($faq);
    }
  }
}
