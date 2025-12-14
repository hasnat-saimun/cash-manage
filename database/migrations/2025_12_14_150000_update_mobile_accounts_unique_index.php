<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Ensure provider column exists (nullable string)
        DB::statement("ALTER TABLE mobile_accounts ADD COLUMN IF NOT EXISTS provider VARCHAR(100) NULL");
        // Drop old unique index on (business_id, number) if present
        $indexes = DB::select("SHOW INDEX FROM mobile_accounts WHERE Key_name = 'mobile_accounts_business_id_number_unique'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE mobile_accounts DROP INDEX mobile_accounts_business_id_number_unique");
        }
        // Also try dropping generic unique on number if exists
        $generic = DB::select("SHOW INDEX FROM mobile_accounts WHERE Column_name = 'number' AND Non_unique = 0");
        foreach ($generic as $idx) {
            DB::statement("ALTER TABLE mobile_accounts DROP INDEX {$idx->Key_name}");
        }
        // Add composite unique index (business_id, number, provider)
        // Name it consistently
        DB::statement("ALTER TABLE mobile_accounts ADD UNIQUE INDEX mob_acc_biz_num_prov_unique (business_id, number, provider)");
    }

    public function down(): void
    {
        // Drop the composite unique index
        $indexes = DB::select("SHOW INDEX FROM mobile_accounts WHERE Key_name = 'mob_acc_biz_num_prov_unique'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE mobile_accounts DROP INDEX mob_acc_biz_num_prov_unique");
        }
        // Optionally restore unique on (business_id, number)
        DB::statement("ALTER TABLE mobile_accounts ADD UNIQUE INDEX mobile_accounts_business_id_number_unique (business_id, number)");
    }
};
