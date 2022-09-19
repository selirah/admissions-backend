<?php

namespace App\Helpers;

class Helper
{
    const apiKey = "daa617a455dba72d84be";
    const endPoint = "http://sms.ebitsgh.com/smsapi";
    const voiceApi = "0kCpURHkdVcBGDQQntz7jnnIkVgdhtHiOjHIbo3pkg11T";
    const voiceEndPoint = "https://api.mnotify.com/api/voice/quick";

    public static function sendSMS($phone, $message, $sender = 'EBITS GH')
    {
        $send = self::endPoint . '?key=' . self::apiKey . '&to=' . $phone . '&msg=' . $message . '&sender_id=' . $sender;
        return file_get_contents($send);
    }

    public function pushSMS($name, $phone, $senderId, $school, $academicYear, $appNo, $pwd)
    {
        $message = "Hello " . strtoupper($name) . ", your request for admission into " . strtoupper($school) . " to pursue a degree programme for the " . $academicYear . " academic year has been granted.\nAccess your admission letter by visiting https://bit.ly/3hjzlfm.\nApplication Number: " . $appNo . "\nPIN: " . $pwd . "\nPlease visit https://bit.ly/35Zphmn to learn more";

        $this->_sendSMS($phone, $message, $senderId);
    }

    public function transferSMS($name, $phone, $senderId, $srcSchool, $desSchool, $programme, $academicYear)
    {
        $message = "Hello " . $name . ",\nYou have been transfered from " . strtoupper($srcSchool) . " to " . strtoupper($desSchool) . " to purse a degree programme in " . strtoupper($programme) . " for the " . $academicYear . " academic year.\nAccess your admission letter by visiting https://bit.ly/3hjzlfm.\Application Number: Your Application Number\PIN: First 2 Characters of your Surname followed by Last 3 Digits of your Phone Number.\nPlease visit https://bit.ly/35Zphmn to learn more";
        $this->_sendSMS($phone, $message, $senderId);
    }

    public function pushBotSMS($phone, $senderId)
    {
        $message = "You can also access your admission letter by using the link below to chat with our bot on WhatsApp\nhttps://bit.ly/3x5dtLk";
        $this->_sendSMS($phone, $message, $senderId);
    }

    public function pushBulkSMS($senderId, $school, $data)
    {
        foreach ($data as $d) {
            $message = "Hello " . strtoupper($d['other_names']) . ", your request for admission into " . strtoupper($school) . " to pursue a degree programme for the " . $d['academic_year'] . " academic year has been granted.\nAccess your admission letter by visiting https://bit.ly/3hjzlfm.\nApplication Number: " . $d['application_number'] . "\nPIN: " . $d['pin'] . "\nPlease visit https://bit.ly/35Zphmn to learn more";
            $this->_sendSMS($d['phone'], $message, $senderId);
        }
    }

    public function pushSingleSMS($senderId, $school, $data)
    {
        $message = "Hello " . strtoupper($data['other_names']) . ", your request for admission into " . strtoupper($school) . " to pursue a degree programme for the " . $data['academic_year'] . " academic year has been granted.\nAccess your admission letter by visiting https://bit.ly/3hjzlfm.\nApplication Number: " . $data['application_number'] . "\nPIN: " . $data['pin'] . "\nPlease visit https://bit.ly/35Zphmn to learn more";
        $this->_sendSMS($data['phone'], $message, $senderId);
    }

    public function pushBulkBotSMS($senderId, $data)
    {
        foreach ($data as $d) {
            $message = "You can also access your admission letter by using the link below to chat with our bot on WhatsApp\nhttps://bit.ly/3x5dtLk";
            $this->_sendSMS($d['phone'], $message, $senderId);
        }
    }

    public function pushSingleBotSMS($senderId, $data)
    {
        $message = "You can also access your admission letter by using the link below to chat with our bot on WhatsApp\nhttps://bit.ly/3x5dtLk";
        $this->_sendSMS($data['phone'], $message, $senderId);
    }

    public function trainingSMS($phone, $name, $school, $senderId, $location, $date)
    {
        $message = "Hello " . $name . ",\nYou have been registered to represent " . $school . " in the upcoming training which would be held at " . $location . " on " . $date . "\nThank You";
        $this->_sendSMS($phone, $message, $senderId);
    }

    private function _sendSMS($phone, $message, $senderId)
    {
        $message = urlencode($message);
        $send = self::endPoint . '?key=' . self::apiKey . '&to=' . $phone . '&msg=' . $message . '&sender_id=' . $senderId;
        return file_get_contents($send);
    }

    public static function generateCode($len = 5)
    {
        return strtoupper(substr(md5(time()), 10, $len));
    }

    public static function generateRandomPassword()
    {
        try {
            $bytes = random_bytes(3);
            $randomPassword = strtoupper(bin2hex($bytes));
            return $randomPassword;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sanitizePhone($phone)
    {
        $phone = str_replace(" ", "", $phone);
        $phone = str_replace("-", "", $phone);
        $phone = str_replace("+", "", $phone);
        filter_var($phone, FILTER_SANITIZE_NUMBER_INT);

        if ((substr($phone, 0, 1) == 0) && (strlen($phone) == 10)) {
            return substr_replace($phone, "233", 0, 1);
        } elseif ((substr($phone, 0, 1) != 0) && (strlen($phone) == 9)) {
            return "233" . $phone;
        } elseif ((substr($phone, 0, 3) == "233") && (strlen($phone) == 12)) {
            return $phone;
        } elseif ((substr($phone, 0, 5) == "00233") && (strlen($phone) == 14)) { //if number begin with 233 and length is 12
            return substr_replace($phone, "233", 0, 5);
        } else {
            return $phone;
        }
    }

    public static function generateGravatar($email, $s = 200, $r = 'pg', $d = 'mm')
    {
        $email = md5(strtolower(trim($email)));
        $gravatarUrl = "http://www.gravatar.com/avatar/" . $email . "?d=" . $d . "&s=" . $s . "&r=" . $r;
        return $gravatarUrl;
    }

    public function sendDataToAdmissionsWebsite($data, $type)
    {
        $url = "http://admissionsghana.com/check/data_import.php";
        $curl = curl_init($url);
        $payload = json_encode([$type => $data, 'mode' => $type]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function pushBulkVoiceSMS(array $phones)
    {
        $client = new \GuzzleHttp\Client();
        $endPoint = self::voiceEndPoint . '?key=' . self::voiceApi;
        $filename = "admissions.mp3";
        $voice_file = public_path() . "/uploads/" . $filename;

        $p = "" . implode('", "', $phones) . "";

        $phone_numbers = json_encode([0 => $p]);

        $request = $client->post($endPoint, [
            'multipart' => [
                [
                    'name' => 'campaign',
                    'contents' => 'Admission Voice Message - ' . date('Y-m-d'),
                ],

                [
                    'name' => 'recipient',
                    'contents' =>  stripslashes($phone_numbers),

                ],

                [
                    'name' => 'voice_id',
                    'contents' => '',
                ],

                [
                    'name' => 'is_schedule',
                    'contents' => 'false',
                ],

                [
                    'name' => 'schedule_date',
                    'contents' => '',
                ],

                [
                    'name' => 'file',
                    'contents' => fopen($voice_file, 'r'),
                ],
            ],
        ]);
        $response = $request->getBody();
        return $response;
    }

    public function pushSingleVoiceSMS($phone)
    {
        $client = new \GuzzleHttp\Client();
        $endPoint = self::voiceEndPoint . '?key=' . self::voiceApi;
        $filename = "admissions.mp3";
        $voice_file = public_path() . "/uploads/" . $filename;

        $request = $client->post($endPoint, [
            'multipart' => [
                [
                    'name' => 'campaign',
                    'contents' => 'Admission Voice Message - ' . date('Y-m-d'),
                ],

                [
                    'name' => 'recipient',
                    'contents' =>  stripslashes($phone),

                ],

                [
                    'name' => 'voice_id',
                    'contents' => '',
                ],

                [
                    'name' => 'is_schedule',
                    'contents' => 'false',
                ],

                [
                    'name' => 'schedule_date',
                    'contents' => '',
                ],

                [
                    'name' => 'file',
                    'contents' => fopen($voice_file, 'r'),
                ],
            ],
        ]);
        $response = $request->getBody();
        return $response;
    }


    public function pushResultsBulkSMS($senderId, $data)
    {
        foreach ($data as $d) {
            $message = "Hello " . strtoupper($d['other_names']) . "\nYour results has been released. Use the details below.\nURL: https://bit.ly/3hjzlfm.\nUser ID: " . $d['application_number'] . "\nPIN: " . $d['pin'] . "";
            $this->_sendSMS($d['phone'], $message, $senderId);
        }
    }

    public function pushStudentResultSMS($senderId, $student)
    {
        $message = "Hello " . strtoupper($student->other_names) . "\nYour results has been released. Use the details below.\nURL: https://bit.ly/3hjzlfm.\nUser ID: " . $student->application_number . "\nPIN: " . $student->pin . "";
        $this->_sendSMS($student->phone, $message, $senderId);
    }

    public function pushResultsOwingBulkSMS($senderId, $data)
    {
        foreach ($data as $d) {
            $message = "Hello " . strtoupper($d['other_names']) . "\nYour results has been released. Settle your debts before you can access it using the details below.\nURL: https://bit.ly/3hjzlfm.\nUser ID: " . $d['application_number'] . "\nPIN: " . $d['pin'] . "";
            $this->_sendSMS($d['phone'], $message, $senderId);
        }
    }
}
