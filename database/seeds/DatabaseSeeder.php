<?php

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        $this->call(view_types::class);
        $this->call(User::class);
        $this->call(Org::class);
        $this->call(Groups::class);
        $this->call(Layouts::class);
        $this->call(archive_access::class);
        $this->call(comment_type::class);
        $this->call(document_status::class);
        $this->call(document_type::class);
        $this->call(file_type::class);
        $this->call(attachment_type::class);


    }
}
