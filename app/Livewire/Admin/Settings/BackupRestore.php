<?php

namespace App\Livewire\Admin\Settings;

use App\Services\System\DatabaseBackupService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Backup & restore database dari admin web. Gate: settings.manage.
 * Restore MENIMPA seluruh database — dilindungi konfirmasi ketik "PULIHKAN".
 */
#[Layout('components.layouts.app')]
class BackupRestore extends Component
{
    use WithFileUploads;

    public $dumpFile = null;

    public string $confirmText = '';

    public ?string $status = null;

    public ?string $error = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.manage'), 403);
    }

    public function restore(DatabaseBackupService $backup): void
    {
        abort_unless(auth()->user()->can('settings.manage'), 403);
        abort_unless($backup->supported(), 404);

        $this->reset(['status', 'error']);

        $this->validate([
            'dumpFile' => ['required', 'file', 'max:102400'],
            'confirmText' => ['required', 'in:PULIHKAN'],
        ], [
            'confirmText.in' => 'Ketik PULIHKAN (huruf besar semua) untuk melanjutkan.',
        ]);

        $original = strtolower($this->dumpFile->getClientOriginalName());

        if (! str_ends_with($original, '.sql') && ! str_ends_with($original, '.sql.gz') && ! str_ends_with($original, '.gz')) {
            $this->addError('dumpFile', 'File harus berupa .sql atau .sql.gz hasil backup.');

            return;
        }

        set_time_limit(600);

        try {
            $backup->restore($this->dumpFile->getRealPath(), str_ends_with($original, '.gz'));
            $this->reset(['dumpFile', 'confirmText']);
            $this->status = 'Restore selesai. Seluruh database telah diganti dengan isi backup.';
        } catch (\Throwable $e) {
            report($e);
            $this->error = 'Restore gagal: '.$e->getMessage();
        }
    }

    public function render(DatabaseBackupService $backup)
    {
        return view('livewire.admin.settings.backup-restore', [
            'supported' => $backup->supported(),
        ]);
    }
}
