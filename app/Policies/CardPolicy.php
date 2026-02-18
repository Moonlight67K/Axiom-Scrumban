<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Card;
use App\Models\User;

class CardPolicy
{
    /**
     * Determine whether the user is an admin.
     */
    private function isAdmin(User $user): bool
    {
        return $user->email === 'admin@example.com';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Card $card): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        $board = $card->column->board;
        return $board->members()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Card $card): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // Only assigned user can update
        return $user->id === $card->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Card $card): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Card $card): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Card $card): bool
    {
        return false;
    }
}
