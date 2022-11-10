<?php
include_once("includes/db.php");
//Check to be sure that its a POST method
if((strtoupper($_SERVER['REQUEST_METHOD'] != 'POST'))){
    exit();
}
$paymentDetails = @file_get_contents("php://input");
$headers = getallheaders();
$headers = json_encode($headers);
file_put_contents("file.html", "<pre>" . $paymentDetails . "</pre>");
file_put_contents("file2.html", "<pre>" . $headers . "</pre>");

define('PAYSTACK_SECRET_KEY', 'YOUR_TEST_SECRETE_KEY_HERE');
if($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $paymentDetails, PAYSTACK_SECRET_KEY))
exit();
http_response_code(200);

//Events from Paystack
$event = json_decode($paymentDetails);
$chargeEvent = $event->event;
$reference = $event->data->reference;
$amount = $event->data->amount / 100;
$status = $event->data->status;
$first_name = $event->data->customer->first_name;
$last_name = $event->data->customer->last_name;
$customer_email = $event->data->customer->email;
$customer_code = $event->data->customer->customer_code;
//Insert Data Into Db
$insert_into = "INSERT INTO first_table(event, ref, amount, status, first_name, last_name, customer_email, customer_code)
VALUES(:chargeEvent, :reference, :amount, :status, :first_name, :last_name, :customer_email, :customer_code)";
if($stmt = $pdo->prepare($insert_into)){
    //Bind Params
    $stmt->bindParam(':chargeEvent', $chargeEvent, PDO::PARAM_STR);
    $stmt->bindParam(':reference', $reference, PDO::PARAM_STR);
    $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
    $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
    $stmt->bindParam(':customer_email', $customer_email, PDO::PARAM_STR);
    $stmt->bindParam(':customer_code', $customer_code, PDO::PARAM_STR);

    //Execute Params
    if($stmt->execute()){
       // echo"Data Inserted";
        $last_id = $pdo->lastInsertId();
        $fetch_data_from_firstTable = "SELECT * FROM first_table WHERE id = :last_id";
        $stmt = $pdo->prepare($fetch_data_from_firstTable);

        //Bind Params
         $stmt->bindParam(':last_id', $last_id, PDO::PARAM_STR);
         $stmt->execute(['last_id' => $last_id]);
         if($row = $stmt->fetch()){
            $id = $row->id;
            $cust_code = $row->customer_code;
            $ref = $row->ref;
            $cust_email = $row->customer_email;
            $notification = "I came in from the First Table, but Webhook made it possible";
         
         //Insert Into a New Table
         $insert_into_table2 = "INSERT INTO second_table(id_from_first_table, cust_code, ref, cust_email, notification) 
              VALUES(:id, :cust_code, :ref, :cust_email, :notification)";
              if($stmt = $pdo->prepare($insert_into_table2)){
                //Bind Param
              $stmt->bindParam(':id', $id, PDO::PARAM_STR);  
              $stmt->bindParam(':cust_code', $cust_code, PDO::PARAM_STR);
              $stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
              $stmt->bindParam(':cust_email', $cust_email, PDO::PARAM_STR);
              $stmt->bindParam(':notification', $notification, PDO::PARAM_STR);
              //Execute
              if($stmt->execute()){
                echo"Awesome";
              }
              }
         }
    }
}
exit();
?>