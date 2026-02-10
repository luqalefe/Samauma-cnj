<?php

namespace App\Policies;

use App\Models\Tarefa;
use App\Models\User;

class TarefaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tarefa.view');
    }

    public function view(User $user, Tarefa $tarefa): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isGerente()) {
            return $tarefa->item?->setor_id === $user->setor_id
                || $user->setoresGerenciados->contains($tarefa->item?->setor_id);
        }

        return $tarefa->responsavel_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('tarefa.create');
    }

    public function update(User $user, Tarefa $tarefa): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isGerente()) {
            return $tarefa->item?->setor_id === $user->setor_id
                || $user->setoresGerenciados->contains($tarefa->item?->setor_id);
        }

        return false;
    }

    public function delete(User $user, Tarefa $tarefa): bool
    {
        return $user->can('tarefa.delete');
    }

    public function concluir(User $user, Tarefa $tarefa): bool
    {
        if ($user->isAdmin() || $user->isGerente()) return true;
        return $tarefa->responsavel_id === $user->id;
    }
}
