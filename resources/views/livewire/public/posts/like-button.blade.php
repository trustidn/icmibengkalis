<button type="button" wire:click="toggle" wire:loading.attr="disabled"
        aria-label="{{ $liked ? 'Batalkan apresiasi' : 'Beri apresiasi' }}"
        class="flex h-9 items-center gap-1.5 rounded-full border px-3.5 font-label-lg text-label-lg transition-colors
               {{ $liked
                    ? 'border-primary bg-primary-container/25 text-primary'
                    : 'border-outline-variant/40 bg-white text-on-surface-variant hover:border-primary hover:text-primary' }}">
    <span class="material-symbols-outlined text-[18px] {{ $liked ? '[font-variation-settings:\'FILL\'_1]' : '' }}">favorite</span>
    <span>{{ $count > 0 ? $count : 'Apresiasi' }}</span>
</button>
