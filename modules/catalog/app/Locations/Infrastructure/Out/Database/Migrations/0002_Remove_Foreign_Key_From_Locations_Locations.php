<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('locations_locations', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
        });
    }

    public function down()
    {
        Schema::table('locations_locations', function (Blueprint $table) {
            $table->foreign('address_id')->references('id')->on('locations_addresses');
        });
    }
};