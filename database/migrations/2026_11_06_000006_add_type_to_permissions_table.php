<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('anjan001.roles-and-permissions.permissions_table', 'permissions');

        Schema::table($tableName, function (Blueprint $table) {
            $table->enum('type', ['System', 'Custom'])->default('Custom')->after('name');
        });
    }

    public function down(): void
    {
        $tableName = config('anjan001.roles-and-permissions.permissions_table', 'permissions');

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
