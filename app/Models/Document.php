<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Document extends Model
{
    private $_connection;
    protected $table = 'document';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _getDocuments($schoolId)
    {
        $query = $this->_connection->table($this->table)->where('school_id', '=', $schoolId)->first();
        return ($query) ? $query : false;
    }

    public function _saveDocuments(array $payload)
    {
        $this->_connection->table($this->table)->insert($payload);
    }

    public function _updateDocumentsBySchool($schoolId, array $payload)
    {
        $this->_connection->table($this->table)->where('school_id', '=', $schoolId)->update($payload);
    }

    public function _updateDocuments($id, array $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $id)->update($payload);
    }
}
