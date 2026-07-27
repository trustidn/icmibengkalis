@props(['label' => null])

{{--
    Editor rich-text (Quill 2, open source) dengan upload gambar & embed video.
    Pakai: <x-rich-editor wire:model="body" label="Isi" />
--}}
<div>
    @if ($label)
        <flux:label class="mb-2 block">{{ $label }}</flux:label>
    @endif

    <div wire:ignore
         x-data="richEditor({ content: @entangle($attributes->wire('model')), uploadUrl: '{{ route('editor.upload') }}' })"
         class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-600 dark:bg-zinc-800 [&_.ql-toolbar]:rounded-t-lg [&_.ql-toolbar]:border-0 [&_.ql-toolbar]:border-b [&_.ql-toolbar]:border-zinc-200 dark:[&_.ql-toolbar]:border-zinc-600 [&_.ql-container]:rounded-b-lg [&_.ql-container]:border-0 [&_.ql-editor]:min-h-64 [&_.ql-editor]:text-base">
        <div x-ref="editor"></div>
    </div>
</div>
