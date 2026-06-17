<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsPostRequest;
use App\Http\Requests\UpdateNewsPostRequest;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsPostController extends Controller
{
    public function publicIndex(Request $request): JsonResponse
    {
        $query = NewsPost::query()
            ->where('status', 'published')
            ->with('category')
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($request->filled('category')) {
            $categorySlug = (string) $request->string('category');
            $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug));
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%' . $search . '%')
                    ->orWhere('excerpt', 'like', '%' . $search . '%');
            });
        }

        $posts = $query->get();

        return response()->json([
            'status' => true,
            'data' => $posts,
        ]);
    }

    public function publicShowBySlug(string $slug): JsonResponse
    {
        $post = NewsPost::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with('category')
            ->firstOrFail();

        $relatedPosts = NewsPost::query()
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->when($post->news_category_id, fn ($query) => $query->where('news_category_id', $post->news_category_id))
            ->with('category')
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $post->increment('views_count');

        return response()->json([
            'status' => true,
            'data' => [
                'post' => $post->fresh()->load('category'),
                'related_posts' => $relatedPosts,
            ],
        ]);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorizeAction($request, 'news_posts.view');

        $posts = NewsPost::query()
            ->with('category')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $posts,
        ]);
    }

    public function store(StoreNewsPostRequest $request): JsonResponse
    {
        $this->authorizeAction($request, 'news_posts.create');

        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['is_featured'] = $request->boolean('is_featured', false);
        $data['views_count'] = (int) ($data['views_count'] ?? 0);
        $data['news_category_id'] = $data['news_category_id'] ?: null;
        $data['published_at'] = $data['status'] === 'published'
            ? ($data['published_at'] ?? now())
            : null;

        $post = NewsPost::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Tạo bài viết thành công.',
            'data' => $post->load('category'),
        ], 201);
    }

    public function show(NewsPost $newsPost): JsonResponse
    {
        $this->authorizeAction(request(), 'news_posts.view');

        return response()->json([
            'status' => true,
            'data' => $newsPost->load('category'),
        ]);
    }

    public function update(UpdateNewsPostRequest $request, NewsPost $newsPost): JsonResponse
    {
        $this->authorizeAction($request, 'news_posts.update');

        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['is_featured'] = $request->boolean('is_featured', $newsPost->is_featured);
        $data['views_count'] = array_key_exists('views_count', $data) ? (int) $data['views_count'] : $newsPost->views_count;
        $data['news_category_id'] = $data['news_category_id'] ?: null;
        $data['published_at'] = $data['status'] === 'published'
            ? ($data['published_at'] ?? $newsPost->published_at ?? now())
            : null;

        $newsPost->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật bài viết thành công.',
            'data' => $newsPost->fresh()->load('category'),
        ]);
    }

    public function destroy(NewsPost $newsPost): JsonResponse
    {
        $this->authorizeAction(request(), 'news_posts.delete');

        $newsPost->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa bài viết thành công.',
        ]);
    }

    public function toggleStatus(NewsPost $newsPost): JsonResponse
    {
        $this->authorizeAction(request(), 'news_posts.update');

        $isPublished = $newsPost->status !== 'published';

        $newsPost->update([
            'status' => $isPublished ? 'published' : 'draft',
            'published_at' => $isPublished ? ($newsPost->published_at ?? now()) : null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật trạng thái bài viết thành công.',
            'data' => $newsPost->fresh()->load('category'),
        ]);
    }

    private function authorizeAction(Request $request, string $permission): void
    {
        $user = $request->user();

        if (!$user || !$user->hasPermission($permission)) {
            abort(403, 'Forbidden.');
        }
    }
}
