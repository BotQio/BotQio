<?php

use App\Enums\HostRequestStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHostRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('host_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();

            $table->string('local_ip')->nullable();
            $table->string('remote_ip')->nullable();
            $table->string('hostname')->nullable();

            $table->string('status')->default(HostRequestStatusEnum::REQUESTED);

            $table->uuid('claimer_id')->nullable();
            $table->foreign('claimer_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('host_requests');
    }
}
