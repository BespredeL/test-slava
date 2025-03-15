<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataRow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RowController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $datesPaginator = DataRow::select('date')
            ->distinct()
            ->orderBy('date')
            ->paginate($perPage);

        $dates = $datesPaginator->pluck('date')->map(function ($date) {
            return $date->toDateString();
        })->toArray();

        $rows = DataRow::whereIn('date', $dates)
            ->orderBy('date')
            ->get()
            ->groupBy(function ($item) {
                return $item->date->toDateString();
            });

        $groups = [];
        foreach ($datesPaginator as $dateItem) {
            $dateString = $dateItem->date->toDateString();
            $groups[] = [
                'date'  => $dateString,
                'items' => isset($rows[$dateString]) ? $rows[$dateString]->toArray() : [],
            ];
        }

        return response()->json([
            'data'  => $groups,
            'meta'  => [
                'current_page' => $datesPaginator->currentPage(),
                'last_page'    => $datesPaginator->lastPage(),
                'per_page'     => $datesPaginator->perPage(),
                'total'        => $datesPaginator->total(),
            ],
            'links' => [
                'first' => $datesPaginator->url(1),
                'last'  => $datesPaginator->url($datesPaginator->lastPage()),
                'prev'  => $datesPaginator->previousPageUrl(),
                'next'  => $datesPaginator->nextPageUrl(),
            ],
        ]);
    }
}
