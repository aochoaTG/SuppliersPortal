<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ProfilePasswordController extends Controller
{
    /**
     * Actualizar la contraseña del usuario autenticado
     */
    public function update(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', Password::min(8)->letters()->numbers()],
                'new_password_confirmation' => ['required', 'string', 'same:new_password'],
            ], [
                'current_password.required' => 'La contraseña actual es requerida.',
                'new_password.required' => 'La nueva contraseña es requerida.',
                'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
                'new_password_confirmation.required' => 'La confirmación de contraseña es requerida.',
                'new_password_confirmation.same' => 'Las contraseñas no coinciden.',
            ]);

            $user = Auth::user();

            // Verificar que la contraseña actual sea correcta
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual no es correcta.'
                ], 400);
            }

            // Verificar que la nueva contraseña sea diferente a la actual
            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La nueva contraseña debe ser diferente a la actual.'
                ], 400);
            }

            // Actualizar la contraseña
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error al cambiar contraseña: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ha ocurrido un error interno. Por favor intenta más tarde.'
            ], 500);
        }
    }
}
