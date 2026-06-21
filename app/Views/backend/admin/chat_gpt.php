<ul class="nav nav-pills border-0 mb-3" id="pills-tab" role="tablist">
	<li class="nav-item w-50 px-1" role="presentation">
		<button class="nav-link border-0 text-center w-100 active" id="pills-gpt-assistant-tab" data-toggle="pill" data-target="#pills-gpt-assistant" type="button" role="tab" aria-controls="pills-gpt-assistant" aria-selected="true"><?php 
$crudModel = new \App\Models\Crud_model();
echo get_phrase('GPT Assistant'); ?></button>
	</li>
	<li class="nav-item w-50 px-1" role="presentation">
		<button class="nav-link border-0 text-center w-100" id="pills-gpt-service-tab" data-toggle="pill" data-target="#pills-gpt-service" type="button" role="tab" aria-controls="pills-gpt-service" aria-selected="false"><?php echo get_phrase('Services'); ?></button>
	</li>
</ul>
<div class="tab-content" id="pills-tabContent">
	<div class="tab-pane fade show active pt-2" id="pills-gpt-assistant" role="tabpanel" aria-labelledby="pills-gpt-assistant-tab">
		<div class="row mx-0" id="gpt-assistant-content">
		</div>
		<div class="form-group position-relative">
			<textarea placeholder="<?php echo get_phrase('How can I help you?'); ?>" name="gpt_assistant_command" id="gpt_assistant_command" class="form-control" rows="3"></textarea>
			<button onclick="gpt_assistant()" type="button" class="btn btn-light mt-2 w-100"><i class="mdi mdi-assistant"></i> <?php echo get_phrase('Submit') ?></button>
		</div>
	</div>
	<div class="tab-pane fade" id="pills-gpt-service" role="tabpanel" aria-labelledby="pills-gpt-service-tab">
		<form class="aiAjaxFormSubmission ai_from" action="<?php echo site_url('admin/chat_gpt') ?>" method="post">
			<div class="form-group">
				<label for="ai_service_selector"><?php echo get_phrase('select_your_service'); ?></label>
				<select id="ai_service_selector" class="form-control select2" name="service_type" onchange="if(this.value == 'Course thumbnail'){$('#aiLanguageField').hide()}else{$('#aiLanguageField').show()}">
					<option value="Course title"><?php echo get_phrase('course_title'); ?></option>
					<option value="Course short description"><?php echo get_phrase('course_short_description'); ?></option>
					<option value="Course short description"><?php echo get_phrase('course_long_description'); ?></option>
					<option value="Course requirements"><?php echo get_phrase('course_requirements'); ?></option>
					<option value="Course outcomes"><?php echo get_phrase('course_outcomes'); ?></option>
					<option value="Course FAQ"><?php echo get_phrase('course_faq'); ?></option>
					<option value="Course SEO Tags"><?php echo get_phrase('course_seo_tags'); ?></option>
					<option value="Course lesson text"><?php echo get_phrase('course_lesson_text'); ?></option>
					<option value="Course certificate text"><?php echo get_phrase('course_certificate_text'); ?></option>
					<option value="Course quiz text"><?php echo get_phrase('course_quiz_text'); ?></option>
					<option value="Course blog title"><?php echo get_phrase('course_blog_title'); ?></option>
					<option value="Course blog post"><?php echo get_phrase('course_blog_post'); ?></option>
					<option value="Course thumbnail"><?php echo get_phrase('course_thumbnail'); ?></option>
				</select>
			</div>

			<div class="form-group">
				<label for="keyword"><?php echo get_phrase('enter_your_keyword'); ?></label>
				<input type="text" class="form-control" id="keyword" name="keyword" />
			</div>

			<div class="form-group" id="aiLanguageField">
				<label for="language"><?php echo get_phrase('language'); ?></label>
				<select class="form-control select2" name="language">
					<?php $languages = $crudModel->get_all_languages();
					foreach ($languages as $language) : ?>
						<?php if (trim($language) != "") : ?>
							<option value="<?php echo $language; ?>"><?php echo ucfirst($language); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div>
			<button type="submit" id="aiSubmissionBtn" class="btn btn-primary w-100"><?php echo get_phrase('generate'); ?></button>
		</form>

		<div class="row mt-3">
			<div class="col-md-12">
				<h5 id="aiResultHeader"></h5>
				<div id="aiGeneratedText" contenteditable="true"></div>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
	"Use strict";

	$('.select2').select2();

	$(function() {
		//The form of submission to RailTeam js is defined here.(Form class or ID)
		$('.aiAjaxFormSubmission').ajaxForm({
			beforeSend: function() {
				$('#aiSubmissionBtn').html('<?php echo get_phrase('generating'); ?>...');
				$('#aiSubmissionBtn').attr('disabled', true);
			},
			uploadProgress: function(event, position, total, percentComplete) {

			},
			complete: function(xhr) {
				var response = xhr.responseText;

				if (/^[\],:{}\s]*$/.test(response.replace(/\\["\\\/bfnrtu]/g, '@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').replace(/(?:^|:|,)(?:\s*\[)+/g, '')) && $('#ai_service_selector').val() == 'Course thumbnail') {
					var parsedJson = JSON.parse(response);
					$('#aiGeneratedText').html('<div class="row"></div>');
					console.log(parsedJson.length);
					for (let i = 0; i < parsedJson.length; i++) {
						var exi = i + 1;
						var img = '<div class="w-50 p-2 position-relative"><a class="position-absolute btn btn-success px-1 py-0 m-1" href="<?php echo site_url('admin/ai_img_download?index='); ?>' + exi + '" target="_blank"><i class="fas fa-download"></i></a><img class="radius-5px" width="100%" src="' + parsedJson[i].url + '"></div>';
						$('#aiGeneratedText .row').append(img);
					}
					$('#aiResultHeader').html('<?php echo get_phrase('your_images'); ?>:');
					$('#aiGeneratedText').attr('contenteditable', 'false');
				} else {
					$('#aiGeneratedText').html(response);
					$('#aiGeneratedText').append('<input type="text" value="' + response + '" id="generatedAiText" class="visibility-hidden">');
					$('#aiResultHeader').html('<?php echo get_phrase('generated_text'); ?>: <a href="javascript:;" onclick="copy_text(this)" data-toggle="tooltip" data-placement="top" title="<?php echo get_phrase('copy'); ?>" class="float-right btn p-0"><i class="far fa-copy"></i> <?php echo get_phrase('copy'); ?></a>');
				}


				$('#aiSubmissionBtn').html('<?php echo get_phrase('generate'); ?>');
				$('#aiSubmissionBtn').attr('disabled', false);
			},
			error: function() {
				//You can write here your js error message
			}
		});
	});

	function copy_text(e) {
		// Get the text field
		var copyText = document.getElementById("generatedAiText");

		// Select the text field
		copyText.select();
		copyText.setSelectionRange(0, 99999); // For mobile devices

		// Copy the text inside the text field
		navigator.clipboard.writeText(copyText.value);

		$(e).html('<i class="far fa-copy"></i> <?php echo get_phrase('copied'); ?>!')
	}

	function gpt_assistant() {
		var gpt_assistant_command = $('#gpt_assistant_command').val();
		$('#gpt-assistant-content').append('<div class="col-md-12 my-2 px-0"><i class="mdi mdi-arrow-right"></i>' + gpt_assistant_command + '</div>');
		$('#gpt_assistant_command').val('');
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('admin/gpt_assistant') ?>',
			data: {
				'gpt_assistant_command': gpt_assistant_command
			},
			success: function(response) {
				if (response != '') {
					$('#gpt-assistant-content').append('<div class="col-md-12 my-2 px-0"><i class="mdi mdi-star-four-points-outline"></i> ' + response + '</div>');
				}
			}
		});
	}
</script>

