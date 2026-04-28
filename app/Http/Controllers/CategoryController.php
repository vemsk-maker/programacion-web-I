<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::with('parent')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = Category::orderBy('name')->get();

        return view('categories.create', compact('parents'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Category $category): View
    {
        // Exclude self and its own children to prevent circular references
        $parents = Category::where('id', '!=', $category->id)
            ->whereNull('parent_id')
            ->orWhere(fn ($q) => $q->where('parent_id', '!=', $category->id)
                ->where('id', '!=', $category->id))
            ->orderBy('name')
            ->get();

        return view('categories.edit', compact('category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'No se puede eliminar: tiene subcategorías asociadas.');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'No se puede eliminar: tiene productos asociados.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
