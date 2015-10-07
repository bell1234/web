$(document).ready(function() {

	$("#post_link").change(function() {
		validateURlField();
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

	$("abbr.timeago").timeago();
});

function vote(post_id, type, guest, self){	//type 1 = up vote, 2 = down vote
	if(guest){
		alert('show login/signup popup');
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
			$(voteup).removeClass('voted');
			var current = Number($('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text(current-1);
			ajaxVoteCancel(post_id, type);
		}else{
			$(voteup).addClass('voted');
			var current = Number($('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text(current+1);
			ajaxVote(post_id, type);
		}

		$(votedown).removeClass('voted');

	}else if(type == 2){	//vote down
		if($(votedown).hasClass('voted')){
			$(votedown).removeClass('voted');
			var current = Number($('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text(current+1);
			ajaxVoteCancel(post_id, type);
		}else{
			$(votedown).addClass('voted');
			var current = Number($('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text());
			$('#post_cell_'+post_id+' > div.post_votes > a.vote_up > div.vote_num').text(current-1);
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
	$('.link_tab').addClass('active');
	$('.content_tab').removeClass('active');
	$('.link_post').show();
	$('.content_post').hide();
}

function show_content(){
	$('#Posts_type').val(2);
	$('.link_tab').removeClass('active');
	$('.content_tab').addClass('active');
	$('.link_post').hide();
	$('.content_post').show();
}

function read_title(){
	if(!validateURlField()){
		return false;
	}
	url = $("#post_link").val();
	if($('#Posts_name').val()){
		var r = confirm("现有的标题将被取代，确定替换吗？");
		if (r == true) {
		} else {
    			return false;
		}
	}
	$('.post_title_loading').show();
	$('.post_title_before').hide();
	$.ajax({
        	url: '/posts/getTitle?url='+url,
        	type: 'POST',
        	//data: someData,
        	//datatype: 'json',
        	success: function (data) {
			if(data != "error"){
				$('.post_title_before').show();
				$('.post_title_loading').hide();
				$('.post_title_error').hide();
				$('#Posts_name').val(data);
			}else{
				$('.post_title_before').show();
				$('.post_title_loading').hide();
				$('.post_title_error').show();
			}
		},
        	error: function (jqXHR, textStatus, errorThrown) {
			$('.post_title_before').show();
			$('.post_title_loading').hide();
			$('.post_title_error').show();
		}
    	});
}