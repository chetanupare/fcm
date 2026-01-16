<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Finance\PaymentReconciliationService;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    protected $reconciliationService;

    public function __construct(PaymentReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $report = $this->reconciliationService->generateDailyReconciliation(\Carbon\Carbon::parse($date));
        
        return view('admin.reconciliation.index', [
            'report' => $report,
            'selectedDate' => $date,
        ]);
    }
}
