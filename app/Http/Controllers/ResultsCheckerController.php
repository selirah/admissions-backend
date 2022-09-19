<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Courses;
use App\Models\Academics;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Exception;

class ResultsCheckerController extends Controller
{
    private $_academics;
    private $_courses;
    private $_student;
    private $_school;

    public function __construct(Academics $academics, Courses $courses, Student $student, School $school)
    {
        $this->_academics = $academics;
        $this->_courses = $courses;
        $this->_student = $student;
        $this->_school = $school;
    }

    public function resultsChecker(Request $request)
    {
        $userId = $request->session()->get('id');
        $schoolId = $request->session()->get('school_id');

        if (empty($userId) && empty($schoolId)) {
            return redirect('/login');
        }

        return view('results-checker.results-checker');
    }

    public function displayResults(Request $request)
    {
        $userId = $request->session()->get('id');
        $schoolId = $request->session()->get('school_id');

        if (empty($userId) && empty($schoolId)) {
            return redirect('/login');
        }

        $year = $request->post('year');
        $semester = $request->post('semester');

        $studentInfo = $this->_student->_getStudent($userId);

        if (empty($year) || empty($semester)) {
            $request->session()->flash('flash_message', 'Please select a Year/Level and a Semester');
            $request->session()->flash('flash_type', 'alert-danger');
            return back();
        }

        if ($studentInfo->owing_fees == 1) {
            $request->session()->flash('flash_message', 'Results with-held. Please ensure you have made full payments of all Bills');
            $request->session()->flash('flash_type', 'alert-info');
            return back();
        }

        $results = $this->_academics->_getStudentResults($schoolId, $studentInfo->application_number, $semester, $year);

        return view('results-checker.display-results', compact('year', 'semester', 'results'));
    }

    public function printResults(Request $request)
    {
        $userId = $request->session()->get('id');
        $schoolId = $request->session()->get('school_id');

        if (empty($userId) && empty($schoolId)) {
            return redirect('/login');
        }

        $year = $request->get('year');
        $semester = $request->get('semester');

        $studentInfo = $this->_student->_getStudent($userId);

        if (empty($year) || empty($semester)) {
            $request->session()->flash('flash_message', 'Please select a Year/Level and a Semester');
            $request->session()->flash('flash_type', 'alert-danger');
            return back();
        }

        $school = $this->_school->_getSchoolById($schoolId);
        $results = $this->_academics->_getStudentResults($schoolId, $studentInfo->application_number, $semester, $year);

        return view('results-checker.print-results', compact('school', 'year', 'semester', 'results'));
    }
}
