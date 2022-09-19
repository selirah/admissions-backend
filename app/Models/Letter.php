<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Letter extends Model
{
    private $_connection;
    protected $table = 'letter';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _getLetter($schoolId)
    {
        $query = $this->_connection->table($this->table)->where('school_id', '=', $schoolId)->first();
        return ($query) ? $query : false;
    }

    public function _saveLetter(array $payload)
    {
        return $this->_connection->table($this->table)->insertGetId($payload);
    }

    public function _updateLetter($id, array $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $id)->update($payload);
    }
}
