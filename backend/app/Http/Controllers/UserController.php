<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const ADMIN = 'Administrador';

    public function index(Request $request)
    {
        $q = User::with('roles:id,name')
            ->when($request->search, function ($query, $s) {
                $query->where(function ($w) use ($s) {
                    $w->where('name', 'ilike', "%{$s}%")
                      ->orWhere('username', 'ilike', "%{$s}%")
                      ->orWhere('email', 'ilike', "%{$s}%");
                });
            })
            ->when($request->rol, fn ($query, $r) => $query->role($r))
            ->orderBy('name');

        return response()->json($q->get()->map(fn ($u) => $this->present($u)));
    }

    public function show(User $user)
    {
        return response()->json($this->present($user->load('roles')));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'alias' => 'nullable|string|max:10',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'activo' => 'boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'string|exists:roles,name',
        ]);

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'alias' => $data['alias'] ?? $this->aliasDe($data['name']),
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'activo' => $data['activo'] ?? true,
            ]);
            $user->syncRoles($data['roles']);

            return response()->json($this->present($user->load('roles')), 201);
        });
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'alias' => 'nullable|string|max:10',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'activo' => 'boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $quitaAdmin = $user->hasRole(self::ADMIN) && !in_array(self::ADMIN, $data['roles']);
        $desactiva = array_key_exists('activo', $data) && !$data['activo'];

        if ($user->id === $request->user()->id && ($quitaAdmin || $desactiva)) {
            abort(422, 'No puede quitarse el rol de Administrador ni desactivarse a sí mismo.');
        }
        if (($quitaAdmin || $desactiva) && $user->hasRole(self::ADMIN)) {
            $this->asegurarOtroAdmin($user);
        }

        return DB::transaction(function () use ($user, $data) {
            $user->fill([
                'name' => $data['name'],
                'alias' => $data['alias'] ?? $user->alias,
                'username' => $data['username'],
                'email' => $data['email'],
                'activo' => $data['activo'] ?? $user->activo,
            ]);
            if (!empty($data['password'])) {
                $user->password = $data['password'];
            }
            $user->save();
            $user->syncRoles($data['roles']);

            return response()->json($this->present($user->load('roles')));
        });
    }

    public function toggleActivo(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            abort(422, 'No puede desactivarse a sí mismo.');
        }
        if ($user->activo && $user->hasRole(self::ADMIN)) {
            $this->asegurarOtroAdmin($user);
        }
        $user->activo = !$user->activo;
        $user->save();

        if (!$user->activo) {
            $user->tokens()->delete(); // cierra sesiones abiertas
        }

        return response()->json($this->present($user->load('roles')));
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            abort(422, 'No puede eliminarse a sí mismo.');
        }
        if ($user->hasRole(self::ADMIN)) {
            $this->asegurarOtroAdmin($user);
        }
        $user->tokens()->delete();
        $user->delete(); // soft delete: conserva trazabilidad de cotizaciones

        return response()->json(['message' => 'Usuario eliminado.']);
    }

    public function roles()
    {
        return response()->json(
            Role::with('permissions:id,name')->orderBy('id')->get()->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'permissions' => $r->permissions->pluck('name'),
                'users_count' => $r->users()->count(),
            ])
        );
    }

    private function asegurarOtroAdmin(User $user): void
    {
        $otros = User::role(self::ADMIN)
            ->where('activo', true)
            ->where('id', '<>', $user->id)
            ->count();

        if ($otros === 0) {
            abort(422, 'Debe existir al menos un Administrador activo en el sistema.');
        }
    }

    private function aliasDe(string $name): string
    {
        $p = preg_split('/\s+/', trim($name));
        return strtoupper(mb_substr($p[0], 0, 1) . (isset($p[1]) ? mb_substr($p[1], 0, 1) : ''));
    }

    private function present(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'alias' => $u->alias,
            'username' => $u->username,
            'email' => $u->email,
            'activo' => (bool) $u->activo,
            'roles' => $u->roles->pluck('name')->values(),
            'created_at' => $u->created_at,
        ];
    }
}
