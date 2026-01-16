<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Finance\PaymentReconciliationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * @tags Admin
 * 
 * Payment reconciliation
 */
class ReconciliationController extends Controller
{
    protected PaymentReconciliationService $reconciliationService;

    public function __construct(PaymentReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Get daily reconciliation report
     */
    public function daily(Request $request)
    {
        $date = $request->has('date') 
            ? Carbon::parse($request->date) 
            : now();

        $report = $this->reconciliationService->generateDailyReconciliation($date);

        return response()->json($report);
    }

    /**
     * Get unmatched payments
     */
    public function unmatchedPayments(Request $request)
    {
        $date = $request->has('date') 
            ? Carbon::parse($request->date) 
            : now();

        $unmatched = $this->reconciliationService->flagUnmatchedPayments($date);

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'unmatched_payments' => $unmatched,
            'count' => count($unmatched),
        ]);
    }
}
