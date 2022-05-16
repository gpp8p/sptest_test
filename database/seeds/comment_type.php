<?php

use Illuminate\Database\Seeder;

class comment_type extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('comment_type')->insert([
            'comment_type'=>'Proposed Revision',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('comment_type')->insert([
            'comment_type'=>'Comment',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('comment_type')->insert([
            'comment_type'=>'Proposal',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
        DB::table('comment_type')->insert([
            'comment_type'=>'Version',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()

        ]);
    }
}
