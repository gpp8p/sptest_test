<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardInLayout extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_in_layout', function (Blueprint $table) {
            $table->integer('row');
            $table->integer('col');
            $table->integer('height');
            $table->integer('width');
            $table->unsignedBigInteger('layout_id');
            $table->unsignedBigInteger("card_instance_id");
            $table->id();
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
        Schema::dropIfExists('card_in_layout');
    }
}

