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
        Schema::create('big_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id');
            $table->foreignId('wallet_id');
            $table->integer("bookable_id")->nullable();
            $table->string("bookable_type")->nullable();
            $table->bigInteger('out');
            $table->bigInteger('in');
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
        Schema::dropIfExists('big_books');
    }
};
