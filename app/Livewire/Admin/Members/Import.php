<?php

namespace App\Livewire\Admin\Members;

use App\Models\Member;
use App\Services\Membership\MemberImportService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Import extends Component
{
    use WithFileUploads;

    public $file = null;

    public ?int $imported = null;

    /** @var array<int, string> */
    public array $errors_list = [];

    public function mount(): void
    {
        $this->authorize('import', Member::class);
    }

    public function import(MemberImportService $importer): void
    {
        $this->authorize('import', Member::class);

        $this->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $result = $importer->import($this->file);

        $this->imported = $result->imported;
        $this->errors_list = $result->errors;
        $this->reset('file');
    }

    public function render()
    {
        return view('livewire.admin.members.import');
    }
}
