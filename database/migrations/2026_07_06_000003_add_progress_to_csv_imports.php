<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csv_imports', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('summary');
            $table->integer('progress')->nullable()->after('status');
            $table->string('file_path')->nullable()->after('progress');
        });
    }

    public function down(): void
    {
        Schema::table('csv_imports', function (Blueprint $table) {
            $table->dropColumn(['status', 'progress', 'file_path']);
        });
    }
};
