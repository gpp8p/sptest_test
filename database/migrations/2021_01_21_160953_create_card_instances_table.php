<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_instances', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('row');
            $table->integer('col');
            $table->integer('height');
            $table->integer('width');
            $table->string('card_name', 80);
            $table->char('restricted', 1);
            $table->unsignedBigInteger('layout_id')->nullable($value = true);
            $table->unsignedBigInteger('view_type_id');
            $table->string('card_component', 32);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_instances');
    }
}
