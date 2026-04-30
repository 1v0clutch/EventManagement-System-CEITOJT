<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'DIT'   => 'Department of Information Technology',
            'DIET'  => 'Department of Industrial Engineering and Technology',
            'DCEEE' => 'Department of Computer, Electronics, and Electrical Engineering',
            'DCEA'  => 'Department of Civil Engineering and Architecture',
            'DAFE'  => 'Department of Agriculture and Food Engineering',
        ];

        // College-level roles (one each, assigned to DIT as representative dept)
        $collegeLevelUsers = [
            ['name' => 'Dean Rodriguez',      'email' => 'dean@cvsu.edu.ph',          'department' => 'Department of Information Technology', 'designation' => 'Dean'],
            ['name' => 'CEIT Official Santos', 'email' => 'ceit.official@cvsu.edu.ph', 'department' => 'Department of Information Technology', 'designation' => 'CEIT Official'],
            ['name' => 'Coordinator Aquino',   'email' => 'coordinator@cvsu.edu.ph',   'department' => 'Department of Information Technology', 'designation' => 'Coordinator'],
        ];

        // Department-level roles — one per role per department
        $deptRoles = [
            'Chairperson'                    => 'chair',
            'Faculty Member'                 => 'faculty',
            'Research Coordinator'           => 'research',
            'Extension Coordinator'          => 'extension',
            'GAD Coordinator'                => 'gad',
            'Department Research Coordinator'  => 'dept.research',
            'Department Extension Coordinator' => 'dept.extension',
        ];

        $users = $collegeLevelUsers;

        foreach ($departments as $code => $dept) {
            foreach ($deptRoles as $designation => $slug) {
                $users[] = [
                    'name'        => ucfirst($slug) . ' ' . $code,
                    'email'       => "{$slug}.{$code}@cvsu.edu.ph",
                    'department'  => $dept,
                    'designation' => $designation,
                ];
            }
        }

        $created = 0;
        foreach ($users as $data) {
            if (!User::where('email', $data['email'])->exists()) {
                User::create([
                    'name'                 => $data['name'],
                    'first_name'           => explode(' ', $data['name'])[0],
                    'last_name'            => explode(' ', $data['name'])[count(explode(' ', $data['name'])) - 1],
                    'email'                => $data['email'],
                    'password'             => Hash::make('11111111'),
                    'department'           => $data['department'],
                    'designation'          => $data['designation'],
                    'is_validated'         => true,
                    'email_verified_at'    => now(),
                    'schedule_initialized' => true,
                ]);
                $created++;
            }
        }

        $this->command->info("UsersSeeder: {$created} users created.");
        $this->command->info("Password for all: 11111111");
        $this->command->info("1 Dean, 1 CEIT Official, 1 Coordinator + 7 roles x 5 departments = 38 users total");
    }
}
