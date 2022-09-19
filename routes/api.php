<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'middleware' => 'guest'], function () {

    Route::post('users/sign-up', 'UserController@register');
    Route::post('users/account-verification', 'UserController@accountVerification');
    Route::post('users/resend-code', 'UserController@resendCode');
    Route::post('users/reset-password', 'UserController@resetPassword');
    Route::post('users/login', 'UserController@login');

    Route::get('get-schools/{category_id}', 'PublicController@getSchools');
    Route::get('get-school/{id}', 'PublicController@getSchool');
    Route::get('get-student/{id}/{app_number}', 'PublicController@getStudentByAcademicYear');
    Route::post('reset-pin', 'PublicController@resetPin');

    Route::get('trainings/schools', 'TrainingController@getTrainingSchools');
    Route::get('trainings', 'TrainingController@getTrainings');

    Route::post('training-list', 'TrainingController@addTrainingToList');


    Route::post('retrieve-letter', 'StudentApiController@retrieveLetter');
    Route::post('change-pin', 'StudentApiController@resetPin');
    Route::post('upload-receipt', 'StudentApiController@uploadReceipt');
});


Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function () {

    Route::get('users/logout', 'UserController@logout');
    Route::post('users/profile', 'UserController@updateProfile');
    Route::post('users/change-password', 'UserController@changePassword');

    Route::get('category', 'CategoryController@getCategories');

    Route::get('school', 'SchoolController@getSchool');
    Route::post('school', 'SchoolController@createSchool');
    Route::get('schools', 'SchoolController@getSchools');
    Route::put('school/{id}', 'SchoolController@updateSchool');
    Route::post('school/logo', 'SchoolController@updateLogo');
    Route::post('school/signature', 'SchoolController@updateLetterSignature');

    Route::get('programmes', 'ProgrammeController@getProgrammes');
    Route::post('programmes', 'ProgrammeController@createProgramme');
    Route::put('programmes/{id}', 'ProgrammeController@updateProgramme');
    Route::delete('programmes/{id}', 'ProgrammeController@deleteProgramme');

    Route::get('fees', 'FeesController@getFees');
    Route::post('fees', 'FeesController@createFees');
    Route::delete('fees/{id}', 'FeesController@deleteFee');


    Route::get('letter', 'LetterController@getLetter');
    Route::post('letter', 'LetterController@createLetter');
    Route::post('letter/notice', 'LetterController@createNotice');
    Route::put('letter/{id}', 'LetterController@updateLetter');

    Route::get('document', 'DocumentController@getDocument');
    Route::post('document/letter-head', 'DocumentController@createLetterHead');
    Route::post('document', 'DocumentController@createDocument');
    Route::post('document/letter-footer', 'DocumentController@createLetterFooter');
    Route::get('document/remove-letter-footer', 'DocumentController@removeLetterFooter');

    Route::get('students', 'StudentsController@getStudents');
    Route::post('students', 'StudentsController@createStudent');
    Route::put('students/{id}', 'StudentsController@updateStudent');
    Route::delete('students/{id}', 'StudentsController@deleteStudent');
    Route::get('students/actions', 'StudentsController@studentsActions');
    Route::get('students/actions/{id}', 'StudentsController@studentAction');
    Route::post('students/upload', 'StudentsController@uploadStudents');
    Route::post('students/actions/export-students', 'StudentsController@exportStudents');
    Route::get('students/get-receipt-students', 'StudentsController@getReceiptStudents');
    Route::get('students/get-fee-students', 'StudentsController@getFeePaymentStudents');
    Route::post('students/upload/import-results-students', 'StudentsController@uploadResultsStudents');

    Route::get('transfers', 'TransfersController@getTransfers');
    Route::get('transfers/get-count', 'TransfersController@getTransfersCount');
    Route::post('transfers', 'TransfersController@createTransfer');
    Route::post('transfers/duplicates', 'TransfersController@transferDuplicates');
    Route::delete('transfers/{id}', 'TransfersController@deleteTransfer');
    Route::get('transfers/{id}', 'TransfersController@performAction');
    Route::post('transfers/export-transfers', 'TransfersController@exportTransferRequests');

    Route::get('admin/clients', 'AdminController@getClients');
    Route::post('admin/clients/impersonate', 'AdminController@impersonate');

    Route::post('trainings', 'TrainingController@addTraining');
    Route::put('trainings/{id}', 'TrainingController@updateTraining');
    Route::post('trainings/import', 'TrainingController@importTrainings');
    Route::delete('trainings/{id}', 'TrainingController@deleteTraining');

    Route::get('training-list', 'TrainingController@getTrainingList');
    Route::post('training-list/export', 'TrainingController@exportTraining');

    Route::get('academics/courses', 'AcademicsController@getCourses');
    Route::post('academics/courses/import-courses', 'AcademicsController@importCourses');
    Route::post('academics/import-results', 'AcademicsController@importResults');
    Route::get('academics/results/get-results', 'AcademicsController@getResults');
    Route::get('academics/get-academics/{resultId}', 'AcademicsController@getAcademics');
    Route::put('academics/results/publish/{resultId}', 'AcademicsController@publishResults');
    Route::post('academics/results/publish', 'AcademicsController@publishStudentResults');
    Route::delete('academics/results/{resultId}', 'AcademicsController@deleteResults');
    Route::get('academics/notification/send-sms', 'AcademicsController@sendNotificationToStudents');
});
