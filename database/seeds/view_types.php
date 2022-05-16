<?php

use Illuminate\Database\Seeder;

class view_types extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('view_types')->insert([
            'view_type_label'=>'Web Browser',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);

        DB::table('view_types')->insert([
            'view_type_label'=>'Mobile Phone',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
    }
}
