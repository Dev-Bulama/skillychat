<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->string('display_name')->after("name")->nullable();
        });

        DB::statement('UPDATE languages SET display_name = name WHERE display_name IS NULL');

        Schema::table('languages', function (Blueprint $table) {
            $table->string('display_name')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });
    }
};