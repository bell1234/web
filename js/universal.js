$(document).ready(function() {

    	FastClick.attach(document.body);

	queried = false;

	$("#post_link").change(function() {
		validateURlField();
		queried = false;
	});	

	//disable space for URL
	$("#post_link").on({
  		keydown: function(e) {
    			if (e.which === 32)
      				return false;
  			},
  		change: function() {
    			this.value = this.value.replace(/\s/g, "");
  		}
	});

	$('#Posts_name').focus(function() {
		if(!queried && $('#post_link').val()){
			read_title(true);
		}
	});

	$("abbr.timeago").timeago();

});

function signup(){
	$('#signup_or_login').modal();
}

function post_new(){
	$('#post_popup').modal();
}

function vote(post_id, type, guest, self){	//type 1 = up vote, 2 = down vote

	if(guest){
		signup();
		return false;	
	}
	if(self){
		alert('自己不可以给自己投票哦！');
		return false;	
	}

	var voteup = $('#post_cell_'+post_id+' > div.post_votes > a.vote_up');
	var votedown = $('#post_cell_'+post_id+' > div.post_votes > a.vote_down');

	if(type == 1){	//vote up
		if($(voteup).hasClass('voted')){
			var current = Number($('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text(current-1);
			$(voteup).removeClass('voted');			
			ajaxVoteCancel(post_id, type);
		}else{
			var current = Number($('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text(current+1);
			$(voteup).addClass('voted');
			ajaxVote(post_id, type);
		}
		$(votedown).removeClass('voted');

	}else if(type == 2){	//vote down
		if($(votedown).hasClass('voted')){
			var current = Number($('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text(current+1);
			$(votedown).removeClass('voted');
			ajaxVoteCancel(post_id, type);
		}else{
			var current = Number($('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text(current-1);
			$(votedown).addClass('voted');
			ajaxVote(post_id, type);

		}
		$(voteup).removeClass('voted');
	}

}


function ajaxVote(post_id, type){
	$.ajax({
        	url: '/posts/vote',
        	type: 'POST',
        	data: {post_id: post_id, type:type},
        	datatype: 'json',
        	success: function (data) {
			if(data){
				//
			}else{
				//
			}
		},
        	error: function (jqXHR, textStatus, errorThrown) {
			//
		}
    	});
}


function ajaxVoteCancel(post_id, type){
	$.ajax({
        	url: '/posts/voteCancel',
        	type: 'POST',
        	data: {post_id: post_id, type:type},
        	datatype: 'json',
        	success: function (data) {
			if(data){
				//
			}else{
				//
			}
		},
        	error: function (jqXHR, textStatus, errorThrown) {
			//
		}
    	});
}


function comment_vote(comment_id, type, guest, self){	//type 1 = up vote, 2 = down vote

	if(guest){
		signup();
		return false;	
	}
	if(self){
		alert('自己不可以给自己投票哦！');
		return false;	
	}

	var voteup = $('#comment_cell_'+comment_id+' > div.post_votes > a.vote_up');
	var votedown = $('#comment_cell_'+comment_id+' > div.post_votes > a.vote_down');

	if(type == 1){	//vote up
		if($(voteup).hasClass('voted')){
			var current = Number($('#comment_cell_'+comment_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#comment_cell_'+comment_id+' > div.post_votes > a.vote_up > div.vote_num').text(current-1);
			$(voteup).removeClass('voted');			
			ajaxVoteCommentCancel(comment_id, type);
		}else{
			var current = Number($('#comment_cell_'+comment_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#comment_cell_'+comment_id+' > div.post_votes > a.vote_up > div.vote_num').text(current+1);
			$(voteup).addClass('voted');
			ajaxVoteComment(comment_id, type);
		}
		$(votedown).removeClass('voted');

	}else if(type == 2){	//vote down
		if($(votedown).hasClass('voted')){
			var current = Number($('#comment_cell_'+comment_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#comment_cell_'+comment_id+' > div.post_votes > a.vote_up > div.vote_num').text(current+1);
			$(votedown).removeClass('voted');
			ajaxVoteCommentCancel(comment_id, type);
		}else{
			var current = Number($('#comment_cell_'+comment_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#comment_cell_'+comment_id+' > div.post_votes > a.vote_up > div.vote_num').text(current-1);
			$(votedown).addClass('voted');
			ajaxVoteComment(comment_id, type);

		}
		$(voteup).removeClass('voted');
	}

}


function ajaxVoteComment(comment_id, type){
	$.ajax({
        	url: '/posts/voteComment',
        	type: 'POST',
        	data: {comment_id: comment_id, type:type},
        	datatype: 'json',
        	success: function (data) {
			if(data){
				//
			}else{
				//
			}
		},
        	error: function (jqXHR, textStatus, errorThrown) {
			//
		}
    	});
}


function ajaxVoteCommentCancel(comment_id, type){
	$.ajax({
        	url: '/posts/voteCommentCancel',
        	type: 'POST',
        	data: {comment_id: comment_id, type:type},
        	datatype: 'json',
        	success: function (data) {
			if(data){
				//
			}else{
				//
			}
		},
        	error: function (jqXHR, textStatus, errorThrown) {
			//
		}
    	});
}



function validateURlField(){
		url = addhttp($("#post_link").val());
		if(isUrlValid(url)){
			$('#url_invalid').hide();
			// grab content	/ picture
			$('#link_post_field').removeClass('has-error');
			checkURLDup(url);
			return true;
		}else{	//invalid URL
			$('#url_invalid').show();
			$('#link_post_field').addClass('has-error');
			return false;
		}
}

function checkURLDup(url){
	$.ajax({
        	url: '/posts/getDupURL?url='+url,
        	type: 'POST',
        	//data: someData,
        	//datatype: 'json',
        	success: function (data) {
			if(data){
				$('#dup_url').show();
				$('#dup_actual_url').html('<a target="_blank" href="'+data+'">'+data+'</a>');
			}else{
				$('#dup_url').hide();
			}
		},
        	error: function (jqXHR, textStatus, errorThrown) {
			$('#dup_url').hide();
		}
    	});
}

function addhttp(val) {
  if (val && !val.match(/^http([s]?):\/\/.*/)) {
    val = 'http://' + val;
  }
  $("#post_link").val(val);
  return val;
}

function isUrlValid(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
}


function show_link(){
	$('#Posts_type').val(1);
	$('.content_tab').removeClass('active');
	$('.ama_tab').removeClass('active');
	$('.link_tab').addClass('active');
	$('.link_post').show();
	$('.content_post').hide();
	$('.ama_alert').hide();
	$('#Posts_category_id').val('');
	//$('.category_drop').show();
	$('#Posts_category_id').val('');
	if($('#Posts_name').val() == '我是XXX (简介自己), 有问必答!'){
		$('#Posts_name').val('');
	}
}

function show_content(){
	$('#Posts_type').val(2);
	$('.link_tab').removeClass('active');
	$('.ama_tab').removeClass('active');
	$('.content_tab').addClass('active');
	$('.link_post').hide();
	$('.ama_alert').hide();
	$('.content_post').show();
	//$('.category_drop').show();
	$('#Posts_category_id').val('');
	if($('#Posts_name').val() == '我是XXX (简介自己), 有问必答!'){
		$('#Posts_name').val('');
	}
}

function show_ama(){
	$('#Posts_type').val(2);
	$('.link_tab').removeClass('active');
	$('.content_tab').removeClass('active');
	$('.ama_tab').addClass('active');
	$('.content_post').show();
	$('.ama_alert').show();
	$('#Posts_name').val('我是XXX (简介自己), 有问必答!');
	$('#Posts_description').val('请介绍自己，如果方便的话，提供可以证明身份的材料可以大幅提高收视率哦。');
	$('.link_post').hide();
	//$('.category_drop').hide();
	$('#Posts_category_id').val(30); //random value here for validation, does not matter
}

function read_title(system_action){		//读取标题，图片或者视频。如果system_action为true, 则为系统读取，不覆盖标题。

	system_action = typeof system_action !== 'undefined' ? system_action : false;

	if(!validateURlField()){
		return false;
	}
	url = $("#post_link").val();

	if(!system_action){	//非系统读取
		if($('#Posts_name').val()){
			var r = confirm("现有的标题将被取代，确定替换吗？");
			if (r == true) {
			} else {
    				return false;
			}
		}
		$('.post_title_loading').show();
		$('.post_title_before').hide();
		$('.post_title_error').hide();
		if($('#temp_title').val() && queried){	//最近地址已经被cache
			$('#Posts_name').val($('#temp_title').val());
			$('.post_title_before').show();
			$('.post_title_loading').hide();
			$('.post_title_error').hide();
			return false;
		}
	}
	$.ajax({
        	url: '/posts/getTitle?url='+url,
        	type: 'POST',
        	success: function (data) {
			if(!system_action){	//非系统读取
				$('#Posts_name').val(data[0]);
			}else{
				$('#temp_title').val(data[0]);
			}
			$('#Posts_thumb_pic').val(data[1]);
			$('#Posts_video_html').val(data[2]);
			queried = true;		//only once.
			if(!system_action){	//非系统读取
				$('.post_title_before').show();
				$('.post_title_loading').hide();
				$('.post_title_error').hide();
				if($('#Posts_name').val() == ""){
					$('.post_title_before').show();
					$('.post_title_loading').hide();
					$('.post_title_error').show();
				}
			}
		},
        	error: function (jqXHR, textStatus, errorThrown) {
			if(!system_action){	//非系统读取
				$('.post_title_before').show();
				$('.post_title_loading').hide();
				$('.post_title_error').show();
			}
		},
 		dataType: 'json',
    	});
}


/*
function read_pic(){
	if(!validateURlField()){
		return false;
	}
	url = $("#post_link").val();
	if($('#Posts_thumb_pic').val()){
		var r = confirm("现有的配图将被取代，确定替换吗？");
		if (r == true) {
		} else {
    			return false;
		}
	}
	$('.post_pic_loading').show();
	$('.post_pic_before').hide();
	$.ajax({
        	url: '/posts/getPic?url='+url,
        	type: 'POST',
        	//data: someData,
        	//datatype: 'json',
        	success: function (data) {
			if(data != "error"){
				$('.post_pic_before').show();
				$('.post_pic_loading').hide();
				$('.post_pic_error').hide();
				$('#Posts_thumb_pic').val(data);
				$('#thumb_pic').attr('src', data);
			}else{
				$('.post_pic_before').show();
				$('.post_pic_loading').hide();
				$('.post_pic_error').show();
			}
		},
        	error: function (jqXHR, textStatus, errorThrown) {
			$('.post_title_before').show();
			$('.post_pic_loading').hide();
			$('.post_pic_error').show();
		}
    	});
}
*/