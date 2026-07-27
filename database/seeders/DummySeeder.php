<?php

namespace Database\Seeders;

use App\Enums\EducationLevel;
use App\Enums\MemberStatus;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\District;
use App\Models\Member;
use App\Models\OrgPeriod;
use App\Models\OrgUnit;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Data contoh yang lebih kaya untuk demo/pengembangan: 10 anggota dengan profil
 * lengkap (foto, bio, pendidikan), masing-masing pemilik 1-3 artikel berfeatured
 * image, plus struktur organisasi yang disusun dari anggota-anggota tersebut.
 *
 * Foto diambil dari layanan publik (randomuser.me/picsum.photos) — HANYA untuk
 * data contoh lokal, TIDAK PERNAH dijalankan di produksi. Idempoten: dilewati
 * total bila sudah pernah dijalankan (ditandai via NIA berawalan "ICMI-DEMO-").
 */
class DummySeeder extends Seeder
{
    private const NIA_PREFIX = 'ICMI-DEMO-';

    /** @var array<int, array<string, mixed>> */
    private array $profiles = [
        [
            'name' => 'Ahmad Zulkarnain', 'title_prefix' => 'Dr. H.', 'title_suffix' => 'M.Pd.', 'gender' => 'L',
            'profession' => 'Dosen', 'institution' => 'STAIN Bengkalis', 'district' => 'Bengkalis',
            'expertise' => 'Pendidikan Karakter, Pendidikan Tinggi',
            'bio' => 'Akademisi dengan fokus kajian pendidikan karakter berbasis nilai keislaman. Aktif membina program pengabdian masyarakat di bidang pendidikan sejak lebih dari satu dekade.',
            'education' => ['S3', 'Universitas Islam Negeri Sultan Syarif Kasim Riau', 'Manajemen Pendidikan Islam', 2016],
            'photo' => 'men/32', 'unit' => 'Ketua Umum', 'position' => 'Ketua Umum', 'sort' => 0,
        ],
        [
            'name' => 'Siti Rahmawati', 'title_prefix' => '', 'title_suffix' => 'S.E., M.M.', 'gender' => 'P',
            'profession' => 'Praktisi Perbankan Syariah', 'institution' => 'Bank Syariah Cabang Bengkalis', 'district' => 'Bengkalis',
            'expertise' => 'Ekonomi Syariah, UMKM',
            'bio' => 'Berpengalaman lebih dari 12 tahun di sektor keuangan syariah, kini aktif mendampingi pelaku UMKM lokal dalam literasi ekonomi syariah.',
            'education' => ['S2', 'Universitas Riau', 'Manajemen Keuangan', 2014],
            'photo' => 'women/44', 'unit' => 'Sekretariat', 'position' => 'Sekretaris Jenderal', 'sort' => 0,
        ],
        [
            'name' => 'Muhammad Rizki Pratama', 'title_prefix' => '', 'title_suffix' => 'S.Kom.', 'gender' => 'L',
            'profession' => 'Konsultan IT', 'institution' => 'PT Riau Digital Nusantara', 'district' => 'Mandau',
            'expertise' => 'Teknologi Informasi, Rekayasa Perangkat Lunak',
            'bio' => 'Pegiat transformasi digital daerah, menginisiasi beberapa program literasi teknologi bagi pelajar dan UMKM di Kabupaten Bengkalis.',
            'education' => ['S1', 'Universitas Gadjah Mada', 'Teknik Informatika', 2012],
            'photo' => 'men/54', 'unit' => 'Sekretariat', 'position' => 'Bendahara Umum', 'sort' => 1,
        ],
        [
            'name' => 'Hasanuddin', 'title_prefix' => 'H.', 'title_suffix' => 'S.Ag., M.E.', 'gender' => 'L',
            'profession' => 'Wirausaha', 'institution' => 'Koperasi Syariah Barokah', 'district' => 'Bantan',
            'expertise' => 'Ekonomi Syariah, UMKM',
            'bio' => 'Penggerak koperasi syariah di tingkat kecamatan, aktif mendorong kemandirian ekonomi umat melalui pendampingan usaha mikro.',
            'education' => ['S2', 'IAIN Sultan Amai Gorontalo', 'Ekonomi Syariah', 2018],
            'photo' => 'men/61', 'unit' => 'Bidang Ekonomi & Kewirausahaan', 'position' => 'Ketua Bidang', 'sort' => 0,
        ],
        [
            'name' => 'Nur Aisyah Lubis', 'title_prefix' => '', 'title_suffix' => 'M.Pd.', 'gender' => 'P',
            'profession' => 'Guru', 'institution' => 'SMA Negeri 1 Bengkalis', 'district' => 'Bengkalis',
            'expertise' => 'Pendidikan Karakter',
            'bio' => 'Pendidik yang concern pada penguatan pendidikan karakter di jenjang menengah, kerap menjadi narasumber pelatihan guru se-Kabupaten Bengkalis.',
            'education' => ['S2', 'Universitas Negeri Padang', 'Pendidikan Bahasa Indonesia', 2015],
            'photo' => 'women/68', 'unit' => 'Bidang Pendidikan & SDM', 'position' => 'Ketua Bidang', 'sort' => 1,
        ],
        [
            'name' => 'Budi Setiawan', 'title_prefix' => '', 'title_suffix' => 'S.T., M.T.', 'gender' => 'L',
            'profession' => 'Dosen', 'institution' => 'Politeknik Negeri Bengkalis', 'district' => 'Bengkalis',
            'expertise' => 'Teknologi Informasi',
            'bio' => 'Peneliti bidang rekayasa perangkat lunak, aktif membina komunitas coding pelajar dan pengembangan aplikasi layanan publik daerah.',
            'education' => ['S2', 'Institut Teknologi Sepuluh Nopember', 'Teknik Informatika', 2017],
            'photo' => 'men/75', 'unit' => 'Bidang Teknologi & Informasi', 'position' => 'Ketua Bidang', 'sort' => 2,
        ],
        [
            'name' => 'dr. Fitriani Ramadhani', 'title_prefix' => '', 'title_suffix' => 'Sp.PD.', 'gender' => 'P',
            'profession' => 'Dokter', 'institution' => 'RSUD Bengkalis', 'district' => 'Bengkalis',
            'expertise' => 'Kesehatan Masyarakat, Gizi',
            'bio' => 'Dokter spesialis penyakit dalam yang rutin turut serta dalam program bakti sosial dan edukasi kesehatan keluarga di berbagai kecamatan.',
            'education' => ['S2', 'Universitas Sumatera Utara', 'Ilmu Penyakit Dalam', 2019],
            'photo' => 'women/21', 'unit' => 'Bidang Kesehatan & Kesejahteraan Umat', 'position' => 'Ketua Bidang', 'sort' => 3,
        ],
        [
            'name' => 'Abdul Karim', 'title_prefix' => '', 'title_suffix' => 'S.Pd.', 'gender' => 'L',
            'profession' => 'PNS', 'institution' => 'Dinas Pendidikan Kabupaten Bengkalis', 'district' => 'Siak Kecil',
            'expertise' => 'Pendidikan Tinggi',
            'bio' => 'Birokrat di bidang pendidikan dengan perhatian besar pada pemerataan akses pendidikan di wilayah pesisir dan kepulauan.',
            'education' => ['S1', 'Universitas Riau', 'Administrasi Pendidikan', 2009],
            'photo' => 'men/15', 'unit' => null, 'position' => null, 'sort' => 0,
        ],
        [
            'name' => 'Ratna Sari Dewi', 'title_prefix' => '', 'title_suffix' => 'S.Sos.', 'gender' => 'P',
            'profession' => 'Aktivis Sosial', 'institution' => 'Yayasan Peduli Umat Bengkalis', 'district' => 'Rupat',
            'expertise' => 'Kesehatan Masyarakat',
            'bio' => 'Menggerakkan program pemberdayaan perempuan dan anak di wilayah pesisir, aktif dalam jejaring organisasi sosial kemasyarakatan.',
            'education' => ['S1', 'Universitas Islam Riau', 'Kesejahteraan Sosial', 2013],
            'photo' => 'women/52', 'unit' => null, 'position' => null, 'sort' => 0,
        ],
        [
            'name' => 'Muhammad Iqbal Nasution', 'title_prefix' => '', 'title_suffix' => 'S.E.', 'gender' => 'L',
            'profession' => 'Wirausaha', 'institution' => 'CV Bengkalis Makmur Sejahtera', 'district' => 'Bukit Batu',
            'expertise' => 'UMKM',
            'bio' => 'Pelaku usaha di sektor perdagangan yang aktif membina generasi muda wirausaha melalui program magang dan kemitraan usaha.',
            'education' => ['S1', 'Universitas Riau', 'Manajemen', 2011],
            'photo' => 'men/40', 'unit' => null, 'position' => null, 'sort' => 0,
        ],
    ];

    /** Kumpulan judul+isi artikel bertema ICMI/cendekiawan untuk didistribusikan ke anggota. */
    private array $articlePool = [
        ['type' => 'artikel', 'title' => 'Ekonomi Syariah sebagai Jalan Kemandirian UMKM Bengkalis', 'body' => "Ekonomi syariah menawarkan lebih dari sekadar alternatif pembiayaan bebas riba — ia menghadirkan kerangka etika bisnis yang menekankan keadilan, transparansi, dan keberkahan dalam setiap transaksi.\n\nBagi pelaku UMKM di Kabupaten Bengkalis, penerapan prinsip ekonomi syariah dapat menjadi pembeda sekaligus penguat daya saing usaha, terutama dalam membangun kepercayaan konsumen dan mitra usaha.\n\nSejumlah program pendampingan yang digagas ICMI Kabupaten Bengkalis telah membantu puluhan pelaku usaha kecil memahami akad-akad syariah sederhana yang dapat langsung diterapkan dalam praktik bisnis harian mereka."],
        ['type' => 'artikel', 'title' => 'Pendidikan Karakter: Fondasi Membangun Generasi Cendekiawan', 'body' => "Di tengah derasnya arus informasi digital, penguatan pendidikan karakter menjadi semakin krusial bagi generasi muda Kabupaten Bengkalis.\n\nPendidikan karakter tidak sekadar mengajarkan nilai moral secara normatif, melainkan menanamkan kebiasaan berpikir kritis, jujur, dan bertanggung jawab yang relevan dengan tantangan zaman.\n\nMelalui berbagai program pelatihan guru dan pendampingan sekolah, ICMI Kabupaten Bengkalis berupaya menghadirkan pendekatan pendidikan karakter yang kontekstual dengan nilai-nilai keislaman dan kearifan lokal."],
        ['type' => 'artikel', 'title' => 'Transformasi Digital: Peluang dan Tantangan bagi Daerah', 'body' => "Transformasi digital bukan lagi pilihan, melainkan keniscayaan bagi daerah yang ingin tetap relevan dan berdaya saing di tengah perubahan zaman.\n\nBagi Kabupaten Bengkalis, transformasi digital membuka peluang besar dalam efisiensi layanan publik, pemasaran produk UMKM ke pasar yang lebih luas, hingga literasi digital masyarakat pesisir yang selama ini kurang terjangkau.\n\nNamun demikian, tantangan infrastruktur dan kesenjangan literasi digital masih perlu menjadi perhatian bersama seluruh pemangku kepentingan, termasuk peran aktif cendekiawan muslim dalam mengawal proses ini agar berjalan inklusif."],
        ['type' => 'berita', 'title' => 'ICMI Kabupaten Bengkalis Gelar Pelatihan Literasi Digital untuk Pelaku UMKM', 'body' => "ICMI Kabupaten Bengkalis menyelenggarakan pelatihan literasi digital bagi puluhan pelaku UMKM di wilayah Kecamatan Bengkalis, akhir pekan lalu.\n\nKegiatan ini bertujuan membekali pelaku usaha kecil dengan keterampilan dasar pemasaran digital, mulai dari pengelolaan media sosial hingga penggunaan platform e-commerce sederhana.\n\nPara peserta menyambut antusias kegiatan ini dan berharap program serupa dapat terus dilaksanakan secara berkala di kecamatan-kecamatan lain."],
        ['type' => 'berita', 'title' => 'Bakti Sosial Kesehatan ICMI Bengkalis Layani Ratusan Warga Pesisir', 'body' => "Bidang Kesehatan & Kesejahteraan Umat ICMI Kabupaten Bengkalis menggelar kegiatan bakti sosial pemeriksaan kesehatan gratis bagi warga di wilayah pesisir.\n\nKegiatan yang melibatkan sejumlah tenaga medis anggota ICMI ini berhasil melayani ratusan warga, mulai dari pemeriksaan kesehatan umum hingga edukasi gizi keluarga.\n\nKegiatan semacam ini direncanakan akan terus dilaksanakan secara rutin sebagai wujud kepedulian sosial organisasi terhadap masyarakat sekitar."],
        ['type' => 'berita', 'title' => 'Rapat Kerja Pengurus ICMI Bengkalis Susun Program Kerja Tahunan', 'body' => "Pengurus ICMI Kabupaten Bengkalis menggelar rapat kerja tahunan untuk menyusun dan mengevaluasi program kerja organisasi ke depan.\n\nRapat yang dihadiri seluruh ketua bidang ini membahas capaian program tahun sebelumnya sekaligus merumuskan prioritas program tahun berjalan, mulai dari bidang pendidikan, ekonomi, teknologi, hingga kesehatan.\n\nKetua Umum ICMI Kabupaten Bengkalis menekankan pentingnya sinergi lintas bidang agar program kerja organisasi dapat memberi dampak nyata bagi masyarakat."],
        ['type' => 'artikel', 'title' => 'Peran Cendekiawan Muslim dalam Pembangunan Daerah', 'body' => "Cendekiawan muslim memiliki tanggung jawab moral untuk tidak sekadar berkontribusi dalam ranah pemikiran, tetapi juga hadir secara nyata dalam proses pembangunan daerah.\n\nDi Kabupaten Bengkalis, peran ini diwujudkan melalui berbagai program lintas bidang — mulai dari pendidikan, ekonomi, hingga kesehatan — yang dijalankan secara kolaboratif oleh anggota ICMI dari berbagai latar belakang profesi.\n\nSinergi antara keilmuan dan aksi nyata inilah yang menjadi ciri khas gerakan cendekiawan muslim dalam merespons kebutuhan pembangunan daerah."],
        ['type' => 'opini', 'title' => 'Menakar Ulang Makna Kecendekiawanan di Era Digital', 'body' => "Di era digital, makna kecendekiawanan tidak lagi cukup diukur dari penguasaan ilmu semata, melainkan juga kemampuan menerjemahkan ilmu tersebut menjadi solusi nyata bagi persoalan masyarakat.\n\nSebagai anggota ICMI, saya meyakini bahwa tantangan zaman menuntut kita untuk terus adaptif — memanfaatkan teknologi sebagai alat, bukan tujuan, dalam menjalankan misi kecendekiawanan yang berlandaskan nilai keislaman.\n\nSudah saatnya kita merefleksikan kembali bagaimana peran cendekiawan muslim dapat terus relevan menjawab kebutuhan generasi yang tumbuh di tengah arus digitalisasi."],
        ['type' => 'artikel', 'title' => 'Gizi Keluarga sebagai Investasi Jangka Panjang Bangsa', 'body' => "Persoalan gizi keluarga kerap luput dari perhatian, padahal dampaknya jangka panjang bagi kualitas sumber daya manusia suatu daerah sangatlah besar.\n\nDi Kabupaten Bengkalis, edukasi gizi keluarga menjadi salah satu program prioritas Bidang Kesehatan & Kesejahteraan Umat ICMI, khususnya menyasar wilayah dengan akses layanan kesehatan yang masih terbatas.\n\nMelalui edukasi yang berkelanjutan, diharapkan kesadaran masyarakat akan pentingnya gizi seimbang dapat terus meningkat dari waktu ke waktu."],
        ['type' => 'artikel', 'title' => 'Menghidupkan Kembali Semangat Koperasi Syariah di Tingkat Kecamatan', 'body' => "Koperasi syariah memiliki potensi besar sebagai instrumen pemberdayaan ekonomi umat di tingkat akar rumput, khususnya di wilayah kecamatan yang jauh dari akses lembaga keuangan formal.\n\nBeberapa koperasi syariah binaan anggota ICMI Kabupaten Bengkalis telah menunjukkan hasil yang menggembirakan dalam mendorong kemandirian ekonomi pelaku usaha mikro.\n\nKe depan, replikasi model koperasi syariah semacam ini perlu terus didorong agar manfaatnya dapat dirasakan lebih merata di seluruh kecamatan."],
        ['type' => 'berita', 'title' => 'Workshop Rekayasa Perangkat Lunak untuk Pelajar SMK se-Bengkalis', 'body' => "Bidang Teknologi & Informasi ICMI Kabupaten Bengkalis menggelar workshop dasar rekayasa perangkat lunak bagi pelajar SMK se-Kabupaten Bengkalis.\n\nWorkshop ini memperkenalkan konsep dasar pemrograman dan pengembangan aplikasi sederhana, sebagai bagian dari upaya menumbuhkan minat generasi muda terhadap bidang teknologi informasi.\n\nPara peserta mendapatkan pendampingan langsung dari anggota ICMI yang berprofesi sebagai praktisi dan akademisi di bidang teknologi."],
        ['type' => 'opini', 'title' => 'Pendidikan Pesisir: Tantangan yang Tak Boleh Diabaikan', 'body' => "Sebagai birokrat di bidang pendidikan, saya menyaksikan langsung betapa besar kesenjangan akses pendidikan antara wilayah daratan dan kepulauan di Kabupaten Bengkalis.\n\nKetimpangan ini bukan sekadar persoalan infrastruktur, melainkan juga menyangkut pemerataan tenaga pendidik dan akses teknologi pembelajaran.\n\nSaya berpandangan bahwa organisasi kecendekiawanan seperti ICMI perlu terus mengawal isu ini agar pendidikan yang berkeadilan benar-benar dapat dirasakan seluruh anak Bengkalis, tanpa terkecuali."],
        ['type' => 'berita', 'title' => 'Silaturahmi Anggota ICMI Bengkalis Perkuat Sinergi Antarbidang', 'body' => "ICMI Kabupaten Bengkalis menggelar acara silaturahmi tahunan yang mempertemukan seluruh anggota dari berbagai bidang keahlian dan profesi.\n\nAcara ini menjadi ajang berbagi capaian program masing-masing bidang sekaligus merajut kembali kedekatan antaranggota yang tersebar di berbagai kecamatan.\n\nKetua Umum berharap silaturahmi semacam ini dapat terus menjaga soliditas organisasi dalam menjalankan program kerja ke depan."],
        ['type' => 'artikel', 'title' => 'Membangun Ekosistem Wirausaha Muda Berbasis Nilai Islami', 'body' => "Menumbuhkan wirausaha muda yang berlandaskan nilai-nilai keislaman menjadi salah satu agenda penting dalam pemberdayaan ekonomi umat di Kabupaten Bengkalis.\n\nEkosistem wirausaha yang sehat tidak hanya menekankan keuntungan semata, tetapi juga kejujuran, tanggung jawab sosial, dan keberlanjutan usaha dalam jangka panjang.\n\nICMI Kabupaten Bengkalis melalui bidang ekonomi terus mendorong pendampingan bagi wirausaha muda agar tumbuh menjadi pelaku usaha yang tangguh sekaligus berintegritas."],
        ['type' => 'artikel', 'title' => 'Peran Perempuan Cendekiawan dalam Pemberdayaan Masyarakat', 'body' => "Perempuan cendekiawan memiliki peran strategis dalam pemberdayaan masyarakat, khususnya pada isu pendidikan keluarga, kesehatan, dan penguatan ekonomi rumah tangga.\n\nDi Kabupaten Bengkalis, banyak anggota ICMI perempuan yang aktif menggerakkan program-program pemberdayaan di tingkat akar rumput, mulai dari pendampingan UMKM hingga edukasi kesehatan keluarga.\n\nPeran ini menegaskan bahwa kecendekiawanan bukan hanya soal gelar akademik, melainkan juga kontribusi nyata bagi kemaslahatan bersama."],
        ['type' => 'berita', 'title' => 'ICMI Bengkalis Jalin Kerja Sama dengan Perguruan Tinggi Lokal', 'body' => "ICMI Kabupaten Bengkalis menandatangani nota kesepahaman kerja sama dengan salah satu perguruan tinggi lokal dalam bidang penelitian dan pengabdian masyarakat.\n\nKerja sama ini mencakup program magang mahasiswa, kajian bersama isu-isu pembangunan daerah, hingga pendampingan penelitian dosen di bidang ekonomi syariah dan pendidikan karakter.\n\nDiharapkan kolaborasi ini dapat memperkuat basis keilmuan program-program ICMI Kabupaten Bengkalis ke depan."],
        ['type' => 'opini', 'title' => 'Digitalisasi UMKM: Bukan Sekadar Ikut Tren', 'body' => "Sebagai konsultan teknologi, saya kerap menjumpai pelaku UMKM yang mengadopsi platform digital hanya karena ikut tren, tanpa memahami strategi pemanfaatannya secara utuh.\n\nDigitalisasi UMKM semestinya dimulai dari pemahaman kebutuhan bisnis, bukan sekadar kehadiran di media sosial atau marketplace semata.\n\nSaya berpandangan pendampingan yang berkelanjutan — bukan pelatihan sekali jalan — adalah kunci agar digitalisasi benar-benar berdampak bagi keberlangsungan usaha kecil di Bengkalis."],
        ['type' => 'artikel', 'title' => 'Kaderisasi Cendekiawan Muda: Investasi Masa Depan Organisasi', 'body' => "Keberlangsungan sebuah organisasi kecendekiawanan sangat bergantung pada keberhasilan kaderisasi generasi penerusnya.\n\nICMI Kabupaten Bengkalis terus berupaya menjaring dan membina cendekiawan muda melalui berbagai program pelatihan kepemimpinan dan pelibatan aktif dalam kegiatan organisasi.\n\nInvestasi pada kaderisasi hari ini adalah jaminan keberlanjutan peran ICMI bagi pembangunan daerah di masa mendatang."],
        ['type' => 'berita', 'title' => 'Edukasi Kesehatan Keluarga Sasar Wilayah Kepulauan Bengkalis', 'body' => "Bidang Kesehatan & Kesejahteraan Umat ICMI Kabupaten Bengkalis memperluas jangkauan program edukasi kesehatan keluarga ke wilayah kepulauan yang selama ini minim akses layanan kesehatan.\n\nProgram ini melibatkan tenaga medis anggota ICMI dalam memberikan edukasi gizi, kesehatan ibu dan anak, serta pola hidup sehat bagi masyarakat pesisir.\n\nKegiatan ini mendapat sambutan positif dari warga dan diharapkan dapat terus diperluas ke wilayah kepulauan lainnya."],
    ];

    public function run(): void
    {
        if (Member::where('nia', 'like', self::NIA_PREFIX.'%')->exists()) {
            $this->command?->info('DummySeeder dilewati — data contoh sudah pernah dibuat.');

            return;
        }

        $members = collect($this->profiles)->map(fn (array $profile, int $index) => $this->createMember($profile, $index));

        $this->createOrgStructure($members);
        $this->createArticles($members);

        $this->command?->info('DummySeeder selesai: 10 anggota + artikel + struktur organisasi contoh dibuat.');
    }

    private function createMember(array $profile, int $index): Member
    {
        $user = User::firstOrCreate(
            ['email' => str($profile['name'])->slug().'@demo.test'],
            ['name' => $profile['name'], 'password' => 'password', 'email_verified_at' => now()],
        );
        $user->assignRole('anggota');

        $district = District::where('name', $profile['district'])->first();

        $member = Member::create([
            'user_id' => $user->id,
            'nia' => self::NIA_PREFIX.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
            'full_name' => $profile['name'],
            'title_prefix' => $profile['title_prefix'] ?: null,
            'title_suffix' => $profile['title_suffix'] ?: null,
            'gender' => $profile['gender'],
            'birth_place' => $district?->name ?? 'Bengkalis',
            'birth_date' => now()->subYears(rand(30, 55))->subDays(rand(0, 365)),
            'address' => 'Jl. Contoh No. '.rand(1, 99).', '.$profile['district'],
            'district_id' => $district?->id,
            'institution' => $profile['institution'],
            'profession' => $profile['profession'],
            'expertise' => $profile['expertise'],
            'bio' => $profile['bio'],
            'social_links' => ['whatsapp' => '628'.rand(1111111111, 1999999999)],
            'status' => MemberStatus::Aktif,
            'joined_at' => now()->subMonths(rand(1, 36)),
            'show_contact_public' => (bool) rand(0, 1),
        ]);

        [$level, $institution, $major, $year] = $profile['education'];
        $member->educations()->create([
            'level' => EducationLevel::from($level),
            'institution' => $institution,
            'major' => $major,
            'graduated_year' => $year,
        ]);

        $this->attachRemotePhoto($member, $profile['photo']);

        return $member;
    }

    private function attachRemotePhoto(Member $member, string $path): void
    {
        try {
            $member->addMediaFromUrl("https://randomuser.me/api/portraits/{$path}.jpg")
                ->toMediaCollection('photo');
        } catch (\Throwable $e) {
            Log::warning("DummySeeder: gagal unduh foto anggota {$member->full_name}: {$e->getMessage()}");
        }
    }

    /** @param  Collection<int, Member>  $members */
    private function createOrgStructure($members): void
    {
        $period = OrgPeriod::firstOrCreate(
            ['name' => '2025-2030'],
            ['starts_at' => '2025-01-01', 'ends_at' => '2030-01-01', 'is_active' => true]
        );

        $unitsBySort = [];

        foreach ($this->profiles as $index => $profile) {
            if (! $profile['unit']) {
                continue;
            }

            $unit = $unitsBySort[$profile['unit']] ??= OrgUnit::firstOrCreate(
                ['org_period_id' => $period->id, 'name' => $profile['unit']],
                ['sort_order' => count($unitsBySort)]
            );

            $unit->assignments()->create([
                'member_id' => $members[$index]->id,
                'position_title' => $profile['position'],
                'sort_order' => $profile['sort'],
            ]);
        }

        // Bidang-bidang berada di bawah Ketua Umum agar hierarki tampil di org chart.
        $ketua = $unitsBySort['Ketua Umum'] ?? null;
        if ($ketua) {
            foreach (['Bidang Ekonomi & Kewirausahaan', 'Bidang Pendidikan & SDM', 'Bidang Teknologi & Informasi', 'Bidang Kesehatan & Kesejahteraan Umat'] as $name) {
                $unitsBySort[$name]?->update(['parent_id' => $ketua->id]);
            }
        }
    }

    /**
     * Distribusi artikel dua tahap agar SETIAP anggota mendapat minimal 1 dan
     * maksimal 3 artikel: tahap 1 menjamin 1 artikel per anggota, tahap 2
     * membagikan sisa pool secara acak ke anggota yang belum mencapai batas 3.
     *
     * @param  Collection<int, Member>  $members
     */
    private function createArticles($members): void
    {
        $pool = collect($this->articlePool)->shuffle()->values();
        $poolIndex = 0;
        /** @var array<int, int> $counts */
        $counts = array_fill_keys($members->pluck('id')->all(), 0);

        foreach ($members as $member) {
            if ($poolIndex >= $pool->count()) {
                break;
            }

            $this->publishArticle($member, $pool[$poolIndex], $poolIndex);
            $counts[$member->id]++;
            $poolIndex++;
        }

        $eligible = $members->filter(fn (Member $member) => $counts[$member->id] < 3)->values();

        while ($poolIndex < $pool->count() && $eligible->isNotEmpty()) {
            $member = $eligible->random();

            $this->publishArticle($member, $pool[$poolIndex], $poolIndex);
            $counts[$member->id]++;
            $poolIndex++;

            $eligible = $eligible->reject(fn (Member $m) => $counts[$m->id] >= 3)->values();
        }
    }

    private function publishArticle(Member $member, array $article, int $seed): void
    {
        $post = Post::create([
            'type' => PostType::from($article['type']),
            'title' => $article['title'],
            'excerpt' => str($article['body'])->limit(150),
            'body' => '<p>'.str_replace("\n\n", '</p><p>', $article['body']).'</p>',
            'status' => PostStatus::Published,
            'author_id' => $member->user_id,
            'published_at' => now()->subDays(rand(1, 90)),
        ]);

        $this->attachFeaturedImage($post, $seed);
    }

    private function attachFeaturedImage(Post $post, int $seed): void
    {
        try {
            $post->addMediaFromUrl("https://picsum.photos/seed/icmi-post-{$seed}/1200/675")
                ->toMediaCollection('featured');
        } catch (\Throwable $e) {
            Log::warning("DummySeeder: gagal unduh featured image post {$post->title}: {$e->getMessage()}");
        }
    }
}
