<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive', function (Blueprint $table) {
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('layout_id');
            $table->unsignedBigInteger('card_id');
            $table->char('historical_date', 8);
            $table->string('title', 64);
            $table->text('metadata');
            $table->unsignedBigInteger('document_type');
            $table->unsignedBigInteger('file_type');
            $table->unsignedBigInteger('access_type');
            $table->boolean('index');
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
        Schema::dropIfExists('archive');
    }
}
