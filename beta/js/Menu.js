function Menu() {
  //'20%', '90%', '75%', '5%', 'Settings'
  var menu = this;
  this.parent;
  this.isOpen = false;
  var SLIDE_TIME = 500;
  this.startingPosition = document.getElementById('mainContent').clientWidth - 25;
  
  function menuHoverOn() {
    $('#menuBlock').css('pointer', 'cursor');
    $('#menuTitle').addClass('ui-state-hover');
    if ($('#menuBlock').position().left > menu.startingPosition - 20) {
      $('#menuBlock').animate({
        left: '-=20px'
      }, 100);
    }
  }

  function menuHoverOff() {
    $('#menuTitle').removeClass('ui-state-hover');
    var newX = menu.startingPosition - $('#menuBlock').position().left;
    $('#menuBlock').animate({
      left: '+=' + newX
    }, 100);
  }
  
  function menuClick() {
    menu.bringToFront();
    $('#menuTitle').unbind('mousedown');
    if (menu.isOpen) {
      $('#menuTitle').removeClass('ui-corner-top');
      $('#menuTitle').addClass('ui-corner-all');
      var destination = document.getElementById('mainContent').clientWidth - 25;
      var newX = menu.startingPosition - $('#menuBlock').position().left;
      $('#menuBlock').focusout();
      $('#menuContent').animate({
        width: '50%'
      }, SLIDE_TIME);
      $('#menuBlock').animate({
        left: '+=' + newX
      }, SLIDE_TIME, function() {
        $('#menuTitle').on('mousedown', function(event) {
          menuClick();
        });
        setTimeout(function() {
          $('#menuBlock').hover(
            menuHoverOn,
            menuHoverOff
          );
        }, SLIDE_TIME);
      });
      $('#menuTitle').animate({
        width: '25px',
        height: '100%'
      }, SLIDE_TIME);
      $('#menuTitleText').css('top', '-10px');
      $('#menuTitleArrow').css('top', '-10px');
      $({deg: 0}).animate({deg: 90}, {
        duration: SLIDE_TIME,
        step: function(now) {
          $('#menuTitleText').css({
              transform: 'rotate(' + now + 'deg)'
          });
          $('#menuTitleArrow').css({
            transform: 'rotate(' + now + 'deg)'
          });
        }
      });
      $('#menuTitleArrow').button("option", {
        icons: { primary: "ui-icon-triangle-1-n" }
      });
    } else {
      $('#menuTitle').addClass('ui-corner-top');
      $('#menuTitle').removeClass('ui-corner-all');
      $('#menuTitle').unbind('mousedown');
      $('#menuBlock').unbind('mouseenter mouseleave');
      $('#menuContent').animate({
        width: '100%'
      }, SLIDE_TIME);
      var newX = $('#menuBlock').width() - 43;
      $('#menuBlock').animate({
        left: '-=' + newX
      }, SLIDE_TIME, function() {
        $('#menuTitle').on('mousedown', function() {
          menuClick();
        });
      });
      $('#menuTitle').animate({
        width: '100%',
        height: '25px'
      }, SLIDE_TIME, function() {
        //$('#menuTitle').addClass('ui-state-active');
      });
      $('#menuTitleText').css('top', '0px');
      $('#menuTitleArrow').css('top', '0px');
      $({deg: 90}).animate({deg: 0}, {
        duration: SLIDE_TIME,
        step: function(now) {
            $('#menuTitleText').css({
                transform: 'rotate(' + now + 'deg)'
            });
            $('#menuTitleArrow').css({
                transform: 'rotate(' + now + 'deg)'
            });
        }
      });
      $('#menuTitleArrow').button("option", {
          icons: { primary: "ui-icon-triangle-1-s" }
        });
        
    }
    menu.isOpen = !menu.isOpen;
  }

  this.setup = function() {
    $.ajax({
      type: 'GET',
      url: TEMPLATE_URL + 'menu.html',
      success : function(data) {
        menu.parent = $(data).first()[0];
        
        $(menu.parent).css('height', $(window).innerHeight() - 100);
        $(menu.parent).css('left', menu.startingPosition);
        
        $(menu.parent).css('top', 50);
        
        $('#mainContent').append(menu.parent);
        $('#menuBlock').hover(
          menuHoverOn,
          menuHoverOff
        );
        $('#menuBlock').click(function(event) {
          menu.bringToFront();
        });
        $('#menuTitle').on('mousedown', function(event) {
          event.preventDefault();
          menuClick();
        });
        $('#menuTitleArrow').button({
          icons: {
            primary: ".ui-icon-triangle-1-e"
          },
          text: false
        })
        $.ajax({
          type: 'GET',
          url: SERVICE_URL + 'settings.php?current=' + currentTheme,
          success : function(themes) {
            var list = $(menu.parent).find('.themeList')[0];
            $('#currentTheme').text(currentTheme);
            var themesArray = [];
            $(themes).find('theme').each(function(increment, item) {
              themesArray.push($(item).text());
            });
            themesArray.sort();

            folderCount = 1;
            for (var i=0; i < themesArray.length; i++) {
              if (i % 5 == 0) {
                var submenuLI = document.createElement('li');
                $(submenuLI).addClass('themeItem');
                var a = document.createElement('a');
                $(a).attr('href', '#');
                $(a).text('Group ' + folderCount);
                var ul = document.createElement('ul');
                $(submenuLI).append(a);
                $(submenuLI).append(ul);
                folderCount++;
                $(list).append(submenuLI);
              }
              var li = document.createElement('li');
              $(li).addClass('themeItem');
              var a = document.createElement('a');
              $(a).attr('href', '#');
              $(a).data('theme', themesArray[i]);

              $(a).click(function(event) {
                var clicked = $(this).data('theme')
                $(this).text($('#currentTheme').text());
                $('#currentTheme').text(clicked);
                currentTheme = clicked;
                $(this).data('theme', $(this).text());
                loadTheme(clicked);
                //$('#settingsMenu').menu('collapse');
              });
              $(a).text(themesArray[i]);
              $(li).append(a);
              $(ul).append(li);
            }
            $('#siteMenu').menu({ icons: { submenu: "ui-icon-triangle-1-e" } });
            $('#siteMenu').find('.ui-menu-icon').each(function(increment, item) {
              $(item).css('position', 'relative');
              $(item).css('top', '5px');
            });
            
            $('#menuItemLogin').click(function(event) {
              spawn(500,400,true,null,null,'Login');
            });
            $(menu).trigger('loadComplete');
          }
        });
      }
    });
  };

  this.bringToFront = function() {
    var zIndexes = [];
    $(document).find('.bubbleParent').each(function(){
      //$(this).removeClass('ui-state-active');
      //$(this).addClass('ui-state-default');
      zIndexes.push(parseInt($(this).css('z-index')));
    });
    if (zIndexes.length > 0) {
      zIndexes.sort();
      $(menu.parent).css('z-index', zIndexes[zIndexes.length-1] + 1);
    } else {
      $(menu.parent).css('z-index', 100);
    }
  };
}