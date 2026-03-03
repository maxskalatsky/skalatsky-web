<?php
require_once('phpmailer/PHPMailerAutoload.php');

function cleanInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function change_key($array, $old_key, $new_key) {
  if(!array_key_exists($old_key, $array)) {
    return $array;
  }
  $keys = array_keys($array);
  $keys[array_search($old_key, $keys)] = $new_key;
  return array_combine($keys, $array);
}

function EmailClosure($postData, $server) {
  $mail = new PHPMailer();
  $emails =  array();
  $inputData = array(); 

  $funcs = array(
    'addRecipientByEmailAndName' => function($email, $name) use (&$emails) {
      $emails[] = array('email' => $email, 'name' => $name);
    },
    'isPost' => function() use ($server) {
      return $server['REQUEST_METHOD'] === 'POST';
    }, 
    'getUserInput' => function($fields) use ($postData, &$inputData) {
      $data = array();
      foreach ($fields as $field) {
        $data[$field] = isset($postData[$field]) ? cleanInput($postData[$field]) : '';
      }
      $inputData = $data;
    },
    'require' => function($fields) use (&$inputData) {
      $valid = true;
      foreach ($fields as $field) {
        if (!isset($inputData[$field]) || !$inputData[$field]) {
          $valid = false;
          break;
        }
      }
      return $valid;
    },
    'passHoneypot' => function() use (&$postData) {
      return isset($postData['email']) && !$postData['email'];
    },
    'setSubject' => function($subj) use (&$mail) {
      $mail->Subject = $subj;
    },
    'setFrom' => function($email, $name) use (&$mail) {
      $mail->SetFrom($email, $name);
    },
    'setEmail' => function($emailField) use (&$inputData) {
      $inputData = change_key($inputData, $emailField, 'email');
    },
    'send' => function($emailField, $nameField) use (&$mail, &$inputData, &$emails, $server) {
      foreach($emails as $to) {
        $mail->AddAddress($to['email'], isset($to['name']) ? $to['name'] : '');
      }
      $mail->SetFrom($inputData[$emailField], $inputData[$nameField]);
      $body = '';
      foreach($inputData as $name => $field) {
        $title = ucwords($name);
        $body .= "$title: $field<br /><br />";
      }

      $body .= $server['HTTP_REFERER'] ? '<br><br><br>This Form was submitted from: ' . $server['HTTP_REFERER'] : '';
      $mail->MsgHTML($body);
      return $mail->Send();
    }
  );
  return function($method) use (&$funcs) {
    return $funcs[$method];
  };
}




