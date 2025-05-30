<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 

class UserSeeder extends Seeder { 
    public function run() {
        DB::table('users')->insert([
            [
                'Username' => 'Septianto Bagus Hidayatullah',
                'Email' => 'septian@example.com',
                'Password' =>'septian123',
                'Character_ID' => null
            ],
        ]);
    }
}