<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('anjan001.roles-and-permissions.user_role_table', 'user_role_xref');
        $userTable = config('anjan001.roles-and-permissions.user_table', 'users');
        $userIdCol = config('anjan001.roles-and-permissions.user_id_column', 'id');
        $rolesTable = config('anjan001.roles-and-permissions.roles_table', 'roles');
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');

        Schema::create($tableName, function (Blueprint $table) use ($userIdCol, $roleIdCol, $userTable, $rolesTable) {
            $table->bigIncrements('id');
            $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;
            $table->unsignedBigInteger($pivotUserIdCol);
            $table->unsignedBigInteger($roleIdCol);
            $table->timestamps();

            $table->foreign($pivotUserIdCol)->references($userIdCol)->on($userTable)->onDelete('cascade');
            $table->foreign($roleIdCol)->references($roleIdCol)->on($rolesTable)->onDelete('cascade');

            $table->unique([$pivotUserIdCol, $roleIdCol]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('anjan001.roles-and-permissions.user_role_table', 'user_role_xref'));
    }
};
