<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOctoPrintAPIUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('octoprint_api_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->uuid('worker_id');
            $table->string('worker_type');

            $table->string('api_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('octoprint_api_user');
    }
}
