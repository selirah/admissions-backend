<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class School extends Model
{
    private $_connection;
    protected $table = 'school';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _getSchool($userId)
    {
        $query = $this->_connection->table($this->table)->where('user_id', '=', $userId)->first();
        return ($query) ? $query : false;
    }

    public function _getSchools($categoryId)
    {
        $query = $this->_connection->table($this->table)->where('category_id', '=', $categoryId)->get();
        return ($query) ? $query : false;
    }

    public function _getAllSchools()
    {
        $query = $this->_connection->table($this->table)->get();
        return ($query) ? $query : false;
    }

    public function _getSchoolById($id)
    {
        $query = $this->_connection->table($this->table)->where('id', '=', $id)->first();
        return ($query) ? $query : false;
    }

    public function _saveSchool(array $payload)
    {
        $id = $this->_connection->table($this->table)->insertGetId($payload);
        return $id;
    }

    public function _updateSchool($id, array $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $id)->update($payload);
    }
}
