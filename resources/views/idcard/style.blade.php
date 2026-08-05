{{-- Gaya kartu 54 x 85,6 mm (potret) — dipakai preview HTML dan PDF (dompdf). --}}
<style>
    .kartu {
        position: relative;
        width: 54mm;
        height: 85.6mm;
        overflow: hidden;
        background: #2E4200;
        font-family: DejaVu Sans, Arial, sans-serif;
    }

    .kartu .bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 54mm;
        height: 85.6mm;
    }

    .kartu .foto {
        position: absolute;
        top: 13mm;
        left: 16mm;
        width: 22mm;
        height: 22mm;
        border-radius: 11mm;
        border: 1.2mm solid #ffffff;
        background: #e1e3e4;
    }

    .kartu .foto img {
        width: 19.6mm;
        height: 19.6mm;
        border-radius: 9.8mm;
    }

    .kartu .panel {
        position: absolute;
        top: 36.5mm;
        left: 3mm;
        width: 46mm;
        padding: 1.3mm 1mm;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 2mm;
        text-align: center;
    }

    .kartu .nama {
        font-size: 8pt;
        font-weight: bold;
        color: #191C1D;
        line-height: 1.2;
    }

    .kartu .nia {
        font-size: 6pt;
        color: #444939;
        margin-top: 0.6mm;
    }

    .kartu .profesi {
        font-size: 6pt;
        color: #486800;
        font-weight: bold;
        margin-top: 0.3mm;
    }

    .kartu .acara {
        font-size: 5.5pt;
        font-weight: bold;
        color: #364E00;
        border-top: 0.3mm solid #C4C9B4;
        margin-top: 0.9mm;
        padding-top: 0.9mm;
        line-height: 1.25;
    }

    .kartu .qr-wrap {
        position: absolute;
        top: 63.5mm;
        left: 19mm;
        width: 16mm;
        height: 16mm;
        padding: 0.8mm;
        background: #ffffff;
        border-radius: 1.5mm;
    }

    .kartu .qr-wrap img {
        width: 14.4mm;
        height: 14.4mm;
    }

    .kartu .ket {
        position: absolute;
        top: 80.8mm;
        left: 3mm;
        width: 46mm;
        text-align: center;
        font-size: 4.8pt;
        color: #ffffff;
        background: rgba(0, 0, 0, 0.35);
        padding: 0.5mm 0;
        border-radius: 1mm;
    }
</style>
