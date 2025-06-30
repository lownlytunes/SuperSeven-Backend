<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Test Owner
        $this->createTestOwner('admin_owner@test.com');

        // Test Secretary
        $this->createTestSecretary('secretary@test.com');

        // Test Photographer
        $this->createTestPhotographer('photographer@test.com');

        // Test Editor
        $this->createTestEditor('editor@test.com');
    }

    /**
     * Create a test user with the given email.
     *
     * @param string $email
     * @return bool
     */
    private function createTestOwner(string $email)
    {
        if (User::whereEmail($email)->exists()) {
            return false;
        }

        $user = User::firstOrCreate([
            'first_name' => 'Test',
            'mid_name' => '',
            'last_name' => 'Owner',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('TestUser123'),
            'contact_num' => '09123456789',
            'address' => 'Test Address',
            'status' => 'active',
        ]);

        $user->markEmailAsVerified();

        Employee::create([
            'user_id' => $user->id,
            'employee_type' => User::OWNER_TYPE,
        ]);
    }

    /**
     * Create a test user with the given email.
     *
     * @param string $email
     * @return bool
     */
    private function createTestSecretary(string $email)
    {
        if (User::whereEmail($email)->exists()) {
            return false;
        }

        $user = User::firstOrCreate([
            'first_name' => 'Test',
            'mid_name' => '',
            'last_name' => 'Secretary',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('TestUser123'),
            'contact_num' => '09123456789',
            'address' => 'Test Address',
            'status' => 'active',
        ]);

        $user->markEmailAsVerified();

        Employee::create([
            'user_id' => $user->id,
            'employee_type' => User::SECRETARY_TYPE,
        ]);
    }

    /**
     * Create a test user with the given email.
     *
     * @param string $email
     * @return bool
     */
    private function createTestPhotographer(string $email)
    {
        if (User::whereEmail($email)->exists()) {
            return false;
        }

        $user = User::firstOrCreate([
            'first_name' => 'Test',
            'mid_name' => '',
            'last_name' => 'Photographer',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('TestUser123'),
            'contact_num' => '09123456789',
            'address' => 'Test Address',
            'status' => 'active',
        ]);

        $user->markEmailAsVerified();

        Employee::create([
            'user_id' => $user->id,
            'employee_type' => User::PHOTOGRAPHER_TYPE,
        ]);
    }

    /**
     * Create a test user with the given email.
     *
     * @param string $email
     * @return bool
     */
    private function createTestEditor(string $email)
    {
        if (User::whereEmail($email)->exists()) {
            return false;
        }

        $user = User::firstOrCreate([
            'first_name' => 'Test',
            'mid_name' => '',
            'last_name' => 'Editor',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('TestUser123'),
            'contact_num' => '09123456789',
            'address' => 'Test Address',
            'status' => 'active',
        ]);

        $user->markEmailAsVerified();

        Employee::create([
            'user_id' => $user->id,
            'employee_type' => User::EDITOR_TYPE,
        ]);
    }
}
