<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("group_id");
            $table->unsignedBigInteger("layout_id");
            $table->tinyInteger('view')->default(0);
            $table->tinyInteger('author')->default(0);
            $table->tinyInteger('admin')->default(0);
            $table->tinyInteger('opt1')->default(0);
            $table->tinyInteger('opt2')->default(0);
            $table->tinyInteger('opt3')->default(0);
            $table->tinyInteger('isLayoutGroup')->default(0);
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
        Schema::dropIfExists('perms');
    }
}
