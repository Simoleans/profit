<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Seller;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $users = User::query()
            ->active() // Solo usuarios activos (status = 1)
            ->where('co_ven', '!=', Auth::user()->co_ven) // Excluir usuario en sesión
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('co_ven', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'co_ven' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:0,1,2',
        ]);

        try {
            $userName = $validated['name'];
            $coVen = $validated['co_ven'];

            // Validar según el rol
            if ($validated['rol'] == 2) {
                // Si es Supervisor, validar en tabla supervisor
                $supervisor = Supervisor::where('co_sup', $coVen)->first();

                if (!$supervisor) {
                    return back()->withErrors([
                        'co_ven' => 'El código de supervisor no existe en la base de datos.'
                    ]);
                }

                $userName = $supervisor->nombre_sup ?? $validated['name'];
            } else {
                // Si es Vendedor o Administrador, validar en tabla vendedores
                $seller = Seller::where('co_ven', $coVen)->first();

                if (!$seller) {
                    return back()->withErrors([
                        'co_ven' => 'El código de vendedor no existe en la base de datos.'
                    ]);
                }

                $userName = $seller->ven_des ?? $validated['name'];
            }

            User::create([
                'name' => $userName,
                'co_ven' => $coVen,
                'password' => bcrypt($validated['password']),
                'rol' => $validated['rol'],
                'status' => 1, // Usuario activo por defecto
            ]);

            return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');

        } catch (\Exception $e) {
            $roleText = $validated['rol'] == 2 ? 'supervisor' : 'vendedor';
            return back()->withErrors([
                'co_ven' => "Error al verificar el código de {$roleText}: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'rol' => 'required|in:0,1,2',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $updateData = [
            'rol' => $validated['rol'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // No permitir que el usuario se elimine a sí mismo
        if ($user->co_ven === Auth::user()->co_ven) {
            return redirect()->route('users.index')->withErrors([
                'error' => 'No puedes eliminarte a ti mismo.'
            ]);
        }

        // Soft delete: cambiar status a 0
        $user->update(['status' => 0]);

        return redirect()->route('users.index')->with('success', 'Usuario desactivado exitosamente.');
    }

    /**
     * Buscar vendedor por código
     */
    public function searchSeller(Request $request)
    {
        $coVen = $request->get('co_ven');

        if (!$coVen) {
            return response()->json(['error' => 'Código de vendedor requerido'], 400);
        }

        try {
            $seller = Seller::where('co_ven', $coVen)->first();

            if (!$seller) {
                return response()->json(['error' => 'Vendedor no encontrado'], 404);
            }

            return response()->json([
                'co_ven' => $seller->co_ven,
                'nombre' => $seller->ven_des ?? '', // Campo correcto del nombre
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al buscar vendedor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Buscar supervisor por código
     */
    public function searchSupervisor(Request $request)
    {
        $coSup = $request->get('co_sup');

        if (!$coSup) {
            return response()->json(['error' => 'Código de supervisor requerido'], 400);
        }

        try {
            $supervisor = Supervisor::where('co_sup', $coSup)->first();

            if (!$supervisor) {
                return response()->json(['error' => 'Supervisor no encontrado'], 404);
            }

            return response()->json([
                'co_sup' => $supervisor->co_sup,
                'nombre' => $supervisor->nombre_sup ?? '',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al buscar supervisor: ' . $e->getMessage()], 500);
        }
    }
}
