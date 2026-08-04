<?php

namespace Tests\Unit;

use App\Support\Html;
use Tests\TestCase;

class HtmlDisplayTest extends TestCase
{
    public function test_tabel_dari_word_atau_excel_dipertahankan(): void
    {
        $body = '<p>Data kegiatan:</p>'
            .'<table style="width:100%"><thead><tr><th>Kegiatan</th><th>Tanggal</th></tr></thead>'
            .'<tbody><tr><td colspan="1">Rapat Perdana</td><td>28 Juli 2026</td></tr></tbody></table>';

        $hasil = (string) Html::display($body);

        $this->assertStringContainsString('<table', $hasil);
        $this->assertStringContainsString('<th>Kegiatan</th>', $hasil);
        $this->assertStringContainsString('<td>28 Juli 2026</td>', $hasil);
    }

    public function test_script_tetap_disaring_dari_dalam_tabel(): void
    {
        $body = '<table><tr><td onclick="alert(1)">Aman<script>alert(2)</script></td></tr></table>';

        $hasil = (string) Html::display($body);

        $this->assertStringNotContainsString('<script', $hasil);
        $this->assertStringNotContainsString('onclick', $hasil);
        $this->assertStringContainsString('Aman', $hasil);
    }
}
