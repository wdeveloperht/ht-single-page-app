<?php
// @todo some validation checks should be done here (XSS escaping).
$tmpSessionId = !empty($_POST['session_id']) ? $_POST['session_id'] : '';
if ( !empty($tmpSessionId) ) {
  $tokensTmp = file_get_contents('../jsondata/tokens.json');
  $jTokens = json_decode($tokensTmp, TRUE);
  $userToken = [];
  // @todo Expiration / refreshing in session_id is not included at this time.
  if ( !empty($tmpSessionId) && !empty($jTokens[$tmpSessionId]) ) {
    $userToken = $jTokens[$tmpSessionId];
    if ( $userToken['ip'] != $_SERVER['REMOTE_ADDR'] || $userToken['agent'] != md5($_SERVER['HTTP_USER_AGENT']) ) {
      echo '{"status":"error"}';
      die;
    }
  }
  // @todo Since I didn't use DB, that's why I have to write like this. With DB, this part would be different․
  $admins = file_get_contents('../jsondata/admins.json');
  $jAdmins = json_decode($admins, TRUE);
  $user = [];
  foreach ( $jAdmins as $key => $jAdmin ) {
    if ( $jAdmin['session_id'] == $tmpSessionId) {
      $user = $jAdmin;
    }
  }
  if ( !empty($user) ) {
    // get exam result by userID.
    $examResultTmp = file_get_contents('../jsondata/exam-result.json');
    $jaExamResults = json_decode($examResultTmp, TRUE);
    $userExam = [];
    foreach ( $jaExamResults as $jExamResult ) {
      if ( $jExamResult['id_user'] == $user['id'] ) {
        $userExam = $jExamResult;
      }
    }

    unset($user['password']);
    echo json_encode( [
                        'status' => 'ok',
                        'user' => $user,
                        'exam' => $userExam
                      ] );

  }
  else {
    echo '{"status":"error"}';
    die;
  }
}
else {
  echo '{"status":"error"}';
  die;
}


/*
 *  $hesh = md5($jAdmin['id']);
    if ( $jAdmin['session_id'] == $tmpSessionId && $user_key === $hesh ) {
      $examResultTmp = file_get_contents('../jsondata/exam-result.json');
      $jaExamResults = json_decode($examResultTmp, TRUE);
      $userExam = [];
      // get exam data by user id.
      foreach ( $jaExamResults as $jExamResult ) {
        if ( $jExamResult['id_user'] == $jAdmin['id'] ) {
          $userExam = $jExamResult;
        }
      }
      unset($jAdmin['password']);
      echo json_encode( [
                          'status' => 'ok',
                          'user' => $jAdmin,
                          'exam' => $userExam
                        ] );
      die;
    }
    else {
      echo '{"status":"error"}';
      die;
    }
 *
 * */