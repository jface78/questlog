
function StandbyScreen(parentDiv) {
  this.width = "100%";
  this.height = "100%";
  this.parentDiv = parentDiv;
  this.timeout = 5;
  var theDiv = document.createElement("div");
  $(theDiv).css("width", this.width);
  $(theDiv).css("height", this.height);
  $(theDiv).css("background-color", "#000000");
  $(theDiv).css("position", "absolute");
  $(theDiv).css("left", "0px");
  $(theDiv).css("top", "0px");
  $(theDiv).css("opacity", "0.7");
  $(theDiv).css('zIndex', 9999);
  var textDiv = document.createElement("div");
  $(textDiv).css("text-align", "center");
  var img = document.createElement("img");
  $(img).attr("src", "img/animated_loading.gif");
  $(textDiv).css("position", "relative");
  $(textDiv).css("top", ($(document).height()/2) - 100);
  $(textDiv).append(document.createElement("br"));
  $(textDiv).append(img);
  $(parentDiv).append(theDiv);
  $(theDiv).append(textDiv);
  this.errorTimer = setTimeout("this.handleTimeout", this.timeout * 1000);
  
  this.handleTimeout = function() {
    alert("TIMED OUT");
    $(textDiv).remove();
    textDiv = document.createElement("div");
    $(textDiv).css("font-family", "Verdana, Tahoma");
    $(textDiv).css("font-size", "20px");
    $(textDiv).css("color", "#FFFFFF");
    $(textDiv).text("Error communicating with database.\n Please try again later.");
    $(theDiv).append(textDiv);
  }
  
  this.close = function() {
    clearTimeout(this.errorTimer);
    $(theDiv).remove();
    delete this;
  }
}