<script type="text/javascript">
	'use strict';

	function load_assignment_list(course_id, load_again){
		if(course_id == "") {
			course_id = $course_details['id'];
		}
		if(!$('#assignment_list').html() || load_again == "load"){
			$('.ajax_loaderBar').addClass('start_ajax_loading');
	    	$.ajax({
		        url: '<?php echo site_url('addons/assignment/load_assignment_list/'.$course_id) ?>',
		        success: function(response){
		        	$('#assignment_list').html(response);
		        	$('.ajax_loaderBar').removeClass('start_ajax_loading');
		        }
		    });
		}
	}

	function load_assignment_form(course_id){
    	$.ajax({
	        url: '<?php echo site_url('addons/assignment/load_assignment_form/') ?>'+course_id,
	        success: function(response){
	        	$('#assignment_form').html(response);
	        }
	    });
	}

	function load_assignment() {
		$('#submitted_list').hide();
		$('#assignment_front_view').show();
	}

	function add_new_assignment(btn){
		//loading start
		var btn_text = $(btn).text();
		$(btn).html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span><?= get_phrase('uploading'); ?>...');
		$(btn).prop("disabled",true);
		$('.ajax_loaderBar').addClass('start_ajax_loading');

		var course_id = $("#course_id").val();
		var jform = new FormData();
		jform.append('assignment_title',$('#assignment_title').val());
		jform.append('course_id',$('#course_id').val());
		jform.append('questions',$('#questions').val());
		if($('#question_file').get(0).files[0]){
			jform.append('question_file',$('#question_file').get(0).files[0]);
		}
		jform.append('total_marks',$('#total_marks').val());
		jform.append('deadline_date',$('#deadline_date').val());
		jform.append('deadline_time',$('#deadline_time').val());
		jform.append('note',$('#note').val());
		jform.append('status',$('input[name=status]:checked').val());

		$.ajax({
	        url: '<?php echo site_url('addons/assignment/add_assignment/') ?>'+course_id,
	        type: 'post',
		    data: jform,
		    dataType: 'json',
		    mimeType: 'multipart/form-data', // this too
		    contentType: false,
		    cache: false,
		    processData: false,                        
	        success: function(response){
	   			if(response.status == 'success'){
	   				
	   				show_added_assignment(response.insert_id);
	   				load_assignment_form(course_id);
	   				load_assignment_list(course_id, 'load');

	         		$.NotificationApp.send("<?php echo get_phrase('success'); ?>!", response.message ,"top-right","rgba(0,0,0,0.2)","success");
				}else{
					$.NotificationApp.send("<?php echo get_phrase('oh_snap'); ?>!", response.message ,"top-right","rgba(0,0,0,0.2)","error");
	   			}
	   			$(btn).html(btn_text);
				$(btn).prop("disabled",false);
				$('.ajax_loaderBar').removeClass('start_ajax_loading');
	        }
	    });
	}

	function update_assignment(btn, assignment_id){
		//loading start
		var btn_text = $(btn).text();
		$(btn).html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span><?= get_phrase('uploading'); ?>...');
		$(btn).prop("disabled",true);
		$('.ajax_loaderBar').addClass('start_ajax_loading');

		var course_id = $("#course_id").val();
		var jform = new FormData();
		jform.append('assignment_title',$('#edit_assignment_title').val());
		jform.append('course_id',$('#course_id').val());
		jform.append('questions',$('#edit_questions_description').val());
		if($('#question_file').get(0).files[0]){
			jform.append('question_file',$('#edit_question_file').get(0).files[0]);
		}
		jform.append('total_marks',$('#edit_total_marks').val());
		jform.append('deadline_date',$('#edit_deadline_date').val());
		jform.append('deadline_time',$('#edit_deadline_time').val());
		jform.append('note',$('#edit_note').val());

		$.ajax({
	        url: '<?php echo site_url('addons/assignment/edit_assignment/') ?>'+ course_id + '/' + assignment_id +'/update',
	        type: 'post',
		    data: jform,
		    dataType: 'json',
		    mimeType: 'multipart/form-data', // this too
		    contentType: false,
		    cache: false,
		    processData: false,                        
	        success: function(response){
	   			if(response.status == 'success'){

	   				$('div').modal('hide');
	   				load_assignment_form(course_id);
	   				load_assignment_list(course_id, 'load');

	         		$.NotificationApp.send("<?php echo get_phrase('success'); ?>!", response.message ,"top-right","rgba(0,0,0,0.2)","success");
				}else{
					$.NotificationApp.send("<?php echo get_phrase('oh_snap'); ?>!", response.message ,"top-right","rgba(0,0,0,0.2)","error");
	   			}
	   			$(btn).html(btn_text);
				$(btn).prop("disabled",false);
				$('.ajax_loaderBar').removeClass('start_ajax_loading');
	        }
	    });
	}

	function show_added_assignment(assignment_id){
		$.ajax({
	        url: '<?php echo site_url('addons/assignment/load_single_assignment/'); ?>' + assignment_id,
	        success: function(response){
	        	$('#notice_list').prepend(response);
	        	$('.ajax_loaderBar').removeClass('start_ajax_loading');
	        }
	    });
	}

	function update_assignment_status(status, assignment_id, course_id){
		$.ajax({
	        url: '<?php echo site_url('addons/assignment/update_assignment_status/') ?>' + status + '/' + assignment_id,
	        success: function(response){
   				load_assignment_list(course_id, "load");
   				var response = JSON.parse(response);
	   			if(response.status == 'success'){
	         		$.NotificationApp.send("<?php echo get_phrase('success'); ?>!", response.message ,"top-right","rgba(0,0,0,0.2)","success");
				}else{
					$.NotificationApp.send("<?php echo get_phrase('oh_snap'); ?>!", response.message ,"top-right","rgba(0,0,0,0.2)","error");
	   			}
	        }
	    });
	}

	function load_submitted_assignment(assignment_id, course_id){
		$('.ajax_loaderBar').addClass('start_ajax_loading');
    	$.ajax({
	        url: '<?php echo site_url('addons/assignment/load_submitted_assignment/') ?>' + assignment_id + '/' + course_id,
	        success: function(response){
	        	$('#assignment_front_view').hide();
	        	$('#submitted_list').show();
	        	$('#submitted_list').html(response);
	        	$('.ajax_loaderBar').removeClass('start_ajax_loading');
	        }
	    });
		
	}

	function view_answer(submission_id, course_id){
		$('.ajax_loaderBar').addClass('start_ajax_loading');
    	$.ajax({
	        url: '<?php echo site_url('addons/assignment/view_answer/') ?>' + submission_id + '/' + course_id,
	        success: function(response){
	        	$('#submitted_list').html(response);
	        	$('.ajax_loaderBar').removeClass('start_ajax_loading');
	        }
	    });
		
	}

	function update_mark(btn, submission_id, assignment_id, course_id){
		//loading start
		var btn_text = $(btn).text();
		$(btn).html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span><?= get_phrase('uploading'); ?>...');
		$(btn).prop("disabled",true);
		$('.ajax_loaderBar').addClass('start_ajax_loading');

		var total_marks = $('#total_marks').val();
		var assignment_marks = $('#marks').val();
		var assignment_remarks = $('#remarks').val();

		if(assignment_marks != "" && assignment_remarks != "") {
	    	$.ajax({
		        url: '<?php echo site_url('addons/assignment/update_assignment_mark/') ?>' + submission_id,
		        type: 'post',
		        data: {total_marks : total_marks, assignment_marks : assignment_marks, assignment_remarks : assignment_remarks},  
		        success: function(response){
		        	load_submitted_assignment(assignment_id, course_id);
		        	var response = JSON.parse(response);
		        	if(response.status == 'success'){
			        	$.NotificationApp.send("<?php echo get_phrase('success'); ?>!", response.message ,"top-right","rgba(0,0,0,0.2)","success");
			        }else{
					$.NotificationApp.send("<?php echo get_phrase('oh_snap'); ?>!", response.message ,"top-right","rgba(0,0,0,0.2)","error");
	   				}
		        }
		    });
		} else {
			$(btn).html(btn_text);
			$(btn).prop("disabled",false);
			$('.ajax_loaderBar').removeClass('start_ajax_loading');
			$.NotificationApp.send("<?php echo get_phrase('oh_snap'); ?>!", "<?php echo get_phrase('fill_up_the_required_feilds'); ?>" ,"top-right","rgba(0,0,0,0.2)","error");
		}
		
	}


</script>

