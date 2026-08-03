<?php

namespace App\Services\Publishing;

use App\Enums\PostStatus;
use App\Events\PostPublished;
use App\Models\Post;
use App\Models\PostRevision;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublishingService
{
    public function create(array $data, int $authorId, array $tagIds = []): Post
    {
        $post = new Post($data);
        $post->author_id = $authorId;
        $post->status = PostStatus::Draft;
        $post->save();

        if ($tagIds !== []) {
            $post->tags()->sync($tagIds);
        }

        return $post;
    }

    public function update(Post $post, array $data, array $tagIds = [], ?int $editedBy = null): Post
    {
        PostRevision::create([
            'post_id' => $post->id,
            'edited_by' => $editedBy ?? $post->author_id,
            'snapshot' => $post->only(['type', 'title', 'excerpt', 'body', 'post_category_id']),
        ]);

        $post->fill($data);
        $post->save();
        $post->tags()->sync($tagIds);

        if ($post->status === PostStatus::Published) {
            Cache::forget("public.post.{$post->slug}");
        }

        return $post;
    }

    public function submitForReview(Post $post): Post
    {
        if (! $post->status->canTransitionTo(PostStatus::InReview)) {
            throw new \DomainException("Post berstatus {$post->status->value} tidak dapat diajukan review.");
        }

        $post->status = PostStatus::InReview;
        $post->save();

        return $post;
    }

    /**
     * Terbitkan langsung tanpa antrean review (anggota mengirim Berita/Artikel sendiri).
     * Tetap melalui transisi status yang sah: Draft -> InReview -> Published.
     */
    public function publishImmediately(Post $post): Post
    {
        $this->submitForReview($post);

        $post->status = PostStatus::Published;
        $post->published_at = now();
        $post->save();

        PostPublished::dispatch($post);

        return $post;
    }

    public function revise(Post $post): Post
    {
        if (! $post->status->canTransitionTo(PostStatus::Draft)) {
            throw new \DomainException("Post berstatus {$post->status->value} tidak dapat direvisi.");
        }

        $post->status = PostStatus::Draft;
        $post->save();

        return $post;
    }

    public function approve(Post $post, int $reviewerId, ?string $publishAt = null): Post
    {
        if (! $post->status->canTransitionTo(PostStatus::Published)) {
            throw new \DomainException("Post berstatus {$post->status->value} tidak dapat disetujui.");
        }

        $post->status = PostStatus::Published;
        $post->reviewed_by = $reviewerId;
        $post->review_note = null;
        $post->published_at = $publishAt ? Carbon::parse($publishAt) : now();
        $post->save();

        if ($post->published_at->lessThanOrEqualTo(now())) {
            PostPublished::dispatch($post);
        }

        return $post;
    }

    public function reject(Post $post, int $reviewerId, string $note): Post
    {
        if (! $post->status->canTransitionTo(PostStatus::Rejected)) {
            throw new \DomainException("Post berstatus {$post->status->value} tidak dapat ditolak.");
        }

        $post->status = PostStatus::Rejected;
        $post->reviewed_by = $reviewerId;
        $post->review_note = $note;
        $post->save();

        return $post;
    }

    public function archive(Post $post): Post
    {
        if (! $post->status->canTransitionTo(PostStatus::Archived)) {
            throw new \DomainException("Post berstatus {$post->status->value} tidak dapat diarsipkan.");
        }

        $post->status = PostStatus::Archived;
        $post->save();

        Cache::forget("public.post.{$post->slug}");

        return $post;
    }

    public function findPublished(string $slug): ?Post
    {
        return Cache::remember("public.post.{$slug}", now()->addMinutes(10), function () use ($slug) {
            return Post::query()
                ->where('slug', $slug)
                ->where('status', PostStatus::Published)
                ->where('published_at', '<=', now())
                ->with(['category', 'tags', 'author.member.media', 'media'])
                ->first();
        });
    }

    public function paginatePublished(?string $categorySlug = null, ?string $tagSlug = null, ?string $search = null, int $perPage = 9): LengthAwarePaginator
    {
        $constrain = fn ($query) => $query
            ->where('status', PostStatus::Published)
            ->where('published_at', '<=', now())
            ->when($categorySlug, fn ($q) => $q->whereHas('category', fn ($q2) => $q2->where('slug', $categorySlug)))
            ->when($tagSlug, fn ($q) => $q->whereHas('tags', fn ($q2) => $q2->where('slug', $tagSlug)))
            ->with(['category', 'media', 'author.member.media']);

        if ($search) {
            return Post::search($search)->query($constrain)->paginate($perPage);
        }

        return $constrain(Post::query())->latest('published_at')->paginate($perPage);
    }

    public function incrementViewCount(Post $post): void
    {
        DB::table('posts')->where('id', $post->id)->increment('view_count');
    }
}
