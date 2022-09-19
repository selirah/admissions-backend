<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    private $_connection;
    protected $table = 'students';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _saveStudent(array $payload)
    {
        return $this->_connection->table($this->table)->insertGetId($payload);
    }

    public function _uploadStudents(array $payload)
    {
        $this->_connection->table($this->table)->insert($payload);
    }

    public function _updateStudent($id, array $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $id)->update($payload);
    }

    public function _getStudents($schoolId, $academicYear)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)
            ->orderByDesc('created_at')->get();
    }

    public function _getStudentByAcademicYear($schoolId, $applicationNumber)
    {
        return $this->_connection->table($this->table)
            ->where('school_id', '=', $schoolId)
            ->where('application_number', '=', $applicationNumber)
            ->first();
    }

    public function _getStudentsByStatus($schoolId, $academicYear, $status)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)
            ->where('status', '=', $status)->orderByDesc('created_at')->get();
    }

    public function _getStudentsByProgramme($schoolId, $programmeId)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('programme_id', '=', $programmeId)->orderByDesc('created_at')->get();
    }

    public function _getStudentsByApplicationNumberAndSchool($schoolId, $applicationNumber)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('application_number', '=', $applicationNumber)->first();
    }

    public function _getStudentsByApplicationNumber($applicationNumber)
    {
        return $this->_connection->table($this->table)->where('application_number', '=', $applicationNumber)->first();
    }

    public function _getStudentByApplicationNumberAndPhone($applicationNumber, $phone)
    {
        return $this->_connection->table($this->table)->where('application_number', '=', $applicationNumber)
            ->where('phone', '=', $phone)->first();
    }

    public function _getStudent($id)
    {
        return $this->_connection->table($this->table)->where('id', '=', $id)->first();
    }

    public function _deleteStudent($id)
    {
        $this->_connection->table($this->table)->delete($id);
    }

    public function _getTotalStudentsByProgramme($programmeId, $academicYear)
    {
        return $this->_connection->table($this->table)->where('programme_id', '=', $programmeId)->where('academic_year', '=', $academicYear)->count();
    }

    public function _checkIfSchoolHasUploadedList($schoolId, $academicYear)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)->where('academic_year', '=', $academicYear)->first();
    }

    public function _checkProgramme($programmeId)
    {
        return $this->_connection->table($this->table)->where('programme_id', '=', $programmeId)->count();
    }

    public function _getStudentsCount($schoolId, $academicYear)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)->count();
    }

    public function _getStudentsStatusCount($schoolId, $academicYear, $status)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)
            ->where('status', '=', $status)
            ->count();
    }

    public function _getReceiptStudents($schoolId, $academicYear)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)
            ->where('fee_receipt', '=', '0')
            ->whereNotNull('receipt')
            ->get();
    }

    public function _getFeeStudents($schoolId, $academicYear, $type)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)
            ->where('fee_receipt', '=', $type)
            ->get();
    }

    public function _blockOrUnblockPendingStudents($schoolId, $academicYear, $oldStatus, $newStatus)
    {
        $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->where('academic_year', '=', $academicYear)
            ->where('status', '=', $oldStatus)
            ->update($newStatus);
    }
}
