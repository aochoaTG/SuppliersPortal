<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->get();
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
            'abbreviated' => ['required', 'string', 'max:10', 'unique:departments,abbreviated'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // ✅ Garantizar valores correctos
        $data['is_active'] = (bool) $request->input('is_active');
        $data['created_by'] = Auth::id();

        Department::create($data);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Departamento creado correctamente.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments', 'name')->ignore($department->id)
            ],
            'abbreviated' => [
                'required',
                'string',
                'max:10',
                Rule::unique('departments', 'abbreviated')->ignore($department->id)
            ],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // ✅ Garantizar que se guarde el valor 0 o 1 según el switch
        $data['is_active'] = (bool) $request->input('is_active');

        $department->update($data);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Departamento actualizado correctamente.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Departamento eliminado.');
    }
}
