<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Fees extends Model
{
    private $_connection;
    protected $table = 'fees';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _getFees($schoolId, $academicYear)
    {
        return $this->_connection->table($this->table)->where($this->table . '.school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)
            ->leftJoin('programme', 'programme.id', '=', $this->table . '.programme_id')
            ->select($this->table . '.*', 'programme.programme')
            ->get();
    }

    public function _getFeesByAcademicYear($schoolId, $academicYear)
    {
        $query = $this->_connection->table($this->table)->where($this->table . '.school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)->get();
        return $query;
    }

    public function _getFeesByAcademicYearAndProgramme($schoolId, $academicYear, $programmeId)
    {
        $query = $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)
            ->where('programme_id', '=', $programmeId)->first();
        return $query;
    }

    public function _getFee($id)
    {
        return $this->_connection->table($this->table)->where($this->table . '.id', '=', $id)
            ->leftJoin('programme', 'programme.id', '=', $this->table . '.programme_id')
            ->select($this->table . '.*', 'programme.programme')
            ->first();
    }

    public function _saveFees(array $payload)
    {
        return $this->_connection->table($this->table)->insertGetId($payload);
    }

    public function _deleteFee($id)
    {
        $this->_connection->table($this->table)->delete($id);
    }

    public function _deleteFeesByProgramme($schoolId, $programmeId)
    {
        $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('programme_id', '=', $programmeId)->delete();
    }
}
