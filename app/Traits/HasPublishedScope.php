<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasPublishedScope
{
    protected function getPublishStartColumn(): ?string
    {
        return 'published_at';
    }

    protected function getPublishEndColumn(): ?string
    {
        return null;
    }

    public function scopePublished(Builder $query): Builder
    {
        $start = $this->getPublishStartColumn();
        $end   = $this->getPublishEndColumn();

        return $query
            ->when($start, function ($q) use ($start) {
                $q->whereNotNull($start)
                    ->where($start, '<=', now());
            })
            ->when($end, function ($q) use ($end) {
                $q->where(function ($q2) use ($end) {
                    $q2->whereNull($end)
                        ->orWhere($end, '>=', now());
                });
            });
    }
}