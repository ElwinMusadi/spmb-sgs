<x-layouts::landing>
    <x-slot:title>Penerimaan Murid Baru</x-slot:title>
    <x-slot:metaDescription>Portal Penerimaan Murid Baru Stella Gracia School Kupang - Daftarkan putra-putri Anda untuk
        meraih masa depan cerah bersama pendidikan berkualitas.</x-slot:metaDescription>

    {{-- ============================================ --}}
    {{-- HERO SECTION --}}
    {{-- ============================================ --}}
    <section id="beranda" class="relative min-h-[600px] lg:min-h-[700px] flex items-center overflow-hidden">
        {{-- Background Image --}}
        <div class="absolute inset-0">
            <img src="{{ asset('images/gedung-stella-gracia.jpg') }}" alt="Gedung Stella Gracia School Kupang"
                class="w-full h-full object-cover">
            {{-- Gradient Overlay --}}
            <div class="absolute inset-0 bg-linear-to-r from-navy-dark/95 via-navy/85 to-navy/60"></div>
        </div>

        {{-- Hero Content --}}
        <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-10 py-20 lg:py-28">
            <div class="max-w-7xl">
                {{-- Badge --}}
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gold/20 border border-gold/30 mb-6">
                    <span class="w-2 h-2 rounded-full bg-gold animate-pulse"></span>
                    <span class="font-body text-xs font-semibold text-gold-light tracking-wide uppercase">Pendaftaran
                        Dibuka</span>
                </div>

                <h1 class="font-heading text-4xl lg:text-5xl xl:text-6xl font-bold text-white leading-tight mb-6">
                    Raih Masa Depan
                    <span class="text-gold-light">Cerah</span>
                    Bersama <span class="text-gold-light">Stella Gracia School Kupang</span>
                </h1>

                <p class="font-body text-lg text-white leading-relaxed mb-8 max-w-lg">
                    Bergabunglah dengan keluarga besar Stella Gracia School Kupang. Kami membentuk generasi berkarakter,
                    berprestasi, dan siap menghadapi tantangan masa depan.
                </p>

                <div class="flex flex-wrap items-center gap-4">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 px-8 py-3.5 rounded-lg bg-gold text-white font-body text-base font-semibold hover:bg-gold-dark hover:text-gold-light shadow-lg hover:shadow-xl transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Daftar Sekarang
                        </a>
                    @endif
                    <a href="#alur-pendaftaran"
                        class="inline-flex items-center gap-2 px-8 py-3.5 rounded-lg border border-white/30 text-white font-body text-base font-medium hover:bg-white/10 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Pelajari Alur
                    </a>
                </div>

                {{-- Stats Row --}}
                <div class="flex items-center gap-8 mt-12 pt-8 border-t border-white/10">
                    <div>
                        <p class="font-heading text-3xl font-bold text-white">25+</p>
                        <p class="font-body text-sm text-royal-dim/70">Tahun Pengalaman</p>
                    </div>
                    <div class="w-px h-10 bg-white/20"></div>
                    <div>
                        <p class="font-heading text-3xl font-bold text-white">500+</p>
                        <p class="font-body text-sm text-royal-dim/70">Siswa Aktif</p>
                    </div>
                    <div class="w-px h-10 bg-white/20"></div>
                    <div>
                        <p class="font-heading text-3xl font-bold text-white">98%</p>
                        <p class="font-body text-sm text-royal-dim/70">Tingkat Kelulusan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- KEUNGGULAN / FEATURES SECTION --}}
    {{-- ============================================ --}}
    <section id="keunggulan" class="py-20 lg:py-28 bg-surface">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-10">
            {{-- Section Header --}}
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span
                    class="inline-block font-body text-sm font-semibold text-royal uppercase tracking-wider mb-3">Keunggulan
                    Kami</span>
                <h2 class="font-heading text-3xl lg:text-4xl font-bold text-navy-dark mb-4">Mengapa Memilih Stella
                    Gracia?</h2>
                <p class="font-body text-base text-on-surface-variant leading-relaxed">
                    Kami berkomitmen memberikan pendidikan berkualitas yang memadukan keunggulan akademik, pembentukan
                    karakter, dan pengembangan bakat secara holistik.
                </p>
            </div>

            {{-- Feature Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-landing.stat-card title="Kurikulum Unggulan"
                    description="Kurikulum nasional yang diperkaya dengan program internasional, mengembangkan kemampuan berpikir kritis dan kreatif siswa.">
                    <svg class="w-7 h-7 text-royal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </x-landing.stat-card>

                <x-landing.stat-card title="Fasilitas Modern"
                    description="Laboratorium sains, perpustakaan digital, ruang multimedia, lapangan olahraga, dan lingkungan belajar yang nyaman dan aman.">
                    <svg class="w-7 h-7 text-royal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </x-landing.stat-card>

                <x-landing.stat-card title="Pengajar Berkualitas"
                    description="Tenaga pendidik profesional dan berpengalaman yang berdedikasi tinggi dalam membimbing setiap siswa mencapai potensi terbaiknya.">
                    <svg class="w-7 h-7 text-royal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </x-landing.stat-card>

                <x-landing.stat-card title="Pembentukan Karakter"
                    description="Program pembinaan karakter berbasis iman yang menanamkan nilai-nilai integritas, disiplin, dan kepedulian sosial.">
                    <svg class="w-7 h-7 text-royal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </x-landing.stat-card>

                <x-landing.stat-card title="Lingkungan Aman"
                    description="Lingkungan sekolah yang asri, aman, dan kondusif untuk proses pembelajaran dengan sistem keamanan terintegrasi.">
                    <svg class="w-7 h-7 text-royal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </x-landing.stat-card>

                <x-landing.stat-card title="Ekstrakurikuler Beragam"
                    description="Berbagai pilihan kegiatan ekstrakurikuler untuk mengembangkan minat dan bakat siswa di bidang seni, olahraga, dan teknologi.">
                    <svg class="w-7 h-7 text-royal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </x-landing.stat-card>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- ALUR PENDAFTARAN SECTION --}}
    {{-- ============================================ --}}
    <section id="alur-pendaftaran" class="py-20 lg:py-28 bg-white">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
                {{-- Left: Section Header --}}
                <div class="lg:sticky lg:top-28">
                    <span
                        class="inline-block font-body text-sm font-semibold text-royal uppercase tracking-wider mb-3">Langkah
                        Mudah</span>
                    <h2 class="font-heading text-3xl lg:text-4xl font-bold text-navy-dark mb-4">Alur Pendaftaran</h2>
                    <p class="font-body text-base text-on-surface-variant leading-relaxed mb-8">
                        Proses pendaftaran kami dirancang sederhana dan transparan. Ikuti langkah-langkah berikut untuk
                        mendaftarkan putra-putri Anda.
                    </p>

                    {{-- Decorative Card --}}
                    <div class="hidden lg:block bg-gradient-to-br from-navy to-navy-dark rounded-2xl p-8 text-white">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gold-light" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="font-heading text-base font-semibold">Butuh Bantuan?</h4>
                        </div>
                        <p class="font-body text-sm text-royal-dim/80 leading-relaxed mb-4">
                            Tim kami siap membantu Anda dalam proses pendaftaran. Jangan ragu untuk menghubungi kami.
                        </p>
                        <a href="mailto:info@stellagracia.sch.id"
                            class="inline-flex items-center gap-2 font-body text-sm font-semibold text-gold-light hover:text-gold-pale transition-colors">
                            Hubungi Kami
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Right: Steps --}}
                <div class="flex flex-col">
                    <x-landing.step-item number="1" title="Registrasi Online"
                        description="Buat akun dan isi formulir pendaftaran melalui portal SPMB kami. Proses mudah dan cepat — hanya membutuhkan data dasar calon siswa dan orang tua." />

                    <x-landing.step-item number="2" title="Lengkapi Dokumen"
                        description="Unggah dokumen yang diperlukan: akta kelahir, kartu keluarga, rapor terakhir, pas foto, dan dokumen pendukung lainnya." />

                    <x-landing.step-item number="3" title="Tes Seleksi"
                        description="Ikuti tes seleksi yang meliputi tes akademik, wawancara siswa, dan wawancara orang tua. Jadwal akan diinformasikan melalui email." />

                    <x-landing.step-item number="4" title="Pengumuman Hasil"
                        description="Hasil seleksi akan diumumkan melalui portal SPMB dan email terdaftar. Siswa yang diterima dapat melanjutkan proses pendaftaran ulang."
                        :isLast="true" />
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- FAQ SECTION --}}
    {{-- ============================================ --}}
    <section id="faq" class="py-20 lg:py-28 bg-surface-container/30">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-10">
            {{-- Section Header --}}
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span
                    class="inline-block font-body text-sm font-semibold text-royal uppercase tracking-wider mb-3">FAQ</span>
                <h2 class="font-heading text-3xl lg:text-4xl font-bold text-navy-dark mb-4">Pertanyaan yang Sering
                    Diajukan</h2>
                <p class="font-body text-base text-on-surface-variant leading-relaxed">
                    Temukan jawaban atas pertanyaan umum seputar proses penerimaan murid baru di Stella Gracia School
                    Kupang.
                </p>
            </div>

            {{-- FAQ Grid --}}
            <div class="max-w-3xl mx-auto flex flex-col gap-4">
                <x-landing.faq-item question="Kapan periode pendaftaran murid baru dibuka?"
                    answer="Periode pendaftaran murid baru biasanya dibuka pada bulan Januari hingga Juni setiap tahun ajaran. Informasi detail mengenai jadwal pendaftaran akan diumumkan melalui website dan media sosial resmi sekolah." />

                <x-landing.faq-item question="Apa saja persyaratan dokumen yang harus disiapkan?"
                    answer="Dokumen yang diperlukan meliputi: akta kelahiran, kartu keluarga, rapor terakhir (untuk siswa pindahan), pas foto terbaru ukuran 3x4, serta fotokopi KTP orang tua/wali. Dokumen lengkap dapat diunggah melalui portal SPMB setelah registrasi." />

                <x-landing.faq-item question="Bagaimana proses seleksi calon murid baru?"
                    answer="Proses seleksi meliputi tes akademik dasar, wawancara siswa, dan wawancara orang tua/wali. Tes dirancang untuk mengenal potensi dan kesiapan belajar calon siswa, bukan untuk menyaring secara ketat." />

                <x-landing.faq-item question="Apakah ada program beasiswa yang tersedia?"
                    answer="Ya, Stella Gracia School menyediakan beberapa jenis beasiswa bagi siswa berprestasi, baik di bidang akademik maupun non-akademik. Informasi lebih lanjut mengenai beasiswa dapat ditanyakan langsung kepada bagian administrasi sekolah." />

                <x-landing.faq-item question="Berapa biaya pendaftaran dan uang sekolah?"
                    answer="Informasi biaya pendaftaran dan uang sekolah dapat diperoleh dengan menghubungi bagian administrasi sekolah melalui email info@stellagracia.sch.id atau datang langsung ke sekretariat sekolah pada jam kerja." />

                <x-landing.faq-item question="Bagaimana cara memantau status pendaftaran?"
                    answer="Setelah melakukan registrasi, Anda dapat memantau status pendaftaran secara real-time melalui portal SPMB dengan login menggunakan akun yang telah didaftarkan. Notifikasi juga akan dikirimkan melalui email." />
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- CTA SECTION --}}
    {{-- ============================================ --}}
    <section class="relative py-20 lg:py-28 overflow-hidden">
        {{-- Background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-navy via-navy-dark to-navy"></div>
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5" />
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#grid)" />
            </svg>
        </div>

        <div class="relative z-10 max-w-[1280px] mx-auto px-6 lg:px-10 text-center">
            <div class="max-w-2xl mx-auto">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gold/20 border border-gold/30 mb-6">
                    <svg class="w-4 h-4 text-gold-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-body text-xs font-semibold text-gold-light tracking-wide uppercase">Kuota
                        Terbatas</span>
                </div>

                <h2 class="font-heading text-3xl lg:text-4xl xl:text-5xl font-bold text-white leading-tight mb-6">
                    Wujudkan Impian Pendidikan
                    <span class="text-gold-light">Terbaik</span>
                </h2>

                <p class="font-body text-lg text-royal-dim/80 leading-relaxed mb-10">
                    Jangan lewatkan kesempatan untuk memberikan pendidikan terbaik bagi putra-putri Anda. Daftarkan
                    sekarang sebelum kuota terpenuhi.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-4">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 px-10 py-4 rounded-lg bg-gold text-white font-body text-lg font-semibold hover:bg-gold-dark hover:text-gold-light shadow-lg hover:shadow-xl transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Daftar Sekarang
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 px-10 py-4 rounded-lg border border-white/30 text-white font-body text-lg font-medium hover:bg-white/10 transition-all duration-300">
                            Sudah Punya Akun? Masuk
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

</x-layouts::landing>
