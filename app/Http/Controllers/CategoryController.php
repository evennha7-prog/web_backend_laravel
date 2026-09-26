<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all categories for authenticated user with task counts.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::where('user_id', $request->user()->id)
            ->withCount('tasks')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $category = Category::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#0075ff',
            'icon' => $validated['icon'] ?? 'folder',
        ]);

        $category->tasks_count = 0;

        return response()->json([
            'status' => true,
            'message' => 'Category created successfully.',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $category = Category::where('user_id', $request->user()->id)
            ->withCount('tasks')
            ->find($id);

        if (!$category) {
            return $this->error('Category not found.', 404);
        }

        return response()->json([
            'status' => true,
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::where('user_id', $request->user()->id)->find($id);

        if (!$category) {
            return $this->error('Category not found.', 404);
        }

        $category->update($request->validated());
        $category->loadCount('tasks');

        return response()->json([
            'status' => true,
            'message' => 'Category updated successfully.',
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Remove the specified category safely with transaction.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $category = Category::where('user_id', $userId)->find($id);

        if (!$category) {
            return $this->error('Category not found.', 404);
        }

        // Atomically unlink associated tasks and remove the category
        DB::transaction(function () use ($userId, $id, $category) {
            Task::where('user_id', $userId)
                ->where('category_id', $id)
                ->update(['category_id' => null]);

            $category->delete();
        });

        return $this->success(null, 'Category deleted successfully.');
    }
}
