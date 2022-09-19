<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ActivationCode extends Model
{
    private $_connection;
    protected $table = 'activation_code';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _saveCode(array $payload)
    {
        $this->_connection->table($this->table)->insert($payload);
    }

    public function _updateCode($userId, array $payload)
    {
        $this->_connection->table($this->table)->where('user_id', '=', $userId)->update($payload);
    }

    public function _getCode($userId, $code)
    {
        $query = $this->_connection->table($this->table)->where('user_id', '=', $userId)
            ->where('code', '=', $code)->first();
        return $query;
    }

    public function _updateCodeExpiry($userId, $code)
    {
        $query = $this->_connection->table($this->table)->where('user_id', '=', $userId)
            ->where('code', '=', $code)->first();

        if (strtotime(date('Y-m-d H:i:s') > strtotime($query->expiry))) {
            $payload = [
                'is_expired' => 1
            ];

            $this->_connection->table($this->table)->where('user_id', '=', $userId)
                ->where('code', '=', $code)->update($payload);
        }
    }

    public function _checkIfCodeIsExpired($userId, $code, $isExpired = 1)
    {
        $query = $this->_connection->table($this->table)->where('user_id', '=', $userId)
            ->where('code', '=', $code)
            ->where('is_expired', '=', $isExpired)->first();
        return ($query) ? true : false;
    }

    public function _forceExpireCode($userId, $code)
    {
        $payload = [
            'is_expired' => 1
        ];

        $this->_connection->table($this->table)->where('user_id', '=', $userId)
            ->where('code', '=', $code)->update($payload);
    }
}
