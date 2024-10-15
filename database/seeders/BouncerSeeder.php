<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class BouncerSeeder extends Seeder
{
    public function run()
    {
        Bouncer::role()->firstOrCreate([
            'name' => 'admin',
            'title' => 'Admin',
        ]);

        Bouncer::ability()->firstOrCreate([
            'name' => 'manage-category',
            'title' => 'Manage Category',
        ]);

        Bouncer::allow('admin')->to('manage-category');
    }
}


