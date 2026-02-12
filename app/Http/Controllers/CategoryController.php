<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
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
    public function store(StoreCategoryRequest $request): RedirectResponse
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
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
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

                return '
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" 
                            id="dropdownActions' . $row->id . '" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        Acciones
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownActions' . $row->id . '">
                        <li>
                            <a class="dropdown-item" href="' . $editUrl . '">
                                <i class="ti ti-edit me-2"></i>Editar
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="' . $deleteUrl . '" method="POST" class="d-inline js-delete-form">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="button" class="dropdown-item text-danger js-delete-btn"
                                        data-entity="' . e($row->name) . '">
                                    <i class="ti ti-trash me-2"></i>Eliminar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>';
            })
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }
}
