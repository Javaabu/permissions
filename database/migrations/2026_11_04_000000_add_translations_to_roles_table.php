<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns javaabu/translatable uses to translate role descriptions. Harmless without it.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table_name = config('permission.table_names.roles', 'roles');

        if (Schema::hasColumn($table_name, 'translations')) {
            return;
        }

        Schema::table($table_name, function (Blueprint $table) use ($table_name) {
            $table->json('translations')->nullable();
            $table->string('lang')->nullable()->index("{$table_name}_lang_index");
        });
    }

    public function down(): void
    {
        $table_name = config('permission.table_names.roles', 'roles');

        if (! Schema::hasColumn($table_name, 'translations')) {
            return;
        }

        Schema::table($table_name, function (Blueprint $table) use ($table_name) {
            $table->dropIndex("{$table_name}_lang_index");
            $table->dropColumn(['translations', 'lang']);
        });
    }
};
