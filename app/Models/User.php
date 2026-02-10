<?php

namespace App\Models;

use App\Enums\UserStatus;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'matricula',
        'password',
        'setor_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    // --- Filament ---
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === UserStatus::Ativo;
    }

    // --- Relationships ---
    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
    }

    public function setoresGerenciados(): BelongsToMany
    {
        return $this->belongsToMany(Setor::class, 'setor_user')
                     ->withTimestamps();
    }

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class, 'responsavel_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }

    // --- Helpers ---
    public function isAdmin(): bool
    {
        return $this->hasRole(['admin', 'super_admin']);
    }

    public function isGerente(): bool
    {
        return $this->hasRole('gerente');
    }
}
