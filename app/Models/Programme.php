<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Programme extends Model
{
    private $_connection;
    protected $table = 'programme';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _getProgrammes($schoolId)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)->get();
    }

    public function _getProgramme($id)
    {
        return $this->_connection->table($this->table)->where('id', '=', $id)->first();
    }

    public function _saveProgramme(array $payload)
    {
        return $this->_connection->table($this->table)->insertGetId($payload);
    }

    public function _updateProgramme($id, array $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $id)->update($payload);
    }

    public function _deleteProgramme($id)
    {
        $this->_connection->table($this->table)->delete($id);
    }
}
