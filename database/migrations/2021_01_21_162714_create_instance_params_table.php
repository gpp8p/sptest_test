<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstanceParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instance_params', function (Blueprint $table) {
            $table->id();
            $table->string('dom_element', 32);
            $table->string('parameter_key', 32);
            $table->mediumText('parameter_value');
            $table->unsignedBigInteger("card_instance_id");
            $table->boolean('isCss')->default(false);
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
        Schema::dropIfExists('instance_params');
    }
}
