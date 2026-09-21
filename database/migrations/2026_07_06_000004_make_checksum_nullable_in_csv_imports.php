<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE csv_imports ALTER COLUMN checksum DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE csv_imports ALTER COLUMN checksum SET NOT NULL');
    }
};
