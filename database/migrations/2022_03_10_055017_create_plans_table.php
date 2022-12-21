<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('identifier');
            $table->string('type');
            $table->string('plan_price_id');
            $table->text('duration')->nullable();
            $table->longtext('description')->nullable();
            $table->string('plan_type')->nullable();
            $table->bigInteger('trial_period')->nullable();
            $table->double('amount',10,2)->nullable();
            $table->tinyInteger('status')->nullable()->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
}
