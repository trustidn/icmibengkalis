{{-- Satu kartu. Variabel dari IdCardService::cardData(): nama, nia, profesi, foto, bg, qr, acara, tanggal. --}}
<div class="kartu">
    @if ($bg)
        <img class="bg" src="{{ $bg }}" alt="" />
    @endif

    <div class="acara">{{ $acara }}@if ($tanggal)<br>{{ $tanggal }}@endif</div>

    <div class="foto">
        @if ($foto)
            <img src="{{ $foto }}" alt="" />
        @endif
    </div>

    <div class="nama">{{ $nama }}</div>
    @if ($profesi)
        <div class="profesi">{{ $profesi }}</div>
    @endif

    <div class="qr-wrap">
        <img src="{{ $qr }}" alt="QR profil anggota" />
    </div>
</div>
