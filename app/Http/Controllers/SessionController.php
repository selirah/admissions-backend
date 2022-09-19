<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    private $_student;

    public function __construct(Student $student)
    {
        $this->_student = $student;
    }

    public function create()
    {
        return view('session.create');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'app_number' => 'required',
            'pin' => 'required'
        ]);

        $applicationNumber = trim($request->post('app_number'));
        $pin = trim($request->post('pin'));


        $access = $this->_student->_getStudentsByApplicationNumber($applicationNumber);
        if ($access) {
            $request->session()->put('isNewStudent', true);
        } else {
            $access = $this->_student->_getStudentsByApplicationNumber($applicationNumber);
            if ($access) {
                $request->session()->put('isNewStudent', false);
            }
        }

        if (!$access) {
            $request->session()->flash('flash_message', 'Invalid credentials');
            $request->session()->flash('flash_type', 'alert-danger');
            return back();
        }

        if ($access && $access->pin !== $pin) {
            $request->session()->flash('flash_message', 'Invalid credentials');
            $request->session()->flash('flash_type', 'alert-danger');
            return back();
        }

        $user = $this->_student->_getStudentsByApplicationNumberAndSchool($access->school_id, $access->application_number);
        if (!$user) {
            $request->session()->flash('flash_message', 'An error occurred authenticating you');
            $request->session()->flash('flash_type', 'alert-danger');
            return back();
        }


        if ($user->status !== env('STATUS_BLOCKED') && $user->fee_receipt === 1) {
            $payload = [
                'status' => env('STATUS_ACCESSED')
            ];
            $this->_student->_updateStudent($user->id, $payload);
        }

        $request->session()->put('name', ucfirst($user->other_names) . ' ' . ucfirst($user->surname));
        $request->session()->put('school_id', $user->school_id);
        $request->session()->put('id', $user->id);
        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['name', 'school_id', 'id']);

        $request->session()->flush();

        return redirect('/login');
    }
}
