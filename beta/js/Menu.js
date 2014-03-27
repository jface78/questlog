function Menu() {
  //'20%', '90%', '75%', '5%', 'Settings'
  var menu = this;
  this.parent;
  this.isOpen = false;
  var SLIDE_TIME = 500;
  this.startingPosition = document.getElementById('mainContent').clientWidth - 25;
  
  function menuHoverOn() {
    menu.bringToFront();
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
        $(menu.parent).prop('tabindex', '-1');
        $(menu.parent).find('li').prop('tabindex', '-1');
        $(menu.parent).find('a').prop('tabindex', '-1');
        
        $(menu.parent).css('height', $(window).innerHeight() - 100);
        $(menu.parent).css('left', menu.startingPosition);
        
        $(menu.parent).css('top', 50);
        
        $('#mainContent').append(menu.parent);
        
        $('#menuContent li a').hover(function(event) {
          menu.bringToFront();
        });
        
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
              spawn(450,200,true,null,null,'Login', 'login.html');
            });
            $(menu).trigger('loadComplete');
          }
        });
      }
    });
  };
  
  this.logout = function() {
  
  };

  this.login = function() {
    $(menu.parent).find('#menuItemLogin').text('Logout');
    $(menu.parent).find('#menuItemLogin').unbind('click');
    $(menu.parent).find('#menuItemLogin').click(function(event) {
      $.ajax({
        url: SERVICE_URL + 'users.php?request=logout',
        success: function() {
          menu.logout();
        }
      });
    });
    $.ajax({
      url: SERVICE_URL + 'quests.php?request=getQuests',
      dataType: 'xml',
      statusCode: {
        200: function(data) {
          var ul = document.createElement('ul');
          $(ul).addClass('ui-menu ui-widget ui-widget-content ui-corner-all');
          $(ul).attr('role', 'menu');
          $(ul).attr('aria-expanded', 'false');
          $(ul).attr('aria-hidden', 'true');
          $(ul).css('display', 'none');
          var li = document.createElement('li');
          $(li).addClass('ui-menu-item');
          $(li).attr('tabindex', '-1');
          $(li).attr('role', 'presentation');
          var a = document.createElement('a');
          $(a).attr('href', '#');
          $(a).attr('aria-haspopup', 'true');
          $(a).attr('tabindex', '-1');
          $(a).addClass('ui-corner-all');
          $(a).attr('role', 'menuitem');
          
          $(li).append(a);
          $(ul).append(li);
          $(menu.parent).find('#questsMenu').append(ul);
          var span = document.createElement('span');
          $(span).addClass('ui-menu-icon ui-icon ui-icon-triangle-1-e');
          $(span).css('position', 'relative');
          $(span).css('top', '5px');
          $(a).html(span);
          $(a).append('GM Quests');
          
          if ($(data).find('gmQuests quest').length > 0 ||
              $(data).find('pcQuests quest').length > 0) {
            span = document.createElement('span');
            $(span).addClass('ui-menu-icon ui-icon ui-icon-triangle-1-e');
            $(span).css('position', 'relative');
            $(span).css('top', '5px');
            $(menu.parent).find('#questsMenu a').first().prepend(span);
            $(menu.parent).find('#questsMenu a').first().attr('aria-haspopup', 'true');
            $(menu.parent).find('#questsMenu').removeAttr('aria-disabled');
            $(menu.parent).find('#questsMenu').removeClass('ui-state-disabled');
          }
          if ($(data).find('gmQuests quest').length > 0) {
            var innerUL = document.createElement('ul');
            $(innerUL).addClass('ui-menu ui-front ui-widget ui-widget-content ui-corner-all');
            $(innerUL).attr('role', 'menu');
            $(innerUL).attr('aria-expanded', 'false');
            $(innerUL).attr('aria-hidden', 'true');
            $(innerUL).css('display', 'none');
            $(data).find('gmQuests quest').each(function(increment, item) {
              var innerLI = document.createElement('li');
              $(innerLI).addClass('ui-menu-item');
              $(innerLI).attr('role', 'presentation');
              var innerA = document.createElement('a');
              $(innerA).attr('href', '#');
              $(innerA).attr('tabindex', '-1');
              $(innerA).addClass('ui-corner-all');
              $(innerA).attr('role', 'menuitem');
              $(innerA).append($(item).find('title').text());
              console.log('item: ' +$(item).find('title').text());
              $(innerLI).append(innerA);
              $(innerUL).append(innerLI);
            });
            $(li).append(innerUL);
          }
          
          var li = document.createElement('li');
          $(li).addClass('ui-menu-item');
          $(li).attr('tabindex', '-1');
          $(li).attr('role', 'presentation');
          var a = document.createElement('a');
          $(a).attr('href', '#');
          $(a).attr('aria-haspopup', 'true');
          $(a).attr('tabindex', '-1');
          $(a).addClass('ui-corner-all');
          $(a).attr('role', 'menuitem');
          
          $(li).append(a);
          $(ul).append(li);
          $(menu.parent).find('#questsMenu').append(ul);
          var span = document.createElement('span');
          $(span).addClass('ui-menu-icon ui-icon ui-icon-triangle-1-e');
          $(span).css('position', 'relative');
          $(span).css('top', '5px');
          $(a).html(span);
          $(a).append('PC Quests');
          
          if ($(data).find('pcQuests quest').length > 0) {
            var innerUL = document.createElement('ul');
            $(innerUL).addClass('ui-menu ui-front ui-widget ui-widget-content ui-corner-all');
            $(innerUL).attr('role', 'menu');
            $(innerUL).attr('aria-expanded', 'false');
            $(innerUL).attr('aria-hidden', 'true');
            $(innerUL).css('display', 'none');
            $(data).find('pcQuests quest').each(function(increment, item) {
              var innerLI = document.createElement('li');
              $(innerLI).addClass('ui-menu-item');
              $(innerLI).attr('role', 'presentation');
              var innerA = document.createElement('a');
              $(innerA).attr('href', '#');
              $(innerA).attr('tabindex', '-1');
              $(innerA).addClass('ui-corner-all');
              $(innerA).attr('role', 'menuitem');
              $(innerA).append($(item).find('title').text());
              console.log('item: ' +$(item).find('title').text());
              $(innerLI).append(innerA);
              $(innerUL).append(innerLI);
            });
            $(li).append(innerUL);
          }
        }
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