/***************************************************
*              DynamicContentBubble.js             *
*         Class to spawn an empty div and          *
*         load in dynamic contents.                *
*         ©2012 Jonathan Face (jface@questlog.org  *
***************************************************/

function DynamicContentBubble(id, width, height, centered, left, top, title, content,
                              background, isMaximized, isMinimized, logoutSafe) {
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
  this.title = title;
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

  !isMaximized ? this.isMaximized = false : this.isMaximized = isMaximized;
  !isMinimized ? this.isMinimized = false : this.isMinimized = isMinimized;
  
  var iconSize = 9;
  var isLoaded = false;

  var bubbleDiv = document.createElement("div");
  this.mainDiv = bubbleDiv;
  $(bubbleDiv).attr("id", "bubbleDiv");
  $(bubbleDiv).data("theClass", this);
  $(bubbleDiv).css("border-top-left-radius", this.borderSize + "px");
  $(bubbleDiv).css("border-top-right-radius", this.borderSize + "px");
  $(bubbleDiv).css("background-color", this.background);
  //alert(this.isMinimized);
  

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
  this.setSize = function(width, height, animated) {
    if (this.isMaximized == false && this.isMinimized == false) {
      this.width = width;
      this.height = height;
    }
    //this.setTitle(this.isMaximized);
    var yRatio;
    var xRatio;
    var newYRange;
    var newY;
    var newXRange;
    var newX;
    if (!animated) {
      $(bubbleDiv).css("width", width);
      $(bubbleDiv).css("height", height);
      if (isLoaded) {
        
        $(verticalScroll).css("height", height - (this.borderSize * 2) + "px");
        $(horizontalScroll).css("width", width - this.borderSize + "px");
        $(verticalTrack).css("top", this.borderSize*2 + "px");
        $(verticalTrack).css("height", this.height - (this.borderSize * 6) + "px");
        $(horizontalTrack).css("width", width - (this.borderSize * 3) + "px");
       //$(upArrow).css("top", this.borderSize + "px");
        //$(upArrow).css("left", (this.borderSize/2) - 6 + "px");
        $(bubbleMask).css("width", width - this.borderSize + "px");
        $(bubbleMask).css("height", height - (this.borderSize * 2) + "px");
        //$(downArrow).css("top", $(verticalTrack).height() + (this.borderSize*2) + "px");
        //$(downArrow).css("left", (this.borderSize/2) - 6 + "px");
        newYRange = $(verticalTrack).height() - $(verticalDragger).height();
        yRatio = getScrollRatio($(verticalTrack), $(verticalDragger), this.borderSize, true);
        xRatio = getScrollRatio($(horizontalTrack), $(horizontalDragger), this.borderSize, false);
        newY = newYRange * yRatio + $(verticalDragger).height();
        newXRange = $(horizontalTrack).width() - $(horizontalDragger).width();
        newX = newXRange * xRatio + $(horizontalDragger).width();
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
    this.checkScrollers();
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
  this.close = function(deleteThis, event) {
    !deleteThis ? deleteThis = true : deleteThis = deleteThis;
    if (this.textareaID) {
      var editor = CKEDITOR.instances[this.textareaID];
      if (editor) { editor.destroy(true); }
    }
    var arrayPos = currentWindows.indexOf(this);
    currentWindows.splice(arrayPos, 1);
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
    $(verticalDragger).css("top", this.borderSize + "px");
    $(bubbleBody).css("top", "0px");
  }
  this.adjustVerticalDragger = function(newHeight, oldHeight) {
    var diff = newHeight - oldHeight;
    var ratio = diff / newHeight;
    var adjust = Math.round(ratio * $(verticalTrack).height());
    $(verticalDragger).css("top", $(verticalTrack).height() - (adjust - $(verticalDragger).height()/2) + "px");
    var displayHeight = $(bubbleMask).innerHeight();
    var totalHeight = $(bubbleBody).outerHeight();
    var currentY = $(verticalDragger).position().top;
    var ratio = Math.round(currentY/$(verticalTrack).height()*100)/100;
    if (ratio < 0.1) {
      ratio = 0;
    }
    //var postBubble = $(bubbleBody).find('#postControls');
    //alert($(postBubble).position().top);
    //$(postBubble).css("top", oldHeight + "px");
    //$(postBubble).css("position", "absolute");
    
    dragVertical(displayHeight, totalHeight, ratio, $(bubbleBody));
  }
  
  this.scrollToLeft = function() {
    $(horizontalDragger).css("left", this.borderSize + "px");
    $(bubbleBody).css("left", "0px");
  }
  

  var topBar = document.createElement("div");
  $(topBar).attr("id", "topBar");
  $(topBar).css("height", this.borderSize + "px");
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
  $(bubbleMask).css("width", this.width - this.borderSize + "px");
  $(bubbleMask).css("height", this.height - (this.borderSize * 2) + "px");
  $(bubbleMask).css("position", "relative");
  $(bubbleMask).addClass("floatLeft");
  $(bubbleMask).css("overflow", "hidden");
  $(bubbleMask).mousedown(this, function(event) {
    event.data.bringToFront();
  });

  var bubbleBody = document.createElement("div");
  $(bubbleBody).attr("id", "bubbleBody");
  $(bubbleBody).css("position", "relative");
  $(bubbleBody).css("padding", "5px");
  $(bubbleBody).css("top", "0px");
  $(bubbleBody).css("left", "0px");
  
  //$(bubbleBody).css("white-space", "nowrap");
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
  /*
  var upArrow = document.createElement("div");
  $(upArrow).attr("id", "upArrow");
  $(upArrow).css("position", "absolute");
  $(upArrow).css("top", this.borderSize + "px");
  $(upArrow).css("left", (this.borderSize/2) - 6 + "px");
  var a = document.createElement("a");
  var img = document.createElement("img");
  $(img).attr("src", "img/dragArrow_up.gif");
  $(img).attr("alt", "up");
  $(a).append(img);
  $(upArrow).append(a);
  $(verticalScroll).append(upArrow);
  */
  
  var verticalTrack = document.createElement("div");
  $(verticalTrack).attr("id", "verticalTrack");
  $(verticalTrack).css("position", "absolute");
  $(verticalTrack).css("left", (this.borderSize/2) - 1 + "px");
  $(verticalTrack).css("top", this.borderSize*2 + "px");
  $(verticalTrack).css("height", this.height - (this.borderSize * 6) + "px");
  $(verticalScroll).append(verticalTrack);
  /*
  var downArrow = document.createElement("div");
  $(downArrow).attr("id", "downArrow");
  $(downArrow).css("position", "absolute");
  $(downArrow).css("top", $(verticalTrack).height() + (this.borderSize*2) + "px");
  $(downArrow).css("left", (this.borderSize/2) - 6 + "px");
  var downArrowA = document.createElement("a");
  var img = document.createElement("img");
  $(img).attr("src", "img/dragArrow_down.gif");
  $(img).attr("alt", "down");
  $(downArrowA).append(img);
  $(downArrow).append(downArrowA);
  $(verticalScroll).append(downArrow);
  
  var verticalTimer;
  var verticalCount;
  var maxHeight = $(verticalTrack).height() + $(verticalTrack).position().top + (this.borderSize*4);
  $(downArrowA).mousedown(this, function(event) {
    
    alert(maxHeight);
    verticalTimer = setInterval(function() {
      if ($(verticalDragger).position().top - $(verticalDragger).height() < maxHeight) {
        verticalCount++;
        var increment = 1;
        if (verticalCount < 50) {
          increment = 5;
        }
        var newTop = parseInt($(verticalDragger).position().top) + increment;
        $(verticalDragger).css("top", newTop + "px");
        var displayHeight = $(bubbleMask).innerHeight();
        var totalHeight = $(bubbleBody).outerHeight();
        var currentY = $(verticalDragger).position().top;
        var ratio = Math.round(currentY/$(verticalTrack).height()*100)/100;
        if (ratio < 0.1) {
          ratio = 0;
        }
        dragVertical(displayHeight, totalHeight, ratio, $(bubbleBody));
      } else {
        alert("??" + $(verticalDragger).position().top - $(verticalDragger).height());
        clearTimeout(verticalTimer);
      }
    }, 10);
  });
  $(downArrowA).mouseup(this, function(event) {
    clearTimeout(verticalTimer);
    
  });
  $(upArrow).mousedown(this, function(event) {
    
  });*/
  
  var verticalDragger = document.createElement("div");
  $(verticalDragger).addClass("scrollDragger");
  $(verticalDragger).css("height", this.borderSize + "px");
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
  $(horizontalDragger).css("width", this.borderSize + "px");
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
    },
    drag: function(event, ui) {
      var displayHeight = $(bubbleMask).innerHeight();
      var totalHeight = $(bubbleBody).outerHeight();
      var currentY = $(verticalDragger).position().top;
      var ratio = Math.round(currentY/$(verticalTrack).height()*100)/100;
      if (ratio < 0.2) {
        ratio = 0;
      }
      dragVertical(displayHeight, totalHeight, ratio, $(bubbleBody));
      
    },
    stop: function(event, ui) {
      var currentY = $(verticalDragger).position().top;
      var ratio = Math.round(currentY/$(verticalTrack).height()*100)/100;
      if (ratio > 0.9 && $(bubbleDiv).data("theClass").content.indexOf("static/viewQuest.php") > -1) {
        if (!$(bubbleDiv).data("theClass").stopLoadingNewContent) {
          var child = $(bubbleBody).children('div');
          loadQuestPosts(child[1], true);
        }
      }
      if (ratio > 0.1 && $(bubbleDiv).data("theClass").content.indexOf("static/viewQuest.php") > -1) {
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
      var totalWidth = $(bubbleBody).outerWidth();
      var currentX = $(horizontalDragger).position().left;
      var ratio = Math.round(currentX/$(horizontalTrack).width()*100)/100;
      if (ratio < 0.1) {
        ratio = 0;
      }
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
    $(bubbleBody).load(content, function(response, status, xhr) {
      if (status == "success") {
        //setTimeout(thischeckScrollers, 500, $(this).width(), $(this).height());
        isLoaded = true;
      }
    });
  } else {
    isLoaded = true;
  }
  
  if (this.isMinimized) {
    this.minimize(true);
  }
  if (this.isMaximized) {
    this.maximize(true);
  }
  
}

function dragVertical(displayHeight, totalHeight, ratio, body) {
  var heightDifference = totalHeight - displayHeight;
  $(body).css("top", (heightDifference*ratio) * -1 + "px");
}
function dragHorizontal(displayWidth, totalWidth, ratio, body) {
  var widthDifference = totalWidth - displayWidth;
  $(body).css("left", (widthDifference*ratio) + "px");
}
function makeDraggable(div, handle, object) {
  $(handle).hover(
    function() {
      $(handle).css("cursor", "move")
    },
    function() {
      $(handle).css("cursor", "default")
    }
  );
  $(div).draggable({ handle: handle,
    start: function(event, ui) {
      this.centered = false;
    },
    stop: function(event, ui) {
      object.left = Math.round($(div).offset().left);
      object.top = Math.round($(div).offset().top);
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
