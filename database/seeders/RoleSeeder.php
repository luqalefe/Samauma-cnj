<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $gerente = Role::firstOrCreate(['name' => 'gerente']);
        $servidor = Role::firstOrCreate(['name' => 'servidor']);

        // Create permissions
        $permissions = [
            // Users
            'user.view', 'user.create', 'user.update', 'user.delete', 'user.approve',
            // Setores
            'setor.view', 'setor.create', 'setor.update', 'setor.delete',
            // Itens
            'item.view', 'item.create', 'item.update', 'item.delete',
            // Tarefas
            'tarefa.view', 'tarefa.create', 'tarefa.update', 'tarefa.delete', 'tarefa.concluir',
            // Chamados
            'chamado.view', 'chamado.create', 'chamado.respond',
            // Monitoramento
            'monitoramento.view', 'organograma.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions([
            'user.view', 'user.create', 'user.update', 'user.delete', 'user.approve',
            'setor.view', 'setor.create', 'setor.update', 'setor.delete',
            'item.view', 'item.create', 'item.update', 'item.delete',
            'tarefa.view', 'tarefa.create', 'tarefa.update', 'tarefa.delete', 'tarefa.concluir',
            'chamado.view', 'chamado.respond',
            'monitoramento.view', 'organograma.view',
        ]);

        $gerente->syncPermissions([
            'item.view', 'item.update',
            'tarefa.view', 'tarefa.create', 'tarefa.update', 'tarefa.concluir',
            'chamado.view', 'chamado.create', 'chamado.respond',
            'monitoramento.view',
        ]);

        $servidor->syncPermissions([
            'item.view',
            'tarefa.view', 'tarefa.concluir',
            'chamado.create',
        ]);
    }
}
