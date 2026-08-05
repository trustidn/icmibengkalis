<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card — {{ $event->name }}</title>
    @include('idcard.style')
    <style>
        @page { margin: 8mm; }
        body { margin: 0; }

        /* 3 kolom x 3 baris kartu per halaman A4 — tabel, bukan float
           (float di dompdf tidak andal untuk grid multi-baris). */
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td { width: 33.33%; padding: 2.2mm 0; text-align: center; }
        table.grid td .kartu { margin: 0 auto; }
        .halaman-baru { page-break-after: always; }
    </style>
</head>
<body>
    @foreach ($cards->chunk(9) as $halaman)
        <table class="grid @if (! $loop->last) halaman-baru @endif">
            @foreach ($halaman->chunk(3) as $baris)
                <tr>
                    @foreach ($baris as $card)
                        <td>@include('idcard.card', $card)</td>
                    @endforeach
                    @for ($i = $baris->count(); $i < 3; $i++)
                        <td></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    @endforeach
</body>
</html>
