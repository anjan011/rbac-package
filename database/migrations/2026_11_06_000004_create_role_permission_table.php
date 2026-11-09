<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('anjan001.roles-and-permissions.role_permission_table', 'role_permission_xref');
        $rolesTable = config('anjan001.roles-and-permissions.roles_table', 'roles');
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $permissionsTable = config('anjan001.roles-and-permissions.permissions_table', 'permissions');
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        Schema::create($tableName, function (Blueprint $table) use ($roleIdCol, $permissionIdCol, $rolesTable, $permissionsTable) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger($roleIdCol);
            $table->unsignedBigInteger($permissionIdCol);
            $table->timestamps();

            $table->foreign($roleIdCol)->references($roleIdCol)->on($rolesTable)->onDelete('cascade');
            $table->foreign($permissionIdCol)->references($permissionIdCol)->on($permissionsTable)->onDelete('cascade');

            $table->unique([$roleIdCol, $permissionIdCol]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('anjan001.roles-and-permissions.role_permission_table', 'role_permission_xref'));
    }
};
