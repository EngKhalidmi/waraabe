<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsController extends Controller
{
    //
    public function processSms($patientPhone, $patientName, $doctor, $card_no )
    {
        $phone = $patientPhone; // Get phone number from form data
        $patient_name = $patientName; // Get customer name from form data
        $doctor =  $doctor; // Get customer name from form data
        $card_no =  $card_no; // Get customer name from form data
        // $amount = $request->input('amount'); // Get amount from form data


        $credentials = app('config')->get('mytelesom.credentials'); // SMS API credentials

        // Construct SMS message
        $message = $patient_name . ", Card Number-kaaga Waa " . $card_no. " Waxaad La Kulmaysaa Doctor " . $doctor;

        // Replace spaces with %20
        $msg = str_ireplace(" ", "%20", $message);

        // Set SMS parameters
        $username = $credentials['username'];
        $password = $credentials['password'];
        $to = $phone;
        $curentDate = date("d/m/Y");
        $from = $credentials['from'];
        $key = $credentials['key'];

        // Generate hash key
        $hashkey = strtoupper(md5($username . "|" . $password . "|" . $to . "|" . $msg . "|" . $from . "|" . $curentDate . "|" . $key));

        // Prepare POST data
        $fields = [
            'from' => $from,
            'to' => $to,
            'msg' => $msg,
            'key' => $hashkey,
        ];

        $postdata = http_build_query($fields);

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, "https://sms.mytelesom.com/index.php/Gway/sendsms/");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL request
        $output = curl_exec($ch);
        $json = $output;
        $data = json_decode($json, true);

        // Check SMS response
        if ($data['status'] === 'success') {
            return response()->json([
                'success' => true,
                'message' => 'SMS Sent successful to the Customer.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'The SMS Not Sent.'
            ]);
        }
    }

    
    public function appointmentSms($patientPhone, $patientName, $doctor, $appointmentDate )
    {
        $phone = $patientPhone; // Get phone number from form data
        $patient_name = $patientName; // Get customer name from form data
        $doctor =  $doctor; // Get customer name from form data
        $appointmentDate =  $appointmentDate; // Get customer name from form data
        // $amount = $request->input('amount'); // Get amount from form data


        $credentials = app('config')->get('mytelesom.credentials'); // SMS API credentials

        // Construct SMS message
        $message = $patient_name . ", Balantaada Waa " . $appointmentDate. " Waxaad La Kulmaysaa Doctor " . $doctor;

        // Replace spaces with %20
        $msg = str_ireplace(" ", "%20", $message);

        // Set SMS parameters
        $username = $credentials['username'];
        $password = $credentials['password'];
        $to = $phone;
        $curentDate = date("d/m/Y");
        $from = $credentials['from'];
        $key = $credentials['key'];

        // Generate hash key
        $hashkey = strtoupper(md5($username . "|" . $password . "|" . $to . "|" . $msg . "|" . $from . "|" . $curentDate . "|" . $key));

        // Prepare POST data
        $fields = [
            'from' => $from,
            'to' => $to,
            'msg' => $msg,
            'key' => $hashkey,
        ];

        $postdata = http_build_query($fields);

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, "https://sms.mytelesom.com/index.php/Gway/sendsms/");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL request
        $output = curl_exec($ch);
        $json = $output;
        $data = json_decode($json, true);

        // Check SMS response
        if ($data['status'] === 'success') {
            return response()->json([
                'success' => true,
                'message' => 'SMS Sent successful to the Customer.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'The SMS Not Sent.'
            ]);
        }
    }

}
