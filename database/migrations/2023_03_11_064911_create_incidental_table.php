<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accidentals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('amount');
            $table->bigInteger('remainder');
            $table->integer('account_id');
            $table->foreignId('user_id');
            $table->tinyInteger('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accidentals');
    }
};
