<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Collection;

final class DataTable
{
    /** @return array{data: Collection, draw: int, recordsTotal: int, recordsFiltered: int} */
    public static function paginate(
        Builder $query,
        ServerRequestInterface $request,
        array $searchable,
        array $orderable
    ): array {
        $total = (clone $query)->count();
        $body = $request->getParsedBody();
        $queryParams = $request->getQueryParams();
        $input = is_array($body) ? $body : $queryParams;
        $search = $input['search']['value'] ?? '';
        if (is_string($search) && $search !== '') {
            $query->where(static function (Builder $query) use ($search, $searchable): void {
                foreach ($searchable as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($column, 'LIKE', '%' . $search . '%');
                }
            });
        }

        $filtered = (clone $query)->count();
        $order = $input['order'][0] ?? [];
        $columnIndex = (int) ($order['column'] ?? -1);
        $columns = $input['columns'] ?? [];
        $columnName = $columns[$columnIndex]['data'] ?? '';
        $columnName = is_string($columnName) ? $columnName : '';
        $direction = strtolower((string) ($order['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        if (isset($orderable[$columnName])) {
            $orderTarget = $orderable[$columnName];
            if (str_contains((string) $orderTarget, '+') || str_contains((string) $orderTarget, '(')) {
                $query->orderByRaw("{$orderTarget} {$direction}");
            } else {
                $query->orderBy($orderTarget, $direction);
            }
        } elseif (in_array($columnName, $orderable, true)) {
            $query->orderBy($columnName, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $start = max(0, (int) ($input['start'] ?? 0));
        $length = (int) ($input['length'] ?? 10);
        $length = $length < 1 ? 100 : min($length, 100);

        return [
            'data' => $query->offset($start)->limit($length)->get(),
            'draw' => max(0, (int) ($input['draw'] ?? 0)),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
        ];
    }
}
