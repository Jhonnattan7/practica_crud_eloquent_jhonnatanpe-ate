<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->has('username'), function (Builder $query) use ($request) {
                $query->where('username', 'like', '%' . $request->input('username') . '%');
            })
            ->when($request->has('email'), function (Builder $query) use ($request) {
                $query->where('email', 'like', '%' . $request->input('email') . '%');
            })
            ->when($request->input('is_trashed') === 'true', function (Builder $query) {
                $query->onlyTrashed();
            })
            ->get();
        
        return UserResource::collection($users);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return UserResource::make($user);
    }

    /**
     * Update the specified resource in storage (PUT - actualización completa).
     */
    public function replace(Request $request, User $user)
    {
        // Validación completa - todos los campos son requeridos excepto los nullable
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'hiring_date' => ['nullable', 'date'],
            'dui' => ['nullable', 'string', 'regex:/^\d{8}-\d$/', 'unique:users,dui,' . $user->id],
            'phone_number' => ['nullable', 'string', 'regex:/^[0-9\-\+\(\)\s]+$/'],
            'birth_date' => ['nullable', 'date', 'before:today'],
        ]);
        
        $user->update($validated);
        
        return UserResource::make($user);
    }

    /**
     * Update the specified resource in storage (PATCH - actualización parcial).
     */
    public function update(Request $request, User $user)
    {
        // Validación con 'sometimes' - solo valida los campos enviados
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'lastname' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => ['sometimes', 'required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['sometimes', 'required', 'email', 'unique:users,email,' . $user->id],
            'hiring_date' => ['sometimes', 'nullable', 'date'],
            'dui' => ['sometimes', 'nullable', 'string', 'regex:/^\d{8}-\d$/', 'unique:users,dui,' . $user->id],
            'phone_number' => ['sometimes', 'nullable', 'string', 'regex:/^[0-9\-\+\(\)\s]+$/'],
            'birth_date' => ['sometimes', 'nullable', 'date', 'before:today'],
        ]);
        
        $user->update($validated);
        
        return UserResource::make($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'El usuario ha sido eliminado correctamente.'
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Str::random(8); // Le colocamos una contraseña por defecto
        
        // Si no se envía hiring_date, asignar la fecha actual
        if (!isset($data['hiring_date'])) {
            $data['hiring_date'] = now()->toDateString();
        }

        $user = User::create($data);
        
        return response()->json(UserResource::make($user), 201);
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->find($id);
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado entre los eliminados.'
            ], 404);
        }
        
        $user->restore();
        
        return response()->json([
            'message' => 'Usuario restaurado correctamente.',
            'data' => UserResource::make($user)
        ]);
    }

    
}

