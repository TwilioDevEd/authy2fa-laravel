$(document).ready(function() {
  console.log('loaded');
  $('#login-form').submit(function(e) {
    e.preventDefault();
    formData = $(e.currentTarget).serialize();
    attemptOneTouchVerification(formData);
  })

  var attemptOneTouchVerification = function(form) {
    $.post( "/auth/login", form, function(data) {
      var data = JSON.parse(data.replace(/1/g, ""));
      $('#authy-modal').modal({backdrop:'static'},'show');
      if (data.success) {
        $('.auth-ot').fadeIn()
        checkForOneTouch();
      } else {
        $('.auth-token').fadeIn()
      }
    });
  };

  var checkForOneTouch = function() {
    $.get( "/authy/status", function(data) {
      
      if (data.status == 'approved') {
        window.location.href = "/home";
      } else if (data.status == 'denied') {
        showTokenForm();
        triggerSMSToken();
      } else {
        setTimeout(checkForOneTouch, 2000);
      }
    })
  };

  var showTokenForm = function() {
    $('.auth-ot').fadeOut(function() {
      $('.auth-token').fadeIn('slow')
    })
  };

  var triggerSMSToken = function() {
    $.get("/authy/send_token")
  };
})

