<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Summary of register
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        if (in_array('is_organizer', $data)) {
            $user->assignRole('organizer');
        } else {
            $user->assignRole('guest');
        }
        return $user;
    }

    /**
     * Summary of login
     * @param string $email
     * @param string $password
     * @throws \Exception
     * @return User
     */
    public function login(string $email, string $password): User
    {
        $user = User::where('email', $email)->firstOrFail();
        if (!Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials');
        }
        return $user;
    }
}
