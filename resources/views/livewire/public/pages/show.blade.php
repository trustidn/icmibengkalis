<div>
    <x-public.page-header :title="$page->title" />

    <div class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        @if ($page->featuredImageUrl())
            <img src="{{ $page->featuredImageUrl() }}" alt="{{ $page->title }}" class="max-w-full h-auto mx-auto rounded-xl mb-10" />
        @endif

        <div class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed [&_a]:text-primary [&_a]:underline [&_img]:rounded-xl [&_img]:my-6 [&_iframe]:w-full [&_iframe]:aspect-video [&_iframe]:rounded-xl [&_iframe]:my-6 [&_h2]:font-headline-md [&_h2]:text-headline-md [&_h2]:text-on-surface [&_h2]:mt-8 [&_h2]:mb-3 [&_h3]:font-bold [&_h3]:text-on-surface [&_h3]:mt-6 [&_h3]:mb-2 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_blockquote]:border-l-4 [&_blockquote]:border-primary-container [&_blockquote]:pl-4 [&_blockquote]:italic [&_p]:mb-4">
            {!! \App\Support\Html::display($page->body) !!}
        </div>
    </div>
</div>
