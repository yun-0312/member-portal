<?php

namespace App\Traits;

trait FiltersByPolicy {
    protected function filterByPolicy($items, $ability = 'view') {
        return $items->filter(function ($item) use ($ability) {
            return auth()->user()->can($ability, $item);
        })->values();
    }
}