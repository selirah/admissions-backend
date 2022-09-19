<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\ActivationCode;
use App\Models\User;
use App\Models\School;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private $_user;
    private $_activationCode;
    private $_school;


    public function __construct(User $user, ActivationCode $activationCode, School $school)
    {
        $this->_user = $user;
        $this->_activationCode = $activationCode;
        $this->_school = $school;
    }

    // @route  POST api/v1/users/sign-up
    // @desc   Register user
    // @access Public
    public function register(Request $request)
    {
        $name = trim($request->input('name'));
        $email = trim($request->input('email'));
        $phone = trim($request->input('phone'));
        $password = trim($request->input('password'));

        $checkEmail = $this->_user->_checkEmailExistence($email);
        if ($checkEmail) {
            return response()->json(['message' => 'Email ' . $email . ' already exists'], 400);
        }
        $checkPhone = $this->_user->_checkPhoneExistence($phone);
        if ($checkPhone) {
            return response()->json(['message' => 'Phone number ' . $phone . ' already exists'], 400);
        }
        $userData = [
            'email' => $email,
            'phone' => $phone,
            'name' => $name,
            'password' => Hash::make($password),
            'role' => env('ROLE_ADMIN'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $userId = $this->_user->_saveUserData($userData);
        $code = Helper::generateCode();
        $codeData = [
            'user_id' => $userId,
            'code' => $code,
            'expiry' => date("Y-m-d H:i:s", strtotime('+24 hours')),
            'is_expired' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        try {
            $this->_activationCode->_saveCode($codeData);
            $message = "Thank you for registering. Your verification code is " . $code . " Thank you.";
            Helper::sendSMS($phone, urlencode($message));

            $user = $this->_user->_getUserById($userId);
            return response()->json($user, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/users/account-verification
    // @desc   Verify user account
    // @access Public
    public function accountVerification(Request $request)
    {
        $email = trim($request->input('email'));
        $code = trim($request->input('code'));

        $user = $this->_user->_getUserByEmail($email);

        if (!$user) {
            return response()->json(['message' => 'This email is not registered on our system.'], 400);
        }

        $checkCode = $this->_activationCode->_getCode($user->id, $code);
        if (!$checkCode) {
            return response()->json(['message' => 'Invalid verification code. Try again.'], 400);
        }

        if (strtotime(date('Y-m-d H:i:s')) > strtotime($checkCode->expiry)) {
            $payload = [
                'is_expired' => 1
            ];
            $this->_activationCode->_updateCode($user->id, $payload);
            return response()->json(['message' => 'Activation code has expired.'], 400);
        }

        $payload = [
            'is_verified' => env('STATUS_ACTIVE'),
            'email_verified_at' => Carbon::now()

        ];
        try {
            $this->_user->_updateUserStatus($user->id, $payload);
            $this->_activationCode->_forceExpireCode($user->id, $code);
            return response()->json(200);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/users/resend-code
    // @desc   Resent verification code
    // @access Public
    public function resendCode(Request $request)
    {
        $email = trim($request->input('email'));

        $user = $this->_user->_getUserByEmail($email);
        if (!$user) {
            return response()->json(['message' => 'This email is not registered on our system.'], 400);
        }
        $userId = $user->id;
        $phone = $user->phone;
        $checkVerification = $this->_user->_checkActivation($userId);
        if ($checkVerification) {
            return response()->json(['message' => 'Your account is already activated.'], 400);
        }
        $code = Helper::generateCode();
        $codeData = [
            'code' => $code,
            'expiry' => date("Y-m-d H:i:s", strtotime('+24 hours')),
            'is_expired' => 0
        ];

        try {
            $this->_activationCode->_updateCode($user->id, $codeData);
            $message = "Your verification code is " . $code . " Thank you.";
            Helper::sendSMS($phone, urlencode($message));
            return response()->json(200);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/users/reset-password
    // @desc   Reset user password
    // @access Public
    public function resetPassword(Request $request)
    {
        $email = trim($request->input('email'));
        $user = $this->_user->_getUserByEmail($email);
        if (!$user) {
            return response()->json(['message' => 'This email is not registered on our system.'], 400);
        }
        $userId = $user->id;
        $phone = $user->phone;

        $password = Helper::generateRandomPassword();
        $data = [
            'password' => Hash::make($password)
        ];

        try {
            $this->_user->_updateUserPassword($userId, $data);

            $message = "Your new password is " . $password . " Thank you.";
            Helper::sendSMS($phone, urlencode($message));
            return response()->json(200);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  POST api/v1/users/login
    // @desc   Log user in
    // @access Public
    public function login(Request $request)
    {
        $emailOrPhone = trim($request->input('email'));
        $password = trim($request->input('password'));

        if (filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL)) {
            $credentialsWithEmail = [
                'email' => $emailOrPhone,
                'password' => $password,
                'is_verified' => env('STATUS_ACTIVE'),
                'is_revoke' => env('IS_ACTIVE')
            ];

            if (!Auth::attempt($credentialsWithEmail)) {
                return response()->json(['message' => 'Invalid credentials. Also make sure your account is verified'], 400);
            }
        } else {

            $credentialsWithPhone = [
                'phone' => $emailOrPhone,
                'password' => $password,
                'is_verified' => env('STATUS_ACTIVE'),
                'is_revoke' => env('IS_ACTIVE')
            ];

            if (!Auth::attempt($credentialsWithPhone)) {
                return response()->json(['message' => 'Invalid credentials. Also make sure your account is verified'], 400);
            }
        }

        try {
            $user = $request->user();
            $school = $this->_school->_getSchool($user->id);
            $tokenResult = $user->createToken('Personal Access Token');
            $user['token'] = $tokenResult->accessToken;
            $user['admin_id'] = 0;
            if ($school) {
                $user['school_id'] = $school->id;
            } else {
                $user['school_id'] = 0;
            }
            return response()->json($user, 200);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // @route  GET api/v1/users/logout
    // @desc   Log user out
    // @access Private
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        $request->user()->token()->delete();
        return response()->json(204);
    }

    // @route  POST api/v1/users/profile
    // @desc   Update profile
    // @access Private
    public function updateProfile(Request $request)
    {
        $name = trim($request->input('name'));
        $email = trim($request->input('email'));
        $phone = trim($request->input('phone'));

        $userData = [
            'email' => $email,
            'phone' => $phone,
            'name' => $name,
            'updated_at' => Carbon::now()
        ];

        $user = $request->user();
        try {
            $this->_user->_updateUserStatus($user->id, $userData);
            $user = $this->_user->_getUserById($user->id);
            return response()->json($user, 201);
        } catch (Exception $e) {
            throw $e;
        }
    }


    // @route  POST api/v1/users/change-password
    // @desc   Change Password
    // @access Private
    public function changePassword(Request $request)
    {
        $oldPassword = trim($request->input('old_password'));
        $newPassword = trim($request->input('new_password'));

        $user = $request->user();

        if (!Hash::check($oldPassword, $user->password)) {
            return response()->json(['message' => 'Old password is incorrect'], 400);
        }

        $userData = [
            'password' => Hash::make($newPassword)
        ];

        try {
            $this->_user->_updateUserPassword($user->id, $userData);
            return response()->json(201);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
