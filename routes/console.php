<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('admin:create', function () {
    $name = $this->ask('Admin name', 'Admin');
    $email = $this->ask('Admin email');
    $password = $this->secret('Password (at least 8 characters)');

    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen((string) $password) < 8) {
        $this->error('Enter a valid email and a password of at least 8 characters.');

        return 1;
    }
    User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make($password)]);
    $this->info('Admin saved. Log in at /admin/login');
})->purpose('Create or update an admin login');
