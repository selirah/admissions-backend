<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    private $_school;
    private $_student;
    private $_category;

    public function __construct(School $school, Student $student, Category $category)
    {
        $this->_school = $school;
        $this->_student = $student;
        $this->_category = $category;
    }

    // @route  GET api/v1/admin/clients?academicYear=
    // @desc   Get Schools
    // @access Private
    public function getClients(Request $request)
    {
        $academicYear = trim($request->get('academic_year'));
        $schools = $this->_school->_getAllSchools();

        if ($schools->isEmpty()) {
            return response()->json([], 200);
        }
        $clients = [];
        $overallStudents = 0;
        $overallPending = 0;
        $overallAccessed = 0;
        $overallBlocked = 0;

        foreach ($schools as $school) {
            $totalStudents  = $this->_student->_getStudentsCount($school->id, $academicYear);
            $overallStudents += $totalStudents;
            $totalPending = $this->_student->_getStudentsStatusCount($school->id, $academicYear, env('STATUS_PENDING'));
            $overallPending += $totalPending;
            $totalAccessed = $this->_student->_getStudentsStatusCount($school->id, $academicYear, env('STATUS_ACCESSED'));
            $overallAccessed += $totalAccessed;
            $totalABlocked = $this->_student->_getStudentsStatusCount($school->id, $academicYear, env('STATUS_BLOCKED'));
            $overallBlocked += $totalABlocked;
            $clients[] = [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'user_id' => $school->user_id,
                'category' => $this->_category->_getCategory($school->category_id)->category,
                'region' => $school->region,
                'town' => $school->town,
                'phone' => $school->phone,
                'total_students' => $totalStudents,
                'total_students_pending' => $totalPending,
                'total_students_accessed' => $totalAccessed,
                'total_students_blocked' => $totalABlocked,
                'created_at' => $school->created_at
            ];
        }
        return response()->json($clients, 200);
    }


    // @route  GET api/v1/admin/clients/impersonate/client
    // @desc   Impersonate Client
    // @access Private
    public function impersonate(Request $request)
    {
        $action = trim($request->get('action'));
        $id = $request->input('id');
        $adminId = $request->input('admin_id');
        $request->user()->token()->revoke();
        $request->user()->token()->delete();

        try {
            $user  = Auth::guard('web')->loginUsingId($id);
            Auth::setUser($user);
            $auth = $request->user();
            $tokenResult = $auth->createToken('Personal Access Token');
            $auth['token'] = $tokenResult->accessToken;
            $auth['admin_id'] = $adminId;
        } catch (Exception $e) {
            throw $e;
        }

        switch ($action) {
            case 'client':
                $school = $this->_school->_getSchool($auth->id);
                if ($school) {
                    $auth['school_id'] = $school->id;
                } else {
                    $auth['school_id'] = 0;
                }
                return response()->json($auth, 201);
                break;
            case 'admin':
                try {
                    $auth['school_id'] = 0;
                    return response()->json($auth, 201);
                } catch (Exception $e) {
                    throw $e;
                }
                break;
        }
    }
}
