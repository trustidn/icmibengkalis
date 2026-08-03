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

    public string $excerpt = '';

    public string $body = '';

    public $featured_image = null;

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
            $this->excerpt = (string) $post->excerpt;
            $this->body = $post->body;
            $this->published_at = $post->published_at?->format('Y-m-d');
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
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        unset($validated['featured_image']);

        // MariaDB strict menolak '' untuk kolom tanggal; kosong berarti
        // "jangan ubah" — tanggal terisi otomatis saat terbit jika belum ada.
        if (blank($validated['published_at'] ?? null)) {
            unset($validated['published_at']);
        }

        if ($this->post) {
            if ($this->post->status === PostStatus::Rejected) {
                $publishing->revise($this->post);
            }

            $post = $publishing->update($this->post, $validated, [], auth()->id());
        } else {
            $post = $publishing->create($validated, auth()->id());
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

        $this->reset(['title', 'excerpt', 'body', 'featured_image', 'published_at']);
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
