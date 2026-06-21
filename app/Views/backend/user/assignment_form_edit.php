<form action="javascript:;" class="ajax_form">
	<div class="form-group row">
		<label class="col-md-3 col-lg-2 col-form-label" for="assignment_title">
			<?php echo get_phrase('assignment_title'); ?><span class="required">*</span>
		</label>
		<div class="col-md-9 col-lg-10">
		    <input type="text" class="form-control" id="edit_assignment_title" name = "assignment_title" value="<?= $assignment['title']; ?>" placeholder="<?php echo get_phrase('enter_assignment_title'); ?>" required>
		</div>
	</div>

	<div>
        <input type="hidden" name="course_id" id="course_id" value="<?php echo $assignment['course_id'] ?>" class="form-field" Placeholder="course_id">
    </div>

	<div class="form-group row">
		<label class="col-md-3 col-lg-2 col-form-label" for="questions_description">
			<?php echo get_phrase('questions'); ?><span class="required">*</span>
		</label>
		<div class="col-md-9 col-lg-10">
            <textarea name="questions" id ="edit_questions_description" class="form-control" placeholder="<?php echo get_phrase('enter_your_assignment_questions'); ?>"><?= $assignment['questions']; ?></textarea>
		</div>
	</div>

    <div class="form-group row">
        <label class="col-md-3 col-lg-2 col-form-label" for="question_file">
            <?php echo get_phrase('question_file'); ?>
        </label>
        <div class="col-md-10 input-group ">
            <div class="custom-file">
                <input type="file" name="question_file" class="custom-file-input" id="edit_question_file" onchange="changeTitleOfImageUploader(this)">
                <label class="custom-file-label" for="inputGroupFile04"><?php echo get_phrase('choose_file'); ?></label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-lg-2 col-form-label" for="total_marks">
        	<?php echo get_phrase('total_marks'); ?> <span class="required">*</span>
        </label>
        <div class="col-md-9 col-lg-10">
        	<input class="form-control" id="edit_total_marks" type="number" min="0" name="total_marks" value="<?= $assignment['total_marks']; ?>">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-lg-2 col-form-label" for="deadline_date">
        	<?php echo get_phrase('deadline').' ('.get_phrase('date').')'; ?> <span class="required">*</span> 
        </label>
        <div class="col-md-9 col-lg-10">
            <input type="text" name="deadline_date" class="form-control date" id="edit_deadline_date" data-toggle="date-picker" data-single-date-picker="true" value="<?php echo date('m/d/Y', $assignment['deadline_date']); ?>">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-lg-2 col-form-label" for="deadline_time">
        	<?php echo get_phrase('deadline').' ('.get_phrase('time').')'; ?> <span class="required">*</span>  
        </label>
        <div class="col-md-9 col-lg-10">
            <input type="text" name="deadline_time" id="edit_deadline_time" class="form-control time" data-toggle="timepicker" value="<?php echo date('h:i:s A', $assignment['deadline_time']); ?>">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-lg-2 col-form-label" for="note">
        	<?php echo get_phrase('note'); ?>
        </label>
        <div class="col-md-9 col-lg-10">
            <textarea name="note" id = "edit_note" class="form-control"><?= $assignment['note']; ?></textarea>
        </div>
    </div>

	<div class="form-group row">
		<label class="col-md-3 col-lg-2 col-form-label"></label>
		<div class="col-md-9 col-lg-10">
		    <button type="button" class="btn btn-success" onclick="update_assignment(this, '<?php echo $assignment['assignment_id']; ?>')"><?php echo get_phrase('update_assignment'); ?></button>
		</div>
	</div>
</form>

<script type="text/javascript">
    'use strict';
    
    $(document).ready(function () {
        initSummerNote(['#edit_questions_description']);
        $('#edit_deadline_date').datepicker();
        $('#edit_deadline_time').timepicker({defaultValue: 'value'});
    });
</script>

