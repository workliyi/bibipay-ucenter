<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name', 'email', 'phone', 'password',
    ];
    protected $hidden = [
        'password', 'remember_token','pivot', 'pay_password',
    ];

    public function createPassword(string $password): self
    {
        $this->password = app('hash')->make($password);

        return $this;
    }
}
