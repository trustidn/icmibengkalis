{{-- Satu kartu. Variabel dari IdCardService::cardData(): nama, nia, foto, bg, qr, acara, tanggal. --}}
<div class="kartu">
    @if ($bg)
        <img class="bg" src="{{ $bg }}" alt="" />
    @endif

    @if ($nia)
        <div class="nia">{{ $nia }}</div>
    @endif

    <div class="acara">{{ $acara }}@if ($tanggal)<br>{{ $tanggal }}@endif</div>

    <div class="foto">
        @if ($foto)
            <img src="{{ $foto }}" alt="" />
        @endif
    </div>

    <div class="nama">{{ $nama }}</div>

    <div class="qr-wrap">
        <img src="{{ $qr }}" alt="QR profil anggota" />
    </div>
</div>
