<?php

namespace App\Services\System;

use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Process\Process;

/**
 * Backup & restore seluruh database dari admin web (butuh binary mariadb-dump/
 * mariadb di container — terpasang via Dockerfile). Hanya untuk koneksi MySQL/
 * MariaDB; di SQLite (dev Herd) fitur dinonaktifkan dengan pesan.
 */
class DatabaseBackupService
{
    public function supported(): bool
    {
        return config('database.default') === 'mysql'
            || config('database.connections.'.config('database.default').'.driver') === 'mysql';
    }

    /** @return array{cmd: string[], env: array<string, string>} */
    private function connectionArgs(): array
    {
        $conn = config('database.connections.mysql');

        return [
            'cmd' => [
                '-h', (string) $conn['host'],
                '-P', (string) ($conn['port'] ?? 3306),
                '-u', (string) $conn['username'],
                (string) $conn['database'],
            ],
            // Password lewat env MYSQL_PWD agar tidak terlihat di daftar proses.
            'env' => ['MYSQL_PWD' => (string) $conn['password']],
        ];
    }

    /** Alirkan dump ter-gzip langsung ke output (dipakai streamDownload). */
    public function streamDump(): void
    {
        $args = $this->connectionArgs();

        $process = new Process(
            ['mariadb-dump', '--single-transaction', '--routines', '--triggers', ...$args['cmd']],
            null,
            $args['env'],
            null,
            600,
        );

        $gzip = deflate_init(ZLIB_ENCODING_GZIP);

        $process->run(function ($type, $buffer) use ($gzip) {
            if ($type === Process::OUT) {
                echo deflate_add($gzip, $buffer, ZLIB_NO_FLUSH);
                flush();
            }
        });

        if (! $process->isSuccessful()) {
            throw new RuntimeException('mariadb-dump gagal: '.trim($process->getErrorOutput()));
        }

        echo deflate_add($gzip, '', ZLIB_FINISH);
        flush();
    }

    /** Pulihkan database dari file .sql atau .sql.gz — MENIMPA seluruh data. */
    public function restore(string $path, bool $gzipped): void
    {
        $args = $this->connectionArgs();

        $input = $gzipped ? gzopen($path, 'rb') : fopen($path, 'rb');

        if (! $input) {
            throw new RuntimeException('File backup tidak bisa dibaca.');
        }

        $process = new Process(['mariadb', ...$args['cmd']], null, $args['env'], null, 600);
        $process->setInput(new class($input, $gzipped) implements \IteratorAggregate
        {
            public function __construct(private $handle, private bool $gz) {}

            public function getIterator(): \Generator
            {
                while (! ($this->gz ? gzeof($this->handle) : feof($this->handle))) {
                    yield $this->gz ? gzread($this->handle, 1024 * 512) : fread($this->handle, 1024 * 512);
                }
            }
        });

        $process->run();

        $gzipped ? gzclose($input) : fclose($input);

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Restore gagal: '.trim($process->getErrorOutput()));
        }

        // Data (termasuk tabel permission & cache) baru saja diganti total.
        Artisan::call('cache:clear');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
