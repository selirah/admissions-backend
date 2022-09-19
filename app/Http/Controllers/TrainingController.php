<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Training;
use App\Models\TrainingList;
use App\Models\TrainingAttendees;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Exception;
use PDF;

class TrainingController extends Controller
{
    private $_school;
    private $_training;
    private $_trainingList;
    private $_trainingAttendees;

    public function __construct(School $school, Training $training, TrainingList $trainingList, TrainingAttendees $trainingAttendees)
    {
        $this->_school = $school;
        $this->_training = $training;
        $this->_trainingList = $trainingList;
        $this->_trainingAttendees = $trainingAttendees;
    }

    // @route  GET api/v1/trainings/schools
    // @desc   Get training schools
    // @access Public
    public function getTrainingSchools()
    {
        $schools = $this->_school->_getAllSchools();

        if ($schools->isEmpty()) {
            return response()->json([], 200);
        }
        $trainingSchools = [];
        foreach ($schools as $school) {
            $year = date('Y');
            $check = $this->_trainingList->_getTrainingBasedOnSchool($school->id, $year);
            if (!$check) {
                $trainingSchools[] = $school;
            }
        }
        return response()->json($trainingSchools, 200);
    }


    // @route  GET api/v1/trainings
    // @desc   Get trainings
    // @access Public
    public function getTrainings(Request $request)
    {
        $year = trim($request->get('year'));
        $trainings = $this->_training->_getTrainings($year);

        if ($trainings->isEmpty()) {
            return response()->json([], 200);
        }

        $t = [];
        foreach ($trainings as $training) {
            $num = $this->_trainingList->_count($training->id, $year);
            $t[] = [
                'id' => $training->id,
                'year' => $training->year,
                'location' => $training->location,
                'date_time' => $training->date_time,
                'list_total' => $num,
                'created_at' => $training->created_at,
                'updated_at' => $training->updated_at
            ];
        }
        return response()->json($t, 200);
    }


    // @route  POST api/v1/trainings
    // @desc   Add training
    // @access Public
    public function addTraining(Request $request)
    {
        $year = trim($request->input('year'));
        $location = trim($request->input('location'));
        $dateTime = trim($request->input('date_time'));

        if (empty($year) || empty($location) || empty($dateTime)) {
            return response()->json(["message" => "All fields are required"], 400);
        }
        $trainingData = [
            'year' => $year,
            'location' => $location,
            'date_time' => $dateTime,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        try {
            $id = $this->_training->_save($trainingData);
            $training = $this->_training->_getTraining($id);
            $t = [
                'id' => $training->id,
                'year' => $training->year,
                'location' => $training->location,
                'date_time' => $training->date_time,
                'list_total' => 0,
                'created_at' => $training->created_at,
                'updated_at' => $training->updated_at
            ];
            return response()->json($t, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  PUT api/v1/trainings/:id
    // @desc   Update training
    // @access Public
    public function updateTraining($id, Request $request)
    {
        $year = trim($request->input('year'));
        $location = trim($request->input('location'));
        $dateTime = trim($request->input('date_time'));

        if (empty($year) || empty($location) || empty($dateTime)) {
            return response()->json(["message" => "All fields are required"], 400);
        }

        $trainingData = [
            'year' => $year,
            'location' => $location,
            'date_time' => $dateTime,
            'updated_at' => Carbon::now()
        ];

        try {
            $this->_training->_update($id, $trainingData);
            $training = $this->_training->_getTraining($id);
            $t = [
                'id' => $training->id,
                'year' => $training->year,
                'location' => $training->location,
                'date_time' => $training->date_time,
                'list_total' => 0,
                'created_at' => $training->created_at,
                'updated_at' => $training->updated_at
            ];
            return response()->json($t, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/trainings/import
    // @desc   Import training
    // @access Public
    public function importTrainings(Request $request)
    {
        $year = trim($request->input('year'));
        $sheet = trim($request->input('sheet'));
        $startRow = trim($request->input('start_row'));

        if (!$request->hasFile('excel') || empty($sheet) || empty($startRow) || empty($year)) {
            return response()->json(["message" => "All fields are required"], 400);
        }

        $excel = $request->file('excel');
        $extension = $excel->getClientOriginalExtension();

        $extensions = ['csv', 'xls', 'xlsx', 'ods'];

        if (!in_array($extension, $extensions)) {
            return response()->json(["message" => "Make sure you upload an excel file of .xls, .xlsx, ods or .csv extension"], 400);
        }

        $size = $excel->getSize();

        if ($size > 5242880) {
            return response()->json(["message" => "Make sure the file does not exceed 5MB"], 400);
        }

        $tmpPath = $excel->getPathname();
        try {
            $fileType = IOFactory::identify($tmpPath);
            $reader = IOFactory::createReader($fileType);
            $spreadsheet = $reader->load($tmpPath);
        } catch (Exception $e) {
            throw $e;
        }

        $sheet = $spreadsheet->getSheet($sheet - 1);
        $sheetData = $sheet->toArray(null, true, true, true);

        $cleanData = [];
        for ($row = $startRow; $row <= count($sheetData); $row++) {
            $cleanData[] = $sheetData[$row];
        }

        $trainingData = [];
        foreach ($cleanData as $c) {
            $trainingData[] = [
                'year' => $year,
                'location' => trim($c['A']),
                'date_time' => date('Y-m-d H:i:s', strtotime($c['B'])),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        try {
            $this->_training->_saveTrainings($trainingData);
            return response()->json(201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  DELETE api/v1/trainings/:id
    // @desc   Delete training
    // @access Public
    public function deleteTraining($id)
    {
        $this->_training->_delete($id);
        return response()->json(200);
    }

    // @route  POST api/v1/training-list
    // @desc   Add training
    // @access Public
    public function addTrainingToList(Request $request)
    {
        $trainingId = trim($request->input('training_id'));
        $schoolId = trim($request->input('school_id'));
        $nameOne = trim($request->input('name_one'));
        $nameTwo = trim($request->input('name_two'));
        $phoneOne = trim($request->input('phone_one'));
        $phoneTwo = trim($request->input('phone_two'));


        if (empty($schoolId) || empty($nameOne) || empty($phoneOne)) {
            return response()->json(["message" => "School, Name and Phone number are required"], 400);
        }

        $training = $this->_training->_getTraining($trainingId);
        $school = $this->_school->_getSchoolById($schoolId);
        $year = $training->year;

        $check = $this->_trainingList->_getTrainingBasedOnSchool($schoolId, $year);

        if ($check) {
            return response()->json(["message" => "This school already has representations"], 400);
        }

        if ($request->hasFile('picture_one')) {
            $pictureOne = $request->file('picture_one');
            $extensionOne = $pictureOne->getClientOriginalExtension();
            $sizeOne = $pictureOne->getSize();
            if ($sizeOne > 1048576) {
                return response()->json(["message" => "Make sure the picture does not exceed 1MB"], 400);
            }
            $pictureOneName = date('YmdHis') . rand(0000, 9999) . '.' . $extensionOne;
            Storage::disk('public')->put('training/' . $pictureOneName, File::get($pictureOne));
            $pictureOneUrl = url('uploads/training/' . $pictureOneName);
        } else {
            $pictureOneUrl = null;
        }

        if ($request->hasFile('picture_two')) {
            $pictureTwo = $request->file('picture_two');
            $extensionTwo = $pictureTwo->getClientOriginalExtension();
            $sizeTwo = $pictureTwo->getSize();
            if ($sizeTwo > 1048576) {
                return response()->json(["message" => "Make sure the picture does not exceed 1MB"], 400);
            }
            $pictureTwoName = date('YmdHis') . rand(0000, 9999) . '.' . $extensionTwo;
            Storage::disk('public')->put('training/' . $pictureTwoName, File::get($pictureTwo));
            $pictureTwoUrl = url('uploads/training/' . $pictureTwoName);
        } else {
            $pictureTwoUrl = null;
        }

        try {
            $traningListData = [
                'school_id' => $schoolId,
                'training_id' => $trainingId,
                'year' => $year,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            $trainingListId = $this->_trainingList->_save($traningListData);

            $attendees = [
                'training_list_id' => $trainingListId,
                'name_one' => $nameOne,
                'phone_one' => Helper::sanitizePhone($phoneOne),
                'picture_one' => $pictureOneUrl,
                'name_two' => !empty($nameTwo) ? $nameTwo : null,
                'phone_two' => !empty($phoneTwo) ? Helper::sanitizePhone($phoneTwo) : null,
                'picture_two' => $pictureTwoUrl,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $this->_trainingAttendees->_save($attendees);

            try {
                $helper = new Helper();
                $helper->trainingSMS(Helper::sanitizePhone($phoneOne), $nameOne, $school->school_name, 'PRINCOF',  $training->location, date("F j, Y, g:i a", strtotime($training->date_time)));
                if (!empty($nameTwo) && !empty($phoneTwo)) {
                    $helper->trainingSMS(Helper::sanitizePhone($phoneTwo), $nameTwo, $school->school_name, 'PRINCOF', $training->location, date("F j, Y, g:i a", strtotime($training->date_time)));
                }
                return response()->json(201);
            } catch (Exception $e) {
                return response()->json(201);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }


    // @route  GET api/v1/training-list
    // @desc   Get training list
    // @access Public
    public function getTrainingList(Request $request)
    {
        $trainingId = trim($request->get('training_id'));
        $year = trim($request->get('year'));
        $trainingList = [];

        $trainingList = $this->_trainingList->_getTrainingListByTrainingIdAndYear($trainingId, $year);
        $t = [];
        foreach ($trainingList as $training) {
            $t[] = [
                'id' => $training->id,
                'school_name' => $training->school_name,
                'region' => $training->region,
                'town' => $training->town,
                'location' => $training->location,
                'date_time' => $training->date_time,
                'reps' => [
                    'rep_one' => $training->name_one,
                    'phone_one' => $training->phone_one,
                    'picture_one' => $training->picture_one,
                    'rep_two' => $training->name_two,
                    'phone_two' => $training->phone_two,
                    'picture_two' => $training->picture_two,
                ],
                'created_at' => $training->created_at,
                'updated_at' => $training->updated_at
            ];
        }
        return response()->json($t, 200);
    }

    // @route  GET api/v1/training-list/export
    // @desc   Export Training
    // @access Private
    public function exportTraining(Request $request)
    {
        $trainingId = trim($request->input('training_id'));
        $year = trim($request->input('year'));
        $type = trim($request->input('type'));

        $list = $this->_trainingList->_getTrainingListByTrainingIdAndYear($trainingId, $year);


        switch ($type) {
            case 'EXCEL':
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $type = 'xlsx';

                $sheet->getStyle('A1:H1')->getFont()->setBold(true);
                $sheet->setCellValue('A1', 'INSTITUTION');
                $sheet->setCellValue('B1', 'REGION');
                $sheet->setCellValue('C1', 'TRAINING LOCATION');
                $sheet->setCellValue('D1', 'DATE');
                $sheet->setCellValue('E1', 'REPRESENTTIVE 1');
                $sheet->setCellValue('F1', 'PHONE NUMBER');
                $sheet->setCellValue('G1', 'REPRESENTTIVE 2');
                $sheet->setCellValue('H1', 'PHONE NUMBER');
                $row = 2;

                foreach ($list as $training) {
                    $sheet->setCellValue('A' . $row, strtoupper($training->school_name));
                    $sheet->setCellValue('B' . $row, strtoupper($training->region));
                    $sheet->setCellValue('C' . $row, strtoupper($training->location));
                    $sheet->setCellValue('D' . $row, date("l F j, Y, g:i a", strtotime($training->date_time)));
                    $sheet->setCellValue('E' . $row, strtoupper($training->name_one));
                    $sheet->setCellValueExplicit('F' . $row, $training->phone_one, DataType::TYPE_STRING);
                    $sheet->setCellValue('G' . $row, strtoupper($training->name_two));
                    $sheet->setCellValueExplicit('H' . $row, $training->phone_two, DataType::TYPE_STRING);
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
                $trainings = [];
                foreach ($list as $training) {
                    $trainings[] = [
                        'school_name' => strtoupper($training->school_name),
                        'region' => strtoupper($training->region),
                        'location' => strtoupper($training->location),
                        'date_time' => date("l F j, Y, g:i a", strtotime($training->date_time)),
                        'name_one' => strtoupper($training->name_one),
                        'phone_one' => $training->phone_one,
                        'name_two' => strtoupper($training->name_two),
                        'phone_two' => $training->phone_two,
                    ];
                }
                $pdf = PDF::loadView('training.training', compact('trainings'));
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
}
