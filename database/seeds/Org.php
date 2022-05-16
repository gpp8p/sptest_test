<?php

use Illuminate\Database\Seeder;

class Org extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $thisOrgId = DB::table('org')->insertGetId([
            'org_label'=>'root',
            'description'=>'root organization',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
        $thisUserId = DB::table('users')->where('name', 'spaces_admin')->first()->id;
        DB::table('userorg')->insert([
            'org_id'=>$thisOrgId,
            'user_id'=>$thisUserId,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
    }
}
