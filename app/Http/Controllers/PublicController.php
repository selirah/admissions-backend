<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Programme;
use App\Models\School;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    private $_school;
    private $_student;
    private $_programme;

    public function __construct(School $school, Student $student, Programme $programme)
    {
        $this->_school = $school;
        $this->_student = $student;
        $this->_programme = $programme;
    }

    // @route  GET api/v1/get-schools/:id
    // @desc   Get schools
    // @access Public
    public function getSchools($categoryId, Request $request)
    {
        $academicYear = trim($request->get('academic_year'));

        $schools = $this->_school->_getSchools($categoryId);

        if ($schools->isEmpty()) {
            $response = [
                'success' => false,
                'description' => [
                    'message' => 'No records found'
                ]
            ];
            return response()->json($response, 200);
        }

        $sch = [];
        foreach ($schools as $school) {
            $check = $this->_student->_checkIfSchoolHasUploadedList($school->id, $academicYear);
            if ($check) {
                $sch[] = $school;
            }
        }
        $response = [
            'success' => true,
            'description' => [
                'message' => 'successful',
                'schools' => $sch
            ]
        ];
        return response()->json($response, 200);
    }


    // @route  POST api/v1/reset-pin
    // @desc   Reset Student Pin
    // @access Private
    public function resetPin(Request $request)
    {
        $applicationNumber = trim($request->input('app_no'));
        $phone = trim($request->input('phone'));

        if (empty($applicationNumber) || empty($phone)) {
            $response = [
                'success' => false,
                'description' => [
                    'message' => 'Make sure there is application number and phone'
                ]
            ];
            return response()->json($response, 201);
        }

        $student = $this->_student->_getStudentByApplicationNumberAndPhone($applicationNumber, Helper::sanitizePhone($phone));
        if (!$student) {
            $response = [
                'success' => false,
                'description' => [
                    'message' => 'The application number or phone provided does not exist in the system'
                ]
            ];
            return response()->json($response, 201);
        }

        $school = $this->_school->_getSchoolById($student->school_id);
        $pin = rand(00000, 99999);
        $payload = [
            'pin' => $pin,
            'status' => env('STATUS_PENDING'),
            'updated_at' => Carbon::now()
        ];
        $this->_student->_updateStudent($student->id, $payload);

        $message = "Hello " . $student->other_names . "\nYour new password is " . $pin . "\nDo not share it with anyone.";
        Helper::sendSMS(Helper::sanitizePhone($phone), urlencode($message), $school->sender_id);

        $response = [
            'success' => true,
            'description' => [
                'message' => 'Pin sent successfully, your pin is ' . $pin
            ]
        ];
        return response()->json($response, 201);
    }


    // @route  GET api/v1/get-students/:school_id/:app_number
    // @desc   Get students by academic year
    // @access Private
    public function getStudentByAcademicYear($schoolId, $applicationNumber)
    {
        $student = $this->_student->_getStudentByAcademicYear($schoolId, $applicationNumber);
        if (!$student) {
            $response = [
                'success' => false,
                'description' => [
                    'message' => 'No records found'
                ]
            ];
            return response()->json($response, 200);
        }


        $status = $student->status;
        $statusMeaning = '';
        if ($status == env('STATUS_PENDING'))
            $statusMeaning = 'Pending';
        if ($status == env('STATUS_ACCESSED'))
            $statusMeaning = 'Accessed';
        if ($status == env('STATUS_BLOCKED'))
            $statusMeaning = 'Blocked';

        $STUDENT = [
            'id' => $student->id,
            'surname' => $student->surname,
            'other_names' => $student->other_names,
            'application_number' => $student->application_number,
            'programme' => $this->_programme->_getProgramme($student->programme_id)->programme,
            'academic_year' => $student->academic_year,
            'status' => $statusMeaning,
            'phone' => $student->phone,
            'created_at' => date('D, d M Y', strtotime($student->created_at))
        ];

        $response = [
            'success' => true,
            'description' => [
                'message' => 'successful',
                'student' => $STUDENT
            ]
        ];
        return response()->json($response, 200);
    }

    public function getSchool($id)
    {

        $school = $this->_school->_getSchoolById($id);

        if (!$school) {
            $response = [
                'success' => false,
                'description' => [
                    'message' => 'No records found'
                ]
            ];
            return response()->json($response, 200);
        }

        $response = [
            'success' => true,
            'description' => [
                'message' => 'successful',
                'school' => $school
            ]
        ];
        return response()->json($response, 200);
    }
}
