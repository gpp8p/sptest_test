<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('layout_id');
            $table->unsignedBigInteger("card_instance_id");
            $table->string('description', 255);
            $table->boolean('isExternal');
            $table->string('link_url', 255);
            $table->unsignedBigInteger("layout_link_to");
            $table->unsignedBigInteger('show_order');
            $table->char('type');
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
        Schema::dropIfExists('links');
    }
}




