<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = Auth::user()?->categories()
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Auth::user()?->categories()->create($request->validated());

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        // Ensure user owns this category
        if ($category->user_id !== Auth::user()?->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'data' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        // Ensure user owns this category
        if ($category->user_id !== Auth::user()?->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $category->update($request->validated());

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Ensure user owns this category
        if ($category->user_id !== Auth::user()?->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ], 204);
    }
}
