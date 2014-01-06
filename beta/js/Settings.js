function Settings() {

  var settings = this;
  this.dom;

  this.setup = function() {
    $.ajax({
      type: 'GET',
      url: TEMPLATE_URL + 'settings.html',
      success : function(data) {
        settings.dom = $(data).first()[0];
        $.ajax({
          type: 'GET',
          url: SERVICE_URL + 'settings.php',
          success : function(themes) {
            var list = $(settings.dom).find('.themeList')[0];
            $(themes).find('theme').each(function(increment, item) {
              var li = document.createElement('li');
              var a = document.createElement('a');
              $(a).attr('href', '#');
              $(a).html($(item).text());
              $(li).append(a);
              $(list).append(li);
            });
            $(settings.dom).menu();
            $(settings).trigger('loadComplete');
          }
        });
      }
    });
  }

}