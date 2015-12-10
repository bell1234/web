console.log("heyhey")
// Export selectors engine
var $$ = Dom7;
var currentUser = null;
var justReadIndex = null;
var isCommentToPost = true;
var targetPostId = null;
var targetCommentId = null;
var targetUserId = null;
var targetUsername = null; 
var ut = null;
var device_token = null;
var commentList = [];
var userPostList = [];
var currentIndex = 1; 
var userCurrentIndex = 1;
var userLoading = false;
var guestSwiper = null;
var workingList = []; 
var notificationList = []; 
var postList = []; 
// var Post = Parse.Object.extend("posts"); 

var ptrContent = $$('.pull-to-refresh-content');
ptrContent.on('refresh', function (e) {
  setTimeout(function () {
    currentIndex = 1
    updateListDataFromServer()
      // When loading done, we need to reset it
    
  }, 200);
});

// Initialize your app
var myApp = new Framework7({
    onPageBack: function (app) {
      // To make sure the keyboard is hidden after swipeback
      try {
        document.getElementById("reply-input").blur()
      } catch (err) {
        
      }
    }, 
    onPageAfterAnimation: function (app, page) {
      // updating current workingList 
      if (page.name == "index") {
        workingList = postList
        updateListHTMLFromListData(workingList, 'post-list')
      } 
      if (page.name == "inbox") {
        workingList = notificationList
      } 
      if (page.name == "user") {
        workingList = userPostList
        updateListHTMLFromListData(workingList, 'user-post-list')
      }       
      if (page.name == "post") {
        $("#reply-input").autoGrow();
        jQuery("#reply-toolbar").css("height", "63px")
        jQuery("#reply-toolbar").css("padding-bottom", "43px")
        try {       
          Keyboard.hideFormAccessoryBar(true);
        } catch (err) {

        }
        // Autogrow formatting the input box
        $("#reply-input").keydown(function(){
          // at most 3 lines
          if (jQuery("#reply-toolbar").height() != jQuery(".autogrow-textarea-mirror").height() && 
            jQuery(".autogrow-textarea-mirror").height() <= 70 && 
            jQuery(".autogrow-textarea-mirror").height() > 17) 
          {            
              jQuery("#reply-toolbar").css("height", jQuery(".autogrow-textarea-mirror").height() + 40)
              jQuery("#reply-toolbar").css("padding-bottom", jQuery(".autogrow-textarea-mirror").height() + 20)                 
          }
        })
      }
    }, 
    tapHold: true, //enable tap hold events        
    modalTitle: '没六儿'
});

// Loading flag
var homeLoading = false; 
var userPageLoading = false; 
// Add view
var mainView = myApp.addView('.view-main', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true, 
    domCache: true, 

});


try {
  if (cordova) 
    // return; 
  loginStatusUpdate(); 
} catch(err) {
  loginStatusUpdate(); 
}

document.addEventListener("deviceready", function () {

  loginStatusUpdate();
  

    // cordova.plugins.Keyboard.hideKeyboardAccessoryBar(false);
    window.addEventListener("statusTap", function () {
      // myApp.alert("hello")
        // scrollUp(); // Now, let's implement this, shall we? :D
    });
    var push = PushNotification.init({
        android: {
            senderID: "12345679"
        },
        ios: {
            alert: "true",
            badge: true,
            sound: 'false', 
            clearBadge: true
        },
        windows: {}
    });
    push.on('registration', function(data) {
        device_token = data.registrationId;
        // cordova.plugins.clipboard.copy(data.registrationId);
        // myApp.alert(data.registrationId);
    });
    push.on('notification', function(data) {
        // myApp.alert(data.title);
        if (data.additionalData.type == "1") {
          // inbox
          inboxPopupOpen ()

        } else if (data.additionalData.type == "2") {
          // home

        }
        // myApp.alert(data.message);
        // myApp.alert(data.additionalData);
        console.log(data.message);
        console.log(data.title);
        console.log(data.count);
        console.log(data.sound);
        console.log(data.image);
        console.log(data.additionalData);
        push.finish(function() {
            console.log("processing of push data is finished");
        });        
    });    
    push.on('error', function(e) {
        console.log("push error");
        myApp.alert("push error"); 
    });    
}, false);




// $(function (){
//   $.

// });

function forgetPassword() {
  function validateEmail(email) {
      var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
      return re.test(email);
  }
  myApp.prompt("请输入您的邮箱，我们帮你找回密码", function(value){

    if (!validateEmail(value)) {
      myApp.alert("您的邮箱不符合规范，请重新输入:)"); 
      return; 
    }
    var ajaxUrl = "http://meiliuer.com/api/forgetPassword?email=" + value; 
    $.ajax({
      url: ajaxUrl, 
      type: 'GET', 
      dataType: 'jsonp', 
      success: function(results) {
        myApp.alert("请查收您的邮件")
      }
    }); 
  }); 

}

function replyToComment(commentId, postId, userId, userName) {
  console.log(postId)
  var userQuery = ""
  var userPrefix = ""
  console.log(userId)
  if (userId != "") {
    userQuery = "&receiver=" + userId
    // userPrefix = "@" + userName + " "
  }
  var urlPost = "http://meiliuer.com/api/createReply?token="+localStorage.usertoken+"&comment_id="+commentId+"&description="+ encodeURIComponent(document.getElementById("reply-input").value)+ userQuery 
  $.ajax({
    url:urlPost, 
    type: 'GET', 
    dataType:"jsonp",
    success:function(json){
      postPage(postId)
      document.getElementById("reply-input").value = ""
      // Autogrow formatting input after posting
      $("#reply-input").height(17)
      jQuery("#reply-toolbar").css("height", "63px")
      jQuery("#reply-toolbar").css("padding-bottom", "43px")      
    }
  });

  for (var i = 0; i < commentList.length; i++) {
    if (commentList[i].comment_id == commentId) {
      var elem = {
        avatar : "", 
        create_time: 0, 
        description: document.getElementById("reply-input").value, 
        reply_id: 0, 
        user_id: 0, 
        username: localStorage.usernickname
      }
      commentList[i].replies.push(elem)
      postPageUI(commentList, postId)      
    }
  }  
  function replyCallback () {
    postPage(postId)
  }
}



function commentToPost(postId) {
  var urlPost = "http://meiliuer.com/api/createComment?token="+localStorage.usertoken+"&post_id="+postId+"&description="+document.getElementById("reply-input").value + '&callback=1'
  $.ajax({
    url:urlPost, 
    dataType:"jsonp",
    type: 'GET', 
    success:function(json){
        postPage(postId)
        document.getElementById("reply-input").value = ""
        // Autogrow formatting input after posting
        $("#reply-input").height(17)
        jQuery("#reply-toolbar").css("height", "63px")
        jQuery("#reply-toolbar").css("padding-bottom", "43px")
    }});

  // putting fake comment placeholder to make it more responsive 
  var elem = {
    avatar : "", 
    comment_id: 0, 
    create_time: 0, 
    description: document.getElementById("reply-input").value, 
    down: 0, 
    replies: [], 
    up: 0, 
    user_id: 0, 
    username: localStorage.usernickname
  }
  commentList.push(elem)
  postPageUI(commentList, postId)

}

function getReplyInputBoxHTML () {
  return "" +
'              <div class="list-block media-list" id="comment-list"> ' +
'                <ul> ' + 
'                </ul> ' +
'              </div> ' +
'            </div>  ' + 
'        <div class="toolbar" id="reply-toolbar" > '+
'          <div class="toolbar-inner bg-gray" data-page="post"> '+
'            <div class="list-block" style="background:transparent;width:100%; "  > '+
'              <ul style="background:transparent;"> '+
'                <li> '+
'                 <div class="item-content" style="padding-left: 0px"> ' +

'                   <div class="item-inner" style="padding-right: 0px"> ' +
'                     <div class="item-media"><i class="fa fa-commenting-o comment-icon"></i></div> ' + 
'                     <div class="item-input"> ' +
'                         <textarea type="text" id="reply-input"  name="reply" placeholder="说点啥。。。"></textarea> ' +
'                     </div> ' +
'                   </div> ' +
'                 </div> ' +
'                </li> '+
'              </ul> ' +
'            </div> ' +    
'          </div> ' 
'        </div> '
}




function openPostPageByElem (elem, index, listName) {
  myApp.closeModal(".popup-inbox")
  if (localStorage.usertoken == null || localStorage.usertoken == "null") {
    myApp.alert("请先登录后再评论~")
    return
  }
  


  mainView.router.loadPage({
    "pageName": "post", 
  });
  postPage(elem.post_id)
  targetPostId = elem.post_id
  var voteUpCSS = ""
  var voteDownCSS = ""
  if (elem.self_vote == 1) {
    voteUpCSS = " voted-block "
  } else if (elem.self_vote == 2) {
    voteDownCSS = " voted-block "
  }  
  var outPutHTML = "<div class='page-content'><div id='post-page-title-cell' class='list-block'><ul>"
  outPutHTML += getListHTMLFromElem(elem, index, listName); 
  outPutHTML += "</ul></div> "; 
  outPutHTML += getReplyInputBoxHTML ();
  document.getElementById("post-page").innerHTML = outPutHTML

  setTimeout(function () {
    setIsCommentToPostToTrue()
    document.getElementById("reply-input").onkeypress = function (e) {
      if ( e.keyCode == 13 && document.getElementById("reply-input").value != ""){
        sendMacro(targetPostId, targetCommentId)
      }
    }      
  }, 400)
}

function openPostPageByIndex (index) {
  if (localStorage.usertoken == null || localStorage.usertoken == "null") {
    myApp.alert("请先登录后再评论~")
    return
  }
  var listName = 'post-page'
  mainView.router.loadPage({
    "pageName": "post", 
  });
  postPage(workingList[index].post_id)
  targetPostId = workingList[index].post_id
  openPostPageByElem(workingList[index], index, listName)
}

function setIsCommentToPostToTrue() {
  document.getElementById("reply-input").placeholder ="回复："
  isCommentToPost = true
}
function setCommentToPostState () {
  setIsCommentToPostToTrue()
  document.getElementById("reply-toolbar").style.display = "block"
  document.getElementById("reply-input").placeholder ="回复："
  try {
    Keyboard.hideFormAccessoryBar(true);
    Keyboard.disableScrollingInShrinkView(true);
  } catch (error) {

  }
  setTimeout(function () {
    document.getElementById("reply-input").focus()
  }, 100)
}



function sendMacro (postId, commentId) {
  if (isCommentToPost) {
    commentToPost(postId)
  } else {
    replyToComment(targetCommentId, targetPostId, targetUserId, targetUsername)
  }
  document.getElementById("reply-input").blur()
}


function setReplyToCommentState (commentId, postId, index, userId, userName) {


  setTimeout(function () {

    if (hasClickedGetUserPost) {
      hasClickedGetUserPost = false;
      return; 
    }
    if (userId != null) {
      document.getElementById("reply-input").placeholder = "@"+userName
    } else {
      document.getElementById("reply-input").placeholder = "@"+commentList[index].username
    }
    try { 
      Keyboard.hideFormAccessoryBar(true);
      Keyboard.disableScrollingInShrinkView(true);
    } catch (err) {

    }
    setTimeout(function() {
      document.getElementById("reply-input").focus()
    }, 200)

    
    isCommentToPost = false
    targetPostId = postId
    targetCommentId = commentId
    targetUserId = userId || ""
    targetUsername = userName || ""
  }, 10)



}




function voteComment(index, type)  {
  if (localStorage.usertoken == null || localStorage.usertoken == "null") {
    myApp.alert("请先登录后再投票~")
    return
  }  
  var postUrl = ""


  if (commentList[index].self_vote == 1 && type == 1) {
    $("#comment-voting-down-arrow-"+index).removeClass("voted-block")
    $("#comment-voting-up-arrow-"+index).removeClass("voted-block")
    commentList[index].up--
    commentList[index].self_vote = 0
    postUrl = "http://meiliuer.com/api/VoteCommentCancel?token="+ localStorage.usertoken +"&type="+type+"&comment_id="+commentList[index].comment_id
  } else if (commentList[index].self_vote == 2 && type == 2) {
    commentList[index].down--
    $("#comment-voting-down-arrow-"+index).removeClass("voted-block")
    $("#comment-voting-up-arrow-"+index).removeClass("voted-block")
    commentList[index].self_vote = 0
    postUrl = "http://meiliuer.com/api/VoteCommentCancel?token="+ localStorage.usertoken +"&type="+type+"&comment_id="+commentList[index].comment_id

  } else if (commentList[index].self_vote == 0) {
    if (type == 1) { //up
      $("#comment-voting-down-arrow-"+index).removeClass("voted-block")
      $("#comment-voting-up-arrow-"+index).addClass("voted-block")
      commentList[index].up++  
    } else { //down
      $("#comment-voting-down-arrow-"+index).addClass("voted-block")
      $("#comment-voting-up-arrow-"+index).removeClass("voted-block")      
      commentList[index].down++ 
    }    
    postUrl = "http://meiliuer.com/api/voteComment?token="+ localStorage.usertoken +"&type="+type+"&comment_id="+commentList[index].comment_id
  } else if (commentList[index].self_vote == 2 && type == 1) {
      commentList[index].up++  
      commentList[index].down--  
      $("#comment-voting-down-arrow-"+index).removeClass("voted-block")
      $("#comment-voting-up-arrow-"+index).addClass("voted-block")    
      postUrl = "http://meiliuer.com/api/voteComment?token="+ localStorage.usertoken +"&type="+type+"&comment_id="+commentList[index].comment_id
  } else if (commentList[index].self_vote == 1 && type == 2) {
      commentList[index].up--  
      commentList[index].down++
      $("#comment-voting-down-arrow-"+index).addClass("voted-block")
      $("#comment-voting-up-arrow-"+index).removeClass("voted-block") 
      postUrl = "http://meiliuer.com/api/voteComment?token="+ localStorage.usertoken +"&type="+type+"&comment_id="+commentList[index].comment_id
  }
  document.getElementById("comment-voting-number-"+index).innerHTML = commentList[index].up - commentList[index].down

  $.ajax({
    url:postUrl,
    dataType:"jsonp",
    jsonpCallback:"haha",
    success:function(results){
      if (type == 1) {
        console.log("voted up")
      } else {
        console.log("voted down")
      }
    }
  });    
}

function postPageUI(results, postId) {
  var listName = "comment-"
      if (results.length == 0) {
        document.getElementById('comment-list').innerHTML = "<div class='post-page-no-comment-placeholder'>暂时没有评论</div>"  
      } else {
        var liHTML = "<ul>"
        for (var i = 0; i < results.length; i++) {
            var voteUpCSS = ""
            var voteDownCSS = ""
            if (results[i].self_vote == 1) {
              voteUpCSS = " voted-block "
            } else if (results[i].self_vote == 2) {
              voteDownCSS = " color-meiliuer-yellow "
            }          
           liHTML += '<li> ' +
            '  <div href="#" class="post-comment item-content"  > ' +
            '    <div class="item-media"><img src="http://meiliuer.com'+results[i].avatar+'" width="44" style="border-radius:44px;"></div> ' +
            '    <div class="item-inner" onclick = "setReplyToCommentState('+results[i].comment_id+', '+postId+', '+i+')" > ' +
            '      <div class="item-title-row"> ' +
            '        <div class="item-title name-link" onclick="getUserPost('+results[i].user_id+')">'+ results[i].username +'</div> ' +
            '      </div> ' +
            '      <div class="item-subtitle" >'+ results[i].description +'</div> ' +
            '    </div> ' +
            '  </div> ' +
         '     <div class = "'+listName+'post-voting-area"> ' + 
         '       <div onclick="voteComment('+i+',1)" id="'+ listName +'voting-up-arrow-'+i+'" class="vote-up-block '+voteUpCSS+' ">' + 
         '        <i class="fa fa-caret-up fa-2x "  ></i> ' + 
         '        <div class="post-voting-text" id="'+ listName +'voting-number-'+i+'">'+ (results[i].up - results[i].down) +'</div> ' + 
         '       </div>' +
         // '       <div onclick="voteDown('+i+')" class="vote-down-block '+voteDownCSS+' " id="'+ listName +'voting-down-arrow-'+i+'">' + 
         // '         <i class="fa fa-caret-down fa-2x  "  ></i> ' + 
         // '       </div> ' + 
         '     </div>'            
                 // '     <div class = "post-voting-area-comment"> ' + 
                 // '       <i class="fa fa-caret-up '+ voteUpCSS +'" id="voting-up-arrow-comment-'+i+'" ></i> ' + 
                 // '       <div class="post-voting-text-comment '+ voteUpCSS +'" id="voting-number-comment-'+i+'">'+ results[i].up +'</div> ' + 
                 // '       <i class="fa fa-caret-down '+ voteDownCSS +'" id="voting-down-arrow-comment-'+i+'" ></i> ' + 
                 // '     </div> ' + 
                 // '     <div class="vote-up-sensitive" onclick="voteComment('+i+',1)"></div>' + 
                 // '     <div class="vote-down-sensitive" onclick="voteComment('+i+',2)"></div>' +             
            '</li> ' 
            for (var j = 0; j < results[i].replies.length; j++) {
             liHTML += ''+
              '<li> ' +
              '  <div href="#" class=" item-content post-reply"  > ' +
              // '    <div class="item-media"><img src="http://meiliuer.com'+results[i].replies[j].avatar+'" width="44" style="border-radius:44px;"></div> ' +
              '    <div class="item-inner" onclick="setReplyToCommentState('+results[i].comment_id+', '+postId+', '+i+', '+ results[i].replies[j].user_id +', \''+ results[i].replies[j].username +'\')" > ' +
              '      <div class="item-title-row"> ' +
              '        <div class="item-title " ><span class="name-link" onclick="getUserPost('+results[i].replies[j].user_id+')">'+ results[i].replies[j].username + "</span>" +(results[i].replies[j].receiver_username ? (' @ ' + '<span class="name-link" onclick="getUserPost('+results[i].replies[j].receiver_id+')">' + results[i].replies[j].receiver_username)  + "</span>": "") + ': </div> ' +
              '      </div> ' +
              '      <div class="item-subtitle" >'+ results[i].replies[j].description +'</div><div class="reply-placeholder"></div> ' +
              '    </div> ' +
              '  </div> ' +              
              '</li> '               
            }
        }
        document.getElementById('comment-list').innerHTML = liHTML + "</ul>"
      }
}

function postPage(postId) {
  $.ajax({
    url:"http://meiliuer.com/api/getallcomments?post_id="+postId+"&token="+localStorage.usertoken, 
    dataType:"jsonp",
    type: 'GET',
    success:function(results){
      commentList = results
      postPageUI(commentList, postId)
    }
  });  
}


function getListHTMLFromElem (elem, index, listName) {
  var index = index || 0; 
  var listName = listName ? (listName+"-") : ""
  var voteUpCSS = ""
  var voteDownCSS = ""
  if (elem.self_vote == 1) {
    voteUpCSS = " voted-block "
  } else if (elem.self_vote == 2) {
    voteDownCSS = " voted-block "
  }
  var replyHTML = ""

  if (mainView.url == "#post") {
    replyHTML = '<div class="col-33 item-link" style = "text-align:center;" onclick = "setCommentToPostState ()">回复</div>'
  } else {
    replyHTML = '<div class="col-33 item-link" style = "text-align:center;" onclick = "openPostPageByIndex('+index+')">评论('+elem.comments+')</div>'
  }
  return  '   <li class="post-unit item-link" id="'+ listName +'post-unit-id-'+index+'"  > ' + 
          '    <img onclick = "openElem('+index+')"  class =  "post-image" onerror="this.onerror = null; this.src= \'img/half_trans_solid.png\' " src="http://meiliuer.com'+ elem.thumb_pic +'">' +   
          '     <div class="post-unit-textblock" > ' + 
          '       <a class = "post-title item-link" onclick = "openElem('+index+')">' + elem.title + '</a> ' + 
          '     </div> ' + 
          '       <img class = "post-author-image"  src="http://meiliuer.com'+ elem.avatar +'">'+           
          '       <div class = "row comment-share-bar">'+
          '         <div class="col-33 item-link" onclick="getUserPost('+elem.user_id+')" >' +
          '           <span>' +
                        elem.username +   
          '           </span>'+
          '         </div>'+  
                      replyHTML +           
          '         <div class="col-33 item-link" style = "text-align:center;" onclick="shareByCopying('+index+',\''+listName+'\')">分享</div>'+
          '       </div> '     +           
          '       <div class = "post-voting-area"> ' + 
          '         <div onclick="voteUp('+index+')" id="'+ listName +'voting-up-arrow-'+index+'" class="vote-up-block '+voteUpCSS+' ">' + 
          '          <i class="fa fa-caret-up fa-2x "  ></i> ' + 
          '          <div class="post-voting-text" id="'+ listName +'voting-number-'+index+'">'+ (elem.up - elem.down) +'</div> ' + 
          '         </div>' +
          '         <div onclick="voteDown('+index+')" class="vote-down-block '+voteDownCSS+' " id="'+ listName +'voting-down-arrow-'+index+'">' + 
          '           <i class="fa fa-caret-down fa-2x  "  ></i> ' + 
          '         </div> ' + 
          '       </div>'
          '   </li> '             
}

function shareByCopying (index, list) {
  myApp.alert("已经复制到你的剪贴板~");
  var text = "我刚才在没六儿看到的：【" + workingList[index].title + "】 http://meiliuer.com/posts/" + workingList[index].post_id 
  try {
    cordova.plugins.clipboard.copy(text);
  } catch (err) {
    myApp.alert(text)    
  }
}

function animateEffect(selector, effect) {
  $(selector).addClass('animated ' + effect);
  setTimeout(function(){
    $(selector).removeClass('animated ' + effect);
  }, 1000)  
}


// if already up
// -1, no show; 
// if nothing 
// +1, show down
// if already down
// +2, show down

function voteUp(index) {
  if (localStorage.usertoken == null || localStorage.usertoken == "null") {
    myApp.alert("请先登录后再投票~")
    return
  }  
  var postGetUrl = ''
  var ut = localStorage.usertoken
  var postId = workingList[index].post_id; 

  if (workingList[index].self_vote == 1) { // cancel
    workingList[index].up-- 
    var number = workingList[index].up - workingList[index].down
    // var newVote = number - 1
    votingCSS(index, number, 0)
    workingList[index].self_vote = 0
    postGetUrl = 'http://meiliuer.com/api/VoteCancel?token=' + ut + '&post_id=' + postId + '&type=1' 

  } else if (workingList[index].self_vote == 2) { // up
    workingList[index].up++
    workingList[index].down--
    var number = workingList[index].up - workingList[index].down
    votingCSS(index, number, 1)
    workingList[index].self_vote = 1
    postGetUrl = 'http://meiliuer.com/api/vote?token='+ut+'&post_id='+postId+'&type=1'
  } else { // == 0 // up 
    workingList[index].up++
    var number = workingList[index].up - workingList[index].down
    votingCSS(index, number, 1)
    workingList[index].self_vote = 1
    postGetUrl = 'http://meiliuer.com/api/vote?token='+ut+'&post_id='+postId+'&type=1'
  }
  responseObject = $.ajax({
    url: postGetUrl,
    type: "GET",
    dataType: "jsonp",
    success: function(results) {
      console.log(results)
    }
  }); 
}

// if already down
// +1, no show; 
// if nothing 
// -1, show down
// if already up
// -2, show down

function votingCSS (index, newVote, newState) {
  if (newState == 0) {
    $("#user-post-list-voting-up-arrow-"+index).removeClass("voted-block")
    $("#user-post-list-voting-down-arrow-"+index).removeClass("voted-block")
    $("#post-page-voting-up-arrow-"+index).removeClass("voted-block")
    $("#post-page-voting-down-arrow-"+index).removeClass("voted-block")
    $("#post-list-voting-up-arrow-"+index).removeClass("voted-block")
    $("#post-list-voting-down-arrow-"+index).removeClass("voted-block")
    $("#post-list-voting-number-"+index).html(newVote)
    $("#user-post-list-voting-number-"+index).html(newVote)
    $("#post-page-voting-number-"+index).html(newVote)      
  } else if (newState == 1) {
    $("#user-post-list-voting-up-arrow-"+index).addClass("voted-block")
    $("#user-post-list-voting-down-arrow-"+index).removeClass("voted-block")
    $("#post-page-voting-up-arrow-"+index).addClass("voted-block")
    $("#post-page-voting-down-arrow-"+index).removeClass("voted-block")
    $("#post-list-voting-up-arrow-"+index).addClass("voted-block")
    $("#post-list-voting-down-arrow-"+index).removeClass("voted-block")
    $("#post-list-voting-number-"+index).html(newVote)
    $("#user-post-list-voting-number-"+index).html(newVote)
    $("#post-page-voting-number-"+index).html(newVote)      
  } else {
    $("#user-post-list-voting-up-arrow-"+index).removeClass("voted-block")
    $("#user-post-list-voting-down-arrow-"+index).addClass("voted-block")
    $("#post-page-voting-up-arrow-"+index).removeClass("voted-block")
    $("#post-page-voting-down-arrow-"+index).addClass("voted-block")
    $("#post-list-voting-up-arrow-"+index).removeClass("voted-block")
    $("#post-list-voting-down-arrow-"+index).addClass("voted-block")
    $("#post-list-voting-number-"+index).html(newVote)
    $("#user-post-list-voting-number-"+index).html(newVote)
    $("#post-page-voting-number-"+index).html(newVote)        
  }
}


function voteDown(index) {
  if (localStorage.usertoken == null || localStorage.usertoken == "null") {
    myApp.alert("请先登录后再投票~")
    return
  }   
  var postGetUrl = ''
  var postId = workingList[index].post_id; 
  var ut = localStorage.usertoken   

  if (workingList[index].self_vote == 2) { // cancel
    workingList[index].down--
    var number = workingList[index].up - workingList[index].down
    votingCSS(index, number, 0)
    workingList[index].self_vote = 0
    postGetUrl = 'http://meiliuer.com/api/VoteCancel?token=' + ut + '&post_id=' + postId + '&type=2'    
  } else if (workingList[index].self_vote == 1) { // up
    workingList[index].up--
    workingList[index].down++
    var number = workingList[index].up - workingList[index].down
    votingCSS(index, number, 2)
    workingList[index].self_vote = 2
    postGetUrl = 'http://meiliuer.com/api/vote?token='+ut+'&post_id='+postId+'&type=2'
  } else { // == 0 // up 
    workingList[index].down++
    var number = workingList[index].up - workingList[index].down
    votingCSS(index, number, 2)
    workingList[index].self_vote = 2
    postGetUrl = 'http://meiliuer.com/api/vote?token='+ut+'&post_id='+postId+'&type=2'
  }
  responseObject = $.ajax({
      url: postGetUrl,
      type: "GET",
      dataType: "jsonp",
      success: function(results) {
        console.log(results)
      }
  });
}

function updateListHTMLFromListData(results, listName) {
    var postListHTML = "<ul>"
    for (var i in results) {
        var elem = results[i]
        postListHTML += getListHTMLFromElem(elem, i, listName)
    }
    document.getElementById(listName).innerHTML =  postListHTML + "</ul>"  
}







function updateListDataFromServer() {
  var postGetUrl = 'http://meiliuer.com/api/index?page='+currentIndex+'&token='+ localStorage.usertoken + '&device_token=' + device_token  + 'device_type=iOS'
  responseObject = $.ajax({
      url: postGetUrl,
      type: "GET",
      dataType: "jsonp",
      success: function (json) {
        postList = json
        workingList = postList
        updateListHTMLFromListData(json, 'post-list')
        myApp.pullToRefreshDone();
      }
  });
}



function openElem(index) {
    $(".post-unit").css("border-left", "none")
    $("#post-list-post-unit-id-"+index).css("border-left", "5px solid #fde125")
    if (workingList[index].url.length != 0) {
      openUrl(workingList[index].url, true);
    } else {
      openUrl("http://meiliuer.com/"+workingList[index].thumb_pic, true);
    }
}


function openUrl(url, readerMode) {
  try {
    SafariViewController.isAvailable(function (available) {
      if (available) {
        SafariViewController.show({
              'url': url,
              'enterReaderModeIfAvailable': readerMode // default false
            },
            function(msg) {
              console.log("OK: " + msg);
            },
            function(msg) {
              alert("KO: " + msg);
            })
      } else {
        // potentially powered by InAppBrowser because that (currently) clobbers window.open
        window.open(url, '_blank', 'location=yes');
      }
    });    
  } catch (err) {
    window.location = url
    // window.open(url, '_blank', 'location=yes');
  }

}
function configPageOpen() {
  myApp.popup(".popup-config")
  if (localStorage.usertoken != "null") {
    document.getElementById("setting-nickname-input").value = localStorage.usernickname
  } else {
    document.getElementById("setting-nickname-input").value = ""
  }
}

function selfPage() {
  getUserPost(localStorage.userId, 1)

}

function configPopupClose () {
  myApp.closeModal(".popup-config")
}

var unreadNumber = 0;
function getUnreadNum (){
  var ajaxUrl = "http://meiliuer.com/api/UnreadNotifications?token=" + localStorage.usertoken;
  $.ajax({
    url: ajaxUrl, 
    dataType: 'jsonp', 
    type: 'GET', 
    success: function (json) {
      console.log(json)
      updateUnreadBadge(json.unread)
    }
  }); 
}

function updateUnreadBadge(unreadNumber) {
  if (unreadNumber != 0 || unreadNumber != "0") {
    document.getElementById("unread-badge").style.display = "inline-block";
    document.getElementById("unread-badge").innerHTML = unreadNumber
  } else {
    document.getElementById("unread-badge").style.display = "none";
  }  
}

function inboxPopupOpen() {
  updateUnreadBadge(0)  
  mainView.router.loadPage({
    pageName: "inbox"
  })
  getNotificationList()
}


function inboxPopupClose () {
  mainView.router.back()
}

function getSinglePage (postId) {
  updateNotificationListUI()
  var ajaxUrl = "http://meiliuer.com/api/view?post_id=" + postId
  $.ajax({
    url: ajaxUrl, 
    type: 'GET', 
    dataType: "jsonp", 
    success: function(singlePageJson) {
      console.log(singlePageJson)  
      openPostPageByElem (singlePageJson, 0, 'post-page')   

    }
  })
}



function notificationListCellUI (elem, index) {
          var typeId = elem.type_id
          var isRead = elem.read
          var unreadStyleSheet = ""
          var prompt = ""
          if (isRead == 0) {
            unreadStyleSheet = " style='background-color: #eee;' "
          }
          if (typeId == 2) { 
            // comment to post
            prompt = " 评论了你的内容："
          } else if (typeId == 3) {
            // reply to comment
            prompt = " 回复了你的评论："
          }
          return '   <li> ' + 
          '     <a href="#" class=" item-content" '+ unreadStyleSheet +' > ' + 
          '       <div class="item-media" onclick="getUserPost('+elem.noti_user_id+')" ><img src="http://meiliuer.com'+elem.noti_avatar+'" width="44" style="border-radius:44px;"></div> ' +              
          '       <div class="item-inner"> ' + 
          '         <div class="item-title-row"> ' + 
          '           <div class="item-title" ><span onclick="getUserPost('+elem.noti_user_id+')" class="name-link">'+elem.noti_username+'</span> ' + prompt + '</div> ' + 
          // '           <div class="item-after">17:14</div> ' + 
          '         </div> ' + 
          // '         <div class="item-subtitle">New messages from John Doe</div> ' + 
          '         <div class="item-text" onclick="openPostPageByIndex ('+index+')">'+elem.post_name+'</div> ' + 
          '       </div> ' + 
          '     </a> ' + 
          '   </li> ' 
}

function updateNotificationListUI () {
        var notificationHTML = "<ul>"
        for (var i = 0; i < notificationList.length; i++) {
          notificationHTML += notificationListCellUI(notificationList[i], i)
        }
        notificationHTML += "</ul>"
        document.getElementById("notification-list").innerHTML = notificationHTML  
}
function markAllAsRead() {
        var ajaxUrl = "http://meiliuer.com/api/ReadNotifications?token=" + localStorage.usertoken;
        $.ajax({
          url: ajaxUrl, 
          dataType: 'jsonp', 
          type: 'GET', 
          success: function (json) {
            console.log(json)
          }
        });  
}

function getNotificationList () {
  var ut = localStorage.usertoken
  var postGetUrl = 'http://meiliuer.com/api/notifications?token='+ut + "&page=1"
  $.ajax({
      url: postGetUrl,
      type: "GET",
      dataType: "jsonp",
      success: function(json) {
        console.log(json)
        notificationList = json
        workingList = notificationList
        updateNotificationListUI()
        markAllAsRead()
      }
  });
}

function getThumbPic (postId) {
  $.ajax({
    url: "http://meiliuer.com/api/SaveTitle?token=" + localStorage.usertoken + "&id=" + postId, 
    dataType:'jsonp', 
    type:'GET', 
    succcess: function(json){
      // myApp.hidePreloader()
      console.log(json)
    }, 
    error: function(error) {
      // myApp.hidePreloader()
    }
  });  
}

function getUrlTitle (){
  myApp.showPreloader("正在读取标题") 
  var url = document.getElementById("post-address-input").value; 
  var responseObject = $.ajax({
    url: "http://meiliuer.com/api/GetTitle?token=" + localStorage.usertoken + "&url=" + encodeURIComponent(url), 
    dataType:'jsonp', 
    type:'GET', 
    succcess: function(json){
      // This success function won't be called now
      myApp.hidePreloader()
      console.log(json)
      // if (json && json.query && json.query.results && json.query.results.title) {
      //   document.getElementById("post-title-input").value = json.query.results.title
      // }      
    }, 
    error: function(error) {
      myApp.hidePreloader()
    }
  });

  // manual way to check if the response is loaded
  var maxWaiting = 24000; 
  var changingPrompt = 15000;
  var refreshRate = 1000; 

  var waiting = true; 
  checkResponseJSON ();
  function checkResponseJSON () {
    console.log("hehe")
    if (waiting) {
      if (responseObject.readyState == 4 || responseObject.status == 200 ) {
        document.getElementById("post-title-input").value = responseObject.responseJSON.title
        myApp.hidePreloader()
        waiting = false
      }  
      setTimeout(function(){
        checkResponseJSON()
      }, refreshRate) ;
    } 
  }
  setTimeout(function() {
    if (waiting) {
      waiting = false;
      myApp.hidePreloader()
      myApp.alert("臣妾读不出来标题啊666666")      
    }
  }, maxWaiting)

  setTimeout(function() {
    if (waiting) {
      myApp.hidePreloader(); 
      myApp.showPreloader("努力加载中666666")
    }
  }, changingPrompt)
}

// function postLinkBy
function postLinkByDeviceToken () {
  if (localStorage.usertoken == null || localStorage.usertoken == "null") {
    myApp.alert("Please register first")
    return
  }

  var url = document.getElementById("post-address-input").value
  var title = document.getElementById("post-title-input").value
  var ut = localStorage.usertoken
  var postGetUrl = 'http://meiliuer.com/api/create?name=' + encodeURIComponent(title)+ '&type=1&link=' + encodeURIComponent(url) + '&device_token='+ device_token +'&device_type=iOS'
  responseObject = $.ajax({
      url: postGetUrl,
      type: "GET",
      dataType: "jsonp",
  });
}



function postLink () {
  if (localStorage.usertoken == null || localStorage.usertoken == "null") {
    myApp.alert("Please register first")
    return
  }

  var url = document.getElementById("post-address-input").value
  var title = document.getElementById("post-title-input").value
  var ut = localStorage.usertoken

  var postGetUrl = 'http://meiliuer.com/api/create?token='+ut+'&'+'name=' + encodeURIComponent(title)+ '&type=1&link=' + encodeURIComponent(url) + "&callback=1"
  $.ajax({
      url: postGetUrl,
      type: "GET",
      dataType: "jsonp",
      success: function(json) {
        console.log(json)
        getThumbPic (json.success)
        myApp.alert("你刚才666666666了", function(){
           myApp.closeModal(".popup-post")
           updateListDataFromServer()
        });             
      }
  });
}

function getPersonalInfo () {
  var ut = localStorage.usertoken
  var postGetUrl = 'http://meiliuer.com/api/GetOwnInfo?token='+ut
  $.ajax({
      url: postGetUrl,
      type: "GET",
      dataType: "jsonp",
      success: function(json) {
        console.log(json)          
      }
  });    
}

function signUp() {
  try {
    Keyboard.hide()
  } catch(err) {
    
  }
  var email = document.getElementById("signup-email-input").value
  var password = document.getElementById("signup-password-input").value
  var username = document.getElementById("signup-username-input").value
  var ajaxUrl = "http://meiliuer.com/api/signup?username="+username+"&email="+email+"&password="+password
  $.ajax({
    url: ajaxUrl,
    type: "GET",
    dataType: "jsonp",
    success: function (json) {
      if (json.token != null) {
        localStorage.usertoken = json.token, 
        localStorage.usernickname = json.username
        localStorage.userId = json.user_id            
        loginPopupClose()      
        loginStatusUpdate() 
      } else {
        if (json[0].email != null ) {
          myApp.alert(json[0].email[0])
        } else if (json[0].password != null ){
          myApp.alert(json[0].password[0])
        }
      }           
    }
  });
}

function logIn() {
  try {
    Keyboard.hide()
  } catch(err) {

  }
  
  var username = document.getElementById("post-email-input").value
  var password = document.getElementById("post-password-input").value
  var postGetUrl = 'http://meiliuer.com/api/login?username=' + username + '&password=' + password + '&device_token=' + device_token + '&device_type=iOS'
  $.ajax({
    url: postGetUrl,
    type: "GET",
    dataType: "jsonp",
    success: function (json) {
      if (json.token != null) {
        console.log(json.token)
        // localStorage.user
        localStorage.usertoken = json.token, 
        localStorage.usernickname = json.username
        localStorage.userId = json.user_id
        loginPopupClose()      
        loginStatusUpdate()  
        getUnreadNum ()            
      } else {
        myApp.alert(json.error)
      }           
    }
  });
}


function addPostPopupOpen () {
  document.getElementById("post-address-input").value = ""
  document.getElementById("post-title-input").value = ""
  myApp.popup(".popup-post")
}

function pastFromClipBoard() {
  cordova.plugins.clipboard.paste(function (text) { 
    document.getElementById("post-address-input").value = text; 
  });  
}
function logOut(){
  localStorage.usertoken = null;
  configPopupClose()
  loginStatusUpdate()
}

function addPostPopupClose () {
  myApp.closeModal(".popup-post")
}
function loginPopupClose () {
  myApp.closeModal(".popup-login")
  try {
    StatusBar.backgroundColorByHexString("#fde125")
  } catch(err) {

  }
  
}


// Changing the background so that it wont overlay the 
// input box when user enters credentials
$(function (){
  $(".login-signup-input").focus(function(){
    $(".login-background").css("display", "none")
  })
  $(".login-signup-input").blur(function(){
    console.log(12)
    $(".login-background").css("display", "block")
  });
});



function loginPopupOpen() {
  myApp.closeModal(".popup-tutorial")
  localStorage.hasShownTutorial = "1";

  // popup dealy hack
  setTimeout(function () {
    myApp.popup(".popup-login");
  }, 100);   
  try {
    Keyboard.hideFormAccessoryBar(false);
    StatusBar.backgroundColorByHexString("#ffffff")
  } catch (err) {

  }
  document.getElementById("signup-username-input").onkeypress = function (e) {
    if ( e.keyCode == 13 ) {
      document.getElementById("signup-email-input").focus()
    }
  }
  document.getElementById("signup-email-input").onkeypress = function (e) {
    if ( e.keyCode == 13 ) {
      document.getElementById("signup-password-input").focus()
    }
  }
  document.getElementById("signup-password-input").onkeypress = function(e) {
    if ( e.keyCode == 13 ) {
      signUp();
    }    
  }
  
  document.getElementById("post-email-input").onkeypress = function (e) {
    if ( e.keyCode == 13 ) {
      document.getElementById("post-password-input").focus()
    }
  }
  document.getElementById("post-password-input").onkeypress = function (e) {
    if ( e.keyCode == 13 ) {
      logIn()
    }
  }    
}



function tutorialPopupOpen () {
  try {
    StatusBar.backgroundColorByHexString("#FFFFFF")
  } catch(err) {

  }  
  // popup delay hack
  setTimeout(function () {
    myApp.popup(".popup-tutorial")
  }, 10)
  
}






function loginStatusUpdate() {
  if (localStorage.hasShownTutorial == "1") {
    if (!(localStorage.usertoken == "null" || localStorage.usertoken == null)) {
      $(".login-only").css("display", "block")
      $(".guest-only").css("display", "none")
      updateListDataFromServer()
    } else {
      $(".login-only").css("display", "none")
      $(".guest-only").css("diloginPopupOpensplay", "block")
      setTimeout(function () {
        loginPopupOpen()  
      }, 10);
    }  
  } else { 
    // show tutorial
    // popup delay hack
    setTimeout(function () {
      tutorialPopupOpen()  
      setTimeout(function () {
        // delay hack
        guestSwiper = myApp.swiper('.swiper-container', {
          onReachEnd: function (haha) {
            setTimeout(function () {
              $("#tutorial-button").css("display","block")
              $("#tutorial-button").addClass("animated bounceIn")
            }, 400);              
          }
        });      
      }, 400); 
    }, 10); 
  }
}

$$('.infinite-scroll').on('infinite', function () {
  var fakeWaitingTime = 300;
  if (mainView.url == "#user" ) {
    if (userLoading) return;
    $('.infinite-scroll-preloader').css("display", "block");
   
    // Set loading flag
    userLoading = true;
    userCurrentIndex ++ 
   
    setTimeout(function () {
      var urlPost = "http://meiliuer.com/api/user?token="+localStorage.usertoken+"&user_id="+targetUserId+"&page="+userCurrentIndex
      $.ajax({
        url:urlPost, 
        type:'GET',
        dataType:"jsonp",
        success:function(json){
          hasClickedGetUserPost = false;
          if (json.length > 0) {
            userPostList = userPostList.concat(json) 
            workingList = userPostList                                
            updateListHTMLFromListData(userPostList, 'user-post-list')       
            userLoading = false;
            $('.infinite-scroll-preloader ').css("display", "none");
            document.getElementById("user-page-nav-title").innerHTML = workingList[0].username + "(" + workingList.length + ")"
          } else {
            userLoading = true;
            $('.infinite-scroll-preloader').css("display", "none");
          }
        }});  
    }, fakeWaitingTime);    

  } else if (mainView.url == "#index") {
    if (homeLoading) return;
    $('.infinite-scroll-preloader').css("display", "block");
    homeLoading = true;
    currentIndex ++ 
    // Emulate 1s loading
    setTimeout(function () {
      var postGetUrl = 'http://meiliuer.com/api/index?page='+currentIndex+'&token='+localStorage.usertoken+ '&device_token=' + device_token + 'device_type=iOS'
      responseObject = $.ajax({
          url: postGetUrl,
          type: "GET",
          dataType: "jsonp",
          success: function (json) {
            if (json.length > 0) {
              postList = postList.concat(json)
              workingList = postList
              updateListHTMLFromListData(postList, 'post-list')
              homeLoading = false;
              $('.infinite-scroll-preloader').css("display", "none");              
            } else {
              homeLoading = true; 
              $('.infinite-scroll-preloader').css("display", "none");    
            }
          },
      });
    }, fakeWaitingTime);
  }
});       


var hasClickedGetUserPost
var targetUserId; 
function getUserPost(userId, selfPage) {
  userCurrentIndex = 1
  if ( selfPage ) {
    document.getElementById("logout-placeholder").innerHTML = "<span onclick='logOut()'>登出</span>"
  } else {
    document.getElementById("logout-placeholder").innerHTML = ""
  }

  hasClickedGetUserPost = true;
  targetUserId = userId;
  userLoading = false;
  var urlPost = "http://meiliuer.com/api/user?token="+localStorage.usertoken+"&user_id="+userId+"&page="+userCurrentIndex
  $.ajax({
    url:urlPost, 
    dataType:"jsonp",
    success:function(json){
      hasClickedGetUserPost = false;
      userPostList = json
      console.log(json)
      if (json.length > 0) {
        document.getElementById("user-page-nav-title").innerHTML = json[0].username + "(" + json.length + ")"
        updateListHTMLFromListData(json, 'user-post-list')
        mainView.router.loadPage({
          pageName: 'user'
        });          
      } else {
        myApp.alert("这个用户没有发过言哦~")
      }
    }});
  document.getElementById("number-of-posts").innerHTML = " - "
  document.getElementById("number-of-likes-sent").innerHTML = " - "
  document.getElementById("number-of-likes-received").innerHTML = " - "
  var urlPostUserData = "http://meiliuer.com/api/getuserdata?user_id="+userId
  $.ajax({
    url: urlPostUserData, 
    dataType: 'jsonp', 
    type: 'GET', 
    success: function(json) {
      document.getElementById("number-of-posts").innerHTML = json.total_posts
      document.getElementById("number-of-likes-sent").innerHTML = json.total_ups_sent
      document.getElementById("number-of-likes-received").innerHTML = json.total_ups_received_posts
      console.log(json)
    }
  }); 
}











