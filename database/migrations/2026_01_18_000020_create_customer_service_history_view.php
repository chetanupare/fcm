<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create a view for customer service history
        // Only create if invoices table exists, otherwise create a simpler version
        if (Schema::hasTable('invoices')) {
            DB::statement("
                CREATE OR REPLACE VIEW customer_service_history AS
                SELECT 
                    t.id as ticket_id,
                    t.customer_id,
                    t.created_at as service_date,
                    t.status as ticket_status,
                    d.brand,
                    d.device_type,
                    t.issue_description,
                    j.id as job_id,
                    j.status as job_status,
                    j.technician_id,
                    tech.user_id as technician_user_id,
                    q.total as quote_amount,
                    q.tax as quote_tax,
                    inv.total_amount as invoice_amount,
                    p.amount as payment_amount,
                    p.status as payment_status,
                    r.rating,
                    r.comment as rating_comment
                FROM tickets t
                LEFT JOIN devices d ON t.device_id = d.id
                LEFT JOIN service_jobs j ON t.id = j.ticket_id
                LEFT JOIN technicians tech ON j.technician_id = tech.id
                LEFT JOIN quotes q ON j.quote_id = q.id
                LEFT JOIN invoices inv ON j.id = inv.job_id
                LEFT JOIN payments p ON j.id = p.job_id
                LEFT JOIN ratings r ON j.id = r.job_id
                ORDER BY t.created_at DESC
            ");
        } else {
            // Create simpler version without invoices
            DB::statement("
                CREATE OR REPLACE VIEW customer_service_history AS
                SELECT 
                    t.id as ticket_id,
                    t.customer_id,
                    t.created_at as service_date,
                    t.status as ticket_status,
                    d.brand,
                    d.device_type,
                    t.issue_description,
                    j.id as job_id,
                    j.status as job_status,
                    j.technician_id,
                    tech.user_id as technician_user_id,
                    q.total as quote_amount,
                    q.tax as quote_tax,
                    NULL as invoice_amount,
                    NULL as quote_tax,
                    p.amount as payment_amount,
                    p.status as payment_status,
                    r.rating,
                    r.comment as rating_comment
                FROM tickets t
                LEFT JOIN devices d ON t.device_id = d.id
                LEFT JOIN service_jobs j ON t.id = j.ticket_id
                LEFT JOIN technicians tech ON j.technician_id = tech.id
                LEFT JOIN quotes q ON j.quote_id = q.id
                LEFT JOIN payments p ON j.id = p.job_id
                LEFT JOIN ratings r ON j.id = r.job_id
                ORDER BY t.created_at DESC
            ");
        }
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS customer_service_history");
    }
};
