<?php

namespace App\Http\Controllers;

use App\Models\Fees;
use App\Models\Programme;
use App\Models\School;
use App\Models\Student;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    private $_programme;
    private $_student;

    public function __construct(Programme $programme, School $school, Fees $fees, Student $student)
    {
        $this->_programme = $programme;
        $this->_school = $school;
        $this->_fees = $fees;
        $this->_student = $student;
    }

    // @route  GET api/v1/programme
    // @desc   Get programmes
    // @access Private
    public function getProgrammes(Request $request)
    {
        $schoolId = trim($request->get('school_id'));
        $academicYear = trim($request->get('academic_year'));

        $programmes = $this->_programme->_getProgrammes($schoolId);


        if ($programmes->isEmpty()) {
            return response()->json($programmes, 200);
        }

        $progs = [];
        foreach ($programmes as $programme) {
            $progs[] = [
                'id' => $programme->id,
                'school_id' => $programme->school_id,
                'programme' => $programme->programme,
                'created_at' => $programme->created_at,
                'total' => $this->_student->_getTotalStudentsByProgramme($programme->id, $academicYear),
                'updated_at' => $programme->updated_at
            ];
        }
        return response()->json($progs, 200);
    }

    // @route  POST api/v1/programme
    // @desc   Create programme
    // @access Private
    public function createProgramme(Request $request)
    {
        $programme = trim($request->input('programme'));
        $schoolId = trim($request->input('school_id'));

        $programmeData = [
            'school_id' => $schoolId,
            'programme' => $programme,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        try {
            $id = $this->_programme->_saveProgramme($programmeData);
            $programme = $this->_programme->_getProgramme($id);
            $p = [
                'id' => $programme->id,
                'school_id' => $programme->school_id,
                'programme' => $programme->programme,
                'total' => 0,
                'created_at' => $programme->created_at,
                'updated_at' => $programme->updated_at
            ];
            return response()->json($p, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/programme
    // @desc   Update programme
    // @access Private
    public function updateProgramme($id, Request $request)
    {
        $programme = trim($request->input('programme'));
        $schoolId = trim($request->input('school_id'));
        $academicYear = trim($request->input('academic_year'));

        $programmeData = [
            'school_id' => $schoolId,
            'programme' => $programme,
            'updated_at' => Carbon::now()
        ];

        try {
            $this->_programme->_updateProgramme($id, $programmeData);
            $programme = $this->_programme->_getProgramme($id);
            $p = [
                'id' => $programme->id,
                'school_id' => $programme->school_id,
                'programme' => $programme->programme,
                'total' => $this->_student->_getTotalStudentsByProgramme($programme->id, $academicYear),
                'created_at' => $programme->created_at,
                'updated_at' => $programme->updated_at
            ];
            return response()->json($p, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  DELETE api/v1/programme/:id
    // @desc   Delete programme
    // @access Private
    public function deleteProgramme($id)
    {
        $this->_programme->_deleteProgramme($id);
        return response()->json(200);
    }
}
