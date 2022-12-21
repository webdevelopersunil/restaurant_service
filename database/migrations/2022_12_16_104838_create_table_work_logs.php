<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWorkLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('booking_id');
            $table->string('comment');
            $table->enum('completion_status',["Completed","Partialy_completed","Pending","Require_More_Work"]);
            $table->enum('type',["Pause","Restart","WorkLog"]);
            $table->dateTime('log_time');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('booking_id')->references('id')->on('job_bookings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_logs');
    }
}
