
function QuestlogOverlay(source, parameter) {
  
  this.foreground;
  this.background;
  
  this.setup = function() {
    var box = this;
    this.background = $('<div class="overlay_background"></div>');
    $(document.body).append(this.background);
    $.get(TEMPLATE_URL + 'questlogOverlay.html', [], function(template) {
      $(document.body).append(template);
      box.foreground = $('.overlay');
      $('.overlay .fa-times').click(function() {
        box.destroy();
      });
      $(box.background).click(function() {
        box.destroy();
      });
      $(box.foreground).mCustomScrollbar();
      $(box).trigger(EVENT_LOADED);
    });
  };
  
  this.setTitle = function(title) {
    $(this.foreground).find('h2').html(title);
  };
  
  this.setContent = function(content) {
    $(this.foreground).find('content').html(content);
  };
  
  this.setThinking = function() {
    this.setContent('<img src="/img/preloader.gif">');
  }
  
  this.destroy = function() {
    var box = this;
    $(this.background).fadeOut('fast', function() {
      $(this).remove();
    });
    $(this.foreground).fadeOut('fast', function() {
      $(this).remove();
      $(box).trigger(EVENT_DESTROYED);
    });
  }
}