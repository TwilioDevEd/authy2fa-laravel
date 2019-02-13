$(document).ready(function() {
  console.log('loaded');
  $('#login-form').submit(function(e) {
    e.preventDefault();
    formData = $(e.currentTarget).serialize();
    attemptOneTouchVerification(formData);
  });

  var attemptOneTouchVerification = function(form) {
      $('#ajax-error').addClass('hidden');
      $.post( "/auth/login", form, function(data) {
      if (data.status === 'ok') {
        $('#authy-modal').modal({backdrop:'static'},'show');
        $('.auth-ot').fadeIn();
        checkForOneTouch();
      } else if (data.status === 'verify') {
        $('#authy-modal').modal({backdrop:'static'},'show');
        $('.auth-token').fadeIn()
      } else if (data.status === 'failed') {
        $('#ajax-error').html(data.message);
        $('#ajax-error').removeClass('hidden');
      }
    });
  };

  var checkForOneTouch = function() {
    $.get("/authy/status", function (data) {
      if (data.status === 'approved') {
        window.location.href = "/home";
      } else if (data.status === 'denied') {
        showTokenForm();
        triggerSMSToken();
      } else {
        setTimeout(checkForOneTouch, 5000);
      }
    });
  };

  var showTokenForm = function() {
    $('.auth-ot').fadeOut(function() {
      $('.auth-token').fadeIn('slow')
    })
  };

  var triggerSMSToken = function() {
    $.get("/authy/send_token")
  };
});
