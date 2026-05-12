<?php

namespace App\Http\Controllers;

use App\Models\AuthorizerRole;
use Illuminate\Http\Request;

class AuthorizerRoleController extends Controller
{
    public function index()
    {
        $roles = AuthorizerRole::orderBy('display_order')->orderBy('name')->get();

        return view('authorizer_roles.index', compact('roles'));
    }

    public function edit(AuthorizerRole $authorizerRole)
    {
        return view('authorizer_roles.edit', compact('authorizerRole'));
    }

    public function update(Request $request, AuthorizerRole $authorizerRole)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'approval_limit' => ['nullable', 'numeric', 'min:0'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'matrix_sheet' => ['nullable', 'string', 'max:100'],
            'matrix_reference' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $authorizerRole->update([
            'name' => $data['name'],
            'approval_limit' => $data['approval_limit'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'matrix_sheet' => $data['matrix_sheet'] ?? null,
            'matrix_reference' => $data['matrix_reference'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('authorizer-roles.index')
            ->with('success', 'Rol autorizador actualizado correctamente.');
    }
}
