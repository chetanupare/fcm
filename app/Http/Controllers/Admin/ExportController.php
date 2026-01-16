<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Component;
use App\Models\Job;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportController extends Controller
{
    public function customers(Request $request)
    {
        $format = $request->get('format', 'csv'); // csv, excel, pdf
        
        $customers = User::where('role', 'customer')
            ->withCount(['devices', 'tickets'])
            ->get();

        if ($format === 'csv') {
            return $this->exportCsv($customers, [
                'ID', 'Name', 'Email', 'Phone', 'Devices', 'Tickets', 'Registered'
            ], function ($customer) {
                return [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone ?? 'N/A',
                    $customer->devices_count,
                    $customer->tickets_count,
                    $customer->created_at->format('Y-m-d H:i:s'),
                ];
            }, 'customers');
        }

        if ($format === 'excel') {
            return Excel::download(new class($customers) implements FromCollection, WithHeadings, WithMapping {
                protected $customers;
                
                public function __construct($customers) {
                    $this->customers = $customers;
                }
                
                public function collection() {
                    return $this->customers;
                }
                
                public function headings(): array {
                    return ['ID', 'Name', 'Email', 'Phone', 'Devices', 'Tickets', 'Registered'];
                }
                
                public function map($customer): array {
                    return [
                        $customer->id,
                        $customer->name,
                        $customer->email,
                        $customer->phone ?? 'N/A',
                        $customer->devices_count,
                        $customer->tickets_count,
                        $customer->created_at->format('Y-m-d H:i:s'),
                    ];
                }
            }, 'customers_' . date('Y-m-d') . '.xlsx');
        }

        return back()->with('error', 'Invalid format');
    }

    public function components(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $components = Component::with(['category', 'brand'])->get();

        if ($format === 'csv') {
            return $this->exportCsv($components, [
                'ID', 'Name', 'SKU', 'Category', 'Brand', 'Stock', 'Cost Price', 'Selling Price', 'Profit Margin %'
            ], function ($component) {
                return [
                    $component->id,
                    $component->name,
                    $component->sku,
                    $component->category->name,
                    $component->brand->name ?? 'N/A',
                    $component->stock_quantity,
                    $component->cost_price,
                    $component->selling_price,
                    number_format($component->profit_margin, 2),
                ];
            }, 'components');
        }

        if ($format === 'excel') {
            return Excel::download(new class($components) implements FromCollection, WithHeadings, WithMapping {
                protected $components;
                
                public function __construct($components) {
                    $this->components = $components;
                }
                
                public function collection() {
                    return $this->components;
                }
                
                public function headings(): array {
                    return ['ID', 'Name', 'SKU', 'Category', 'Brand', 'Stock', 'Cost Price', 'Selling Price', 'Profit Margin %'];
                }
                
                public function map($component): array {
                    return [
                        $component->id,
                        $component->name,
                        $component->sku,
                        $component->category->name,
                        $component->brand->name ?? 'N/A',
                        $component->stock_quantity,
                        $component->cost_price,
                        $component->selling_price,
                        number_format($component->profit_margin, 2),
                    ];
                }
            }, 'components_' . date('Y-m-d') . '.xlsx');
        }

        return back()->with('error', 'Invalid format');
    }

    public function jobs(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $jobs = Job::with(['ticket.customer', 'ticket.device', 'technician.user', 'quote'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($format === 'csv') {
            return $this->exportCsv($jobs, [
                'ID', 'Customer', 'Device', 'Technician', 'Status', 'Quote Total', 'Payment Status', 'Created'
            ], function ($job) {
                return [
                    $job->id,
                    $job->ticket->customer->name,
                    $job->ticket->device->brand . ' ' . $job->ticket->device->device_type,
                    $job->technician->user->name ?? 'N/A',
                    $job->status,
                    $job->quote->total ?? 0,
                    $job->payments()->where('status', 'completed')->exists() ? 'Paid' : 'Unpaid',
                    $job->created_at->format('Y-m-d H:i:s'),
                ];
            }, 'jobs');
        }

        return back()->with('error', 'Invalid format');
    }

    protected function exportCsv($data, array $headers, callable $mapper, string $filename): StreamedResponse
    {
        return new StreamedResponse(function() use ($data, $headers, $mapper) {
            $output = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($output, $headers);
            
            // Data
            foreach ($data as $item) {
                fputcsv($output, $mapper($item));
            }
            
            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
