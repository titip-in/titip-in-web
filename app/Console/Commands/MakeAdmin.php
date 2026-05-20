<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

#[Signature('titipin:make-admin')]
#[Description('Create a new admin securely')]
class MakeAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Admin Name');
        $email = $this->ask('Admin Email');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admins,email',
            ]
        );

        if ($validator->fails()) {
            $this->error('Validation failed: ' . implode(', ', $validator->errors()->all()));
            return 1;
        }

        $password = $this->secret('Password (min 8 chars)');
        $confirm = $this->secret('Confirm Password');

        if ($password !== $confirm || strlen($password) < 8) {
            $this->error('Password invalid or confirmation mismatch.');
            return 1;
        }

        Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Admin account '{$email}' created successfully.");
        return 0;
    }
}