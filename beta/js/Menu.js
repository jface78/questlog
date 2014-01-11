function Menu() {
  //'20%', '90%', '75%', '5%', 'Settings'
  var menu = this;
  this.parent;
  this.isOpen = false;
  
  function menuHoverOn() {
    $('#menuBlock').css('pointer', 'cursor');
    $('#menuTitle').addClass('ui-state-hover');
    $('#menuBlock').animate({
      left: '-=20px'
    }, 100);
  }

  function menuHoverOff() {
    $('#menuTitle').removeClass('ui-state-hover');
    $('#menuBlock').animate({
      left: '+=20px'
    }, 100);
  }
  
  function menuClick() {
    $('#menuTitle').removeClass('ui-state-hover');
    if (menu.isOpen) {
      $('#menuContent').css('width', '');
      $('#menuTitle').removeClass('ui-state-active');
      var destination = document.getElementById('mainContent').clientWidth - 25;
      var newX = destination - $('#menuBlock').position().left;
      $('#menuBlock').focusout();
      $('#menuBlock').animate({
        left: '+=' + newX
      }, 100, function() {
        setTimeout(function() {
          $('#menuBlock').hover(
            menuHoverOn,
            menuHoverOff
          );
        }, 500);
      });
      $('#menuTitle').animate({
        width: '25px',
        height: '100%'
      }, 100);
      $('#menuTitleText').css('top', '-10px');
      $('#menuTitleArrow').css('top', '-10px');
      $({deg: 0}).animate({deg: 90}, {
        duration: 100,
        step: function(now) {
            // in the step-callback (that is fired each step of the animation),
            // you can use the `now` paramter which contains the current
            // animation-position (`0` up to `angle`)
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
      $('#menuContent').css('width', '100%');
      $('#menuTitle').addClass('ui-state-active');
      $('#menuBlock').unbind('mouseenter mouseleave');
      $('#menuBlock').animate({
        left: '-=270'
      }, 100);
      $('#menuTitle').animate({
        width: '100%',
        height: '25px'
      }, 100);
      $('#menuTitleText').css('top', '0px');
      $('#menuTitleArrow').css('top', '0px');
      $({deg: -90}).animate({deg: 0}, {
        duration: 100,
        step: function(now) {
            // in the step-callback (that is fired each step of the animation),
            // you can use the `now` paramter which contains the current
            // animation-position (`0` up to `angle`)
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
        $(menu.parent).css('left', document.getElementById('mainContent').clientWidth - 25);
        $(menu.parent).css('top', 50);
        //menu.bringToFront();

        $('#mainContent').append(menu.parent);
        $('#menuBlock').hover(
          menuHoverOn,
          menuHoverOff
        );
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

            var parentLI;
            var ul;
            var folderCount = 1;
            for (var i=0; i < themesArray.length; i++) {
              if (i % 10 == 0) {
                parentLI = document.createElement('li');
                $(parentLI).css('padding', '5px');
                $(parentLI).css('cursor', 'pointer');
                $(parentLI).css('white-space', 'nowrap');
                $(parentLI).text('Group ' + folderCount);
                //$(parentLI).addClass('ui-state-default');
                ul = document.createElement('ul');
                $(parentLI).hover(function(event) {
                  $(this).addClass('ui-state-hover');
                }, function(event) {
                  $(this).removeClass('ui-state-hover');
                });
                
                $(parentLI).css('padding', '5px');
                $(parentLI).css('cursor', 'pointer');
                $(parentLI).css('white-space', 'nowrap');
                $(parentLI).append(ul);
                folderCount++;
              }
              var li = document.createElement('li');
              $(li).addClass('ui-state-active');
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
              if (i % 10 == 0) {
                $(list).append(parentLI);
              }
            }
            $('#settingsMenu').menu();
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