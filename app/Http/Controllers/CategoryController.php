<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables; // Asegúrate de tenerlo importado arriba


/**
 * CategoryController
 *
 * Controlador REST para CRUD de categorías.
 * Mantiene el flujo simple: index, create, store, edit, update, destroy.
 */
class CategoryController extends Controller
{
    /**
     * Lista de categorías con filtro básico por estado (opcional).
     */
    public function index(): View
    {
        // Paginación ligera para no saturar.
        $categories = Category::orderBy('name')->paginate(15);

        return view('categories.index', compact('categories'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create(): View
    {
        // Valores por defecto (is_active = true).
        $category = new Category(['is_active' => true]);

        return view('categories.create', compact('category'));
    }

    /**
     * Guarda una categoría nueva.
     */
    public function store(SaveCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Muestra el formulario de edición.
     * Route Model Binding inyecta el modelo automáticamente.
     */
    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Actualiza la categoría.
     */
    public function update(SaveCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Elimina la categoría.
     * Nota: si luego hay FK a cost_centers, podrías impedir borrar si está en uso.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return view('categories.show', compact('category'));
    }

    public function datatable()
    {
        $query = Category::query();

        return DataTables::of($query)
            ->editColumn('is_active', function ($row) {
                // Renderiza badge con color
                return $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', function ($row) {
                $editUrl = route('categories.edit', $row->id);
                $deleteUrl = route('categories.destroy', $row->id);

                return '<div class="d-flex justify-content-end gap-1">'
                    . '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary" title="Editar"><i class="ti ti-pencil"></i></a>'
                    . '<form action="' . $deleteUrl . '" method="POST" class="d-inline js-delete-form">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-btn" data-entity="' . e($row->name) . '" title="Eliminar"><i class="ti ti-trash"></i></button>'
                    . '</form>'
                    . '</div>';
            })
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }
}
