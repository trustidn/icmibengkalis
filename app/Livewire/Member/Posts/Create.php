<?php

namespace App\Livewire\Member\Posts;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Services\Publishing\PublishingService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    /** Jenis yang boleh dipilih anggota — Siaran Pers tetap khusus pengurus/admin. */
    private const ALLOWED_TYPES = [PostType::Berita, PostType::Artikel, PostType::Opini];

    public ?Post $post = null;

    public string $type = 'opini';

    public string $title = '';

    public string $body = '';

    /** Kata kunci dipisah koma — jadi tag artikel & hashtag saat share. */
    public string $tags = '';

    public $featured_image = null;

    public string $featured_caption = '';

    public ?string $published_at = null;

    public bool $submitted = false;

    public function mount(?Post $post = null): void
    {
        if ($post?->exists) {
            $this->authorize('update', $post);
            abort_unless(auth()->id() === $post->author_id, 403);

            $this->post = $post;
            $this->type = $post->type->value;
            $this->title = $post->title;
            $this->body = $post->body;
            $this->featured_caption = (string) $post->featured_caption;
            $this->published_at = $post->published_at?->format('Y-m-d');
            $this->tags = $post->tags()->pluck('name')->implode(', ');
        } else {
            $this->authorize('create', Post::class);

            abort_unless(auth()->user()->member, 403, 'Hanya anggota terdaftar yang bisa mengirim tulisan.');
        }
    }

    public function submit(PublishingService $publishing): void
    {
        $validated = $this->validate([
            'type' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, self::ALLOWED_TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'tags' => ['nullable', 'string', 'max:500'],
            'featured_caption' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        unset($validated['featured_image'], $validated['tags']);
        $validated['featured_caption'] = $validated['featured_caption'] ?: null;

        // MariaDB strict menolak '' untuk kolom tanggal; kosong berarti
        // "jangan ubah" — tanggal terisi otomatis saat terbit jika belum ada.
        if (blank($validated['published_at'] ?? null)) {
            unset($validated['published_at']);
        }

        $tagIds = $publishing->tagIdsFromKeywords($this->tags);

        if ($this->post) {
            if ($this->post->status === PostStatus::Rejected) {
                $publishing->revise($this->post);
            }

            $post = $publishing->update($this->post, $validated, $tagIds, auth()->id());
        } else {
            $post = $publishing->create($validated, auth()->id(), $tagIds);
        }

        if ($this->featured_image) {
            $post->addMedia($this->featured_image->getRealPath())
                ->usingFileName('featured.'.$this->featured_image->getClientOriginalExtension())
                ->toMediaCollection('featured');
        }

        if ($post->status === PostStatus::Draft) {
            // Kebijakan: seluruh tulisan anggota (termasuk Opini) langsung tayang
            // tanpa antrean review — pengurus tetap dapat menyunting/menghapus
            // tulisan yang melanggar aturan lewat permission publishing.update/delete.
            $publishing->publishImmediately($post);
        }

        if ($this->post) {
            $this->redirectRoute('member.posts.index', navigate: true);

            return;
        }

        $this->reset(['title', 'body', 'tags', 'featured_image', 'featured_caption', 'published_at']);
        $this->type = 'opini';
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.member.posts.create', [
            'types' => self::ALLOWED_TYPES,
        ]);
    }
}
