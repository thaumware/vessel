<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Addresses general (prefijo locations_)
        Schema::create('locations_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 255);
            $table->string('address_type', 255);
            $table->text('description')->nullable();

            $table->uuid('workspace_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Locations (prefijo locations_)
        Schema::create('locations_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('type', 255);

            $table->uuid('address_id')->nullable();

            $table->uuid('workspace_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint removed
        });
    }

    public function down()
    {
        Schema::dropIfExists('locations_locations');
        Schema::dropIfExists('locations_addresses');
    }
};