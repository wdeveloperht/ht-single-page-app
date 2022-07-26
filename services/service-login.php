<?php
// @todo some validation checks should be done here (XSS escaping).
$username = !empty($_POST['username']) ? $_POST['username'] : '';
$password = !empty($_POST['password']) ? $_POST['password'] : '';

if ( !empty($username) && !empty($password) ) {
  // @todo Since I didn't use DB, that's why I have to write like this. With DB, this part would be different․
  $admins = file_get_contents('../jsondata/admins.json');
  $jAdmins = json_decode($admins, TRUE);
  foreach ( $jAdmins as $key => $jAdmin ) {
    if ( $jAdmin['username'] == $username && password_verify($password, $jAdmin['password']) ) {
      // @todo Expiration / refreshing in session_id is not included at this time.
      $session_id = $_SERVER['REMOTE_ADDR'] . '' . $jAdmin['id'] . '' . $jAdmin['username'] . '' . $_SERVER['HTTP_USER_AGENT'];
      $jAdmin['session_id'] = md5($session_id) . '-' . md5($jAdmin['id']);

      // update user session_id.
      $jAdmins[$key]['session_id'] = $jAdmin['session_id'];
      file_put_contents('../jsondata/admins.json', json_encode($jAdmins));

      // set user token meta data.
      $tokensTmp = file_get_contents('../jsondata/tokens.json');
      $jTokens = json_decode($tokensTmp, TRUE);
      $date = date('Y-m-d');
      $tokenData[$jAdmin['session_id']] = [
        'key'   => $jAdmin['session_id'],
        'ip'    => $_SERVER['REMOTE_ADDR'],
        'agent' => md5($_SERVER['HTTP_USER_AGENT']),
        'cr_date' => $date,
        'ex_time' =>  date('Y-m-d', strtotime($date. ' + 10 days'))
      ];
      $newTokens = array_merge($jTokens, $tokenData);
      file_put_contents('../jsondata/tokens.json', json_encode($newTokens));

      // get exam result by userID.
      $examResultTmp = file_get_contents('../jsondata/exam-result.json');
      $jaExamResults = json_decode($examResultTmp, TRUE);
      $userExam = [];
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
  }
}
echo '{"status":"error"}';
die;