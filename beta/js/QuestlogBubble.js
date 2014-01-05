function QuestlogBubble(width, height, left, top) {
  
  var bubble = this;
  this.parent;
  this.width = width ? width : 300;
  this.height = height ? height : 300;
  this.left = left ? left : 100;
  this.top = top ? top : 100;
  
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
        bubble.bringToFront();
        $('#mainContent').append(bubble.parent);
      }
    });
  };
  this.setup();

  this.bringToFront = function() {
    var zIndexes = [];
    $('.bubbleParent').each(function(){
      zIndexes.push(parseInt($(this).css('z-index')));
    });
    if (zIndexes.length > 0) {
      zIndexes.sort();
      $(bubble.parent).css('z-index', zIndexes[zIndexes.length-1] + 1);
    } else {
      $(bubble.parent).css('z-index', 100);
    }
  };
}