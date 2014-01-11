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
          url: SERVICE_URL + 'settings.php?current=' + currentTheme,
          success : function(themes) {
            var list = $(settings.dom).find('.themeList')[0];
            var li = document.createElement('li');
            $(li).addClass('ui-state-disabled');
            var aTop = document.createElement('a');
            $(aTop).attr('href', '#');
            $(aTop).text(currentTheme);
            $(li).append(aTop);
            $(list).append(li);
            var themesArray = [];
            $(themes).find('theme').each(function(increment, item) {
              themesArray.push($(item).text());
            });
            themesArray.sort();
            var folderCount = 0;
            for (var i=0; i < themesArray.length; i++) {
              if (folderCount % 10 == 0) {
                var ul = document.createElement('ul');
              }
              var li = document.createElement('li');
              var a = document.createElement('a');
              $(a).data('theme', themesArray[i]);
              $(a).css('cursor', 'pointer');
              $(a).click(function(event) {
                var clicked = $(this).data('theme')
                $(this).text($(aTop).text());
                $(aTop).text(clicked);
                loadTheme(clicked);
                //$(this).parent().parent().menu('collapse');
              });
              $(a).html(themesArray[i]);
              $(li).append(a);
              $(ul).append(li);
              if (folderCount % 10 == 0) {
                $(list).append(ul);
              }
              folderCount++;
            }
            $(settings.dom).menu();
            $(settings).trigger('loadComplete');
          }
        });
      }
    });
  }

}