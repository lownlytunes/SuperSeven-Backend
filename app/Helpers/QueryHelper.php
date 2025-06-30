<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class QueryHelper
{
    protected const WITH_PIVOT = 3;

    public static function searchCallback(Builder $query, Request $request, array $targets)
    {
        if (!$request->has('search') || !isset($request->search['value'])) {
            return $query;
        }

        $searches = $request->search['value'];

        if (is_string($searches)) {
            foreach ($targets as $target) {
                if (!str_contains($target, '.')) {
                    $query->orWhere(str_replace('-', '.', $target), 'LIKE', '%' . $searches . '%');
                    continue;
                }

                $relationship = explode('.', $target);
                list($model, $column) = $relationship;

                if (count($relationship) === self::WITH_PIVOT) {
                    $pivotColumn = $relationship[self::WITH_PIVOT - 1];
                    $query->orWhereHas($model, function ($subquery) use ($column, $searches, $pivotColumn) {
                        $subquery->whereHas($column, function ($pivotQuery) use ($pivotColumn, $searches) {
                            $pivotQuery->where($pivotColumn, 'LIKE', '%' . $searches . '%');
                        });
                    });
                } else {
                    $query->orWhereHas($model, function ($subquery) use ($column, $searches) {
                        $subquery->where($column, 'LIKE', '%' . $searches . '%');
                    });
                }
            }

            return $query;
        }

        foreach ($searches as $search) {
            $sources = str_contains($search['key'], ',')
                ? explode(',', $search['key'])
                : [$search['key']];

            $query->where(function ($subquery) use ($search, $sources, $targets) {
                foreach ($targets as $target) {
                    if (!in_array($target, $sources)) {
                        continue;
                    }

                    $subquery->orWhere($target, 'LIKE', '%' . $search['input'] . '%');
                }

                return $subquery;
            });
        }

        return $query;
    }

    public static function filterCallback(Builder $query, Request $request, array $queries)
    {
        if (!$request->has('filters')) {
            return $query;
        }

        foreach ($request->filters as $requestKey => $requestFilter) {
            foreach ($queries as $key => $filter) {
                if ($requestKey !== $key || $requestFilter === 'false') {
                    continue;
                }

                if ($filter['type'] === 'relationship') {
                    if (!isset($filter['callback'])) {
                        $query->has($filter['model'], $filter['condition'], $filter['target']);
                        continue;
                    }

                    if (isset($filter['logical_operator']) && $filter['logical_operator'] === 'or') {
                        $query->orWhereHas($filter['model'], $filter['callback']);
                        continue;
                    }

                    $query->whereHas($filter['model'], $filter['callback']);
                    continue;
                }

                if ($filter['type'] === 'raw') {
                    $query->whereRaw($filter['condition']);
                    continue;
                }

                if ($filter['type'] === 'or') {
                    $query->orWhereRaw($filter['condition']);
                    continue;
                }

                $query->where($key, $filter['condition'], $filter['target']);
            }
        }

        return $query;
    }
}
