<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Document;
use App\Models\Fees;
use App\Models\Letter;
use App\Models\Programme;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use PDF;

set_time_limit(1200);

class StudentApiController extends Controller
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
        $appNumber = trim($request->input('app_number'));
        $pin = trim($request->input('pin'));

        if (empty($appNumber) || empty($pin)) {
            return response()->json(['code' => 101, 'message' => 'All fields required'], 400);
        }

        $student = $this->_student->_getStudentsByApplicationNumber($appNumber);

        if (!$student) {
            return response()->json(['code' => 102, 'message' => 'Invalid Credentials'], 400);
        }
        if ($student && $student->pin !== $pin) {
            return response()->json(['code' => 102, 'message' => 'Invalid Credentials'], 400);
        }
        if ($student->status === env('STATUS_BLOCKED')) {
            return response()->json(['code' => 104, 'message' => 'Account is blocked by school'], 400);
        }

        $document = $this->_document->_getDocuments($student->school_id);
        $letter = $this->_letter->_getLetter($student->school_id);
        $programme = $this->_programme->_getProgramme($student->programme_id)->programme;
        $fees = $this->_fees->_getFeesByAcademicYearAndProgramme($student->school_id, $student->academic_year, $student->programme_id);
        $school = $this->_school->_getSchoolById($student->school_id);

        if ($school->fee_payment === 1 && $student->fee_receipt === 0) {
            if (empty($letter->notice)) {
                return response()->json(['code' => 105, 'message' => 'Owing fees.', 'fee_letter' => ''], 400);
            }
            $pdf = PDF::loadView('student-access.api-fee-print', compact('school', 'student', 'letter', 'document', 'programme', 'fees'));
            $fileName = date('YmdHis');
            $content = $pdf->download()->getOriginalContent();
            Storage::disk('public')->put('fee_letters/' . $fileName . '.pdf', $content);
            $feeLetterUrl = url("/uploads/fee_letters/" . $fileName . '.pdf');
            return response()->json(['code' => 105, 'message' => 'Owing fees.', 'fee_letter' => $feeLetterUrl], 400);
        }

        $pdf = PDF::loadView('student-access.api-print', compact('school', 'student', 'letter', 'document', 'programme', 'fees'));

        $fileName = date('YmdHis');

        $content = $pdf->download()->getOriginalContent();
        Storage::disk('public')->put('letters/' . $fileName . '.pdf', $content);

        $letterUrl = url("/uploads/letters/" . $fileName . '.pdf');
        $docs = [];
        if (!empty($document->docs)) {
            $documents = explode(',', $document->docs);

            for ($i = 0; $i < count($documents); $i++) {
                $docs[] = url("/uploads/docs/" . $documents[$i]);
            }
        }

        $payload = [
            'status' => env('STATUS_ACCESSED')
        ];

        $this->_student->_updateStudent($student->id, $payload);

        return response()->json([
            'code' => 100,
            'student' => $student,
            'letter' => $letterUrl,
            'documents' => $docs
        ], 200);
    }

    public function resetPin(Request $request)
    {
        $appNumber =  trim($request->input('app_number'));
        $phone = trim($request->input('phone'));

        if (empty($appNumber) || empty($phone)) {
            return response()->json(['code' => 101, 'message' => 'All fields required'], 400);
        }

        $student = $this->_student->_getStudentByApplicationNumberAndPhone($appNumber, Helper::sanitizePhone($phone));
        if (!$student) {
            return response()->json(['code' => 106, 'message' => 'Phone number does not exist'], 400);
        }

        $school = $this->_school->_getSchoolById($student->school_id);
        $pin = Helper::generateCode();

        $payload = [
            'pin' => $pin,
            'status' => env('STATUS_PENDING'),
            'updated_at' => Carbon::now()
        ];

        $this->_student->_updateStudent($student->id, $payload);
        $message = "Hello " . $student->other_names . "\nYour new password is " . $pin . "\nDo not share it with anyone.";
        Helper::sendSMS($student->phone, urlencode($message), $school->sender_id);

        return response()->json(['code' => 100, 'message' => 'PIN is set. Check phone for SMS'], 400);
    }

    public function uploadReceipt(Request $request)
    {
        $appNumber = trim($request->input('app_number'));
        $pin = trim($request->input('pin'));

        if (empty($appNumber) || empty($pin) || !$request->hasFile('file')) {
            return response()->json(['code' => 101, 'message' => 'All fields required'], 400);
        }

        $access = $this->_student->_getStudentsByApplicationNumber($appNumber);

        if (!$access) {
            return response()->json(['code' => 102, 'message' => 'Invalid Credentials'], 400);
        }
        if ($access && $access->pin !== $pin) {
            return response()->json(['code' => 102, 'message' => 'Invalid Credentials'], 400);
        }

        $student = $this->_student->_getStudentsByApplicationNumberAndSchool($access->school_id, $access->application_number);
        if (!$student) {
            return response()->json(['code' => 103, 'message' => 'No admission found'], 400);
        }
        if ($student->status === env('STATUS_BLOCKED')) {
            return response()->json(['code' => 104, 'message' => 'Account is blocked by school'], 400);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $x = ['png', 'jpg', 'jpeg', 'pdf'];
        if (!in_array($extension, $x)) {
            return response()->json(['code' => 107, 'message' => 'File must be in .png, .jpg, .jpeg, or .pdf format'], 400);
        }

        $size = $file->getSize();

        if ($size > 1048576) {
            return response()->json(['code' => 108, 'message' => 'File must not be more than 1MB'], 400);
        }

        // Everything is ok now...upload
        $fileName = date('YmdHis') . '.' . $extension;
        Storage::disk('public')->put('receipts/' . $fileName, File::get($file));
        $fileUrl = url('uploads/receipts/' . $fileName);

        $receipts = [];
        if (!empty($student->receipt)) {
            $receipts = explode(',', $student->receipt);
            $receipts[] = $fileUrl;
        } else {
            $receipts[] = $fileUrl;
        }

        $payload = [
            'receipt' => implode(',', $receipts),
        ];

        try {
            $this->_student->_updateStudent($student->id, $payload);
            return response()->json(['code' => 100, 'receipt' => $fileUrl], 200);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
