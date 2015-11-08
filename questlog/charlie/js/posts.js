function newPost(characterID, postText, dialog) {
  $.ajax({
    url: API_URL + 'posts',
    method: 'POST',
    data: {apiKey: LOCAL_API_KEY, QID: currentQuestData.questID, CID: characterID, BODY: sanitizeTextForDB(postText)},
    dataType: 'json',
    statusCode: {
      200: function(response) {
        dialog.dialog('close');
        getPostsByPage();
      }
    }
  });
}

function renderNewPostWindow() {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'new post');
  var div = document.createElement('div');
  $(div).css('height', '22px');
  $(div).text('posting as ');
  var select = document.createElement('select');
  $(select).addClass('posterSelect');
  if (userID == currentQuestData.gmID) {
    var option = document.createElement('option');
    $(option).val('0');
    $(option).text(currentQuestData.gmName + ' - GM');
    $(select).append(option);
  }
  for (var i=0; i < currentQuestData.players.length; i++) {
    if (currentQuestData.players[i].userID == userID) {
      var option = document.createElement('option');
      $(option).val(currentQuestData.players[i].characterID);
      $(option).text(currentQuestData.players[i].name);
      $(select).append(option);
    }
  }
  $(div).append(select);
  $(popupContainer).append(div);
  var textArea = document.createElement('textarea');
  $(textArea).addClass('postTextArea');
  $(popupContainer).append(textArea);
  var dialog = $(popupContainer).dialog({
    height: 400,
    width: 450,
    modal: false,
    buttons: {
      'Post': function() {
        newPost($(select).val(), $(textArea).val(), dialog);
      },
      Cancel: function() {
        dialog.dialog('close');
      }
    },
    close: function() {
    }
  });
}

function editPost(postID, characterID, postText, dialog) {
  postText = sanitizeTextForDB(postText);
  $.ajax({
    url: API_URL + 'posts?apiKey=' + LOCAL_API_KEY,
    method: 'PUT',
    dataType: 'json',
    data: {pid: postID, cid: characterID, body: postText},
    statusCode: {
      200: function(response) {
        dialog.dialog('close');
        getPostsByPage();
      }
    }
  });
}

function renderEditPostWindow(button) {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'edit post #' + $(button).data('post-id'));
  var div = document.createElement('div');
  $(div).text('posting as ');
  var select = document.createElement('select');
  $(select).addClass('posterSelect');
  if (userID == currentQuestData.gmID) {
    var option = document.createElement('option');
    $(option).val('0');
    $(option).text(currentQuestData.gmName + ' - GM');
    if ($(button).data('character-id') == 'GM') {
        $(option).prop('selected', true);
      }
    $(select).append(option);
  }
  for (var i=0; i < currentQuestData.players.length; i++) {
    if (currentQuestData.players[i].userID == userID) {
      var option = document.createElement('option');
      $(option).val(currentQuestData.players[i].characterID);
      $(option).text(currentQuestData.players[i].name);
      if ($(button).data('character-id') == currentQuestData.players[i].characterID) {
        $(option).prop('selected', true);
      }
      $(select).append(option);
    }
  }
  $(div).append(select);
  $(popupContainer).append(div);
  var textArea = document.createElement('textarea');
  $(textArea).addClass('postTextArea');
  var text = $(button).parent().parent().parent().find('.postBody').html();
  //text = convertHTMLToBB(text);
  text = santizeTextForTextarea(text);
  //text = $('<div>').html(text).text();
  $(textArea).val(text);
  $(popupContainer).append(textArea);
  var dialog = $(popupContainer).dialog({
    height: 400,
    width: 450,
    modal: false,
    buttons: {
      'Edit': function() {
        editPost($(button).data('post-id'), $(button).data('character-id'), $(textArea).val(), dialog);
      },
      Cancel: function() {
        dialog.dialog( "close" );
      }
    },
    close: function() {
    }
  });
}

function deletePost(postID) {
  $.ajax({
    url: API_URL + 'POSTS/PID/' + postID + '?apiKey=' + LOCAL_API_KEY,
    method: 'DELETE',
    dataType: 'json',
    statusCode: {
      200: function() {
        getPostsByPage();
      }
    }
  });
}

function renderDeletePostWindow(button) {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'Sure about that?');
  $(popupContainer).append('Delete post #' + $(button).data('postId') + '? ' + generateNPCName() + ' hasn\'t read it yet.');
  var dialog = $(popupContainer).dialog({
    height: 200,
    width: 350,
    modal: false,
    buttons: {
      'Delete': function() {
        deletePost($(button).data('postId'));
        dialog.dialog('close');
      },
      Cancel: function() {
        dialog.dialog('close');
      }
    },
    close: function() {
    }
  });
}

function addPagination() {
  $('.questNavigation').empty();
  var buttonStartIndex = currentQuestData.page - 2;
  if (buttonStartIndex >= currentQuestData.pageCount) {
    buttonStartIndex = currentQuestData.page - 4;
  }
  if (buttonStartIndex < 1) {
    buttonStartIndex = 1;
  }
  if (currentQuestData.pageCount - buttonStartIndex < 4 && currentQuestData.pageCount - 4 > 1) {
    buttonStartIndex = currentQuestData.pageCount - 4;
  }
  function addClickEvent(btn) {
    $(btn).click(function(event) {
      var page;
      if ($(btn).text() == '<') {
        currentQuestData.page--;
      } else if ($(btn).text() == '>') {
        currentQuestData.page++;
      }
      else if ($(btn).text() == '<<') {
        currentQuestData.page = 1;
      } else if ($(btn).text() == '>>') {
        currentQuestData.page  = currentQuestData.pageCount;
      } else {
        currentQuestData.page  = parseInt($(btn).text());
      }
      getPostsByPage();
    });
  }
  var btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('<<');
  if (currentQuestData.page > 1) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
  btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('<');
  if (currentQuestData.page > 1) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
  for (var i=buttonStartIndex; i < buttonStartIndex+5 && i<currentQuestData.pageCount+1; i++) {
    btn = document.createElement('button');
    $(btn).addClass('questNavButton');
    $(btn).text(i);
    if (i==currentQuestData.page) {
      $(btn).prop('disabled', true);
      $(btn).css('text-decoration', 'underline');
      $(btn).addClass('disabled');
    }
    addClickEvent(btn);
    $('.questNavigation').append(btn);
  }
  btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('>');
  if (currentQuestData.page < currentQuestData.pageCount) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
  btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('>>');
  if (currentQuestData.page < currentQuestData.pageCount) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
}

function addEditedHover(parent, text) {
  var hoverSpan = document.createElement('span');
  $(hoverSpan).text('*');
  $(hoverSpan).addClass('editedAsterisk');
  $(parent).find('.editedAsterisk').remove();
  var remainder = $(parent).html().split('Posted')[1];
  $(parent).text('Posted');
  $(parent).append(hoverSpan);
  $(parent).append(remainder);
  $(hoverSpan).qtip({
    content: {
      text: 'Edited ' + text
    },
    position: {
      my: 'bottom left',
      at: 'top right',
      target: $(hoverSpan)
    },
    style: {
      classes: 'qtip-dark qtip-shadow qtip-rounded'
    }
  });
}

function renderPostBubble(postObject, index, prepend) {
  var div = document.createElement('div');
  $(div).attr('id', 'post_' + postObject.postID);
  var header = document.createElement('header');
  var span = document.createElement('span');
  $(span).addClass('floatLeft');
  $(span).text('#' + postObject.postID);
  $(span).append('&nbsp;&nbsp;');
  $(span).append('Posted');
  if (postObject.edited != 'never') {
    addEditedHover(span, formatDate(parseInt(postObject.edited)));
  }
  $(span).append('&nbsp;');
  var date = formatDate(parseInt(postObject.date));
  $(span).append(date + '&nbsp;by&nbsp;');
  var a = document.createElement('a');
  $(a).addClass('characterNameLink');
  $(a).text(postObject.character);
	if (postObject.characterID == 'GM') {
		$(a).append(' - GM');
	}
  $(span).append(a);
  $(header).append(span);
  if (postObject.editable == 'true') {
    span = document.createElement('span');
    $(span).addClass('floatRight');
    var img = document.createElement('img');
    $(img).addClass('pointer editPostBtn');
    $(img).attr('alt', 'edit post');
    $(img).attr('title', 'edit post');
    $(img).attr('src', 'img/icon.edit_dark.gif');
    $(img).attr('data-post-id', postObject.postID);
    $(img).attr('data-character-id', postObject.characterID);
    $(img).click(function(event) {
      event.preventDefault();
      event.stopPropagation();
      renderEditPostWindow(this);
    });
    $(span).append(img);
    $(span).append('&nbsp;');
    img = document.createElement('img');
    $(img).attr('alt', 'delete post');
    $(img).attr('title', 'delete post');
    $(img).addClass('pointer deletePostBtn');
    $(img).attr('src', 'img/icon.delete_dark.gif');
    $(img).attr('data-post-id', postObject.postID);
    $(img).click(function(event) {
      event.preventDefault();
      event.stopPropagation();
      renderDeletePostWindow(this);
    });
    $(span).append(img);
    $(header).append(span);
  }
  $(header).addClass('postHeader');
  $(div).append(header);
  var section = document.createElement('section');
  $(section).addClass('postBody');
  if (index % 2 == 0) {
    $(section).addClass('even');
  } else {
    $(section).addClass('odd');
  }

  $(section).html(postObject.text);
  $(div).append(section);
  if (prepend) {
    $('#questContent').prepend(div);
  } else {
    $('#questContent').append(div);
  }
}

function resetPostsView() {
  $('.postBody').removeClass('even odd');
  $('.postBody').each(function(index, item) {
    if (index % 2 == 0) {
      $(item).addClass('even');
    } else {
      $(item).addClass('odd');
    }
  });
}

function getPostsByPage() {
  $('#questContent').fadeOut('fast', function() {
    $('#questContent').empty();
    var service = API_URL + 'posts/QID/' + currentQuestData.questID;
    if (currentQuestData.limit) {
      service += '/limit/' + currentQuestData.limit;
    } else {
      service += '/limit/50';
    }
    if (currentQuestData.page) {
      service += '/page/' + currentQuestData.page;
    } else {
      service += '/page/1';
    }
    if (currentQuestData.sort) {
      service += '/order/' + currentQuestData.sort;
    } else {
      service += '/order/DESC';
    }
    $.ajax({
      url: service,
      method: 'GET',
      data: {apiKey: LOCAL_API_KEY},
      dataType: 'json',
      statusCode: {
        200: function(response) {
          for (var i=0; i < response.posts.length; i++) {
            renderPostBubble(response.posts[i], i);
          }
          currentQuestData.page = parseInt(response.currentPage);
          currentQuestData.limit = parseInt(response.delimiter);
          currentQuestData.pageCount = parseInt(response.pageCount);
          currentQuestData.sort = response.order;
          addPagination();
          $('#questContent').fadeIn('fast');
        }, 404: function(response) {
          $('#questContent').text('No posts yet.');
          $('#questContent').fadeIn('fast');
        }
      }
    });
  });
}