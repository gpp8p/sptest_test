<?php

use Illuminate\Database\Seeder;
use App\Classes\Constants;

class linkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $thisConstants = new Constants;
        $menuCardIds = [11,12];
        $sourceLayout = 1;
        $targetLayout = 27;
        foreach($menuCardIds as $thisId){
            $thisOrgId = DB::table('links')->insertGetId([
                'org_id'=>1,
                'layout_id'=>$sourceLayout,
                'card_instance_id'=>$thisId,
                'description'=>'Link from card '.$thisId.' to '.$targetLayout,
                'isExternal'=>0,
                'link_url'=>$thisConstants->Options['linkUrlBase'].'2',
                'layout_link_to'=>2,
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ]);
        }
    }
}
