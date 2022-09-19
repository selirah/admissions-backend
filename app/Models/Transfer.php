<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transfer extends Model
{
    private $_connection;
    protected $table = 'transfers';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _save(array $payload)
    {
        return $this->_connection->table($this->table)->insertGetId($payload);
    }

    public function _bulkSave(array $payload)
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

    public function _getTransfers($academicYear, $schoolId)
    {
        return $this->_connection->table($this->table)
            ->where($this->table . '.academic_year', '=', $academicYear)
            ->where(function ($query) use ($schoolId) {
                $query->where($this->table . '.source_school', '=', $schoolId);
                $query->orWhere($this->table . '.destination_school', '=', $schoolId);
            })
            ->join('students', $this->table . '.student_id', '=', 'students.id')
            ->join('school', $this->table . '.source_school', '=', 'school.id')
            ->join('school as destination', $this->table . '.destination_school', '=', 'destination.id')
            ->join('programme as source', $this->table . '.source_programme', '=', 'source.id')
            ->join('programme', $this->table . '.destination_programme', '=', 'programme.id')
            ->select($this->table . '.*', 'school.school_name as source_school_name', 'destination.school_name as destination_school_name', 'students.application_number', 'students.surname', 'students.other_names', 'students.phone', 'students.programme_id', 'programme.programme as destination_programme_name', 'source.programme as source_programme_name')
            ->orderByDesc($this->table . '.created_at')
            ->get();
    }

    public function _getTransfer($id)
    {
        return $this->_connection->table($this->table)
            ->where($this->table . '.id', '=', $id)
            ->join('students', $this->table . '.student_id', '=', 'students.id')
            ->join('school', $this->table . '.source_school', '=', 'school.id')
            ->join('school as destination', $this->table . '.destination_school', '=', 'destination.id')
            ->join('programme as source', $this->table . '.source_programme', '=', 'source.id')
            ->join('programme', $this->table . '.destination_programme', '=', 'programme.id')
            ->select($this->table . '.*', 'school.school_name as source_school_name', 'destination.school_name as destination_school_name', 'students.application_number', 'students.surname', 'students.other_names', 'students.phone', 'students.programme_id', 'programme.programme as destination_programme_name', 'source.programme as source_programme_name')
            ->first();
    }

    public function _get($id)
    {
        return $this->_connection->table($this->table)->where('id', '=', $id)->first();
    }
}
