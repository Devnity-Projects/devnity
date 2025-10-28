<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('system_settings')->insert([
            'key' => 'menu_visibility',
            'value' => json_encode([
                'financial' => false,
                'support' => false,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
