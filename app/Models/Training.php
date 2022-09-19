<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Training extends Model
{
    private $_connection;
    protected $table = 'training';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _getTrainings($year)
    {
        $query = $this->_connection->table($this->table)->where('year', '=', $year)->get();
        return $query;
    }

    public function _getTraining($id)
    {
        $query = $this->_connection->table($this->table)
            ->where('id', '=', $id)
            ->first();
        return $query;
    }

    public function _save(array $payload)
    {
        return $this->_connection->table($this->table)->insertGetId($payload);
    }

    public function _saveTrainings(array $payload)
    {
        $this->_connection->table($this->table)->insert($payload);
    }

    public function _update($id, array $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $id)->update($payload);
    }

    public function _delete($id)
    {
        $this->_connection->table($this->table)->delete($id);
    }

    public function _saveBatch(array $payload)
    {
        $this->_connection->table($this->table)->insertOrIgnore($payload);
    }
}
