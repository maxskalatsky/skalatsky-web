<?php
/**
 * Submits contact form *
 */
require('utils.php');

// DEFINE FUNCTIONS
$email = EmailClosure($_POST, $_SERVER);
$addRecipientByEmailAndName = $email("addRecipientByEmailAndName");
$getUserInput = $email("getUserInput");
$setSubject = $email("setSubject");
$isPost = $email("isPost");
$requireFields = $email("require");
$passHoneypot = $email("passHoneypot");
$send = $email("send");
$setEmail = $email("setEmail");

// ADD EMAIL RECIPIENTS
$addRecipientByEmailAndName('info@skalatsky.com', 'Skalatsky Contact');
// $addRecipientByEmailAndName('brent@brandishstudio.com', 'Brent');

// SET EMAIL SUBJECT
$setSubject('New Message From Contact Form');

// CONFIGURE FORM INPUTS
$getUserInput(['name', 'user', 'company', 'message']);

header("Content-Type: application/json");
if ($isPost() && $requireFields(['name', 'user', 'message']) && $passHoneypot()) {
  $setEmail('user');
  $emailSent = $send('email', 'name');
  echo '{ "sent": ' . $emailSent . '}';
} else {
  $file = fopen("contact-errors.log", "a");
  if ($file) {
    fwrite($file, json_encode($_POST) . "\n");
    fclose($file);
  } 
  echo '{ "sent": 0 }';
}
