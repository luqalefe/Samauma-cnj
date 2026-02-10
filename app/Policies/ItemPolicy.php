<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('item.view');
    }

    public function view(User $user, Item $item): bool
    {
        if ($user->can('item.view') && $user->isAdmin()) {
            return true;
        }

        // Gerentes see only their sector's items
        if ($user->isGerente()) {
            return $item->setor_id === $user->setor_id
                || $user->setoresGerenciados->contains($item->setor_id);
        }

        // Servidores see items where they have tasks
        return $item->tarefas()->where('responsavel_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('item.create');
    }

    public function update(User $user, Item $item): bool
    {
        if ($user->can('item.update') && $user->isAdmin()) {
            return true;
        }

        return $user->can('item.update')
            && ($item->setor_id === $user->setor_id
                || $user->setoresGerenciados->contains($item->setor_id));
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->can('item.delete');
    }
}
