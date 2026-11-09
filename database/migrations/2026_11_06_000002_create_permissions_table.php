<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('anjan001.roles-and-permissions.permissions_table', 'permissions');
        $idColumn = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        Schema::create($tableName, function (Blueprint $table) use ($idColumn) {
            $table->bigIncrements($idColumn);
            $table->uuid('permission_uid')->unique();
            $table->string('name', 255)->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('is_enabled')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('anjan001.roles-and-permissions.permissions_table', 'permissions'));
    }
};
