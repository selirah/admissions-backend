<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Programme;
use App\Models\School;
use App\Models\Student;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use PDF;

class TransfersController extends Controller
{
    private $_student;
    private $_school;
    private $_programme;
    private $_transfer;

    public function __construct(Student $student, School $school, Programme $programme, Transfer $transfer)
    {
        $this->_student = $student;
        $this->_school = $school;
        $this->_programme = $programme;
        $this->_transfer = $transfer;
    }

    // @route  GET api/v1/transfers
    // @desc   Get students
    // @access Private
    public function getTransfers(Request $request)
    {
        $schoolId = trim($request->get('school_id'));
        $academicYear = trim($request->get('academic_year'));
        $transfers = $this->_transfer->_getTransfers($academicYear, $schoolId);
        return response()->json($transfers, 200);
    }

    // @route  POST api/v1/transfers
    // @desc   Add Transfer
    // @access Private
    public function createTransfer(Request $request)
    {
        $applicationNumber = trim($request->input('application_number'));
        $destinationProgramme = trim($request->input('programme_id'));
        $academicYear = trim($request->input('academic_year'));
        $destinationSchool = trim($request->input('school_id'));

        if (empty($applicationNumber) || empty($destinationProgramme) || empty($academicYear)) {
            return response()->json(['message' => 'Make sure all fields are filled'], 400);
        }

        $student = $this->_student->_getStudentsByApplicationNumber($applicationNumber);
        if (!$student) {
            return response()->json(['message' => 'Student with this application number does not exist'], 400);
        }

        if ($student &&  $student->school_id === $destinationSchool) {
            return response()->json(['message' => 'Student you are trying to transfer is already in your school'], 400);
        }

        $payload = [
            'student_id' => $student->id,
            'source_school' => $student->school_id,
            'destination_school' => $destinationSchool,
            'source_programme' =>  $student->programme_id,
            'destination_programme' => $destinationProgramme,
            'academic_year' => $academicYear,
            'status' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        try {
            $id = $this->_transfer->_save($payload);
            $transfer = $this->_transfer->_getTransfer($id);
            return response()->json($transfer, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/transfers/duplicates
    // @desc   Add bulk transfer from duplicate upload
    // @access Private
    public function transferDuplicates(Request $request)
    {
        $duplicates = $request->input('duplicates');
        $payload = [];
        foreach ($duplicates as $duplicate) {
            $student = $this->_student->_getStudentsByApplicationNumber($duplicate['application_number']);
            // make sure student does not belong to the same school transfer is taken place
            if ($student && $student->school_id !== $duplicate['school_id']) {
                $payload[] = [
                    'student_id' => $student->id,
                    'source_school' => $student->school_id,
                    'destination_school' => $duplicate['school_id'],
                    'source_programme' => $student->programme_id,
                    'destination_programme' => $duplicate['programme_id'],
                    'academic_year' => $duplicate['academic_year'],
                    'status' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        }
        try {
            $this->_transfer->_bulkSave($payload);
            return response()->json(201);
        } catch (Exception $e) {
            throw $e;
        }
    }


    // @route  DELETE api/v1/transfer/:id
    // @desc   Delete Transfer
    // @access Private
    public function deleteTransfer($id)
    {
        $this->_transfer->_delete($id);
        return response()->json(200);
    }


    // @route  GET api/v1/transfers/:id
    // @desc   Add Transfer
    // @access Private
    public function performAction($id, Request $request)
    {
        $action = trim($request->get('action'));

        switch ($action) {
            case 'grant':
                // grant transfer request
                try {
                    $payload = [
                        'status' => 1,
                        'updated_at' => Carbon::now()
                    ];
                    $this->_transfer->_update($id, $payload);
                    $transfer = $this->_transfer->_get($id);
                    // Now transfer student
                    $actionPayload = [
                        'school_id' => $transfer->destination_school,
                        'programme_id' => $transfer->destination_programme,
                    ];
                    $this->_student->_updateStudent($transfer->student_id, $actionPayload);
                    $srcSchool = $this->_school->_getSchoolById($transfer->source_school);
                    $desSchool = $this->_school->_getSchoolById($transfer->destination_school);
                    $programme = $this->_programme->_getProgramme($transfer->destination_programme);
                    $student = $this->_student->_getStudent($transfer->student_id);
                    $name = $student->other_names . " " . $student->surname;
                    $helper = new Helper();
                    $helper->transferSMS($name, $student->phone, $srcSchool->sender_id, $srcSchool->school_name, $desSchool->school_name, $programme->programme, $transfer->academic_year);
                    return response()->json(200);
                } catch (Exception $e) {
                    throw $e;
                }
                break;
            case 'deny':
                try {
                    $payload = [
                        'status' => 2,
                        'updated_at' => Carbon::now()
                    ];
                    $this->_transfer->_update($id, $payload);
                    return response()->json(200);
                } catch (Exception $e) {
                    throw $e;
                }
                break;
        }
    }

    // @route  POST api/v1/transfers/export-transfers
    // @desc   Export Transfers
    // @access Private
    public function exportTransferRequests(Request $request)
    {
        $schoolId = trim($request->input('school_id'));
        $academicYear = trim($request->input('academic_year'));
        $transfers = $this->_transfer->_getTransfers($academicYear, $schoolId);
        $school = $this->_school->_getSchoolById($schoolId);

        $trans = [];

        foreach ($transfers as $transfer) {
            if ($transfer->destination_school !== intval($schoolId) && $transfer->status === 0) {
                $trans[] = $transfer;
            }
        }

        if (count($trans) > 0) {
            $pdf = PDF::loadView('student-access.transfer-export', compact('trans', 'school'));
            $fileName = date('YmdHis');
            $content = $pdf->download()->getOriginalContent();
            $headers = [
                'Content-Type: application/pdf',
                'Content-Transfer-Encoding: Binary',
                'Content-Disposition: attachment; filename=' . $fileName
            ];
            $content = base64_encode($content);

            return response()->json(['content' => $content, 'fileName' => $fileName])->withHeaders($headers);
        }
    }

    // @route  POST api/v1/transfers/get-count
    // @desc   Transfers count
    // @access Private

    public function getTransfersCount(Request $request)
    {
        $schoolId = trim($request->input('school_id'));
        $academicYear = trim($request->input('academic_year'));
        $transfers = $this->_transfer->_getTransfers($academicYear, $schoolId);

        $trans = [];

        foreach ($transfers as $transfer) {
            if ($transfer->destination_school !== intval($schoolId) && $transfer->status === 0) {
                $trans[] = $transfer;
            }
        }
        return response()->json(count($trans));
    }
}
