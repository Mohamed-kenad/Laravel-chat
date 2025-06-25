<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'ahmed',
            'email' => 'ahmed@gmail.com',
            'password' => Hash::make('password'),
        ]);
        User::create([
            'name' => 'ali',
            'email' => 'ali@gmail.com',
            'password' => Hash::make('password'),
        ]);
    }
}
