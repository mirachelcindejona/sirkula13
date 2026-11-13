<?php

namespace App\Support;

class Listing
{
    public const SIZES = [10, 15, 25, 50];

    public static function perPage(): int
    {
        $value = (int) request()->query('per_page', 10);

        return in_array($value, self::SIZES, true) ? $value : 10;
    }

    public static function term(): string
    {
        return trim((string) request()->query('q', ''));
    }

    public static function like(): ?string
    {
        $term = self::term();

        return $term === '' ? null : '%'.addcslashes($term, '%_\\').'%';
    }

    public static function oneOf(string $key, array $allowed): ?string
    {
        $value = (string) request()->query($key, '');

        return in_array($value, $allowed, true) ? $value : null;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<string, string>  $relations
     */
    public static function search(object $query, array $columns = [], array $relations = []): void
    {
        $like = self::like();
        if ($like === null || ($columns === [] && $relations === [])) {
            return;
        }

        $query->where(function ($inner) use ($columns, $relations, $like): void {
            $started = false;
            foreach ($columns as $column) {
                $started ? $inner->orWhere($column, 'like', $like) : $inner->where($column, 'like', $like);
                $started = true;
            }
            foreach ($relations as $relation => $column) {
                $method = $started ? 'orWhereHas' : 'whereHas';
                $inner->{$method}($relation, fn ($related) => $related->where($column, 'like', $like));
                $started = true;
            }
        });
    }
}
