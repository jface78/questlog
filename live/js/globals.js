jQuery.fx.interval = 10;
var handle;
var DCBzIndex;
var minimizedWindows = new Array();
var currentWindows = new Array();
var soundEnabled = true;
var alertChecker;
var isLoggedIn = false;
var shiftDown = false;
var ctrlDown = false;

var randomsDir = "img/random/";
var randomImgs = ["swords.png", "goblin.png", "beholder.png", "heroquest.png", "dwarfmask.png", "compass.png", "flintlock.png"];

function validateEmail(email) { 
  var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return regex.test(email);
}

function generateDivID(oldDivID) {
  var div = document.getElementById("oldDivID");
  var rand = Math.floor(Math.random() * 9999);
  var newID = oldDivID + rand.toString();
  $(document.getElementById(oldDivID)).attr("id", newID);
  return newID;
}

function getDCBByContent(url) {
  var returnArray = [];
  for (var i=0; i < currentWindows.length; i++) {
    if (currentWindows[i].content.indexOf(url) > -1) {
      returnArray.push(currentWindows[i]);
    }
  }
  return returnArray;
}

function spawnWindow(birth, id, width, height, centered, left, top, title, url, background, isMinimized, isMaximized, logoutSafe, borderColor, fontColor) {
  var windowExists = false;
  if (!windowExists) {
    if (id == null) {
      id = getNextDCBID();
    }
    !width ? width = "50%" : width = width;
    !height ? height = "50%" : height = height;
    !centered ? centered = false : centered = centered;
    !left ? left = 0 : left = left;
    !top ? top = 0 : top = top;
    !title ? title = "Title" : title = title;
    !url ? url = null : url = url;
    !background ? background = "#F0F0F0" : background = background;
    !isMaximized ? isMaximized = false : isMaximized = isMaximized;
    !isMinimized ? isMinimized = false : isMinimized = isMinimized;
    !logoutSafe ? logoutSafe = false : logoutSafe = logoutSafe;
    !borderColor ? borderColor = "#000000" : borderColor = borderColor;
    !fontColor ? fontColor = "#000000" : fontColor = fontColor;
 
    var win = new DynamicContentBubble(id, width, height, evaluateBool(centered), left, top, title, unescape(url),
                                       background, evaluateBool(isMaximized), evaluateBool(isMinimized),
                                       evaluateBool(logoutSafe), borderColor, fontColor);
    currentWindows.push(win);
    // Fix for Chrome bug -
    // right and bottom borders don't appear without resize
    if ($.browser.webkit && !win.isMaximized && !win.isMinimized) {
      win.setSize(parseInt(win.width)+1, parseInt(win.height)+1, false, true);
    }
    if (isLoggedIn) {
      win.isLoggedIn = true;
    }
    if (birth && isLoggedIn) {
      addSpawnToDB(id, width, height, centered, left, top, title, url, background, isMaximized, isMinimized, logoutSafe);
    }
    return win;
  } else {
    return null;
  }
}


function addSpawnToDB(id, width, height, centered, left, top, title, url, background, isMaximized, isMinimized, logoutSafe) {
  var serviceURL = "services/manageWindows.php";
  $.ajax({
    type: "POST",
    url: serviceURL,
    data: { operation: "new", dcbID: id, width: escape(width), height: escape(height), centered: centered,
            left: escape(left), top: escape(top), title: escape(title), url: escape(url), background: escape(background),
            isMaximized: isMaximized, isMinimized:isMinimized, logoutSafe: logoutSafe},
    statusCode: {
      404: function() {
      },
      200: function() {
      }
    }
  });
}
function updateWindows(loggedIn) {
  if (!loggedIn) {
    var z = 0;
    while (z < currentWindows.length) {
      currentWindows[z].isLoggedIn = false;
      if (currentWindows[z].logoutSafe == false) {
        currentWindows[z].close(false);
      } else {
        z++;
      }
    }
    isLoggedIn = false;
    if (!getDCBByContent("static/menu.php")) {
      spawnWindow(true, null, '20%', '90%', false, '75%', '5%', 'Menu', 'static/menu.php', '#F0F0F0', false, false, true);
    } else {
      setTimeout(reloadContents, 500, "static/menu.php");
    }
    delete handle;
    if (FB) {
      FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
          FB.logout();
        }
      });
    }
    clearTimeout(alertChecker);
    $.ajax({url: "services/handleLogout.php"});
  }
}
function getNextDCBID() {
  if (currentWindows.length == 0) {
    return 0;
  } else {
    var sortArray = [];
    for (var i=0; i < currentWindows.length; i++) {
      sortArray.push(currentWindows[i].id);
    }
    sortArray.sort();
    return parseInt(sortArray[sortArray.length-1])+1;
  }
}
function evaluateBool(value) {
  if (isNaN(parseInt(value))) {
    return value;
  }
  else if (parseInt(value) == 0) {
    return false;
  } else {
    return true;
  }
}
function htmlDecode(value){ 
  return $('<div/>').html(value).html();
}

function editQuestOptions(questID) {
  var win = spawnWindow(true, null, '30%', '55%', true, '0', '0', 'Options', 'static/editQuestOptions.php?questID='+questID);
}

function launchNewPost(questID, sectionID, divID, participant) {
  var win = spawnWindow(true, null, '60%', '55%', true, '0', '0', 'New Post', 'static/makeNewPost.php?questID='+questID+'&sectionID='+sectionID+'&divID='+divID+'&participant='+participant);
}
function launchNewThread(questID, divID) {
  var win = spawnWindow(true, null, '60%', '55%', true, '0', '0', 'New Thread', 'static/makeNewThread.php?questID='+questID+'&divID='+divID);
}
function launchPostEditor(postID, tableID, questID, participant) {
  var win = spawnWindow(true, null, '60%', '55%', true, '0', '0', 'Edit Post', 'static/editPost.php?postID='+postID+"&tableID="+tableID+"&questID="+questID+'&participant='+participant);
}
function createHandle(name) {
  var win = spawnWindow(false, null, '70%', '50%', true, '0', '0', 'Welcome, n00b scum.', 'static/welcome.php?name='+name, "#F0F0F0", false, false, false);
  $(win.mainDiv).css("zIndex", 9999);
}

function declineQuest(alertID, div, senderID ) {
  var operation;
  if (senderID != "undefined") {
    operation = "declineAndBlockQuest";
  } else {
    operation = "declineQuest";
  }
  $.ajax({
    type: "POST",
    dataType: "text",
    data: {operation: operation, alertID: alertID, senderID: senderID},
    url: "services/manageQuests.php",
    statusCode: {
      200: function(data) {
        var topDiv = document.getElementById(div);
        var dcb = getDCBFromChild(topDiv);
        dcb.close();
      },
      404: function(data) {
      }
    }
  });
}

function acceptQuest(alertID, div) {
  $.ajax({
    type: "POST",
    dataType: "text",
    data: {operation: "acceptQuest", alertID: alertID},
    url: "services/manageQuests.php",
    statusCode: {
      200: function(data) {
        var topDiv = document.getElementById(div);
        var dcb = getDCBFromChild(topDiv);
        dcb.close();
      },
      404: function(data) {
      }
    }
  });
}

function checkAlerts() {
  $.ajax({
    dataType: "text",
    type: "POST",
    data: {operation: "checkAlerts"},
    url: "services/fetchAlerts.php",
    statusCode: {
      404: function(data) {
        //alert("NONE");
      },
      200: function(data) {
        var splitArray = data.split("|");
        for (var i=0; i < splitArray.length-1; i++) {
          var subArray = splitArray[i].split("&");
          spawnWindow(true, null, '50%', '50%', true, '0', '0', 'ACHTUNG!', 'static/alert.php?id=' + subArray[0]);
        }
      }
    }
  });
  alertChecker = setTimeout(checkAlerts, 300000);
}

function validateAccount(providerID, name) {
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  var resolution = screen.width + "x" + screen.height;
  $.ajax({
    type: "POST",
    dataType: "text",
    data: {openID: providerID, name: name, resolution:resolution},
    url: "services/fetchAccount.php",
    statusCode: {
      404: function(data) {
        var splitData = data.responseText.split("&");
        isLoggedIn = true;
        for (var i=0; i < currentWindows.length; i++) {
          var menu = currentWindows[i];
          var dcbID = getNextDCBID();
          addSpawnToDB(dcbID, Math.round(menu.width), Math.round(menu.height),
          menu.centered, Math.round(menu.left), Math.round(menu.top),
          menu.title, menu.content, menu.background,
          menu.isMaximized, menu.isMinimized, menu.logoutSafe);
          menu.id = dcbID;
          menu.isLoggedIn = true;
        }
        waitScreen.close();
        createHandle(name);
      },
      200: function(data) {
        var splitParent = data.split("|");
        var splitID = splitParent[0].split("&");
        isLoggedIn = true;
        handle = splitID[2];
        splitParent[1] == "0" ? soundEnabled = false : soundEnabled = true;
        var menuExists = false;
        for (var i=0; i < currentWindows.length; i++) {
          if (currentWindows[i].title != "Menu") {
            currentWindows[i].close();
          } else {
            menuExists = true;
          }
        }
        if (splitParent.length > 2) {
          for (var s=2; s < splitParent.length-1; s++) {
            var splitArray = splitParent[s].split("&");
            if (splitArray[7] == "static/menu.php" && menuExists) {
              for (var q=0; q < currentWindows.length; q++) {
                if (currentWindows[q].title == "Menu") {
                  currentWindows[q].centered = evaluateBool(splitArray[3]);
                  currentWindows[q].isMaximized = evaluateBool(splitArray[9]);
                  currentWindows[q].isMinimized = evaluateBool(splitArray[10]);
                  currentWindows[q].setSize(splitArray[1], splitArray[2]);
                  currentWindows[q].setPosition(splitArray[4], splitArray[5]);
                  currentWindows[q].id = splitArray[0];
                  currentWindows[q].isLoggedIn = true;
                  setTimeout(reloadContents, 500, "static/menu.php");
                }
              }
            } else {
              var win = spawnWindow(false, splitArray[0], splitArray[1], splitArray[2],
                                    splitArray[3], splitArray[4], splitArray[5],
                                    unescape(splitArray[6]), splitArray[7], splitArray[8],
                                    splitArray[9], splitArray[10], splitArray[11]);
              win.isLoggedIn = true;
            }
          }
        } else {
          if (menuExists == true) {
            setTimeout(reloadContents, 500, "static/menu.php");
          }
        }
        waitScreen.close();
        checkAlerts();
        if (handle == "") {
          createHandle(name);
        }
      }
    }
  });
}

function getDCBFromID(id) {
  for (var i=0; i < currentWindows.length; i++) {
    if (currentWindows[i].id == id) {
      return currentWindows[i];
    }
  }
}
function setTitles(url, title) {
  for (var i=0; i < currentWindows.length; i++ ){
    if (currentWindows[i].content.indexOf(url) > -1) {
      currentWindows[i].setTitle(title, true);
    }
  }
}
function reloadContents(content) {
  for (var i=0; i < currentWindows.length; i++) {
    if (currentWindows[i].content.indexOf(content) > -1) {
      currentWindows[i].reload();
    }
  }
}
function remoteClose(div) {
  var dcb = getDCBFromChild(document.getElementById(div));
  dcb.close();
}

function resetMinimizedWindows(tabWidth, borderSize) {
  for (var i = 0; i < minimizedWindows.length; i++) {
    var divider = Math.floor($(window).width() / tabWidth);
    var yMultiplier = Math.floor(i / divider);
    var yPos = $(window).height() - ((borderSize) * (yMultiplier+1));
    var arrayPos = (i - ((yMultiplier) * (divider)));
    var xPos = tabWidth * arrayPos;
    $(minimizedWindows[i].getDiv()).stop(true, true);
    minimizedWindows[i].setPosition(xPos, yPos, true);
  }
}

function getDCBFromChild(div) {
  return $(div).parent().parent().parent().data("theClass");
}

function getUrlVars() {
  var vars = [], hash;
  var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
  for(var i = 0; i < hashes.length; i++) {
    hash = hashes[i].split('=');
    vars.push(hash[0]);
    vars[hash[0]] = hash[1];
  }
  return vars;
}