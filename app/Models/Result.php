<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Result extends Model
{
    private $_connection;
    protected $table = 'results';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _save(array $payload)
    {
        return $this->_connection->table($this->table)->insertGetId($payload);
    }

    public function _getResults($schoolId, $courseCode)
    {
        if (!empty($courseCode)) {
            return $this->_connection->table($this->table)
                ->where($this->table . '.school_id', '=', $schoolId)
                ->where($this->table . '.course_code', '=', $courseCode)
                ->join('programme', $this->table . '.programme_id', '=', 'programme.id')
                ->join('courses', $this->table . '.course_code', '=', 'courses.course_code')
                ->select($this->table . '.*', 'programme.programme', 'courses.course_code', 'courses.course')
                ->get();
        } else {
            return $this->_connection->table($this->table)
                ->where($this->table . '.school_id', '=', $schoolId)
                ->join('programme', $this->table . '.programme_id', '=', 'programme.id')
                ->join('courses', $this->table . '.course_code', '=', 'courses.course_code')
                ->select($this->table . '.*', 'programme.programme', 'courses.course_code', 'courses.course')
                ->get();
        }
    }

    public function _getResultsById($id)
    {
        return $this->_connection->table($this->table)
            ->where($this->table . '.id', '=', $id)
            ->join('programme', $this->table . '.programme_id', '=', 'programme.id')
            ->join('courses', $this->table . '.course_code', '=', 'courses.course_code')
            ->select($this->table . '.*', 'programme.programme', 'courses.course_code', 'courses.course')
            ->get();
    }

    public function _publishResults($id, array $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $id)->update($payload);
    }

    public function _checkResult($schoolId, $courseCode, $year, $semester, $programmeId)
    {
        return $this->_connection->table($this->table)
            ->where($this->table . '.school_id', '=', $schoolId)
            ->where($this->table . '.course_code', '=', $courseCode)
            ->where($this->table . '.year', '=', $year)
            ->where($this->table . '.semester', '=', $semester)
            ->where($this->table . '.programme_id', '=', $programmeId)
            ->first();
    }

    public function _deleteResults($id)
    {
        $this->_connection->table($this->table)->where($this->table . '.id', '=', $id)->delete();
    }

    public function _getNonSMSResults()
    {
        return $this->_connection->table($this->table)->where($this->table . '.sms', '=', 0)
            ->whereYear($this->table . '.created_at', '=', date('Y'))
            ->get();
    }

    public function _updateSMSField(array $ids, array $payload)
    {
        $this->_connection->table($this->table)->whereIn('id', $ids)->update($payload);
    }
}
