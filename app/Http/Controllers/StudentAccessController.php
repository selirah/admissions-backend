<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Fees;
use App\Models\Letter;
use App\Models\Programme;
use App\Models\School;
use App\Models\Student;
use Exception;
use Illuminate\Http\Request;

class StudentAccessController extends Controller
{
    private $_school;
    private $_student;
    private $_letter;
    private $_document;
    private $_programme;
    private $_fees;

    public function __construct(School $school, Student $student, Letter $letter, Document $document, Programme $programme, Fees $fees)
    {
        $this->_school = $school;
        $this->_student = $student;
        $this->_letter = $letter;
        $this->_document = $document;
        $this->_programme = $programme;
        $this->_fees = $fees;
    }


    public function retrieveLetter(Request $request)
    {

        $userId = $request->session()->get('id');
        $schoolId = $request->session()->get('school_id');

        if (empty($userId) && empty($schoolId)) {
            return redirect('/login');
        }

        $school = $this->_school->_getSchoolById($schoolId);
        $student = $this->_student->_getStudent($userId);
        $letter = $this->_letter->_getLetter($schoolId);
        $document = $this->_document->_getDocuments($schoolId);
        $programme = $this->_programme->_getProgramme($student->programme_id)->programme;
        $fees = $this->_fees->_getFeesByAcademicYearAndProgramme($schoolId, $student->academic_year, $student->programme_id);

        return view('student-access.student-access', compact('school', 'student', 'letter', 'document', 'programme', 'fees'));
    }

    public function printLetter(Request $request)
    {
        $userId = $request->session()->get('id');
        $schoolId = $request->session()->get('school_id');

        if (empty($userId) && empty($schoolId)) {
            return redirect('/login');
        }

        $school = $this->_school->_getSchoolById($schoolId);
        $student = $this->_student->_getStudent($userId);
        $letter = $this->_letter->_getLetter($schoolId);
        $document = $this->_document->_getDocuments($schoolId);
        $programme = $this->_programme->_getProgramme($student->programme_id)->programme;
        $fees = $this->_fees->_getFeesByAcademicYearAndProgramme($schoolId, $student->academic_year, $student->programme_id);

        $payload = [
            'status' => env('STATUS_ACCESSED')
        ];

        $this->_student->_updateStudent($userId, $payload);

        return view('student-access.print_out', compact('school', 'student', 'letter', 'document', 'programme', 'fees'));
    }

    public function printNotice(Request $request)
    {
        $userId = $request->session()->get('id');
        $schoolId = $request->session()->get('school_id');

        if (empty($userId) && empty($schoolId)) {
            return redirect('/login');
        }

        $school = $this->_school->_getSchoolById($schoolId);
        $student = $this->_student->_getStudent($userId);
        $letter = $this->_letter->_getLetter($schoolId);
        $document = $this->_document->_getDocuments($schoolId);
        $programme = $this->_programme->_getProgramme($student->programme_id)->programme;
        $fees = $this->_fees->_getFeesByAcademicYearAndProgramme($schoolId, $student->academic_year, $student->programme_id);

        $payload = [
            'status' => env('STATUS_ACCESSED')
        ];

        $this->_student->_updateStudent($userId, $payload);

        return view('student-access.print-notice', compact('school', 'student', 'letter', 'document', 'programme', 'fees'));
    }

    public function downloadDocuments(Request $request)
    {
        $userId = $request->session()->get('id');
        $schoolId = $request->session()->get('school_id');

        if (empty($userId) && empty($schoolId)) {
            return redirect('/login');
        }
        $document = $this->_document->_getDocuments($schoolId);
        $docs = explode(',', $document->docs);

        $documents = [];
        for ($i = 0; $i < count($docs); $i++) {
            $documents[] = str_replace('http://admissions.test/uploads/', '', $docs[$i]);
        }

        return view('student-access.download', compact('documents'));
    }

    public function uploadReceipt()
    {
        return view('student-access.upload');
    }

    public function uploadReceiptPost(Request $request)
    {
        $userId = $request->session()->get('id');

        $imageName = date('YmdHis') . '.' . $request->image->extension();

        $request->image->move(public_path('uploads/receipts'), $imageName);

        $imageUrl = url('uploads/receipts/' . $imageName);

        $student = $this->_student->_getStudent($userId);

        $receipts = [];
        if (!empty($student->receipt)) {
            $receipts = explode(',', $student->receipt);
            $receipts[] = $imageUrl;
        } else {
            $receipts[] = $imageUrl;
        }

        $payload = [
            'receipt' => implode(',', $receipts),
        ];

        try {
            $this->_student->_updateStudent($student->id, $payload);
            return back()
                ->with('success', 'You have successfully uploaded receipt. Please wait for approval from the school via SMS');
        } catch (Exception $e) {
            throw $e;
        }
    }
}
