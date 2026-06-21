<?php
	

$assignmentModel = new \App\Models\addons\Assignment_model();
$db = \Config\Database::connect();
$request = service('request');
    $CI->load->database();
    $CI->load->model('addons/assignment_model');
	$assignments = $assignmentModel->get_assignment_by_course_id($course_id);
?>

<style>
.view-field {
    display: -webkit-box!important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: normal !important;
}
</style>

<div class="row justify-content-center">
    <div class="col-md-8">
    	<?php $key = 0; ?>
    	<?php foreach($assignments->getResultArray() as $assignment): ?>
    		<?php if($assignment['status'] == 'active') { ?>
    			<?php $key = $key + 1 ;
    			?>
    		<?php } ?>
    	<?php endforeach; ?>
    	<h6 class="text-left"><?php echo site_phrase('total').' '.$key.' '.site_phrase('assignments');; ?></h6>
    	<hr>
    	<?php foreach($assignments->getResultArray() as $assignment): ?>
    		<?php 
            $today_d = date('d M Y');
		    $today_t = date('h:i:s a');
		    $today_date = strtotime($today_d);
		    $today_time = strtotime($today_t);
		    $expire_date = $assignment['deadline_date'];
		    $expire_time = $assignment['deadline_time'];
            if($assignment['status'] == 'active' ) { ?>
	    		<div class="card mt-3">
		    		<div class="card-body">
		    			<div class="row">
	    			    	<div class="col-md-8">
	    			    		<b><i class="far fa-clipboard pr-2"></i>
		    			    	<?php echo htmlspecialchars_decode($assignment['title']); ?></b>
	    			    	</div>

	    			    	<?php if($expire_time < $today_time && $expire_date <= $today_date) { ?>
	    			    		<?php 
	    			    		$user_id = session()->get('user_id');
	    			    		$check_submit = $db->table('assignment_submission')->where(array('user_id' => $user_id, 'assignment_id' => $assignment['assignment_id']))->get()->getRowArray();
	    			    		if ($check_submit != "") { ?>
	    			    			<div class="col-md-4">
					    				<a class="btn btn-outline-primary btn-rounded btn-sm ml-1 my-1 alignToTitle" href="javascript:;" onclick="load_submitted_assignment_result('<?= $course_id; ?>', '<?= $assignment['assignment_id']; ?>')"> <i class="far fa-file-alt"></i> <?= site_phrase('view_result'); ?></a>
					    			</div>
	    			    		<?php } ?>
				    		<?php } else if($expire_time >= $today_time && $expire_date >= $today_date) { ?>
				    			<div class="col-md-4">
				    				<a class="btn btn-outline-primary btn-rounded btn-sm ml-1 my-1 alignToTitle" href="javascript:;" onclick="load_assignment_submit_form('<?= $course_id; ?>', '<?= $assignment['assignment_id']; ?>')"> <i class="far fa-edit"></i> <?= site_phrase('submit_assignment'); ?></a>
				    			</div>
				    		<?php } ?>
	    			    </div>
		    			
		    			<div class="description text-left view-field mb-3">
		    				<?php echo htmlspecialchars_decode($assignment['questions']); ?>
		    			</div>
		    			<?php if($assignment['question_file'] != "") { ?>
	                        <div class="col-md-6 pb-2">
	                            <div>
	                                <?php echo get_phrase('download_attached_file:'); ?>
	                            </div>
	                            <a href="<?php echo site_url('uploads/assignment_files/assignments/'.$assignment['question_file']); ?>" download class="btn-outline-primary"> <?php echo $assignment['question_file']; ?> </a>
	                        </div>
	                    <?php } ?> 
		    			<div class="mb-3">
	                        <p><?php echo get_phrase('deadline:')." ".date('d M Y', $assignment['deadline_date'])." -"; ; ?> <small class="pl-3 text-muted text-12"><i class="text-dark far fa-clock"></i> <?php echo date('h:i A', $assignment['deadline_time']); ?></small></p>
	                    </div>
	                    <hr>
	                    <div class="row">
	                    	<div class="col-md-8">
	                    		<?php echo get_phrase('status: '); ?>
	                    		<?php if($expire_time < $today_time && $expire_date <= $today_date) { ?>
	                    			<span class="badge bg-danger"><?php echo get_phrase('expired'); ?></span>
	                    		<?php } else if($expire_time > $today_time && $expire_date >= $today_date) { ?>
				                    <span class="badge bg-success"><?php echo get_phrase('active'); ?></span>
				                <?php } ?>
		                    </div>
	                    	<div class="col-md-4">
	                    		<?php echo get_phrase('total_marks:')." ".get_phrase($assignment['total_marks']); ?>
	                    	</div>
	                    </div>
		    		</div>
		    	</div>
		    <?php } ?>
		<?php endforeach; ?>
	</div>
</div>
<script type="text/javascript">
	'use strict';

	function load_course_assignments(course_id){
		$('.remove-active').removeClass('active');
		$('#assignment_tab').addClass('active');

		$('#assignment-content').hide();
		$('#show_assignments').show();
		$.ajax({
			url: '<?= site_url('addons/assignment/load_assignments_with_ajax/'); ?>'+course_id,
			success: function(response){
				setTimeout(function(){
					$('#show_assignments').hide();
					$('#assignment-content').show();
					$('#assignment-content').html(response);
				},200);
			}
  		});
	}

	function load_assignment_submit_form(course_id, assignment_id){
		$.ajax({
			url: '<?= site_url('addons/assignment/assignment_submit_form/'); ?>'+course_id+'/'+assignment_id,
			success: function(response){
				$('#assignment-content').html(response);
			}
  		});
	}

	function load_submitted_assignment_result(course_id, assignment_id){
		$.ajax({
			url: '<?= site_url('addons/assignment/submitted_assignment_result/'); ?>'+course_id+'/'+assignment_id,
			success: function(response){
				$('#assignment-content').html(response);
			}
  		});
	}

</script>



