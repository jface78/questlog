/***************************************************
*              DynamicContentBubble.js             *
*         Class to spawn an empty div and          *
*         load in dynamic contents.                *
*         ©2012 Jonathan Face (jface@questlog.org  *
***************************************************/

function DynamicContentBubble(id, width, height, centered, left, top, title, content,
                              background, isMaximized, isMinimized, logoutSafe, fontColor, borderColor) {
  if (soundEnabled) {
    document.getElementById("popupSound").play();
  }
  if (left && left.toString().indexOf("%") > -1) {
    left = percentageToPixels(left, "left");
  }
  if (top && top.toString().indexOf("%") > -1) {
    top = percentageToPixels(top, "top");
  }
  if (width.toString().indexOf("%") > -1) {
    width = percentageToPixels(width, "left");
  }
  if (height.toString().indexOf("%") > -1) {
    height = percentageToPixels(height, "top");
  }
  this.id = id;
  this.width = width;
  this.height = height;
  this.centered = centered;
  this.left = left;
  this.top = top;
  this.title = unescape(title);
  this.content = content;
  this.background = background;
  this.isMaximized = isMaximized;
  this.isMinimized = isMinimized;
  this.logoutSafe = logoutSafe;
  this.textareaID;
  
  this.minimumWidth = 120;
  this.minimumHeight = 80;
  this.borderSize = 15;
  this.tabWidth = 120;
  this.isResizing = false;
  this.isLoggedIn = false;
  this.stopLoadingNewContent = false;
  this.scrollerYPos = 0;
  
  this.fontColor;
  this.borderColor;

  !isMaximized ? this.isMaximized = false : this.isMaximized = isMaximized;
  !isMinimized ? this.isMinimized = false : this.isMinimized = isMinimized;
  
  !fontColor ? this.fontColor = "#000000" : this.fontColor = fontColor;
  !borderColor ? this.borderColor = "#000000" : this.borderColor = borderColor;
  
  var iconSize = 9;
  var isLoaded = false;

  var bubbleDiv = document.createElement("div");
  this.mainDiv = bubbleDiv;
  $(bubbleDiv).attr("id", "bubbleDiv");
  $(bubbleDiv).data("theClass", this);
  $(bubbleDiv).css("border", "1px solid " + this.borderColor);
  $(bubbleDiv).css("border-top-left-radius", this.borderSize/2 + "px");
  $(bubbleDiv).css("border-top-right-radius", this.borderSize/2 + "px");
  $(bubbleDiv).css("background-color", this.background);
  $(bubbleDiv).css("overflow", "hidden");

  this.bringToFront = function(event) {
    !DCBzIndex ? DCBzIndex = 0 : DCBzIndex = DCBzIndex;
    var currentZ = parseInt($(bubbleDiv).css('zIndex'));
    DCBzIndex = currentZ > DCBzIndex ? $(bubbleDiv).css( 'zIndex') : DCBzIndex;
    $(bubbleDiv).css('zIndex', DCBzIndex++);
  }
  this.setPosition = function(left, top, animated) {

    if (this.centered && !this.isMaximized && !this.isMinimized) {
      left = $(window).width()/2 - this.width/2;
      top = $(window).height()/2 - this.height/2;
      for (var i=0; i < currentWindows.length; i++) {
        if (currentWindows[i].left == left && currentWindows[i].isMinimized == false) {
          for (var q=0; q < currentWindows.length; q++) {
            if (currentWindows[q].top == top && currentWindows[q].isMinimized == false) {
              left += 10;
              top += 10;
            }
          }
        }
      }
    }
    if (!this.isMaximized && !this.isMinimized) {
      this.left = left;
      this.top = top;
    }
    if (!animated) {
      $(bubbleDiv).css("left", left + "px");
      $(bubbleDiv).css("top", top + "px");
    } else {
      var anim = $(bubbleDiv).animate({
        top: top + "px",
        left: left + "px"
      }, 500);
    }
  }

  this.checkScrollers = function (childDiv) {
    if (!childDiv) {
      childDiv = $(bubbleBody);
    }
    var width = $(childDiv).width();
    var height = $(childDiv).height();
    //!width ? width = $(bubbleBody).width() : width = width;
    //!height ? height = $(bubbleBody).height() : height = height;
    if (height > $(bubbleMask).height()) {
      $(verticalScroll).css("visibility", "visible");
    } else {
      $(verticalScroll).css("visibility", "hidden");
    }
    if (width > $(bubbleMask).width()) {
      $(horizontalScroll).css("visibility", "visible");
    } else {
      $(horizontalScroll).css("visibility", "hidden");
    }
    
  }
  this.setSize = function(width, height, animated, resetScrollers) {
    if (this.isMaximized == false && this.isMinimized == false) {
      this.width = width;
      this.height = height;
    }
    if (!animated) {
      $(bubbleDiv).css("width", width);
      $(bubbleDiv).css("height", height);
      if (isLoaded) {
        $(bubbleMask).css("width", width - (this.borderSize*2) + "px");
        if (height - (this.borderSize * 2) < this.borderSize) {
          height = this.borderSize;
        } else {
          height -= this.borderSize*2;
        }
        
        $(bubbleMask).css("height", height + "px");
        $(verticalScroll).css("height", height + "px");
        $(horizontalScroll).css("width", width - this.borderSize + "px");
        $(verticalScroll).css("height", this.height - (this.borderSize * 2) + "px");
        var oldHeight = $(verticalTrack).height();
        var newHeight = this.height - (this.borderSize * 5);
        $(verticalTrack).css("height", newHeight + "px");
        var oldY = $(verticalDragger).position().top - (this.borderSize*2)
        var yRatio = oldHeight/oldY;
        var newY = newHeight/yRatio + (this.borderSize*2);
        if (newY < (this.borderSize*2)) {
          newY = this.borderSize*2;
        }
        if (newY > $(verticalTrack).height() - $(verticalDragger).height() - (this.borderSize*2)) {
          //newY = $(verticalTrack).height() - $(verticalDragger).height() - (this.borderSize*2);
        }
        var oldWidth = $(horizontalTrack).height();
        var newWidth = this.width - (this.borderSize*2);
        var oldX = $(horizontalDragger).position().left - (this.borderSize*2);
        var xRatio = oldWidth / oldX;
        var newX = newWidth/xRatio + (this.borderSize*2);
        if (newX < (this.borderSize*2)) {
          newX = this.borderSize * 2;
        }
        if (newX > $(horizontalTrack).width() - $(horizontalDragger).width() - (this.borderSize*2)) {
          newX = $(horizontalTrack).width() - $(horizontalDragger).width() - (this.borderSize*2);
        }
        $(verticalDragger).css("top",  newY + "px");
        $(horizontalDragger).css("left", newX + "px");

        var displayHeight = $(bubbleMask).height();
        var totalHeight = $(bubbleBody).height();
        var currentY = $(verticalDragger).position().top;
        var ratio = Math.round(currentY/$(verticalTrack).height());
        dragVertical(displayHeight, totalHeight, ratio, $(bubbleBody));
        var displayWidth = $(bubbleMask).width();
        var totalWidth = $(bubbleBody).width();
        var currentX = $(horizontalDragger).position().left;
        ratio = Math.round(currentX/$(horizontalTrack).width());
        dragHorizontal(displayWidth, totalWidth, ratio, $(bubbleBody));
        var textSize = ($(titleDiv).position().left + $(titleDiv).width());
        
        while (textSize >= $(rightIcons).position().left - 5) {
          var lastThree = $(titleDiv).text().substr($(titleDiv).text().length-3,$(titleDiv).text().length);
          var endSub;
          if (lastThree == "...") {
            endSub = $(titleDiv).text().length-4;
          } else {
            endSub = $(titleDiv).text().length-1;
          }
          var newTitle = $(titleDiv).text().substr(0, endSub) + "...";
          this.setTitle(newTitle);
          textSize = ($(titleDiv).position().left + $(titleDiv).width());
        } 
        while (($(titleDiv).text() != this.title) && (textSize < $(rightIcons).position().left - 5) ) {
          lastThree = $(titleDiv).text().substr($(titleDiv).text().length-3,$(titleDiv).text().length);
          if (lastThree == "...") {
            endSub = $(titleDiv).text().length-3;
          } else {
            endSub = $(titleDiv).text().length;
          }
          newTitle = $(titleDiv).text().substr(0, endSub);
          var textArray = this.title.split(newTitle);
          if (textArray.length > 1) {
            var remainder = textArray[1].charAt(0);
            if (newTitle + remainder != this.title) {
              remainder += "...";
            }
            newTitle += remainder;
          }
          this.setTitle(newTitle);
          textSize = ($(titleDiv).position().left + $(titleDiv).width());
        }
      }
    } else {
      $(bubbleDiv).css("width", width);
      $(bubbleDiv).css("height", height);
      $(bubbleDiv).animate({
        height:height,
        width:width
      }, 500, function() {
        // Animation complete.
      });
    }
    if (!resetScrollers) {
      this.checkScrollers();
    }
  }
  
  this.setTitle = function(text, update) {
    $(titleDiv).text(text);
    if (update) {
      $.ajax({
        type: "POST",
        data: {operation: "title", dcbID: this.id, title:text},
        url: "services/manageWindows.php"});
    }
  }

  this.maximize = function(initializing, event) {
    this.isMaximized = true;
    this.setPosition(0, 0);
    this.setSize($(window).width() - 2, $(window).height() - 2);
    $(draggerDiv).unbind();
    $(draggerDiv).css("cursor", "default");
    $(bubbleDiv).draggable("destroy");
    $(maxA).unbind("click");
    this.checkScrollers();
    $(maxA).click(this, function(event) {
      event.data.restore();
    });
    if (!initializing) {
      if (this.isLoggedIn) {
        $.ajax({
          type: "POST",
          data: {operation: "maximize", dcbID: this.id},
          url: "services/manageWindows.php"});
      }
    }
  }
  this.getDiv = function() {
    return $(bubbleDiv);
  }
  
  this.restore = function(event) {
    this.bringToFront();
    $(bubbleDiv).stop(true, true);
    $(rightIcons).css("visibility", "visible");
    $(bubbleBody).css("visibility", "visible");
    $(draggerDiv).unbind();
    makeDraggable($(bubbleDiv), $(draggerDiv), this);
    if (this.isMaximized && !this.isMinimized) {
      this.isMaximized = false;
      this.setPosition(this.left, this.top);
      this.setSize(this.width, this.height);
      this.enableControls();
    }
    else if (this.isMaximized && this.isMinimized) {
      this.isMinimized = false;
      $(draggerDiv).css("z-index", "1");
      this.enableControls();
      this.maximize();
    }
    else {
      this.isMinimized = false;
      $(draggerDiv).css("z-index", "1");
      this.setPosition(this.left, this.top, true);
      var arrayPos = minimizedWindows.indexOf(this);
      minimizedWindows.splice(arrayPos, 1);
      resetMinimizedWindows(this.tabWidth, this.borderSize);
      this.setSize(this.width, this.height);
      this.enableControls();
    }
    this.checkScrollers();
    if (this.isLoggedIn) {
      $.ajax({
        type: "POST",
        data: {operation: "restore", id: this.id, isMaximized: this.isMaximized, isMinimized: this.isMinimized},
        url: "services/manageWindows.php"});
    }
  }
  this.close = function(deleteThis, removeFromArray, event) {
    !deleteThis ? deleteThis = true : deleteThis = deleteThis;
    !removeFromArray? removeFromArray = true : removeFromArray = removeFromArray;
    if (removeFromArray == true) {
      var arrayPos = currentWindows.indexOf(this);
      currentWindows.splice(arrayPos, 1);
    }
    $(bubbleDiv).stop(true, true);
    $(bubbleDiv).remove();
    $(maxA).unbind();
    $(minA).unbind();
    $(closeA).unbind();
    $(draggerDiv).unbind();
    $(bubbleDiv).draggable("destroy");
    $(verticalDragger).draggable("destroy");
    $(horizontalDragger).draggable("destroy");
    if (this.isLoggedIn && deleteThis == true) {
      $.ajax({
        type: "POST",
        data: {operation: "close", dcbID: this.id},
        url: "services/manageWindows.php"});
    }
    delete this;
  }
  this.minimize = function(initializing, event) {
    this.isMinimized = true;
    if (minimizedWindows.length > 0) {
      resetMinimizedWindows(this.tabWidth, this.borderSize);
    }
    this.setSize(this.tabWidth, this.borderSize);
    var divider = Math.floor($(window).width() / this.tabWidth);
    var yMultiplier = Math.floor(minimizedWindows.length / divider);
    var yPos = $(window).height() - ((this.borderSize) * (yMultiplier+1));
    var arrayPos = (minimizedWindows.length - ((yMultiplier) * (divider)));
    var xPos = this.tabWidth * arrayPos;
    this.setPosition(xPos, yPos, true);
    this.disableControls();
    $(rightIcons).css("visibility", "hidden");
    $(bubbleBody).css("visibility", "hidden");
    $(bubbleMask).css("visibility", "hidden");
    $(verticalScroll).css("visibility", "hidden");
    $(draggerDiv).css("cursor", "pointer");
    $(draggerDiv).css("z-index", "2");
    $(rightIcons).css("z-index", "1");
    $(draggerDiv).click(this, function(event) {
      event.data.restore();
    });
    minimizedWindows.push(this);
    if (!initializing) {
      if (this.isLoggedIn) {
        $.ajax({
          type: "POST",
          data: {operation: "minimize", dcbID: this.id},
          url: "services/manageWindows.php"});
      }
    }
  }
  this.disableControls = function() {
    $(topBar).unbind();
    $(draggerDiv).unbind();
    $(maxA).unbind();
    $(minA).unbind();
    $(closeA).unbind();
    $(resizeA).unbind();
    $(draggerDiv).css("cursor", "default");
    $(maxA).css("cursor", "default");
    $(minA).css("cursor", "default");
    $(closeA).css("cursor", "default");
    $(resizeA).css("cursor", "default");
    $(bubbleDiv).draggable("destroy");
  }
  this.enableControls = function() {
    $(maxA).unbind();
    $(minA).unbind();
    $(closeA).unbind();
    $(resizeA).unbind();

    $(maxA).click(this, function(event) {
      event.data.maximize();
    });
    $(maxA).hover(function() {$(maxA).css("cursor", "pointer")});
    $(minA).click(this, function(event) {
      event.data.minimize();
    });
    $(minA).hover(function() {$(minA).css("cursor", "pointer")});
    $(closeA).click(this, function(event) {
      event.data.close();
    });
    $(closeA).hover(function() {$(closeA).css("cursor", "pointer")});
    $(resizeA).mousedown(this, function(event) {
      event.data.isResizing = true;
    });
    $(resizeA).mouseup(this, function(event) {
      event.data.isResizing = false;
      if (event.data.isLoggedIn) {
        $.ajax({
          type: "POST",
          data: {operation: "resize", dcbID: event.data.id, width: Math.round(event.data.width), height: Math.round(event.data.height)},
          url: "services/manageWindows.php"});
      }
    });
    
    $(resizeA).mouseout(this, function(event) {
      //event.data.isResizing = false;
    });
    $(resizeA).hover(function() {$(resizeA).css("cursor", "pointer")});
    makeDraggable($(bubbleDiv), $(draggerDiv), this);
  }
  
  this.reload = function(content, isHTML) {
    isLoaded = false;
    if (content && isHTML) {
      $(bubbleBody).html(content);
      isLoaded = true;
    }
    if (content && !isHTML) {
      this.content = content;
      $(bubbleBody).load(this.content, function(response, status, xhr) {
        isLoaded = true;
      });
    } else {
      $(bubbleBody).load(this.content, function(response, status, xhr) {
        isLoaded = true;
      });
    }
  }
  
  this.scrollToTop = function() {
    $(verticalDragger).css("top", this.borderSize + "px");
    $(bubbleBody).css("top", "0px");
  }
  this.scrollToBottom = function() {
    $(verticalDragger).css("top", $(verticalTrack).height() - $(verticalDragger).height() + (this.borderSize * 2) + "px");
    var newY = $(bubbleBody).innerHeight() - $(bubbleMask).height();
    $(bubbleBody).css("top", newY * - 1 + "px");
  }
  this.adjustVerticalDragger = function(oldHeight) {
    var totalHeight = $(bubbleBody).innerHeight();
    var diff = totalHeight - oldHeight;
    var ratio = Math.round(diff/(totalHeight - $(verticalDragger).height())*100)/100;
    var newY = ($(verticalDragger).position().top - ($(verticalDragger).position().top * ratio)) + $(verticalDragger).height();
    $(verticalDragger).css("top", newY + "px");
    //$(bubbleDiv).data("theClass").setTitle(ratio);
  }
  
  this.scrollToLeft = function() {
    $(horizontalDragger).css("left", this.borderSize + "px");
    $(bubbleBody).css("left", "0px");
  }
  

  var topBar = document.createElement("div");
  $(topBar).attr("id", "topBar");
  $(topBar).css("border-bottom", "1px solid " + this.borderColor);
  $(topBar).css("height", this.borderSize + "px");
  $(topBar).css("color", this.fontColor);
  var leftIcons = document.createElement("span");
  $(leftIcons).attr("id", "leftIcons");
  $(leftIcons).css("padding-left", this.borderSize + "px");
  
  var favImg = document.createElement("img");
  $(favImg).attr("src", "img/favicon.png");
  $(favImg).attr("id", "favIcon");
  var titleDiv = document.createElement("div");
  $(titleDiv).attr("id", "titleDiv");
  $(titleDiv).text(this.title);
  $(leftIcons).append(favImg);
  $(leftIcons).append(titleDiv);
  var rightIcons = document.createElement("span");
  $(rightIcons).attr("id", "rightIcons");
  $(rightIcons).css("padding-right", this.borderSize + "px");
  $(rightIcons).css("width", 25 + this.borderSize + "px");
  //$(rightIcons).css("border", "1px solid blue");
  var minA = document.createElement("a");
  $(minA).addClass("rightButtons");
  var minImg = document.createElement("img");
  $(minImg).attr("src", "img/minimize.png");
  var maxA = document.createElement("a");
  $(maxA).addClass("rightButtons");
  var maxImg = document.createElement("img");
  $(maxImg).attr("src", "img/maximize.png");
  var closeA = document.createElement("a");
  var closeImg = document.createElement("img");
  $(closeImg).attr("src", "img/close.png");
  $(minA).append(minImg);
  $(maxA).append(maxImg);
  $(closeA).append(closeImg);
  $(rightIcons).append(minA);
  $(rightIcons).append(maxA);
  $(rightIcons).append(closeA);
  
  var draggerDiv = document.createElement("span");
  $(draggerDiv).attr("id", "draggerDiv");
  $(draggerDiv).css("z-index", "1");
  $(rightIcons).css("z-index", "2");
  $(draggerDiv).css("height", this.borderSize + "px");
  $(draggerDiv).css("border-top-left-radius", this.borderSize + "px");
  $(draggerDiv).css("border-top-right-radius", this.borderSize + "px");
  $(topBar).append(leftIcons);
  $(topBar).append(draggerDiv);
  $(topBar).append(rightIcons);
  $(bubbleDiv).append(topBar);
  $(draggerDiv).mousedown(this, function(event) {
    event.data.bringToFront();
  });
  
  this.setSize(this.width, this.height);
  this.setPosition(this.left, this.top);
  var bubbleMask = document.createElement("div");
  $(bubbleMask).attr("id", "bubbleMask");
  $(bubbleMask).css("width", this.width - (this.borderSize + (this.borderSize/2)) + "px");
  $(bubbleMask).css("height", this.height - (this.borderSize * 2) + "px");
  $(bubbleMask).css("position", "relative");
  $(bubbleMask).addClass("floatLeft");
  $(bubbleMask).css("overflow", "hidden");
  $(bubbleMask).mousedown(this, function(event) {
    event.data.bringToFront();
  });
  $(bubbleDiv).css("vertical-align", "top");
  var bubbleBody = document.createElement("div");
  $(bubbleBody).attr("id", "bubbleBody");
  $(bubbleBody).css("position", "relative");
  
  $(bubbleBody).css("padding", "5px");
  $(bubbleBody).css("top", "0px");
  $(bubbleBody).css("left", "0px");
  $(bubbleMask).append(bubbleBody);
  $(bubbleDiv).append(bubbleMask);
  
  var verticalScroll = document.createElement("div");
  $(verticalScroll).attr("id", "verticalScroll");
  $(verticalScroll).css("position", "relative");
  $(verticalScroll).css("width", this.borderSize + "px");
  $(verticalScroll).css("height", this.height - (this.borderSize * 2) + "px");
  
  $(verticalScroll).addClass("floatRight");
  $(verticalScroll).css("visibility", "hidden");
  $(bubbleDiv).append(verticalScroll);

  var horizontalScroll = document.createElement("div");
  $(horizontalScroll).attr("id", "horizontalScroll");
  $(horizontalScroll).css("position", "relative");
  $(horizontalScroll).css("width", this.width - this.borderSize + "px");
  $(horizontalScroll).css("height", this.borderSize + "px");
  $(horizontalScroll).addClass("floatLeft");
  $(horizontalScroll).css("visibility", "hidden");
  $(bubbleDiv).append(horizontalScroll);

  var verticalTrack = document.createElement("div");
  $(verticalTrack).attr("id", "verticalTrack");
  $(verticalTrack).css("position", "absolute");
  $(verticalTrack).css("left", (this.borderSize/2) - 1 + "px");
  $(verticalTrack).css("top", this.borderSize*2 + "px");
  $(verticalTrack).css("height", this.height - (this.borderSize * 5) + "px");
  $(verticalScroll).append(verticalTrack);
  
  
  var verticalDragger = document.createElement("div");
  $(verticalDragger).addClass("scrollDragger");
  $(verticalDragger).css("height", this.borderSize *2 + "px");
  $(verticalDragger).css("width", (this.borderSize/2) + "px");
  $(verticalDragger).css("top", this.borderSize * 2 + "px");
  $(verticalDragger).css("left", this.borderSize - (this.borderSize/2) - (this.borderSize/4) + "px");
  $(verticalScroll).append(verticalDragger);

  var horizontalTrack = document.createElement("div");
  $(horizontalTrack).attr("id", "horizontalTrack");
  $(horizontalTrack).css("position", "absolute");
  $(horizontalTrack).css("top", this.borderSize/2 - 0.5 + "px");
  $(horizontalTrack).css("left", this.borderSize*2 + "px");
  $(horizontalTrack).css("width", this.width - (this.borderSize * 4) + "px");
  $(horizontalScroll).append(horizontalTrack);
  
  var horizontalDragger = document.createElement("div");
  $(horizontalDragger).addClass("scrollDragger");
  $(horizontalDragger).css("width", this.borderSize*2 + "px");
  $(horizontalDragger).css("height", this.borderSize/2 + "px");
  $(horizontalDragger).css("left", this.borderSize + "px");
  $(horizontalDragger).css("top", this.borderSize - (this.borderSize/2) - (this.borderSize/4) + "px");
  $(horizontalScroll).append(horizontalDragger);
  
  var resizeImg = document.createElement("img");
  $(resizeImg).attr("src", "img/drag.png");
  var resizeA = document.createElement("a");
  $(resizeA).css("position", "relative");
  $(resizeA).css("top", (this.borderSize - iconSize)/2 + "px");
  $(resizeA).addClass("floatRight");
  $(resizeA).css("height", this.borderSize + "px");
  $(resizeA).mousedown(this, function(event) {
    event.data.isResizing = true;
  });
  $(resizeA).mouseup(this, function(event) {
    event.data.isResizing = false;
  });
  $(resizeA).append(resizeImg);
  $(bubbleDiv).append(resizeA);

  $(verticalDragger).draggable({
    axis: 'y',
    containment: $(verticalTrack),
    start: function(event, ui) {
      $(document.getElementById("mainSection")).addClass("unselectable");
    },
    drag: function(event, ui) {
      var displayHeight = $(bubbleMask).innerHeight();
      var totalHeight = $(bubbleBody).innerHeight();
      var currentY = $(verticalDragger).position().top - ($(bubbleDiv).data("theClass").borderSize*2);
      var ratio = Math.round(currentY/($(verticalTrack).height() - $(verticalDragger).height())*100)/100;
      //$(bubbleDiv).data("theClass").setTitle(ratio);
      dragVertical(displayHeight, totalHeight, ratio, $(bubbleBody));
    },
    stop: function(event, ui) {
      $(document.getElementById("mainSection")).removeClass("unselectable");
      var currentY = $(verticalDragger).position().top - ($(bubbleDiv).data("theClass").borderSize*2);
      var ratio = Math.round(currentY/($(verticalTrack).height() - $(verticalDragger).height())*100)/100;
      if (ratio >= 1 && $(bubbleDiv).data("theClass").content.indexOf("static/viewQuest.php") > -1) {
        if (!$(bubbleDiv).data("theClass").stopLoadingNewContent) {
          var child = $(bubbleBody).children('div');
          loadQuestPosts(child[1], true);
        }
      }
      
    }
  });
  $(horizontalDragger).draggable({
    axis: 'x',
    containment: $(horizontalTrack),
    start: function(event, ui) {
    },
    drag: function(event, ui) {
      var displayWidth = $(bubbleMask).innerWidth();
      var totalWidth = $(bubbleBody).innerWidth();
      var currentX = $(horizontalDragger).position().left - ($(bubbleDiv).data("theClass").borderSize*2);
      var ratio = Math.round(currentX/($(horizontalTrack).width() - $(horizontalDragger).width())*100)/100;
      dragHorizontal(displayWidth, totalWidth, ratio, $(bubbleBody));
    },
    stop: function(event, ui) {
    }
  });
  var mainBody = document.getElementById("mainSection");
  $(mainBody).append(bubbleDiv);
  this.enableControls();
  this.bringToFront();
  if (this.content) {
    $(bubbleBody).data("theClass", this);
    $(bubbleBody).load(content, function(response, status, xhr) {
      if (status == "success") {
        //setTimeout(thischeckScrollers, 500, $(this).width(), $(this).height());
        isLoaded = true;
        if ($(this).data("theClass").isMinimized) {
          $(this).data("theClass").minimize(true);
        }
        if ($(this).data("theClass").isMaximized) {
          $(this).data("theClass").maximize(true);
        }
        $(this).data("theClass", null);
      }
    });
  } else {
    isLoaded = true;
  }
  
  
  
}

function dragVertical(displayHeight, totalHeight, ratio, body) {
  var heightDifference = Math.round(totalHeight - displayHeight);
  //heightDifference = heightDifference/2;
  $(body).css("top", (heightDifference*ratio) * -1 + "px");
}
function dragHorizontal(displayWidth, totalWidth, ratio, body) {
  var widthDifference = totalWidth - displayWidth;
  $(body).css("left", (widthDifference*ratio) + "px");
}
function makeDraggable(div, handle, object) {
  //fix for chrome bug not maintaining the "move" type cursor on drag
  document.onselectstart = function () { return false; };

  $(handle).hover(
    function() {
      $(handle).css("cursor", "move")
    },
    function() {
      $(handle).css("cursor", "default")
    }
  );
  $(handle).mousedown(this, function(event) {
    object.bringToFront();
  });
  $(div).draggable({ handle: handle,
    start: function(event, ui) {
      $(document.getElementById("mainSection")).css("cursor", "move")
      $(document.getElementById("mainSection")).addClass("unselectable");
      this.centered = false;
    },
    stop: function(event, ui) {
      $(document.getElementById("mainSection")).css("cursor", "default")
      $(document.getElementById("mainSection")).removeClass("unselectable");
      var leftValue = Math.round($(div).offset().left);
      var topValue = Math.round($(div).offset().top);
      if (leftValue < ($(div).width() * -1)) {
        leftValue = 0;
        $(div).css("left", leftValue + "px");
      }
      if (topValue < 0 - $(handle).height()) {
        topValue = 0;
        $(div).css("top", topValue + "px");
      }
      if (topValue > $(document).height()) {
        topValue = $(document).height() - $(handle).height();
        $(div).css("top", topValue + "px");
      }
      if (leftValue > $(document).width()) {
        leftValue = $(document).width() - 50;
        $(div).css("left", leftValue + "px");
      }
      object.left = leftValue;
      object.top = topValue;
      if (object.isLoggedIn) {
        $.ajax({
          type: "POST",
          data: {operation: "move", dcbID: object.id, left: escape(object.left), top: escape(object.top)},
          url: "services/manageWindows.php"});
      }
		}
  });
  isLoaded = true;
}


function getScrollRatio(track, dragger, border, vertical) {
  var range;
  if (vertical) {
    range = $(track).height() - $(dragger).height();
  } else {
    range = $(track).width() - $(dragger).width();
  }
  var pos;
  if (vertical) {
    pos = $(dragger).position().top - border;
  } else {
    pos = $(dragger).position().left - border;
  }
  return (Math.round(100*(pos/range))/100);
}
function percentageToPixels(value, type) {
  switch (type) {
    case "left":
    return parseInt(value)/100 * $(window).width();
    break;
    case "top":
    return parseInt(value)/100 * $(window).height();
    break;
  }
}
function pixelsToPercentage(value, type) {
  switch(type) {
    case "left":
    return value / ($(window).width()/100);
    break;
    case "top":
    return value / ($(window).height()/100);
    break;
  }
}
