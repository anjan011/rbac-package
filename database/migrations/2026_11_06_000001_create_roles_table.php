<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('anjan001.roles-and-permissions.roles_table', 'roles');
        $idColumn = config('anjan001.roles-and-permissions.role_id_column', 'role_id');

        Schema::create($tableName, function (Blueprint $table) use ($idColumn) {
            $table->bigIncrements($idColumn);
            $table->uuid('role_uid')->unique();
            $table->string('name', 255);
            $table->enum('type', ['System', 'Custom'])->default('Custom');
            $table->text('description')->nullable();
            $table->tinyInteger('is_enabled')->default(1);
            $table->timestamps();

            $table->unique(['name', 'type']);
        });

        // Seed Super Admin role
        DB::table($tableName)->insert([
            $idColumn => 1,
            'role_uid' => (string) Str::uuid(),
            'name' => 'Super Admin',
            'type' => 'System',
            'description' => 'Predefined Super Admin role',
            'is_enabled' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists(config('anjan001.roles-and-permissions.roles_table', 'roles'));
    }
};
