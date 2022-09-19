<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Programme;
use App\Models\School;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Exception;
use PDF;

set_time_limit(1200);

class StudentsController extends Controller
{
    private $_student;
    private $_school;
    private $_programme;

    public function __construct(Student $student, School $school, Programme $programme)
    {
        $this->_student = $student;
        $this->_school = $school;
        $this->_programme = $programme;
    }


    // @route  GET api/v1/students
    // @desc   Get students
    // @access Private
    public function getStudents(Request $request)
    {
        $schoolId = trim($request->get('school_id'));
        $academicYear = trim($request->get('academic_year'));

        $students = $this->_student->_getStudents($schoolId, $academicYear);

        return response()->json($students, 200);
    }


    // @route  POST api/v1/students
    // @desc   Add student
    // @access Private
    public function createStudent(Request $request)
    {
        $surname = trim($request->input('surname'));
        $otherNames = trim($request->input('other_names'));
        $applicationNumber = trim($request->input('application_number'));
        $programme = trim($request->input('programme_id'));
        $academicYear = trim($request->input('academic_year'));
        $phone = trim($request->input('phone'));
        $hall = trim($request->input('hall'));
        $schoolId = trim($request->input('school_id'));

        $school = $this->_school->_getSchoolById($schoolId);

        if (empty($surname) || empty($otherNames) || empty($applicationNumber) || empty($programme) || empty($academicYear) || empty($phone)) {
            return response()->json(['message' => 'Surname, othernames, application number, programme, academic year and phone number fields are all required'], 400);
        }

        if ($this->_student->_getStudentsByApplicationNumber($applicationNumber)) {
            return response()->json(['message' => 'Application number already exists. Application Number must be unique'], 400);
        }
        $phones = [];
        $phone = Helper::sanitizePhone($phone);
        $pwd = rand(00000, 99999);
        $phones[] = $phone;

        $payload = [
            'school_id' => $schoolId,
            'surname' => strtoupper($surname),
            'other_names' => strtoupper($otherNames),
            'application_number' => $applicationNumber,
            'pin' => $pwd,
            'programme_id' => $programme,
            'academic_year' => $academicYear,
            'status' => env('STATUS_PENDING'),
            'phone' => $phone,
            'hall' => strtoupper($hall),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        try {
            $id = $this->_student->_saveStudent($payload);
            $helper = new Helper();
            $helper->pushSMS($otherNames, $phone, $school->sender_id, $school->school_name, $academicYear, $applicationNumber, $pwd);
            $helper->pushBotSMS($phone, $school->sender_id);
            try {
                $helper->pushBulkVoiceSMS($phones);
                $student = $this->_student->_getStudent($id);
                return response()->json($student, 201);
            } catch (Exception $e) {
                return response()->json($student, 201);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }


    // @route  PUT api/v1/students/:id
    // @desc   Update student
    // @access Private
    public function updateStudent($id, Request $request)
    {
        $surname = trim($request->input('surname'));
        $otherNames = trim($request->input('other_names'));
        $applicationNumber = trim($request->input('application_number'));
        $programme = trim($request->input('programme_id'));
        $academicYear = trim($request->input('academic_year'));
        $phone = trim($request->input('phone'));
        $hall = trim($request->input('hall'));
        $schoolId = trim($request->input('school_id'));
        $feeReceipt = trim($request->input('fee_receipt'));
        $owingFees = trim($request->input('owing_fees'));

        $student = $this->_student->_getStudent($id);
        $school = $this->_school->_getSchoolById($schoolId);
        $fr = $student->fee_receipt;

        if (empty($surname) || empty($otherNames) || empty($applicationNumber) || empty($programme) || empty($academicYear) || empty($phone)) {
            return response()->json(['message' => 'Surname, othernames, application number, programme, academic year and phone number fields are all required'], 400);
        }

        $phone = Helper::sanitizePhone($phone);
        $payload = [
            'school_id' => $schoolId,
            'surname' => strtoupper($surname),
            'other_names' => strtoupper($otherNames),
            'application_number' => $applicationNumber,
            'programme_id' => $programme,
            'academic_year' => $academicYear,
            'phone' => $phone,
            'hall' => $hall,
            'fee_receipt' => $feeReceipt,
            'owing_fees' => $owingFees,
            'updated_at' => Carbon::now()
        ];

        try {
            $this->_student->_updateStudent($id, $payload);
            // check if student has been approved for printing letter through this update and send sms
            if ($fr === 0 && $feeReceipt === "1") {
                $message = "Hello " . strtoupper($otherNames) . "\nYou have successfully been approved to print your admission letter.\nAccess your admission letter by visiting https://admission.ebitsapps.com.\nUsername: " . $applicationNumber . "\nPassword: " . $student->pin;
                Helper::sendSMS($phone, urlencode($message), $school->sender_id);
            }
            $student = $this->_student->_getStudent($id);
            return response()->json($student, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  DELETE api/v1/students/:id
    // @desc   Delete student
    // @access Private
    public function deleteStudent($id)
    {
        $this->_student->_deleteStudent($id);
        return response()->json(200);
    }

    // @route  GET api/v1/students/actions
    // @desc   Block / unblock / sms students
    // @access Private
    public function studentsActions(Request $request)
    {
        $schoolId = trim($request->get('school_id'));
        $academicYear = trim($request->get('academic_year'));
        $action = trim($request->get('action'));

        switch ($action) {
            case 'block':
                $payload = [
                    'status' => env('STATUS_BLOCKED')
                ];
                try {
                    $this->_student->_blockOrUnblockPendingStudents($schoolId, $academicYear, env('STATUS_PENDING'), $payload);
                    return response()->json(200);
                } catch (Exception $e) {
                    throw $e;
                }
                break;
            case 'unblock':
                $payload = [
                    'status' => env('STATUS_PENDING')
                ];
                try {
                    $this->_student->_blockOrUnblockPendingStudents($schoolId, $academicYear, env('STATUS_BLOCKED'), $payload);
                    return response()->json(200);
                } catch (Exception $e) {
                    throw $e;
                }
            case 'sms':
                $students = $this->_student->_getStudentsByStatus($schoolId, $academicYear, env('STATUS_PENDING'));
                if ($students->isEmpty()) {
                    return response()->json(200);
                }
                $school = $this->_school->_getSchoolById($schoolId);
                $helper = new Helper();
                foreach ($students as $student) {
                    $pwd = Helper::generateCode();
                    try {
                        $helper->pushSMS($student->other_names, $student->phone, $school->sender_id, $school->school_name, $academicYear, $student->application_number, $pwd);
                    } catch (Exception $e) {
                        throw $e;
                    }
                }
                return response()->json(200);
                break;
        }
    }


    // @route  PUT api/v1/students/actions/:id
    // @desc   Block / Unblock / SMS Student
    // @access Private
    public function studentAction($id, Request $request)
    {
        $student = $this->_student->_getStudent($id);
        $action = trim($request->get('action'));

        switch ($action) {
            case 'block':
                $payload = [
                    'status' => env('STATUS_BLOCKED')
                ];
                try {
                    $this->_student->_updateStudent($id, $payload);
                    return response()->json(200);
                } catch (Exception $e) {
                    throw $e;
                }
                break;

            case 'unblock':
                $payload = [
                    'status' => env('STATUS_PENDING')
                ];
                try {
                    $this->_student->_updateStudent($id, $payload);
                    return response()->json(200);
                } catch (Exception $e) {
                    throw $e;
                }
                break;
            case 'sms':
                $school = $this->_school->_getSchoolById($student->school_id);
                $phones = [];
                $phones[] = $student->phone;
                $helper = new Helper();
                $pwd = Helper::generateCode();
                try {
                    $helper->pushSMS($student->other_names, $student->phone, $school->sender_id, $school->school_name, $student->academic_year, $student->application_number, $pwd);
                    return response()->json(200);
                } catch (Exception $e) {
                    throw $e;
                }
                break;
        }
    }


    // @route  POST api/v1/students/upload
    // @desc   Import students from Excel
    // @access Private
    public function uploadStudents(Request $request)
    {
        $programme = trim($request->input('programme_id'));
        $academicYear = trim($request->input('academic_year'));
        $sheet = trim($request->input('sheet'));
        $startRow = trim($request->input('start_row'));
        $schoolId = trim($request->input('school_id'));


        if (!$request->hasFile('excel') || empty($academicYear) || empty($sheet) || empty($startRow)) {
            return response()->json(['message' => 'The excel file, academic year, programme, sheet number and start row fields are all required'], 400);
        }

        $school = $this->_school->_getSchoolById($schoolId);

        $excel = $request->file('excel');
        $extension = $excel->getClientOriginalExtension();

        $extensions = ['csv', 'xls', 'xlsx'];

        if (!in_array($extension, $extensions)) {
            return response()->json(['message' => 'Make sure you upload an excel file of .xls, .xlsx, or .csv extension'], 400);
        }

        $size = $excel->getSize();

        if ($size > 5242880) {
            return response()->json(['message' => 'Make sure the file does not exceed 5MB'], 400);
        }

        $tmpPath = $excel->getPathname();
        try {
            $fileType = IOFactory::identify($tmpPath);
            $reader = IOFactory::createReader($fileType);
            $spreadsheet = $reader->load($tmpPath);
        } catch (Exception $e) {
            throw $e;
        }

        $sheetm = $spreadsheet->getSheet($sheet - 1);
        $sheetData = $sheetm->toArray(null, true, true, true);

        $cleanData = [];
        for ($row = $startRow; $row <= count($sheetData); $row++) {
            $cleanData[] = $sheetData[$row];
        }

        $studentData = [];
        $duplicates = [];
        $phones = [];
        $activeSheet = $spreadsheet->getActiveSheet();
        $lastColumn = $activeSheet->getHighestColumn();

        if (empty($programme)) {
            if ($lastColumn != "G") {
                return response()->json(['message' => 'Please make sure the column G is included as the last column in the excel sheet which contains programme ID'], 400);
            }
        } else {
            if ($lastColumn != "F") {
                return response()->json(['message' => 'Please make sure the column F is the last column included in the excel sheet when you select programme on the page'], 400);
            }
        }

        foreach ($cleanData as $c) {
            $excelDuplicates = $this->_checkDuplicates($cleanData, $c['A']);
            if ($excelDuplicates > 1) {
                return response()->json(['message' => 'There are some duplicates in the application numbers. Check and correct them before you upload.'], 400);
            }
        }
        $helper = new Helper();

        foreach ($cleanData as $c) {

            if ($c['A'] !== null) {
                $check = $this->_student->_getStudentsByApplicationNumber($c['A']);

                if ($check) {
                    // we will send this back to the user as duplicates to perform transfer request
                    $duplicates[] = [
                        'application_number' => $check->application_number,
                        'programme_id' => !empty($programme) ? $programme : $c['G'],
                        'academic_year' => $academicYear,
                        'school_id' => $schoolId
                    ];
                } else {
                    $name = "";
                    // some of them upload with all names in one column
                    if (empty($c['C'])) {
                        if (strpos($c['B'], ',') === false) {
                            return response()->json(['message' => 'Since column C is empty, it means you have all names in column B. Make sure the other names and surnames are separated by comma'], 400);
                        }
                        $name = explode(', ', $c['B']);
                    }
                    $phone = Helper::sanitizePhone($c['D']);
                    $phones[] = $phone;

                    $studentData[] = [
                        'school_id' => $schoolId,
                        'surname' => empty($c['C']) ? strtoupper($name[0]) : strtoupper($c['B']),
                        'other_names' => empty($c['C']) ? strtoupper($name[1]) : strtoupper($c['C']),
                        'application_number' => $c['A'],
                        'pin' => rand(00000, 99999),
                        'programme_id' => !empty($programme) ? $programme : trim($c['H']),
                        'academic_year' => $academicYear,
                        'status' => env('STATUS_PENDING'),
                        'phone' => $phone,
                        'hall' => $c['F'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }
        }
        try {
            $this->_student->_uploadStudents($studentData);
            try {
                $helper->pushBulkSMS($school->sender_id, $school->school_name, $studentData);
                $helper->pushBulkBotSMS($school->sender_id, $studentData);
                $helper->pushBulkVoiceSMS($phones);
                return response()->json($duplicates, 201);
            } catch (Exception $e) {
                return response()->json($duplicates, 201);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }


    private function _checkDuplicates(array $students, $applicationNumber)
    {
        $duplicates = 0;
        foreach ($students as $student) {
            if ($student['A'] == $applicationNumber) {
                $duplicates++;
            }
        }
        return $duplicates;
    }

    // @route  POST api/v1/students/actions/export-students
    // @desc   Export Students
    // @access Private
    public function exportStudents(Request $request)
    {
        $type = $request->input('type');
        $schoolId = $request->input('school_id');
        $students = $request->input('students');
        $feeType = $request->input('fee_type');

        $header = '';

        if (!empty($feeType)) {
            switch ($feeType) {
                case 'paid':
                    $header = "LIST OF PAID STUDENTS";
                    break;
                case 'notpaid':
                    $header = "LIST OF UNPAID STUDENTS";
            }
        } else {
            $header = "LIST OF STUDENTS";
        }

        switch ($type) {
            case 'EXCEL':
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $type = 'xlsx';

                $sheet->getStyle('A1:G1')->getFont()->setBold(true);
                $sheet->setCellValue('A1', 'APPLICATION NUMBER');
                $sheet->setCellValue('B1', 'SURNAME');
                $sheet->setCellValue('C1', 'OTHERNAMES');
                $sheet->setCellValue('D1', 'PROGRAMME');
                $sheet->setCellValue('E1', 'STATUS');
                $sheet->setCellValue('F1', 'ACADEMIC YEAR');
                $sheet->setCellValue('G1', 'PHONE');
                $sheet->setCellValue('H1', 'HALL');

                $row = 2;

                foreach ($students as $student) {
                    $status = '';
                    if ($student['status'] == 1) {
                        $status = 'ACCESSED';
                    } elseif ($student['status'] == 2) {
                        $status = 'BLOCKED';
                    } else {
                        $status = 'PENDING';
                    }

                    $sheet->setCellValue('A' . $row, $student['application_number']);
                    $sheet->setCellValue('B' . $row, $student['surname']);
                    $sheet->setCellValue('C' . $row, $student['other_names']);
                    $sheet->setCellValue('D' . $row, $this->_programme->_getProgramme($student['programme_id'])->programme);
                    $sheet->setCellValue('E' . $row, $status);
                    $sheet->setCellValue('F' . $row, $student['academic_year']);
                    $sheet->setCellValueExplicit('G' . $row, $student['phone'], DataType::TYPE_STRING);
                    $sheet->setCellValue('H' . $row, strtoupper($student['hall']));
                    $row++;
                }

                $fileName = date('YmdHis') . '.' . $type;
                $writer = new Xlsx($spreadsheet);
                $headers = [
                    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Transfer-Encoding: Binary',
                    'Content-Disposition: attachment; filename=' . $fileName
                ];

                ob_start();
                $writer->save("php://output");
                $xlsData = ob_get_contents();
                ob_end_clean();

                $content = base64_encode($xlsData);

                return response()->json(['content' => $content, 'fileName' => $fileName])->withHeaders($headers);
                break;
            case 'PDF':
                $school = $this->_school->_getSchoolById($schoolId);
                $stds = [];
                foreach ($students as $student) {
                    $status = '';
                    if ($student['status'] == 1) {
                        $status = 'ACCESSED';
                    } elseif ($student['status'] == 2) {
                        $status = 'BLOCKED';
                    } else {
                        $status = 'PENDING';
                    }
                    $stds[] = [
                        'application_number' => $student['application_number'],
                        'surname' => $student['surname'],
                        'other_names' => $student['other_names'],
                        'programme' => $this->_programme->_getProgramme($student['programme_id'])->programme,
                        'status' => $status,
                        'academic_year' => $student['academic_year'],
                        'phone' => $student['phone'],
                        'hall' => $student['hall'],
                    ];
                }
                $pdf = PDF::loadView('student-access.pdf-export', compact('school', 'stds', 'header'));
                $fileName = date('YmdHis');
                $content = $pdf->download()->getOriginalContent();
                $headers = [
                    'Content-Type: application/pdf',
                    'Content-Transfer-Encoding: Binary',
                    'Content-Disposition: attachment; filename=' . $fileName
                ];
                $content = base64_encode($content);

                return response()->json(['content' => $content, 'fileName' => $fileName])->withHeaders($headers);
                break;
        }
    }

    public function getReceiptStudents(Request $request)
    {
        $schoolId = trim($request->input('school_id'));
        $academicYear = trim($request->input('academic_year'));

        $students = $this->_student->_getReceiptStudents($schoolId, $academicYear);

        return response()->json($students);
    }

    public function getFeePaymentStudents(Request $request)
    {
        $schoolId = trim($request->input('school_id'));
        $academicYear = trim($request->input('academic_year'));
        $type = trim($request->input('type'));

        switch ($type) {
            case 'paid':
                $students = $this->_student->_getFeeStudents($schoolId, $academicYear, 1);
                return response()->json($students);
                break;
            case 'notpaid':
                $students = $this->_student->_getFeeStudents($schoolId, $academicYear, 0);
                return response()->json($students);
                break;
        }
    }


    public function uploadResultsStudents(Request $request)
    {
        $programme = trim($request->input('programme_id'));
        $schoolId = trim($request->input('school_id'));
        $students = $request->input('students');
        $academicYear = $request->input('academic_year');
        $payload = [];

        if (count($students) === 0) {
            return response()->json(['message' => 'The excel file cannot be empty'], 400);
        }

        try {
            foreach ($students as $data) {
                $student = $this->_student->_getStudentsByApplicationNumber($data['index_no']);
                if (!$student) {
                    $payload[] = [
                        'school_id' => $schoolId,
                        'application_number' => trim($data['index_no']),
                        'programme_id' => $programme,
                        'surname' => trim($data['surname']),
                        'other_names' => trim($data['other_names']),
                        'pin' => rand(00000, 99999),
                        'academic_year' => $academicYear,
                        'status' => env('STATUS_ACCESSED'),
                        'phone' => Helper::sanitizePhone($data['phone']),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }
            $this->_student->_uploadStudents($payload);
            return response()->json(201);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
