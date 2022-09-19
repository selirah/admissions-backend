<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Courses;
use App\Models\Academics;
use App\Models\Student;
use App\Models\School;
use App\Models\Result;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class AcademicsController extends Controller
{
    private $_academics;
    private $_courses;
    private $_student;
    private $_results;
    private $_school;

    public function __construct(Academics $academics, Courses $courses, Student $student, School $school, Result $result)
    {
        $this->_academics = $academics;
        $this->_courses = $courses;
        $this->_student = $student;
        $this->_school = $school;
        $this->_results = $result;
    }

    // @route  POST api/v1/academics/import-courses
    // @desc   Import courses
    // @access Private
    public function importCourses(Request $request)
    {
        $schoolId = trim($request->input('school_id'));
        $courses = $request->input('courses');
        $payload = [];
        if (count($courses) === 0) {
            return response()->json(['message' => 'The excel file cannot be empty'], 400);
        }
        foreach ($courses as $course) {
            $c = $this->_courses->_getCourse($course['course_code']);
            if (!$c) {
                $payload[] = [
                    'school_id' => $schoolId,
                    'course_code' => trim($course['course_code']),
                    'course' => trim($course['course']),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        }
        try {
            if (count($payload) > 0) {
                $this->_courses->_save($payload);
                return response()->json(201);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  GET api/v1/academics/courses
    // @desc   Get courses
    // @access Private
    public function getCourses(Request $request)
    {
        $schoolId = trim($request->get('school_id'));
        $courses = $this->_courses->_getCourses($schoolId);
        return response()->json($courses, 200);
    }

    // @route  POST api/v1/academics/import-results
    // @desc   Import results
    // @access Private
    public function importResults(Request $request)
    {
        $results = $request->input('results');
        $programme = trim($request->input('programme_id'));
        $semester = trim($request->input('semester'));
        $schoolId = trim($request->input('school_id'));
        $year = trim($request->input('year'));
        $courseCode = trim($request->input('course_code'));

        $payload = [];

        if (count($results) === 0) {
            return response()->json(['message' => 'The excel file cannot be empty'], 400);
        }

        $check = $this->_results->_checkResult($schoolId, $courseCode, $year, $semester, $programme);

        if ($check) {
            return response()->json(['message' => 'This result already exist'], 400);
        }

        $data = [
            'school_id' => $schoolId,
            'programme_id' => $programme,
            'course_code' => $courseCode,
            'semester' => $semester,
            'year' => $year,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        try {
            $resultId = $this->_results->_save($data);
            foreach ($results as $result) {
                $student = $this->_student->_getStudentsByApplicationNumber($result['index_no']);
                if ($student) {
                    $payload[] = [
                        'school_id' => $schoolId,
                        'result_id' => $resultId,
                        'index_no' => trim($result['index_no']),
                        'course_code' => $courseCode,
                        'programme_id' => $programme,
                        'total' => trim($result['total']),
                        'grade' => trim($result['grade']),
                        'year' => $year,
                        'semester' => $semester,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }
            $this->_academics->_save($payload);
            return response()->json(201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  GET api/v1/academics/get-academics
    // @desc   Get academics
    // @access Private
    public function getAcademics($resultId)
    {
        $academics = $this->_academics->_getResults($resultId);
        return response()->json($academics, 200);
    }

    // @route  GET api/v1/academics/results/get-results'
    // @desc   Get results
    // @access Private
    public function getResults(Request $request)
    {
        $schoolId = trim($request->get('school_id'));
        $courseCode = trim($request->get('course_code'));
        $results = $this->_results->_getResults($schoolId, $courseCode);
        return response()->json($results, 200);
    }

    // @route  PUT api/v1/academics/results/publish:id
    // @desc   Publish Results
    // @access Private
    public function publishResults($id, Request $request)
    {
        try {
            $payload = [
                'published' => 1
            ];
            $this->_results->_publishResults($id, $payload);
            return response()->json(201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function publishStudentResults(Request $request)
    {
        $schoolId = trim($request->input('school_id'));
        $appNo = trim($request->input('index_no'));
        $school = $this->_school->_getSchoolById($schoolId);
        $student = $this->_student->_getStudentsByApplicationNumber($appNo);

        try {
            // send SMS
            if ($student) {
                $helper = new Helper();
                $helper->pushStudentResultSMS($school->sender_id, $student);
            }
            return response()->json(201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteResults($id)
    {
        try {
            $this->_academics->_deleteStudentResults($id);
            $this->_results->_deleteResults($id);
            return response()->json(200);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function sendNotificationToStudents(Request $request)
    {
        $schoolId = trim($request->get('school_id'));
        $results = $this->_results->_getNonSMSResults();
        $school = $this->_school->_getSchoolById($schoolId);
        $notOwingStudents = [];
        $owingStudents = [];
        $ids = [];

        if ($results->isNotEmpty()) {
            foreach ($results as $result) {
                $ids[] = $result->id;
                $academics = $this->_academics->_getResults($result->id);
                foreach ($academics as $aca) {
                    if ($aca->owing_fees !== '1') {
                        $notOwingStudents[] = [
                            'other_names' => $aca->other_names,
                            'phone' => $aca->phone,
                            'application_number' => $aca->application_number,
                            'pin' => $aca->pin,
                        ];
                    } else {
                        $owingStudents[] = [
                            'other_names' => $aca->other_names,
                            'phone' => $aca->phone,
                            'application_number' => $aca->application_number,
                            'pin' => $aca->pin,
                        ];
                    }
                }
            }
        }

        try {
            if (count($ids) > 0) {
                $payload = [
                    'sms' => 1
                ];
                $this->_results->_updateSMSField($ids, $payload);
            }
            if (count($notOwingStudents) > 0) {
                $sorted = array_unique($notOwingStudents, SORT_REGULAR);
                $helper = new Helper();
                $helper->pushResultsBulkSMS($school->sender_id, $sorted);
            }
            if (count($owingStudents) > 0) {
                $sorted = array_unique($owingStudents, SORT_REGULAR);
                $helper = new Helper();
                $helper->pushResultsOwingBulkSMS($school->sender_id, $sorted);
            }
            return response()->json(200);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
