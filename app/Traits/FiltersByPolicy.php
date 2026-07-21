<?php

namespace App\Traits;

trait FiltersByPolicy {
    protected function filterByPolicy($items, $ability = 'view')
    {
        $user = auth()->user();

        if (!$user) {
            return collect();
        }

        return $items
            ->filter(fn ($item) => $user->can($ability, $item))
            ->values();
    }
}