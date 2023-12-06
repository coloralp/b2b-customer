<?php

namespace App\Services;

use Doctrine\DBAL\Schema\Schema;
use Illuminate\Pagination\Paginator;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginatorService
{

    public static function getPaginatorData(LengthAwarePaginator $paginator, $perPage = 0, $allDataCount = 0): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'next_url' => $paginator->nextPageUrl(),
            'per_page' => $paginator->perPage(),
            'total_items' => $allDataCount
        ];
    }

    public static function simplePaginate(Paginator $paginator, $perPage = 0, $allDataCount = 0): array
    {
        $data = $paginator->toArray();

        unset($data['data']);


        if ($perPage != 0 && $allDataCount != 0) {
            $data['last_page'] = ($allDataCount / $perPage);

            if (is_float($data['last_page'])) {
                $data['last_page'] = ((int)$data['last_page']) + 1;
            }
        }
        $data['total_items'] = $allDataCount;
        return $data;
    }

    public static function manuelPaginationInfo($currentPage, $perPage, $allDataCount)
    {
        if ($perPage != 0 && $allDataCount != 0) {
            $lastPage = ($allDataCount / $perPage);

            if (is_float($lastPage)) {
                $lastPage = ((int)$lastPage) + 1;
            }
        }

        return [
            'current_page' => $currentPage,
            'last_page' => $lastPage ?? null,
            'per_page' => $perPage,
            'all_data' => $allDataCount
        ];
    }
}
