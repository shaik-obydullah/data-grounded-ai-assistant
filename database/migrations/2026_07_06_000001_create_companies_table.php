<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('organisation_name');
            $table->string('town_city')->nullable();
            $table->string('county')->nullable();
            $table->string('type_rating')->nullable();
            $table->string('route')->nullable();
            $table->string('website_url')->nullable();
            $table->string('hr_phone')->nullable();
            $table->string('hr_email')->nullable();

            $table->string('csv_checksum')->nullable();
            $table->unsignedBigInteger('csv_import_id')->nullable();
            $table->string('change_type')->nullable()->comment('new, updated, removed, unchanged');

            $table->timestamps();
        });

        Schema::create('csv_imports', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('checksum');
            $table->integer('total_rows')->default(0);
            $table->integer('new_rows')->default(0);
            $table->integer('updated_rows')->default(0);
            $table->integer('removed_rows')->default(0);
            $table->integer('unchanged_rows')->default(0);
            $table->json('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('csv_imports');
        Schema::dropIfExists('companies');
    }
};
