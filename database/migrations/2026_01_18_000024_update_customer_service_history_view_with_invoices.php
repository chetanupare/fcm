<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update view to include invoices if invoices table exists
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
        }
    }

    public function down(): void
    {
        // Revert to simpler version
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
};
