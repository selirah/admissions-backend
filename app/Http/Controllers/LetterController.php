<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\School;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class LetterController extends Controller
{
    private $_letter;

    public function __construct(Letter $letter, School $school)
    {
        $this->_letter = $letter;
        $this->_school = $school;
    }

    // @route  GET api/v1/letter
    // @desc   Get letter
    // @access Private
    public function getLetter(Request $request)
    {
        $schoolId = trim($request->get('school_id'));

        $letter = $this->_letter->_getLetter($schoolId);
        if (!$letter) {
            return response()->json(null, 200);
        }
        return response()->json($letter, 200);
    }

    // @route  POST api/v1/letter
    // @desc   Create letter
    // @access Private
    public function createLetter(Request $request)
    {
        $admission = trim($request->input('admission'));
        $acceptance = trim($request->input('acceptance'));
        $schoolId = trim($request->input('school_id'));

        $letter = $this->_letter->_getLetter($schoolId);
        if ($letter) {
            return response()->json(['message' => 'Your letter has already been created.'], 400);
        }

        $letterData = [
            'school_id' => $schoolId,
            'admission' => $admission,
            'acceptance' => $acceptance,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        try {
            $id = $this->_letter->_saveLetter($letterData);
            $letter = $this->_letter->_getLetter($id);
            return response()->json($letter, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  PUT api/v1/letter
    // @desc   Update letter
    // @access Private
    public function updateLetter($id, Request $request)
    {
        $admission = trim($request->input('admission'));
        $schoolId = trim($request->input('school_id'));
        $acceptance = trim($request->input('acceptance'));

        $letterData = [
            'school_id' => $schoolId,
            'admission' => $admission,
            'acceptance' => $acceptance,
            'updated_at' => Carbon::now()
        ];

        try {
            $this->_letter->_updateLetter($id, $letterData);
            $letter = $this->_letter->_getLetter($id);
            return response()->json($letter, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/letter/notice
    // @desc   Create Notice
    // @access Private
    public function createNotice(Request $request)
    {
        $notice = trim($request->input('notice'));
        $id = trim($request->input('id'));

        $letterData = [
            'notice' => $notice,
            'updated_at' => Carbon::now()
        ];

        try {
            $this->_letter->_updateLetter($id, $letterData);
            $letter = $this->_letter->_getLetter($id);
            return response()->json($letter, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
