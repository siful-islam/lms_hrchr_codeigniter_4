<div class="row">
    <div class="col-md-6">
        <h5 class="header-title my-1"><?php echo get_phrase('create_new_assignment'); ?></h4>
    </div>
</div>
<form action="javascript:;" class="ajax_form">
	<div class="form-group">
		<label class="col-form-label" for="assignment_title">
			<?php echo get_phrase('assignment_title'); ?><span class="required">*</span>
		</label>
		<div>
		    <input type="text" name="assignment_title" id="assignment_title" class="form-control" placeholder="<?php echo get_phrase('enter_assignment_title'); ?>">
		</div>
	</div>

	<div>
        <input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id;?>" class="form-field" Placeholder="course_id">
    </div>

	<div class="form-group">
		<label class="col-form-label" for="questions">
			<?php echo get_phrase('questions'); ?><span class="required">*</span>
		</label>
		<div>
		    <textarea name="questions" id="questions" class="form-control" placeholder="<?php echo get_phrase('enter_your_assignment_questions'); ?>"></textarea>
		</div>
	</div>

	<div class="form-group">
        <label class="col-form-label" for="question_file">
        	<?php echo get_phrase('question_file'); ?>
        </label>
        <div class="input-group ">
            <div class="custom-file">
                <input type="file" name="question_file" class="custom-file-input" id="question_file" onchange="changeTitleOfImageUploader(this)">
                <label class="custom-file-label" for="inputGroupFile04"><?php echo get_phrase('choose_file'); ?></label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-form-label" for="total_marks">
        	<?php echo get_phrase('total_marks'); ?> <span class="required">*</span>
        </label>
        <div>
        	<input class="form-control" id="total_marks" type="number" min="0" name="total_marks">
        </div>
    </div>

    <div class="form-group">
        <label class="col-form-label" for="deadline_date">
        	<?php echo get_phrase('deadline').' ('.get_phrase('date').')'; ?> <span class="required">*</span> 
        </label>
        <div>
            <input type="text" name="deadline_date" class="form-control date" id="deadline_date" data-toggle="date-picker" data-single-date-picker="true" value="<?php echo date('m/d/Y', strtotime(date('m/d/Y'))); ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-form-label" for="deadline_time">
        	<?php echo get_phrase('deadline').' ('.get_phrase('time').')'; ?> <span class="required">*</span>  
        </label>
        <div>
            <input type="text" name="deadline_time" id="deadline_time" class="form-control" data-toggle="timepicker" value="<?php echo date('h:i:s A', strtotime(date('h:i:s A'))); ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-form-label" for="note">
        	<?php echo get_phrase('note'); ?>
        </label>
        <div>
            <textarea name="note" id = "note" class="form-control"></textarea>
        </div>
    </div>

    <div>
        <label class="col-form-label" for="note">
            <?php echo get_phrase('submission_status'); ?>
        </label>
    </div>

    <div class="form-group mt-2">
        <div class="form-check form-check-inline">
            <input type="radio" id="status1" name="status" value="<?php echo 'draft' ?>" class="form-check-input" checked>
            <label class="form-check-label" for="status1"><?php echo get_phrase('draft'); ?></label>
        </div>
        <div class="form-check form-check-inline">
            <input type="radio" id="status2" name="status" value="<?php echo 'active' ?>" class="form-check-input">
            <label class="form-check-label" for="status2"><?php echo get_phrase('active'); ?></label>
        </div>
    </div>

	<div class="form-group">
		<label class="col-form-label"></label>
		<div>
		    <button type="button" class="btn btn-success" onclick="add_new_assignment(this)"><?php echo get_phrase('add_new_assignment'); ?></button>
		</div>
	</div>
</form>

<script type="text/javascript">
	'use strict';
	
	$(document).ready(function () {
	    initSummerNote(['#questions']);
	});
</script>

