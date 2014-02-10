function onSignInCallback() {

}

function loginInit(data) {
  $(data).find('.usernameEnter').focusout(function(event) {
    if ($(this).val().length > 0) {
      $(data).find('.usernameCheck').css('visibility', 'visible');
    }
  });
  $(data).find('.passwordEnter').focusout(function(event) {
    if ($(this).val().length > 0) {
      $(data).find('.passwordCheck').css('visibility', 'visible');
    }
  });
  $(data).find('.signupClick').click(function(event) {
    spawn(550, 350, true, null, null, 'Signup', 'signup.html');
    $(event.target).parent().parent().parent().data('bubble').close();
  });
};

(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=623854231027617";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
    
window.fbAsyncInit = function() {
      FB.init({
        appId      : '623854231027617',
        status     : true,
        xfbml      : true
      });
};


(function() {
      var po = document.createElement('script');
      po.type = 'text/javascript'; po.async = true;
      po.src = 'https://plus.google.com/js/client:plusone.js';
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(po, s);
})();
