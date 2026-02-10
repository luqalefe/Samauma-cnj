<?php

namespace App\Policies;

use App\Models\Setor;
use App\Models\User;

class SetorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('setor.view');
    }

    public function view(User $user, Setor $setor): bool
    {
        return $user->can('setor.view');
    }

    public function create(User $user): bool
    {
        return $user->can('setor.create');
    }

    public function update(User $user, Setor $setor): bool
    {
        return $user->can('setor.update');
    }

    public function delete(User $user, Setor $setor): bool
    {
        return $user->can('setor.delete');
    }
}
