<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Usuários autenticados podem listar seus próprios pedidos
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Usuário pode ver seu próprio pedido ou admin pode ver qualquer pedido
        return $user->id === $order->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Qualquer usuário autenticado pode criar pedidos
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        // Apenas admin pode atualizar pedidos
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        // Apenas admin pode deletar pedidos
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the status of the model.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        // Apenas admin pode atualizar status de pedidos
        return $user->isAdmin();
    }
}
