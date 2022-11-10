<?php
$url = "https://api.paystack.co/transaction/initialize";

//Gather the data to be sent to the endpoint
$data = [
    "email" => "YourEmail@something.com",
    "amount" => 100 * 100
    //"callback_url" => "https://louicare.com/verify.php"
];

//Create cURL session
$curl = curl_init($url);

//Turn off Mandatory SSL Checker
//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

//Configure the cURL  session based on the type of request
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

//Decide that this is a POST request
curl_setopt($curl, CURLOPT_POST, true);

//Convert the request data to a JSON data
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

//Set the API headers
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer YOUR_TEST_SECRETE_KEY_HERE", 
    "Content-type: Application/json"
]);

//Run the curl
$run = curl_exec($curl);

//Error checker
$error = curl_error($curl);

if($error){
    die("Curl returned some errors: " . $error);
}

//Convert to jSON object

$result = json_decode($run);
//Close cURL session
curl_close($curl);

header("Location: " . $result->data->authorization_url);
//var_dump($run);
?>