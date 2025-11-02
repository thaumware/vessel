<?php

namespace App\Shared\Adapters\Database;

use Illuminate\Database\Schema\Blueprint;

class MigrationsHelper
{
    public static function loadBaseColumns(Blueprint $table)
    {
        $table->uuid('id')->primary();


        $table->uuid('workspace_id')->nullable();

        $table->dateTime('created_at')->nullable();
        $table->dateTime('updated_at')->nullable();
        $table->dateTime('deleted_at')->nullable();

    }
}