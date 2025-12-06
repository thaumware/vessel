<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Older installs had the FK; fresh schema no longer creates it, so guard to avoid errors
        $hasFk = false;
        try {
            $connection = Schema::getConnection()->getDoctrineSchemaManager();
            $foreignKeys = $connection->listTableForeignKeys('locations_locations');
            foreach ($foreignKeys as $fk) {
                if ($fk->getLocalColumns() === ['address_id']) {
                    $hasFk = true;
                    break;
                }
            }
        } catch (\Throwable $e) {
            $hasFk = false;
        }

        if ($hasFk) {
            Schema::table('locations_locations', function (Blueprint $table) {
                $table->dropForeign(['address_id']);
            });
        }
    }

    public function down()
    {
        Schema::table('locations_locations', function (Blueprint $table) {
            $table->foreign('address_id')->references('id')->on('locations_addresses');
        });
    }
};