<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Halaman statis inti Fase 1, diisi konten bertema ICMI (naskah asli, bukan kutipan
 * situs mana pun) agar halaman publik tidak kosong. Idempoten: hanya mengisi ulang
 * record yang isinya masih placeholder lama (belum pernah disunting pengurus).
 */
class StaticPageSeeder extends Seeder
{
    private array $pages = [
        'tentang' => [
            'title' => 'Tentang Kami',
            'body' => <<<'HTML'
                <p>Ikatan Cendekiawan Muslim se-Indonesia (ICMI) Kabupaten Bengkalis adalah organisasi daerah dari ICMI, wadah perhimpunan cendekiawan muslim yang berikhtiar menyatukan pemikiran, keahlian, dan kepedulian untuk kemajuan umat dan bangsa di Kabupaten Bengkalis.</p>
                <p>Sebagai organisasi kecendekiawanan, ICMI Kabupaten Bengkalis menghimpun akademisi, praktisi, birokrat, pengusaha, dan tokoh masyarakat muslim lintas profesi yang memiliki kepedulian yang sama: menjadikan ilmu pengetahuan, teknologi, dan nilai-nilai keislaman sebagai landasan pembangunan daerah yang berkeadaban.</p>
                <h3>Peran Kami</h3>
                <p>Melalui berbagai program kajian, pendidikan, pemberdayaan ekonomi umat, dan pengabdian masyarakat, ICMI Kabupaten Bengkalis berupaya hadir sebagai mitra strategis pemerintah daerah, dunia akademik, dan masyarakat dalam merespons tantangan zaman — mulai dari transformasi digital, penguatan pendidikan karakter, hingga pengembangan ekonomi syariah dan UMKM.</p>
                <p>Portal digital ini adalah salah satu wujud komitmen kami untuk terus beradaptasi, menjadi rumah informasi, pengetahuan, dan silaturahmi bagi seluruh anggota dan masyarakat Kabupaten Bengkalis.</p>
                HTML,
        ],
        'sejarah' => [
            'title' => 'Sejarah',
            'body' => <<<'HTML'
                <p>Ikatan Cendekiawan Muslim se-Indonesia (ICMI) lahir pada 7 Desember 1990 di Malang, Jawa Timur, digagas oleh sekelompok cendekiawan muslim yang meyakini pentingnya wadah bersama bagi kalangan intelektual muslim Indonesia untuk berkontribusi secara lebih terorganisasi bagi pembangunan bangsa. Sejak awal berdirinya, ICMI mengusung semangat keterbukaan, keilmuan, dan kebangsaan sebagai landasan gerak organisasinya.</p>
                <h3>Berdirinya ICMI Kabupaten Bengkalis</h3>
                <p>Seiring berkembangnya jaringan ICMI hingga ke tingkat kabupaten/kota di seluruh Indonesia, para cendekiawan muslim di Kabupaten Bengkalis turut berhimpun membentuk Organisasi Daerah (Orda) ICMI Kabupaten Bengkalis. Diinisiasi oleh akademisi, praktisi pendidikan, dan tokoh masyarakat setempat, ICMI Kabupaten Bengkalis dibentuk sebagai respons atas kebutuhan akan wadah sinergi antara kalangan cendekiawan dengan program pembangunan daerah yang berlandaskan nilai-nilai keislaman.</p>
                <p>Sejak berdiri, ICMI Kabupaten Bengkalis telah melalui beberapa periode kepengurusan, masing-masing membawa warna dan fokus program yang menyesuaikan kebutuhan zaman — mulai dari penguatan pendidikan, pemberdayaan ekonomi umat, hingga kini transformasi digital organisasi melalui portal ini.</p>
                <p>Perjalanan panjang ini menjadi pengingat bahwa ICMI Kabupaten Bengkalis adalah kesinambungan estafet perjuangan intelektual muslim lintas generasi yang terus relevan menjawab tantangan zamannya masing-masing.</p>
                HTML,
        ],
        'visi-misi' => [
            'title' => 'Visi & Misi',
            'body' => <<<'HTML'
                <h3>Visi</h3>
                <p>Terwujudnya masyarakat Kabupaten Bengkalis yang beradab, berkeadilan, dan berdaya saing melalui peran aktif cendekiawan muslim dalam ilmu pengetahuan, teknologi, dan pembangunan umat.</p>
                <h3>Misi</h3>
                <ul>
                    <li>Menghimpun dan memberdayakan potensi cendekiawan muslim di berbagai bidang keahlian untuk berkontribusi nyata bagi pembangunan daerah.</li>
                    <li>Mendorong penguatan pendidikan, ilmu pengetahuan, dan teknologi yang dilandasi nilai-nilai keislaman dan kebangsaan.</li>
                    <li>Membangun jejaring kerja sama dengan pemerintah daerah, dunia usaha, akademisi, dan masyarakat dalam merumuskan solusi atas persoalan umat.</li>
                    <li>Mengembangkan pemberdayaan ekonomi umat, termasuk penguatan UMKM dan ekonomi syariah, sebagai wujud kepedulian sosial cendekiawan muslim.</li>
                    <li>Menjadi rumah silaturahmi dan pengkaderan bagi generasi cendekiawan muslim Kabupaten Bengkalis di masa depan.</li>
                </ul>
                HTML,
        ],
        'sambutan-ketua' => [
            'title' => 'Sambutan Ketua',
            'body' => <<<'HTML'
                <p>Assalamu'alaikum warahmatullahi wabarakatuh.</p>
                <p>Puji syukur kita panjatkan kehadirat Allah Subhanahu wa Ta'ala atas segala limpahan rahmat dan karunia-Nya, sehingga ICMI Kabupaten Bengkalis dapat terus berikhtiar menjalankan amanah sebagai wadah perhimpunan cendekiawan muslim di daerah kita tercinta.</p>
                <p>Sebagai organisasi kecendekiawanan, ICMI Kabupaten Bengkalis senantiasa berupaya hadir di tengah masyarakat — bukan sekadar sebagai forum diskusi, melainkan sebagai mitra aktif pembangunan daerah yang berlandaskan ilmu pengetahuan, teknologi, dan nilai-nilai keislaman. Di tengah derasnya arus transformasi digital dan tantangan zaman yang terus berubah, kami meyakini peran cendekiawan muslim menjadi semakin penting: menjembatani ilmu dan amal, gagasan dan aksi nyata.</p>
                <p>Portal digital yang tengah Anda baca ini adalah salah satu ikhtiar kami untuk terus beradaptasi — menjadi rumah informasi, pusat pengetahuan, dan ruang silaturahmi bagi seluruh anggota, pengurus, dan masyarakat Kabupaten Bengkalis. Kami berharap kehadiran portal ini dapat mempererat sinergi antaranggota, memudahkan akses informasi kegiatan organisasi, serta menjadi wadah publikasi karya dan pemikiran para cendekiawan di daerah kita.</p>
                <p>Atas nama pengurus, saya mengajak seluruh anggota dan masyarakat untuk turut berperan aktif bersama ICMI Kabupaten Bengkalis, membangun peradaban melalui intelektualitas, sebagaimana semangat yang kita usung bersama.</p>
                <p>Wassalamu'alaikum warahmatullahi wabarakatuh.</p>
                <p><strong>Ketua Umum</strong><br>ICMI Kabupaten Bengkalis</p>
                HTML,
        ],
        'program-kerja' => [
            'title' => 'Program Kerja',
            'body' => <<<'HTML'
                <p>Dalam menjalankan amanah organisasi, ICMI Kabupaten Bengkalis menyusun program kerja yang terbagi ke dalam beberapa bidang strategis, selaras dengan visi menghadirkan peran cendekiawan muslim bagi pembangunan daerah.</p>
                <h3>Bidang Pendidikan &amp; Sumber Daya Manusia</h3>
                <ul>
                    <li>Beasiswa dan pendampingan pendidikan bagi anak-anak berprestasi kurang mampu.</li>
                    <li>Pelatihan dan seminar penguatan pendidikan karakter bagi guru dan pelajar.</li>
                    <li>Kerja sama dengan perguruan tinggi dalam pengembangan kajian keislaman dan keilmuan.</li>
                </ul>
                <h3>Bidang Ekonomi &amp; Kewirausahaan</h3>
                <ul>
                    <li>Pendampingan dan literasi ekonomi syariah bagi pelaku UMKM.</li>
                    <li>Fasilitasi akses permodalan dan pemasaran produk usaha mikro anggota masyarakat.</li>
                </ul>
                <h3>Bidang Teknologi &amp; Informasi</h3>
                <ul>
                    <li>Transformasi digital layanan organisasi, termasuk pengelolaan portal dan data keanggotaan.</li>
                    <li>Pelatihan literasi digital bagi anggota dan masyarakat umum.</li>
                </ul>
                <h3>Bidang Kesehatan &amp; Kesejahteraan Umat</h3>
                <ul>
                    <li>Kegiatan bakti sosial dan layanan kesehatan masyarakat.</li>
                    <li>Edukasi gizi dan kesehatan keluarga di tingkat kecamatan.</li>
                </ul>
                <p>Seluruh program kerja ini dijalankan secara kolaboratif oleh masing-masing bidang, dengan pelaporan berkala kepada pengurus inti dan anggota melalui rapat kerja organisasi.</p>
                HTML,
        ],
    ];

    public function run(): void
    {
        foreach ($this->pages as $slug => $data) {
            $page = Page::firstOrCreate(['slug' => $slug], [
                'title' => $data['title'],
                'body' => $data['body'],
            ]);

            if (str_starts_with((string) $page->body, 'Konten halaman')) {
                $page->update(['body' => $data['body']]);
            }
        }
    }
}
