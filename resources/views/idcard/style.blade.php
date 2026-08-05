{{-- Gaya kartu 54 x 85,6 mm (potret) — dipakai preview HTML dan PDF (dompdf).
     Zona overlay (desain latar menyisakan area ini):
     - 12–21 mm  : nama kegiatan & tanggal
     - 21,5–54,5 : foto (kotak membulat 44 x 33 mm)
     - 55–67 mm  : nama & jabatan (putih tebal)
     - 67–83 mm  : QR transparan --}}
<style>
    .kartu {
        position: relative;
        width: 54mm;
        height: 85.6mm;
        overflow: hidden;
        background: #EAF3DC;
        font-family: DejaVu Sans, Arial, sans-serif;
    }

    .kartu .bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 54mm;
        height: 85.6mm;
    }

    .kartu .acara {
        position: absolute;
        top: 11.8mm;
        left: 4mm;
        width: 46mm;
        text-align: center;
        font-size: 6pt;
        color: #444939;
        line-height: 1.25;
    }

    .kartu .foto {
        position: absolute;
        top: 22.5mm;
        left: 5mm;
        width: 44mm;
        height: 32mm;
        border-radius: 3.5mm;
        background: #99C24D;
        overflow: hidden;
    }

    .kartu .foto img {
        width: 44mm;
        height: 32mm;
        border-radius: 3.5mm;
    }

    .kartu .nama {
        position: absolute;
        top: 55.5mm;
        left: 3mm;
        width: 48mm;
        text-align: center;
        font-size: 9.5pt;
        font-weight: bold;
        color: #ffffff;
        line-height: 1.15;
    }

    .kartu .profesi {
        position: absolute;
        top: 65.3mm;
        left: 4mm;
        width: 46mm;
        text-align: center;
        font-size: 6.5pt;
        font-weight: bold;
        color: #ffffff;
    }

    .kartu .qr-wrap {
        position: absolute;
        top: 69.5mm;
        left: 20.25mm;
        width: 13.5mm;
        height: 13.5mm;
    }

    .kartu .qr-wrap img {
        width: 13.5mm;
        height: 13.5mm;
    }
</style>
