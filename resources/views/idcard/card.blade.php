{{-- Satu kartu. Variabel dari IdCardService::cardData(): nama, nia, profesi, foto, bg, qr, acara, tanggal. --}}
<div class="kartu">
    @if ($bg)
        <img class="bg" src="{{ $bg }}" alt="" />
    @endif

    <div class="foto">
        @if ($foto)
            <img src="{{ $foto }}" alt="" />
        @endif
    </div>

    <div class="panel">
        <div class="nama">{{ $nama }}</div>
        @if ($nia)
            <div class="nia">NIA: {{ $nia }}</div>
        @endif
        @if ($profesi)
            <div class="profesi">{{ $profesi }}</div>
        @endif
        <div class="acara">{{ $acara }}@if ($tanggal) &middot; {{ $tanggal }}@endif</div>
    </div>

    <div class="qr-wrap">
        <img src="{{ $qr }}" alt="QR profil anggota" />
    </div>

    <div class="ket">Pindai QR untuk verifikasi profil anggota</div>
</div>
