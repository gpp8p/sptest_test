<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('layouts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('menu_label',32);
            $table->string('description', 255);
            $table->integer('height');
            $table->integer('width');
            $table->unsignedBigInteger('org_id');
            $table->char('backgroundType', 1);
            $table->char('backgroundDisplay', 10);
            $table->string('backgroundColor',10);
            $table->string('backgroundUrl', 80);
            $table->char('deleted',1)->nullable($value = true);
            $table->string('customcss',512)->default('');
            $table->char('template', 1)->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('layouts');
    }
}
