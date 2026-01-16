<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $customerId = $request->user()->id;

        $history = DB::table('customer_service_history')
            ->where('customer_id', $customerId)
            ->orderBy('service_date', 'desc')
            ->paginate(20);

        return response()->json($history);
    }

    public function show($ticketId)
    {
        $history = DB::table('customer_service_history')
            ->where('customer_id', request()->user()->id)
            ->where('ticket_id', $ticketId)
            ->first();

        if (!$history) {
            return response()->json(['message' => 'Service history not found'], 404);
        }

        return response()->json($history);
    }
}
