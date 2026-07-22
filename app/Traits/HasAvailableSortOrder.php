<?php

namespace App\Traits;

trait HasAvailableSortOrder
{
    /**
     * 空いている一番若い sort_order の番号を取得する
     */
    public static function getNextAvailableSortOrder(): int
    {
        $orders = static::pluck('sort_order')->filter()->sort()->values()->all();

        $expected = 1;
        foreach ($orders as $order) {
            if ($order > $expected) {
                break;
            }
            if ($order == $expected) {
                $expected++;
            }
        }

        return $expected;
    }
}