<?php

use App\Shared\Adapters\Database\MigrationsHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('taxonomy_vocabularies', function (Blueprint $table) {
            MigrationsHelper::loadBaseColumns($table);
            $table->string('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('taxonomy');
    }
};