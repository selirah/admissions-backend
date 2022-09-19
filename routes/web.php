<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'web'], function () {
    Route::get('/login', [
        'as' => 'login',
        'uses' => 'SessionController@create'
    ]);
    Route::post('/login', [
        'as' => 'session.login',
        'uses' => 'SessionController@login'
    ]);
});


Route::get('/', [
    'uses' => 'DashboardController@dashboard',
])->name('dashboard');
Route::get('/home', [
    'uses' => 'StudentAccessController@retrieveLetter',
])->name('home');
Route::get('/logout', [
    'uses' => 'SessionController@logout',
])->name('logout');
Route::get('/print-letter', [
    'uses' => 'StudentAccessController@printLetter',
])->name('print-letter');
Route::get('/download-documents', [
    'uses' => 'StudentAccessController@downloadDocuments',
])->name('download-documents');
Route::get('/upload-receipt', [
    'uses' => 'StudentAccessController@uploadReceipt',
])->name('upload-receipt');
Route::post('/upload-receipt', [
    'uses' => 'StudentAccessController@uploadReceiptPost',
])->name('upload-receipt.post');
Route::get('/print-notice', [
    'uses' => 'StudentAccessController@printNotice',
])->name('print-notice');
Route::get('/results-checker', [
    'uses' => 'ResultsCheckerController@resultsChecker',
])->name('results-checker');
Route::post('/results-checker/display-results', [
    'uses' => 'ResultsCheckerController@displayResults',
])->name('display-results');
Route::get('/dashboard', [
    'uses' => 'DashboardController@dashboard',
])->name('dashboard');
Route::get('/print-results', [
    'uses' => 'ResultsCheckerController@printResults',
])->name('print-results');
