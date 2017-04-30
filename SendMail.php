<?php
date_default_timezone_set('Asia/Kolkata');
$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
require 'PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Debugoutput = 'html';
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth = true;
$mail->Username = "your_mailid";
$mail->Password = "your_password";


$post_userid = intval($_POST["api_user"]);
$post_key = $_POST["api_key"];
$post_from = $_POST["from"];
$post_subject = $_POST["subject"];
$post_message = $_POST["message"];
$post_to=$_POST["to"];
$post_toname=$_POST["toname"];

$filter = ['user_id' => $post_userid];

$query = new MongoDB\Driver\Query($filter);

$rows = $manager->executeQuery('email_as_service.user_details', $query);

$isdocumnet_availabe=0;

foreach ($rows as $each_document) {
	if(isset($each_document))
	{
		$mongodb_key = $each_document->key;
		$isdocumnet_availabe=1;
	}
}

if($isdocumnet_availabe == 0)
{
	$array = array('success' => 'FALSE','err' => 'Wrong Credentials', 'detailed_message'=> 'the credentials you have passed in post is not authenticate' );
	echo json_encode($array, true);
	exit;
}

if($post_key != $mongodb_key)
{
	$array = array('success' => 'FALSE','err' => 'Wrong Credentials', 'detailed_message'=> 'the credentials you have passed in post is not authenticate' );
	echo json_encode($array, true);
	exit;
}

$post_to_size=sizeof($post_to);
$post_toname_size=sizeof($post_toname);

if($post_to_size != $post_toname_size)
{
	$array = array('success' => 'FALSE','err' => 'Wrong Inputs', 'detailed_message'=> 'Number of to and toname must be same' );
	echo json_encode($array, true);
	exit;
}

$mail->setFrom($post_from, $post_from);

for($i=0; $i < $post_to_size; $i++)
{
	$mail->addAddress($post_to[$i], $post_toname[$i]);
	$mail->addReplyTo($post_to[$i], $post_toname[$i]);
}

$mail->Subject = $post_subject;

$mail->Body = $post_message;

if (!$mail->send()) {
    $array = array('success' => 'FALSE','err' => 'Server Problem', 'detailed_message'=> 'There is a problem on our side we regret it' );
	echo json_encode($array, true);
	exit;
} else {
    $array = array('success' => 'TRUE', 'message' => "Mail sent !" );
	echo json_encode($array, true);
	exit;
}