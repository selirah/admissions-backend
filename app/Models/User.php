<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    private $_connection;
    protected $table = 'users';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _checkEmailExistence($email)
    {
        $query = $this->_connection->table($this->table)->where('email', '=', $email)->first();
        return ($query) ? true : false;
    }

    public function _checkPhoneExistence($phone)
    {
        $query = $this->_connection->table($this->table)->where('phone', '=', $phone)->first();
        return ($query) ? true : false;
    }

    public function _checkActivation($userId, $isVerified = 1)
    {
        $query = $this->_connection->table($this->table)->where('id', '=', $userId)
            ->where('is_verified', '=', $isVerified)->first();
        return ($query) ? true : false;
    }

    public function _saveUserData(array $payload)
    {
        $query = $this->_connection->table($this->table)->insertGetId($payload);
        return $query;
    }

    public function _updateUserStatus($userId, array $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $userId)->update($payload);
    }

    public function _getUserById($userId)
    {
        $query = $this->_connection->table($this->table)->where('id', '=', $userId)->first();
        return $query;
    }

    public function _getUserByEmail($email)
    {
        $query = $this->_connection->table($this->table)->where('email', '=', $email)->first();
        return $query;
    }

    public function _getUserByPhone($phone)
    {
        $query = $this->_connection->table($this->table)->where('phone', '=', $phone)->first();
        return $query;
    }

    public function _updateUserPassword($userId, $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $userId)->update($payload);
    }
}
