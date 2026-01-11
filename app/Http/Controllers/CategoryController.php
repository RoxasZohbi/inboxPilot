<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories = Auth::user()?->categories()
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $priorityLevels = Category::getPriorityLevels();
        return view('categories.create', compact('priorityLevels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // Handle checkbox: if not present in request, set to false
        $data['archive_after_processing'] = $request->has('archive_after_processing');
        
        Auth::user()?->categories()->create($data);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        // Ensure user owns this category
        if ($category->user_id !== Auth::user()?->id) {
            abort(403);
        }

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        // Ensure user owns this category
        if ($category->user_id !== Auth::user()?->id) {
            abort(403);
        }

        $priorityLevels = Category::getPriorityLevels();
        return view('categories.edit', compact('category', 'priorityLevels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        // Ensure user owns this category
        if ($category->user_id !== Auth::user()?->id) {
            abort(403);
        }

        $data = $request->validated();
        
        // Handle checkbox: if not present in request, set to false
        $data['archive_after_processing'] = $request->has('archive_after_processing');

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Ensure user owns this category
        if ($category->user_id !== Auth::user()?->id) {
            abort(403);
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
