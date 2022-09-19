<?php

namespace App\Http\Controllers;

use App\Models\Fees;
use App\Models\Programme;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FeesController extends Controller
{
    private $_fees;
    public function __construct(Fees $fees, School $school, Programme $programme)
    {
        $this->_fees = $fees;
        $this->_school = $school;
        $this->_programme = $programme;
    }

    // @route  GET api/v1/fees
    // @desc   Get fees
    // @access Private
    public function getFees(Request $request)
    {
        $schoolId = trim($request->get('school_id'));
        $academicYear = trim($request->get('academic_year'));

        $fees = $this->_fees->_getFees($schoolId, $academicYear);
        return response()->json($fees, 200);
    }

    // @route  POST api/v1/fees
    // @desc   Create fees
    // @access Private
    public function createFees(Request $request)
    {
        $academicYear = trim($request->input('academic_year'));
        $programmeId = $request->input('programme_id');
        $amount = $request->input('amount');
        $schoolId = trim($request->input('school_id'));

        $fee = $this->_fees->_getFeesByAcademicYearAndProgramme($schoolId, $academicYear, $programmeId);

        if ($fee) {
            $this->_fees->_deleteFee($fee->id);
        }

        $feesData = [
            'school_id' => $schoolId,
            'academic_year' => $academicYear,
            'programme_id' => $programmeId,
            'amount' => $amount,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];


        $id = $this->_fees->_saveFees($feesData);

        $fee = $this->_fees->_getFee($id);

        return response()->json($fee, 201);
    }

    // @route  DELETE api/v1/fees/:id
    // @desc   Delete fee
    // @access Private
    public function deleteFee($id)
    {
        $this->_fees->_deleteFee($id);
        return response()->json(200);
    }
}
