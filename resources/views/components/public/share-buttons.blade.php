@props(['url', 'title' => ''])

@php
    $encodedUrl = urlencode($url);
    $encodedTitle = urlencode($title);
@endphp

<div class="flex flex-wrap items-center gap-2"
     x-data="{
        tersalin: false,
        salin() {
            const url = @js($url);
            const tandai = () => { this.tersalin = true; setTimeout(() => this.tersalin = false, 2000); };
            if (navigator.clipboard?.writeText) {
                navigator.clipboard.writeText(url).then(tandai);
            } else {
                const t = document.createElement('textarea');
                t.value = url; document.body.appendChild(t); t.select();
                document.execCommand('copy'); document.body.removeChild(t); tandai();
            }
        }
     }">
    <span class="font-label-lg text-label-lg text-on-surface-variant mr-1">Bagikan:</span>

    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}" target="_blank" rel="noopener"
       aria-label="Bagikan ke Facebook" title="Bagikan ke Facebook"
       class="flex h-9 w-9 items-center justify-center rounded-full border border-outline-variant/40 bg-white text-on-surface-variant transition-colors hover:border-primary hover:text-primary">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M13.5 21.9v-8.4h2.8l.4-3.3h-3.2V8.1c0-1 .3-1.6 1.7-1.6h1.6V3.6c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.4-4 4.1v2.6H7.6v3.3h2.8v8.4h3.1Z"/></svg>
    </a>

    <a href="https://twitter.com/intent/tweet?url={{ $encodedUrl }}&text={{ $encodedTitle }}" target="_blank" rel="noopener"
       aria-label="Bagikan ke X (Twitter)" title="Bagikan ke X (Twitter)"
       class="flex h-9 w-9 items-center justify-center rounded-full border border-outline-variant/40 bg-white text-on-surface-variant transition-colors hover:border-primary hover:text-primary">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M18.9 2.1h3.4l-7.5 8.6 8.8 11.2h-6.9l-5.4-6.8-6.2 6.8H1.7l8-8.7L1.3 2.1h7.1l4.9 6.2 5.6-6.2Zm-1.2 17.8h1.9L7.3 4H5.2l12.5 15.9Z"/></svg>
    </a>

    <a href="https://wa.me/?text={{ urlencode(trim($title.' '.$url)) }}" target="_blank" rel="noopener"
       aria-label="Bagikan ke WhatsApp" title="Bagikan ke WhatsApp"
       class="flex h-9 w-9 items-center justify-center rounded-full border border-outline-variant/40 bg-white text-on-surface-variant transition-colors hover:border-primary hover:text-primary">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.6-6.1c-.3-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.3-.6.8-.8 1-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.4-3c-.3-.4 0-.5.2-.7l.4-.6c.1-.2.1-.4 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2 0 1.3.9 2.5 1 2.7.1.2 1.8 2.8 4.4 3.9.6.3 1.1.4 1.5.5.6.2 1.2.2 1.6.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2l-.2-.4Z"/></svg>
    </a>

    <button type="button" x-on:click="salin"
            aria-label="Salin tautan" title="Salin tautan"
            class="flex h-9 items-center gap-1.5 rounded-full border border-outline-variant/40 bg-white px-3.5 font-label-lg text-label-lg text-on-surface-variant transition-colors hover:border-primary hover:text-primary">
        <template x-if="!tersalin">
            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]">link</span> Salin Tautan</span>
        </template>
        <template x-if="tersalin">
            <span class="flex items-center gap-1.5 text-primary"><span class="material-symbols-outlined text-[16px]">check</span> Tersalin!</span>
        </template>
    </button>
</div>
