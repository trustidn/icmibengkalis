@props(['url', 'title' => '', 'description' => '', 'hashtags' => [], 'image' => null])

@php
    // Hashtag: "ekonomi syariah" -> #EkonomiSyariah (tanpa spasi).
    $tagar = collect($hashtags)->map(fn ($t) => str($t)->studly()->toString())->filter()->values();
    $captionShare = trim($title.($description ? "\n\n".$description : '')
        .($tagar->isNotEmpty() ? "\n\n".$tagar->map(fn ($t) => '#'.$t)->implode(' ') : '')
        ."\n\n".$url);

    $encodedUrl = urlencode($url);
    $encodedTitle = urlencode($title);
@endphp

<div class="flex flex-wrap items-center gap-2"
     x-data="{
        tersalin: false,
        pesanApp: '',
        tandaiSalin() { this.tersalin = true; setTimeout(() => this.tersalin = false, 2000); },
        salinTeks(teks) {
            if (navigator.clipboard?.writeText) return navigator.clipboard.writeText(teks);
            const t = document.createElement('textarea');
            t.value = teks; document.body.appendChild(t); t.select();
            document.execCommand('copy'); document.body.removeChild(t);
            return Promise.resolve();
        },
        salin() { this.salinTeks(@js($url)).then(() => this.tandaiSalin()); },
        async bagikanApp(namaApp) {
            const caption = @js($captionShare);
            const gambar = @js($image);
            // Web Share API (HP): buka share sheet OS — pengguna pilih Instagram/TikTok,
            // gambar utama ikut terunggah sebagai berkas.
            try {
                if (navigator.share) {
                    let data = { title: @js($title), text: caption };
                    if (gambar && navigator.canShare) {
                        const blob = await fetch(gambar).then(r => r.blob());
                        const file = new File([blob], 'artikel.jpg', { type: blob.type || 'image/jpeg' });
                        if (navigator.canShare({ files: [file] })) data = { ...data, files: [file] };
                    }
                    await navigator.share(data);
                    return;
                }
            } catch (e) { if (e.name === 'AbortError') return; }
            // Fallback desktop: salin caption + buka gambar untuk diunduh manual.
            await this.salinTeks(caption);
            this.pesanApp = 'Caption tersalin — tempel di ' + namaApp + '.';
            if (gambar) window.open(gambar, '_blank');
            setTimeout(() => this.pesanApp = '', 4000);
        }
     }">
    <span class="font-label-lg text-label-lg text-on-surface-variant mr-1">Bagikan:</span>

    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}&quote={{ urlencode($title.($description ? ' — '.$description : '')) }}" target="_blank" rel="noopener"
       aria-label="Bagikan ke Facebook" title="Bagikan ke Facebook"
       class="flex h-9 w-9 items-center justify-center rounded-full border border-outline-variant/40 bg-white text-on-surface-variant transition-colors hover:border-primary hover:text-primary">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M13.5 21.9v-8.4h2.8l.4-3.3h-3.2V8.1c0-1 .3-1.6 1.7-1.6h1.6V3.6c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.4-4 4.1v2.6H7.6v3.3h2.8v8.4h3.1Z"/></svg>
    </a>

    <a href="https://twitter.com/intent/tweet?url={{ $encodedUrl }}&text={{ urlencode($title.($description ? ' — '.$description : '')) }}@if ($tagar->isNotEmpty())&hashtags={{ urlencode($tagar->implode(',')) }}@endif" target="_blank" rel="noopener"
       aria-label="Bagikan ke X (Twitter)" title="Bagikan ke X (Twitter)"
       class="flex h-9 w-9 items-center justify-center rounded-full border border-outline-variant/40 bg-white text-on-surface-variant transition-colors hover:border-primary hover:text-primary">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M18.9 2.1h3.4l-7.5 8.6 8.8 11.2h-6.9l-5.4-6.8-6.2 6.8H1.7l8-8.7L1.3 2.1h7.1l4.9 6.2 5.6-6.2Zm-1.2 17.8h1.9L7.3 4H5.2l12.5 15.9Z"/></svg>
    </a>

    <a href="https://wa.me/?text={{ urlencode($captionShare) }}" target="_blank" rel="noopener"
       aria-label="Bagikan ke WhatsApp" title="Bagikan ke WhatsApp"
       class="flex h-9 w-9 items-center justify-center rounded-full border border-outline-variant/40 bg-white text-on-surface-variant transition-colors hover:border-primary hover:text-primary">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.6-6.1c-.3-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.3-.6.8-.8 1-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.4-3c-.3-.4 0-.5.2-.7l.4-.6c.1-.2.1-.4 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2 0 1.3.9 2.5 1 2.7.1.2 1.8 2.8 4.4 3.9.6.3 1.1.4 1.5.5.6.2 1.2.2 1.6.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2l-.2-.4Z"/></svg>
    </a>

    <button type="button" x-on:click="bagikanApp('Instagram')"
            aria-label="Bagikan ke Instagram" title="Bagikan ke Instagram"
            class="flex h-9 w-9 items-center justify-center rounded-full border border-outline-variant/40 bg-white text-on-surface-variant transition-colors hover:border-primary hover:text-primary">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.2 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.2-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2Zm0 1.8c-3.1 0-3.5 0-4.8.1-1.1.1-1.5.2-1.8.3-.5.2-.8.4-1.1.7-.3.3-.5.6-.7 1.1-.1.3-.3.8-.3 1.8-.1 1.3-.1 1.7-.1 4.8s0 3.5.1 4.8c.1 1.1.2 1.5.3 1.8.2.5.4.8.7 1.1.3.3.6.5 1.1.7.3.1.8.3 1.8.3 1.3.1 1.7.1 4.8.1s3.5 0 4.8-.1c1.1-.1 1.5-.2 1.8-.3.5-.2.8-.4 1.1-.7.3-.3.5-.6.7-1.1.1-.3.3-.8.3-1.8.1-1.3.1-1.7.1-4.8s0-3.5-.1-4.8c-.1-1.1-.2-1.5-.3-1.8-.2-.5-.4-.8-.7-1.1-.3-.3-.6-.5-1.1-.7-.3-.1-.8-.3-1.8-.3-1.3-.1-1.7-.1-4.8-.1Zm0 3a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.8a3.2 3.2 0 1 0 0 6.4 3.2 3.2 0 0 0 0-6.4Zm5.2-3a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4Z"/></svg>
    </button>

    <button type="button" x-on:click="bagikanApp('TikTok')"
            aria-label="Bagikan ke TikTok" title="Bagikan ke TikTok"
            class="flex h-9 w-9 items-center justify-center rounded-full border border-outline-variant/40 bg-white text-on-surface-variant transition-colors hover:border-primary hover:text-primary">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M19.6 6.7a4.8 4.8 0 0 1-3.5-1.6 4.8 4.8 0 0 1-1.2-3.1h-3.2v13.2a2.9 2.9 0 1 1-2.1-2.8V9.1a6.1 6.1 0 1 0 4.3 5.8V8.9a8 8 0 0 0 4.7 1.5V7.2l-1-.5Z"/></svg>
    </button>

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

    <span x-show="pesanApp" x-cloak x-text="pesanApp" class="w-full text-sm font-medium text-primary"></span>
</div>
