function QuestlogBubble(width, height, centered, left, top, title, content) {
  
  var bubble = this;
  this.parent;
  this.title = title ? title : 'Questlog';
  this.content = content ? content : 'ok';
  this.width = width ? width : 300;
  this.height = height ? height : 300;
  if (centered || (!left && !top)) {
    this.left = $(window).innerWidth()/2 - this.width/2;
    this.top = $(window).innerHeight()/2 - this.height/2;
  } else {
    this.left = left ? left : 100;
    this.top = top ? top : 100;
  }
  this.isMaximized = false;
  this.isMinimized = false;

  /*
  if (this.width.toString().indexOf('%') > -1) {
    this.width = percentageToPixels(this.width, 'horizontal');
  }
  if (this.height.toString().indexOf('%') > -1) {
    this.height = percentageToPixels(this.height, 'vertical');
  }*/
  if (this.left.toString().indexOf('%') > -1) {
    this.left = percentageToPixels(this.left, 'horizontal');
  }
  if (this.top.toString().indexOf('%') > -1) {
    this.top = percentageToPixels(this.top, 'vertical');
  }
  
  for (var i=0; i < bubbles.length; i++) {
    if (bubbles[i].top == this.top) {
      this.top += 10;
    }
    if (bubbles[i].left == this.left) {
      this.left += 10;
    }
  }
  
  this.setup = function() {
    $.ajax({
      type: 'GET',
      url: TEMPLATE_URL + 'bubbleDefault.html',
      success : function(data) {
        
        bubble.parent = $(data).first()[0];
        $(bubble.parent).css('width', bubble.width);
        $(bubble.parent).css('height', bubble.height);
        $(bubble.parent).css('left', bubble.left);
        $(bubble.parent).css('top', bubble.top);
        var titleSlot = $(bubble.parent).find('.title')[0];
        $(titleSlot).html(bubble.title);
        var topBar = $(bubble.parent).find('.bubbleTopBar')[0];
        $(topBar).hover(function(event) {
          $(topBar).addClass('ui-state-hover');
          $(topBar).removeClass('ui-state-default');
        }, function(event) {
          $(topBar).removeClass('ui-state-hover');
          $(topBar).addClass('ui-state-default');
        });

        $(bubble.parent).find('.closeBtn').button({
            icons: {
                primary: "ui-icon-close"
            },
            text:false
        });
        $(bubble.parent).find('.minBtn').button({
            icons: {
                primary: "ui-icon-arrowreturn-1-s"
            },
            text:false
        });
        $(bubble.parent).find('.maxBtn').button({
            icons: {
                primary: "ui-icon-arrow-4-diag"
            },
            text:false
        });
        $(bubble.parent).find('.closeBtn').click(function(event) {
          bubble.close();
        });
        $(bubble.parent).find('.minBtn').click(function(event) {
          bubble.minimize();
        });
        
        $(bubble.parent).find('.maxBtn').click(function(event) {
          bubble.maximize();
        });
        bubble.bringToFront();
        bubble.enableDraggable();
        bubble.enableResize();
        $(bubble.parent).mousedown(function(event) {
          $(topBar).addClass('ui-state-active');
          bubble.bringToFront();
        });
        $(bubble.parent).mouseup(function(event) {
          $(topBar).removeClass('ui-state-active');
        });
        if (bubble.content) {
        }
        $('#mainContent').append(bubble.parent);
        $(bubble).trigger('loadComplete');
      }
    });
  };
  
  this.disableResize = function() {
    $(bubble.parent).resizable('destroy');
  }
  
  this.enableResize = function() {
    $(bubble.parent).resizable({
      ghost: true,
      animate: true,
      animateDuration: 'fast',
      resize: function(event, ui) {
        bubble.width = ui.size.width;
        bubble.height = ui.size.height;
      }
    });
  }
  
  this.disableDraggable = function() {
    $(bubble.parent).draggable('destroy');
  }
  
  this.enableDraggable = function() {
    $(bubble.parent).draggable({
      handle: $(bubble.parent).find('.bubbleTopBar'),
      stop: function( event, ui ) {
        bubble.left = $(bubble.parent).position().left;
        bubble.top = $(bubble.parent).position().top;
      }
    });
  }

  this.setContent = function(element) {
    var contentArea = $(bubble.parent).find('.bubbleContent')[0];
    console.log('content ' + bubble.parent);
    $(contentArea).html(element);
  };
  
  this.bringToFront = function() {
    var zIndexes = [];
    $('.bubbleParent').each(function(increment, item){
      var topBar = $(item).find('.bubbleTopBar')[0];
      zIndexes.push(parseInt($(this).css('z-index')));
    });

    var topBar = $(bubble.parent).find('.bubbleTopBar')[0];

    if (zIndexes.length > 0) {
      zIndexes.sort();
      $(bubble.parent).css('z-index', zIndexes[zIndexes.length-1] + 1);
    } else {
      $(bubble.parent).css('z-index', 100);
    }
  };
  
  this.close = function() {
    $(bubble.parent).remove();
    bubbles.splice(bubbles.indexOf(bubble), 1);
  };
  
  this.maximize = function() {
    this.isMaximized = true;
    bubble.bringToFront();
    bubble.disableResize();
    bubble.disableDraggable();
    $(bubble.parent).find('.maxBtn').attr('title', 'restore');
    $(bubble.parent).find('.maxBtn').unbind('click');
    $(bubble.parent).find('.maxBtn').click(function(event) {
      bubble.restorePositionAndSize();
    });
    $(bubble.parent).animate({
      left: '0px',
      top: '0px',
      height: '100%',
      width: '100%'
    }, 250, function() {
    });
  };
  
  this.minimize = function() {
    this.isMinimized = true;
    
    var divider = Math.floor($(window).width() / BUBBLE_MINIMIZED_WIDTH);
    var yMultiplier = Math.floor(minimizedArray.length / divider);
    var yPos = $(window).height() - ((yMultiplier+1) * BUBBLE_TOOLBAR_HEIGHT);
    var arrayPos = minimizedArray.length - (yMultiplier * divider);
    var xPos = BUBBLE_MINIMIZED_WIDTH * arrayPos;
    minimizedArray.push(this);

    $(bubble.parent).removeClass('ui-corner-bottom');
    $(bubble.parent).addClass('ui-corner-top');
    $(bubble.parent).find('.tools').hide();
    bubble.disableResize();
    bubble.disableDraggable();
    $(bubble.parent).find('.bubbleTopBar').css('cursor', 'pointer');
    var bottom = document.getElementById('mainContent').clientHeight -
        BUBBLE_TOOLBAR_HEIGHT;
    $(bubble.parent).animate({
      left: xPos,
      top: yPos,
      height: BUBBLE_TOOLBAR_HEIGHT,
      width: BUBBLE_MINIMIZED_WIDTH
    }, 250, function() {
      $(bubble.parent).find('.bubbleTopBar').click(function(event) {
        bubble.restorePositionAndSize();
      });
    });
    
  }
  
  this.restorePositionAndSize = function() {
    if (bubble.isMinimized) {
      $(bubble.parent).find('.bubbleTopBar').unbind('click');
      $(bubble.parent).find('.tools').show();
      $(bubble.parent).addClass('ui-corner-bottom');
      $(bubble.parent).removeClass('ui-corner-top');
      minimizedArray = removeFromArray(minimizedArray, this);
      /*
      for (var i=0; i < minimizedArray.length; i++) {
        var newX = i * 100;
        $(minimizedArray[i].parent).animate({
          left: newX
        }, 250);
      }*/
      for (var i = 0; i < minimizedArray.length; i++) {
        var divider = Math.floor($(window).width() / BUBBLE_MINIMIZED_WIDTH);
        var yMultiplier = Math.floor(i / divider);
        var yPos = $(window).height() - ((yMultiplier+1) * BUBBLE_TOOLBAR_HEIGHT);
        var arrayPos = i - (yMultiplier * divider);
        var xPos = BUBBLE_MINIMIZED_WIDTH * arrayPos;
        if (xPos != $(minimizedArray[i].parent).position().left || yPos != $(minimizedArray[i].parent).position().top) {
          $(minimizedArray[i].parent).animate({
            left: xPos,
            top: yPos
          }, 250);
        }
      }
    }
    
    $(bubble.parent).animate({
      left: bubble.left,
      top: bubble.top,
      width: bubble.width,
      height: bubble.height
    }, 250, function() {
      if (bubble.isMinimized) {
        bubble.isMinimized = false;
        bubble.enableDraggable();
        bubble.enableResize();
        $(bubble.parent).find('.bubbleTopBar').css('cursor', 'move');
      }
      if (bubble.isMaximized) {
        bubble.isMaximized = false;
        bubble.enableDraggable();
        bubble.enableResize();
        $(bubble.parent).find('.maxBtn').attr('title', 'maximize');
        $(bubble.parent).find('.maxBtn').unbind('click');
        $(bubble.parent).find('.maxBtn').click(function(event) {
          bubble.maximize();
        });
      }
    });
    bubble.bringToFront();
    
  };
}

function percentageToPixels(value, type) {
  if (type == 'horizontal') {
    return parseInt(value)/100 * $(window).innerWidth();
  } else {
    return parseInt(value)/100 * $(window).innerHeight();
  }
}
function pixelsToPercentage(value, type) {
  if (type == 'horizontal') {
    return value / ($(window).width()/100);
  } else {
    return value / ($(window).height()/100);
  }
}