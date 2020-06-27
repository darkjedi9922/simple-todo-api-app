<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    protected $primaryKey = 'user_id';
    protected $fillable = ['first_name', 'last_name', 'email', 'password', 'api_token'];
    protected $hidden = ['password', 'api_token'];

    public static function generateApiToken(): string
    {
        return Str::random(80);
    }

    public static function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    public static function checkPassword(string $password, string $hashedPassword): bool
    {
        return Hash::check($password, $hashedPassword);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id');
    }
}
