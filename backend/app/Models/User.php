<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['alias', 'name', 'username', 'email', 'password', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * Guard usado por Spatie (las rutas API usan Sanctum, pero los roles se registran en 'web').
     */
    protected $guard_name = 'web';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    /**
     * Payload estándar de sesión: datos del usuario + roles + permisos.
     */
    public function toAuthPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alias' => $this->alias,
            'username' => $this->username,
            'email' => $this->email,
            'activo' => $this->activo,
            'roles' => $this->getRoleNames()->values(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
        ];
    }
}
