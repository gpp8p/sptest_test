<?php

use Illuminate\Database\Seeder;

class User extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        {
            $lastRcd = DB::table('users')->insertGetId([
                'name' => 'spaces_admin',
                'email' => 'gpp8pvirginia@gmail.com',
                'password' => Hash::make('n1tad0g'),
                'is_admin' => true,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
            $lastRcd = DB::table('users')->insertGetId([
                'name' => 'Shannon User2',
                'email' => 'shan2@gmail.com',
                'password' => Hash::make('n1tad0g'),
                'is_admin' => false,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
            $lastRcd = DB::table('users')->insertGetId([
                'name' => 'Shannon User3',
                'email' => 'shan3@gmail.com',
                'password' => Hash::make('n1tad0g'),
                'is_admin' => false,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
            $lastRcd = DB::table('users')->insertGetId([
                'name' => 'Billy Budd',
                'email' => 'budd@gmail.com',
                'password' => Hash::make('n1tad0g'),
                'is_admin' => false,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
            $lastRcd = DB::table('users')->insertGetId([
                'name' => 'Nancy Normal',
                'email' => 'normal@gmail.com',
                'password' => Hash::make('n1tad0g'),
                'is_admin' => false,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
            $lastRcd = DB::table('users')->insertGetId([
                'name' => 'Shirley Skyfall',
                'email' => 'shirley@gmail.com',
                'password' => Hash::make('n1tad0g'),
                'is_admin' => false,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
            $lastRcd = DB::table('users')->insertGetId([
                'name' => 'GuestUser',
                'email' => 'GuestUser@nomail.com',
                'password' => Hash::make('GuestUser'),
                'is_admin' => false,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
        }
    }
}
