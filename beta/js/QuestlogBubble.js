function QuestlogBubble(width, height, left, top, title, content) {
  
  var bubble = this;
  this.parent;
  this.title = title ? title : 'Questlog';
  this.content = content ? content : 'ok';
  this.width = width ? width : 300;
  this.height = height ? height : 300;
  this.left = left ? left : 100;
  this.top = top ? top : 100;
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
        }, function(event) {
          $(topBar).removeClass('ui-state-hover');
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
        
        $(bubble.parent).find('.maxBtn').click(function(event) {
          bubble.maximize();
        });
        bubble.bringToFront();
        $(bubble.parent).draggable({ handle: $(bubble.parent).find('.bubbleTopBar') });
        $(bubble.parent).resizable({
          ghost: true,
          animate: true,
          animateDuration: 'fast'
        });
        $(bubble.parent).mousedown(function(event) {
          bubble.bringToFront();
        });
        if (bubble.content) {
        }
        $('#mainContent').append(bubble.parent);
        $(bubble).trigger('loadComplete');
      }
    });
  };

  this.setContent = function(element) {

    var contentArea = $(bubble.parent).find('.bubbleContent')[0];
    console.log('content ' + bubble.parent);
    $(contentArea).html(element);
  };
  
  this.bringToFront = function() {
    var zIndexes = [];
    $('.bubbleParent').each(function(){
      //$(this).removeClass('ui-state-active');
      //$(this).addClass('ui-state-default');
      zIndexes.push(parseInt($(this).css('z-index')));
    });

    //$(bubble.parent)
    var content = $(bubble.parent).find('.bubbleContent')[0];
    //$(content).addClass('ui-state-active');
    //$(content).removeClass('ui-state-default');

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
    $(bubble.parent).css('left', 0);
    $(bubble.parent).css('top', 0);
    $(bubble.parent).css('width', '100%');
    $(bubble.parent).css('height', '100%');
    bubble.bringToFront();
    $(bubble.parent).find('.maxBtn').attr('title', 'restore');
    $(bubble.parent).find('.maxBtn').click(function(event) {
      bubble.restorePositionAndSize();
    });
  };
  
  this.restorePositionAndSize = function() {
    this.isMaximized = false;
    this.isMinimized = false;
    $(bubble.parent).css('left', bubble.left);
    $(bubble.parent).css('top', bubble.top);
    $(bubble.parent).css('width', bubble.width);
    $(bubble.parent).css('height', bubble.height);
    bubble.bringToFront();
    $(bubble.parent).find('.maxBtn').attr('title', 'maximize');
    $(bubble.parent).find('.maxBtn').click(function(event) {
      bubble.maximize();
    });
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