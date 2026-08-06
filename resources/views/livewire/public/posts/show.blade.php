<div>
    <div class="relative bg-gradient-to-b from-white to-surface-container-low border-b border-outline-variant/20 px-margin-mobile md:px-margin-desktop pt-16 pb-12 md:pt-24 md:pb-16 overflow-hidden">
        <div class="absolute inset-0 hero-pattern -z-10"></div>
        <div class="max-w-3xl mx-auto">
            <span class="text-secondary font-bold text-[12px] uppercase tracking-widest">{{ $post->category?->name ?? $post->type->label() }}</span>
            <h1 class="capitalize mt-3 font-headline-lg text-headline-lg md:font-display-lg md:text-display-lg text-on-surface leading-tight">{{ $post->title }}</h1>
            <div class="mt-6 flex flex-wrap items-center gap-x-8 gap-y-3">
                <x-public.author-chip :user="$post->author" class="hover:opacity-80 transition-opacity" />
                <span class="flex items-center gap-2 font-label-lg text-label-lg text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">calendar_today</span> {{ $post->published_at?->translatedFormat('d F Y') }}</span>
            </div>

            <div class="mt-6">
                <x-public.share-buttons :url="route('posts.show', $post->slug)" :title="$post->title" />
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        @if ($post->featuredImageUrl())
            <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="max-w-full h-auto mx-auto rounded-xl mb-10" />
        @else
            <x-public.image-placeholder icon="article" class="aspect-[21/9] w-full mb-10" />
        @endif

        <div class="rich-content font-body-lg text-body-lg text-on-surface-variant leading-relaxed [&_a]:text-primary [&_a]:underline [&_img]:rounded-xl [&_img]:my-6 [&_iframe]:w-full [&_iframe]:aspect-video [&_iframe]:rounded-xl [&_iframe]:my-6 [&_h2]:font-headline-md [&_h2]:text-headline-md [&_h2]:text-on-surface [&_h2]:mt-8 [&_h2]:mb-3 [&_h3]:font-bold [&_h3]:text-on-surface [&_h3]:mt-6 [&_h3]:mb-2 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_blockquote]:border-l-4 [&_blockquote]:border-primary-container [&_blockquote]:pl-4 [&_blockquote]:italic [&_p]:mb-4">
            {!! \App\Support\Html::display($post->body) !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <div class="mt-10 pt-8 border-t border-outline-variant/20 flex flex-wrap gap-2">
                @foreach ($post->tags as $tag)
                    <span class="bg-surface-container text-on-surface-variant px-4 py-1.5 rounded-full text-label-lg font-label-lg">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif

        <div class="mt-10">
            <a href="{{ route('posts.index') }}" wire:navigate class="text-primary font-bold flex items-center gap-2 hover:gap-3 transition-all w-fit">
                <span class="material-symbols-outlined text-[18px]">west</span> Kembali ke Berita
            </a>
        </div>
    </div>
</div>
