<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function resolveDateRange(Request $request): array
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $singleDate = $request->input('tanggal');

        if ((!$dateFrom || !$dateTo) && $singleDate) {
            $dateFrom = $dateFrom ?: $singleDate;
            $dateTo = $dateTo ?: $singleDate;
        }

        $from = null;
        $to = null;

        if (!empty($dateFrom)) {
            try {
                $from = Carbon::parse($dateFrom)->startOfDay();
            } catch (\Throwable) {
                $from = null;
            }
        }

        if (!empty($dateTo)) {
            try {
                $to = Carbon::parse($dateTo)->endOfDay();
            } catch (\Throwable) {
                $to = null;
            }
        }

        if ($from && !$to) {
            $to = $from->copy()->endOfDay();
        }

        if ($to && !$from) {
            $from = $to->copy()->startOfDay();
        }

        if ($from && $to && $from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }
}
