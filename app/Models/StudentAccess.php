<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StudentAccess extends Model
{
    private $_connection;
    protected $table = 'student_access';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _saveStudentAccess(array $payload)
    {
        $this->_connection->table($this->table)->insert($payload);
    }

    public function _getStudentAccess($applicationNumber, $pin)
    {
        $query = $this->_connection->table($this->table)->where('application_number', '=', $applicationNumber)
            ->where('pin', '=', $pin)->first();
        return $query;
    }

    public function _updateStudentAccess($schoolId, $applicationNumber, array $payload)
    {
        $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('application_number', '=', $applicationNumber)->update($payload);
    }
}
