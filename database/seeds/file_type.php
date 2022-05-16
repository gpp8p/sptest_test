<?php

use Illuminate\Database\Seeder;

class file_type extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('file_type')->insert([
            'file_type'=>'Rich Text HTML',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('file_type')->insert([
            'file_type'=>'Word Doc',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('file_type')->insert([
            'file_type'=>'Word Doc HTML',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('file_type')->insert([
            'file_type'=>'PDF',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
        DB::table('file_type')->insert([
            'file_type'=>'Image',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
    }
}
