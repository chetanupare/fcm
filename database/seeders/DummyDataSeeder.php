<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Device;
use App\Models\Ticket;
use App\Models\Job;
use App\Models\Technician;
use App\Models\Service;
use App\Models\Quote;
use App\Models\Payment;
use App\Models\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if dummy data already exists
        if (User::where('role', 'customer')->count() > 10) {
            echo "Dummy data already exists. Skipping...\n";
            return;
        }

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@repair.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create Technicians
        $technicians = [];
        for ($i = 1; $i <= 5; $i++) {
            $techUser = User::firstOrCreate(
                ['email' => "tech{$i}@repair.com"],
                [
                    'name' => "Technician {$i}",
                    'password' => Hash::make('password'),
                    'role' => 'technician',
                    'phone' => '+123456789' . $i,
                ]
            );

            $technicians[] = Technician::firstOrCreate(
                ['user_id' => $techUser->id],
                [
                    'status' => $i <= 3 ? 'on_duty' : 'off_duty',
                    'commission_rate' => 15 + ($i * 2),
                    'latitude' => 28.6139 + (rand(-100, 100) / 1000),
                    'longitude' => 77.2090 + (rand(-100, 100) / 1000),
                ]
            );
        }

        // Create Customers
        $customers = [];
        $customerNames = ['John Doe', 'Jane Smith', 'Bob Johnson', 'Alice Williams', 'Charlie Brown', 'Diana Prince', 'Ethan Hunt', 'Fiona Apple'];
        
        for ($i = 0; $i < 20; $i++) {
            $name = $customerNames[$i % count($customerNames)] . ($i > 7 ? ' ' . ($i + 1) : '');
            $email = 'customer' . ($i + 1) . '@example.com';
            $customers[] = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'phone' => '+1234567' . str_pad($i, 4, '0', STR_PAD_LEFT),
                ]
            );
        }

        // Create Services
        $services = [
            ['name' => 'Screen Replacement', 'price' => 150.00, 'category' => 'repair', 'device_type' => 'laptop'],
            ['name' => 'Battery Replacement', 'price' => 80.00, 'category' => 'repair', 'device_type' => 'laptop'],
            ['name' => 'Motherboard Repair', 'price' => 200.00, 'category' => 'repair', 'device_type' => 'laptop'],
            ['name' => 'Diagnosis Fee', 'price' => 25.00, 'category' => 'diagnosis'],
            ['name' => 'Printer Toner Replacement', 'price' => 50.00, 'category' => 'repair', 'device_type' => 'other'],
            ['name' => 'Scanner Glass Replacement', 'price' => 60.00, 'category' => 'repair', 'device_type' => 'other'],
            ['name' => 'Home Visit Fee', 'price' => 30.00, 'category' => 'visit_fee'],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                ['name' => $service['name']],
                array_merge($service, ['is_active' => true])
            );
        }

        // Create Devices for Customers
        $devices = [];
        $deviceTypes = ['laptop', 'phone', 'desktop', 'tablet'];
        $brands = ['HP', 'Dell', 'Lenovo', 'Apple', 'Samsung', 'Canon', 'Epson'];

        foreach ($customers as $customer) {
            $deviceCount = rand(1, 3);
            for ($i = 0; $i < $deviceCount; $i++) {
                $serialNumber = 'SN' . Str::random(10);
                $devices[] = Device::firstOrCreate(
                    ['serial_number' => $serialNumber],
                    [
                        'customer_id' => $customer->id,
                        'device_type' => $deviceTypes[array_rand($deviceTypes)],
                        'brand' => $brands[array_rand($brands)],
                        'model' => 'Model ' . rand(1000, 9999),
                        'purchase_date' => now()->subMonths(rand(6, 36)),
                    ]
                );
            }
        }

        // Create Tickets and Jobs
        foreach ($devices as $device) {
            if (rand(1, 3) === 1) { // 33% chance
                $ticket = Ticket::create([
                    'customer_id' => $device->customer_id,
                    'device_id' => $device->id,
                    'issue_description' => 'Device not working properly. ' . ['Screen flickering', 'Battery not charging', 'Printer not printing', 'Scanner not scanning', 'Overheating'][array_rand([0,1,2,3,4])],
                    'status' => ['pending_triage', 'triage', 'accepted', 'completed'][array_rand([0,1,2,3])],
                    'priority' => ['low', 'medium', 'high'][array_rand([0,1,2])],
                    'triage_deadline_at' => now()->addMinutes(rand(1, 10)),
                    'is_warranty' => rand(1, 5) === 1,
                ]);

                if (in_array($ticket->status, ['accepted', 'completed'])) {
                    $tech = $technicians[array_rand($technicians)];
                    
                    $quote = Quote::create([
                        'ticket_id' => $ticket->id,
                        'subtotal' => rand(50, 300),
                        'tax' => 0,
                        'total' => rand(50, 300),
                    ]);

                    $job = Job::create([
                        'ticket_id' => $ticket->id,
                        'technician_id' => $tech->id,
                        'quote_id' => $quote->id,
                        'status' => $ticket->status === 'completed' ? 'completed' : 'in_progress',
                        'offer_accepted_at' => now()->subDays(rand(1, 7)),
                        'payment_received_at' => $ticket->status === 'completed' ? now()->subDays(rand(1, 3)) : null,
                    ]);

                    if ($ticket->status === 'completed') {
                        Payment::create([
                            'job_id' => $job->id,
                            'quote_id' => $quote->id,
                            'amount' => $quote->total,
                            'currency' => 'USD',
                            'method' => ['cash', 'cod', 'razorpay', 'stripe'][array_rand([0,1,2,3])],
                            'status' => 'completed',
                        ]);
                    }
                }
            }
        }

        // Add component usage
        $components = Component::all();
        $jobs = Job::where('status', 'completed')->get();
        
        foreach ($jobs->take(10) as $job) {
            $component = $components->random();
            $quantity = rand(1, 3);
            
            \DB::table('component_usage_logs')->insert([
                'component_id' => $component->id,
                'job_id' => $job->id,
                'quantity' => $quantity,
                'unit_cost' => $component->cost_price,
                'created_at' => $job->payment_received_at ?? now(),
                'updated_at' => $job->payment_received_at ?? now(),
            ]);

            $component->incrementUsage($quantity);
        }
    }
}
