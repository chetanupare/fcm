<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use App\Models\Ticket;
use App\Models\Job;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer')
            ->withCount(['devices', 'tickets', 'tickets as completed_tickets_count' => function ($q) {
                $q->where('status', 'completed');
            }]);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = User::where('role', 'customer')
            ->with(['devices.tickets', 'tickets.jobs.technician.user', 'tickets.jobs.quote', 'tickets.jobs.payments'])
            ->findOrFail($id);

        // Statistics
        $stats = [
            'total_devices' => $customer->devices->count(),
            'total_tickets' => $customer->tickets->count(),
            'completed_jobs' => $customer->tickets->flatMap->jobs->where('status', 'completed')->count(),
            'total_spent' => $customer->tickets->flatMap->jobs->flatMap->payments->where('status', 'completed')->sum('amount'),
            'active_jobs' => $customer->tickets->flatMap->jobs->whereNotIn('status', ['completed', 'cancelled'])->count(),
        ];

        // Job history
        $jobHistory = Job::whereHas('ticket', function ($q) use ($id) {
            $q->where('customer_id', $id);
        })
        ->with(['ticket.device', 'technician.user', 'quote', 'payments'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('admin.customers.show', compact('customer', 'stats', 'jobHistory'));
    }

    public function edit($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'language' => 'nullable|string|max:10',
            'currency_preference' => 'nullable|string|max:3',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully');
    }

    public function destroy($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        
        // Check if customer has active jobs
        $activeJobs = Job::whereHas('ticket', function ($q) use ($id) {
            $q->where('customer_id', $id);
        })->whereNotIn('status', ['completed', 'cancelled'])->count();

        if ($activeJobs > 0) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Cannot delete customer with active jobs');
        }

               // Log activity before deletion
               ActivityLog::log('deleted', $customer, $customer->toArray(), null, "Customer {$customer->name} deleted");
               
               $customer->delete();

               return redirect()->route('admin.customers.index')
                   ->with('success', 'Customer deleted successfully');
    }
}
