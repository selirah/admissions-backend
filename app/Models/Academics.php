<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Academics extends Model
{
    private $_connection;
    protected $table = 'academics';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _save(array $payload)
    {
        return $this->_connection->table($this->table)->insert($payload);
    }

    public function _getResults($resultId)
    {
        return $this->_connection->table($this->table)
            ->where($this->table . '.result_id', '=', $resultId)
            ->join('courses', $this->table . '.course_code', '=', 'courses.course_code')
            ->join('programme', $this->table . '.programme_id', '=', 'programme.id')
            ->join('students', $this->table . '.index_no', '=', 'students.application_number')
            ->select($this->table . '.*', 'courses.course', 'programme.programme', 'students.surname', 'students.other_names', 'students.pin', 'students.application_number', 'students.owing_fees', 'students.phone')
            ->get();
    }

    public function _getStudentResults($schoolId, $appNo, $sem, $year)
    {
        return $this->_connection->table($this->table)
            ->where($this->table . '.school_id', '=', $schoolId)
            ->where($this->table . '.index_no', '=', $appNo)
            ->where($this->table . '.semester', '=', $sem)
            ->where($this->table . '.year', '=', $year)
            ->join('courses', $this->table . '.course_code', '=', 'courses.course_code')
            ->join('programme', $this->table . '.programme_id', '=', 'programme.id')
            ->join('students', $this->table . '.index_no', '=', 'students.application_number')
            ->join('school', $this->table . '.school_id', '=', 'school.id')
            ->select($this->table . '.*', 'courses.course', 'programme.programme', 'students.id', 'students.surname', 'students.other_names', 'students.owing_fees', 'school.school_name', 'school.region', 'school.town', 'school.email', 'school.phone', 'school.address', 'school.logo')
            ->get();
    }

    public function _deleteStudentResults($resultId)
    {
        $this->_connection->table($this->table)->where($this->table . '.result_id', '=', $resultId)->delete();
    }
}
