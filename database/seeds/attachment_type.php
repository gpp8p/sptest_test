<?php

use Illuminate\Database\Seeder;

class attachment_type extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('attachment_type')->insert([
            'attachment_type'=>'PDF',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('attachment_type')->insert([
            'attachment_type'=>'Word Document',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('attachment_type')->insert([
            'attachment_type'=>'Word RDF',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('attachment_type')->insert([
            'attachment_type'=>'Word HTML',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('attachment_type')->insert([
            'attachment_type'=>'Rich Text - HTML',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('attachment_type')->insert([
            'attachment_type'=>'Text',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('attachment_type')->insert([
            'attachment_type'=>'Image - jpeg',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('attachment_type')->insert([
            'attachment_type'=>'Image = PNG',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
    }
}
