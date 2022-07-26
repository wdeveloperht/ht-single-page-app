$(document).on('click', '#btn-login', function () {
  var userName = $('#userName').val();
  var userPassword = $('#userPassword').val();
  if ( userName == '' && userPassword == '' ) {
    $('#login-error-msg').text('Please fill out the fields');
  }
  else {
    var apiLogin = 'services/service-login.php';
    $.ajax({
      'url': apiLogin,
      'method': 'POST',
      'dataType': 'JSON',
      'data': { 'username': userName, 'password': userPassword },
      'cache': false
    }).done(function ( jData ) {
      user = jData['user'];
      if ( jData.status == 'error' ) {
        $('#login-error-msg').text('Unknown user or wrong password');
      }
      else {
        if ( localStorage.getItem('session_id') ) {
          localStorage.removeItem('session_id');
        }
        localStorage.setItem('session_id', user.session_id);

        $('#ht-login').hide();
        $('#login-error-msg').empty();
        $('input:text').val('');
        $('input:password').val('');
        userProfile(jData);
      }
    })
  }
});

$(document).on('click', '#btn-logout', function () {
  var apiLogout = 'services/service-logout.php';
  $.get(apiLogout, function () {
    $('#ht-dashboard').hide();
    $('#ht-login').css('display', 'flex');
    localStorage.clear();
  })
});

checkIsLoggedIn();

function checkIsLoggedIn() {
  if ( localStorage.getItem('session_id') ) {
    let session_id = localStorage.getItem('session_id');
    var apiLogged = 'services/service-is-user-logged.php';
    var user = {};
    $.ajax({
      'url': apiLogged,
      'method': 'POST',
      'dataType': 'JSON',
      'data': { 'session_id': session_id },
      'cache': false
    }).done(function ( jData ) {
      user = jData;
      if ( jData.status == 'error' ) {
        $('#ht-login').css('display', 'flex');
      }
      else {
        $('#ht-login').hide();
        userProfile(user);
      }
    });
  }
  else {
    $('#ht-login').css('display', 'flex');
  }
}

function userProfile( obj ) {
  document.title = 'Dashboard';
  $('#welcome-msg').text('Hi, ' + obj.user.username);
  $('#ht-dashboard .ht-score').text(obj.exam.value);
  $('#ht-dashboard').css('display', 'flex');
}