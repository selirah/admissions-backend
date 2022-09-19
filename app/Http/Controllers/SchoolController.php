<?php

namespace App\Http\Controllers;

use App\Models\School;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    private $_school;

    public function __construct(School $school)
    {
        $this->_school = $school;
    }

    // @route  GET api/v1/school
    // @desc   Get school
    // @access Private
    public function getSchool(Request $request)
    {
        $userId = $request->user()->id;
        $school = $this->_school->_getSchool($userId);

        if (!$school) {
            return response()->json(null, 200);
        }

        return response()->json($school, 200);
    }


    // @route  POST api/v1/school
    // @desc   Add school
    // @access Private
    public function createSchool(Request $request)
    {
        $schoolName = trim($request->input('school_name'));
        $categoryId = trim($request->input('category_id'));
        $region = trim($request->input('region'));
        $town = trim($request->input('town'));
        $phone = trim($request->input('phone'));
        $email = trim($request->input('email'));
        $address = trim($request->input('address'));
        $senderId = trim($request->input('sender_id'));
        $letterSignatory = trim($request->input('letter_signatory'));
        $signatoryPosition = trim($request->input('signatory_position'));
        $academicYear = trim($request->input('academic_year'));
        $feePayment = trim($request->input('fee_payment'));


        $userId = $request->user()->id;
        $schoolData = [
            'user_id' => $userId,
            'school_name' => $schoolName,
            'category_id' => $categoryId,
            'region' => $region,
            'town' => $town,
            'email' => $email,
            'phone' => $phone,
            'sender_id' => $senderId,
            'address' => $address,
            'letter_signatory' => $letterSignatory,
            'signatory_position' => $signatoryPosition,
            'academic_year' => $academicYear,
            'fee_payment' => $feePayment,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        try {
            $id = $this->_school->_saveSchool($schoolData);
            $school = $this->_school->_getSchoolById($id);
            return response()->json($school, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  PUT api/v1/school/:id
    // @desc   Update school
    // @access Private
    public function updateSchool($id, Request $request)
    {
        $schoolName = trim($request->input('school_name'));
        $categoryId = trim($request->input('category_id'));
        $region = trim($request->input('region'));
        $town = trim($request->input('town'));
        $phone = trim($request->input('phone'));
        $email = trim($request->input('email'));
        $address = trim($request->input('address'));
        $senderId = trim($request->input('sender_id'));
        $letterSignatory = trim($request->input('letter_signatory'));
        $signatoryPosition = trim($request->input('signatory_position'));
        $academicYear = trim($request->input('academic_year'));
        $feePayment = trim($request->input('fee_payment'));

        $schoolData = [
            'school_name' => $schoolName,
            'category_id' => $categoryId,
            'region' => $region,
            'town' => $town,
            'email' => $email,
            'phone' => $phone,
            'sender_id' => $senderId,
            'address' => $address,
            'letter_signatory' => $letterSignatory,
            'signatory_position' => $signatoryPosition,
            'academic_year' => $academicYear,
            'fee_payment' => $feePayment,
            'updated_at' => Carbon::now()
        ];

        try {
            $this->_school->_updateSchool($id, $schoolData);
            $school = $this->_school->_getSchoolById($id);
            return response()->json($school, 200);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/school/logo
    // @desc   Update school logo
    // @access Private
    public function updateLogo(Request $request)
    {

        if (!$request->hasFile('file')) {
            $response = [
                'message' => 'Make sure you select a file upload'
            ];
            return response()->json($response, 400);
        }


        $logo = $request->file('file');
        $extension = $logo->getClientOriginalExtension();

        $logoName = date('YmdHis') . '.' . $extension;

        Storage::disk('public')->put('logos/' . $logoName, File::get($logo));

        $logoUrl = url('uploads/logos/' . $logoName);

        $userId = $request->user()->id;
        $school = $this->_school->_getSchool($userId);

        $logoData = [
            'logo' => $logoUrl
        ];

        try {
            $this->_school->_updateSchool($school->id, $logoData);
            $school = $this->_school->_getSchoolById($school->id);
            return response()->json($school, 200);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/school/logo
    // @desc   Update letter signature
    // @access Private
    public function updateLetterSignature(Request $request)
    {

        if (!$request->hasFile('file')) {
            $response = [
                'message' => 'Make sure you select a file upload'
            ];
            return response()->json($response, 400);
        }
        $signature = $request->file('file');
        $extension = $signature->getClientOriginalExtension();

        $signatureName = date('YmdHis') . '.' . $extension;
        Storage::disk('public')->put('signatures/' . $signatureName, File::get($signature));
        $signatureUrl = url('uploads/signatures/' . $signatureName);

        $userId = $request->user()->id;
        $school = $this->_school->_getSchool($userId);

        $signatureData = [
            'letter_signature' => $signatureUrl
        ];

        try {
            $this->_school->_updateSchool($school->id, $signatureData);
            $school = $this->_school->_getSchoolById($school->id);
            return response()->json($school, 200);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getSchools()
    {
        $schools = $this->_school->_getAllSchools();
        return response()->json($schools, 200);
    }
}
