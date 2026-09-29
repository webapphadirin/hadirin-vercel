<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        if (DB::getDriverName() !== 'pgsql') return;
        // Laravel uses its privileged server-side database connection.
        // These tables must not be readable via Supabase's anonymous Data API.
        foreach (['users','password_reset_tokens','sessions','cache','cache_locks','locations','settings','attendances','activity_logs'] as $table) {
            DB::statement('ALTER TABLE "'.$table.'" ENABLE ROW LEVEL SECURITY');
        }
    }
    public function down(): void {
        // Preserve privacy protection during rollback.
    }
};
