{{-- Gaya kartu 54 x 85,6 mm (potret) — dipakai preview HTML dan PDF (dompdf).
     Zona overlay (desain latar menyisakan area ini):
     - kanan atas   : NIA anggota (di bawah label "id anggota" milik template)
     - 14,5–23 mm   : nama kegiatan & tanggal
     - 23,5–54,5 mm : foto potret (kotak membulat 25 x 31 mm, rasio ~4:5)
     - 56–65,5 mm   : nama (putih tebal)
     - 66–79,5 mm   : QR transparan --}}
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

    .kartu .nia {
        position: absolute;
        top: 8mm;
        left: 26mm;
        width: 24mm;
        text-align: right;
        font-size: 6.5pt;
        font-weight: bold;
        color: #364E00;
    }

    .kartu .acara {
        position: absolute;
        top: 14.5mm;
        left: 4mm;
        width: 46mm;
        text-align: center;
        font-size: 6pt;
        font-weight: bold;
        color: #444939;
        line-height: 1.25;
    }

    .kartu .acara .tanggal {
        font-weight: normal;
    }

    .kartu .foto {
        position: absolute;
        top: 24.5mm;
        left: 14.5mm;
        width: 25mm;
        height: 30mm;
        border-radius: 3.5mm;
        background: #99C24D;
        overflow: hidden;
    }

    .kartu .foto img {
        width: 25mm;
        height: 30mm;
        border-radius: 3.5mm;
    }

    .kartu .nama {
        position: absolute;
        top: 56mm;
        left: 3mm;
        width: 48mm;
        text-align: center;
        font-size: 9.5pt;
        font-weight: bold;
        color: #ffffff;
        line-height: 1.15;
    }

    .kartu .qr-wrap {
        position: absolute;
        top: 66mm;
        left: 20.25mm;
        width: 13.5mm;
        height: 13.5mm;
    }

    .kartu .qr-wrap img {
        width: 13.5mm;
        height: 13.5mm;
    }
</style>
