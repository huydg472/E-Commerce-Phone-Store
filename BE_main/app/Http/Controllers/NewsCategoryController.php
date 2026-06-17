<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsCategoryRequest;
use App\Http\Requests\UpdateNewsCategoryRequest;
use App\Models\NewsCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsCategoryController extends Controller
{
    public function publicIndex(): JsonResponse
    {
        $categories = NewsCategory::query()
            ->where('status', 'active')
            ->withCount(['newsPosts as posts_count' => fn ($query) => $query->where('status', 'published')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $categories,
        ]);
    }

    public function publicShowBySlug(string $slug): JsonResponse
    {
        $category = NewsCategory::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->withCount(['newsPosts as posts_count' => fn ($query) => $query->where('status', 'published')])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => $category,
        ]);
    }

    public function adminIndex(): JsonResponse
    {
        $this->authorizeAction(request(), 'news_categories.view');

        $categories = NewsCategory::query()
            ->withCount(['newsPosts as posts_count'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $categories,
        ]);
    }

    public function store(StoreNewsCategoryRequest $request): JsonResponse
    {
        $this->authorizeAction($request, 'news_categories.create');

        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $category = NewsCategory::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Tạo danh mục tin tức thành công.',
            'data' => $category,
        ], 201);
    }

    public function show(NewsCategory $newsCategory): JsonResponse
    {
        $this->authorizeAction(request(), 'news_categories.view');

        $newsCategory->loadCount(['newsPosts as posts_count']);

        return response()->json([
            'status' => true,
            'data' => $newsCategory,
        ]);
    }

    public function update(UpdateNewsCategoryRequest $request, NewsCategory $newsCategory): JsonResponse
    {
        $this->authorizeAction($request, 'news_categories.update');

        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $newsCategory->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật danh mục tin tức thành công.',
            'data' => $newsCategory->fresh()->loadCount(['newsPosts as posts_count']),
        ]);
    }

    public function destroy(NewsCategory $newsCategory): JsonResponse
    {
        $this->authorizeAction(request(), 'news_categories.delete');

        $newsCategory->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa danh mục tin tức thành công.',
        ]);
    }

    public function toggleStatus(NewsCategory $newsCategory): JsonResponse
    {
        $this->authorizeAction(request(), 'news_categories.update');

        $newsCategory->update([
            'status' => $newsCategory->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật trạng thái danh mục tin tức thành công.',
            'data' => $newsCategory->fresh()->loadCount(['newsPosts as posts_count']),
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
