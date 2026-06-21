<div class="row">
	<div class="col-md-6">
        <h5 class="header-title my-1"><?php echo get_phrase('assignment_list'); ?></h4>
    </div>
</div>
<?php foreach($assignment_list as $assignment): ?>
	<?php 
    $today_d = date('d M Y');
    $today_t = date('h:i:s a');
    $today_date = strtotime($today_d);
    $today_time = strtotime($today_t);
    $expire_date = $assignment['deadline_date'];
    $expire_time = $assignment['deadline_time'];
    ?>
    <div class="pt-3">
		<div class="alert alert-success pb-1 pt-1" role="alert" id="assignment_del_button_<?php echo $assignment['assignment_id']; ?>">
		    <a class="text"><strong><i class="mdi mdi-clipboard-arrow-right-outline"></i> <?php echo $assignment['title']; ?> </strong></a>
		    <div class="dropright dropright float-right">
			    <button type="button" class="border-0 bg-transparent" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			    	<i class="mdi mdi-dots-vertical"></i>
			    </button>
			    <ul class="dropdown-menu" x-placement="left-start" y-placement="bottom">
		        <li><a class="dropdown-item" href="javascript:;" onclick="showLargeModal('<?php echo site_url('addons/assignment/edit_assignment/'.$assignment['course_id'].'/'.$assignment['assignment_id']); ?>', '<?php echo get_phrase('edit_assignment') ?>')"><?php echo get_phrase('edit'); ?></a></li>

		        <?php if($expire_time >= $today_time && $expire_date >= $today_date) { ?>

		        	<?php if($assignment['status'] == 'active') { ?>
		        		<li><a class="dropdown-item" href="javascript:;" onclick="update_assignment_status('<?php echo 'draft' ?>', '<?php echo $assignment['assignment_id']; ?>', '<?php echo $assignment['course_id']; ?>')"><?php echo get_phrase('mark_as_draft'); ?></a>
		        		</li>
		        	<?php } else { ?>
		        		<li><a class="dropdown-item" href="javascript:;" onclick="update_assignment_status('<?php echo 'active' ?>', '<?php echo $assignment['assignment_id']; ?>', '<?php echo $assignment['course_id']; ?>')"><?php echo get_phrase('mark_as_active'); ?></a>
		        		</li>
		        	<?php } ?>

		        <?php } ?>

		        <li><a class="dropdown-item" href="javascript:;" onclick="load_submitted_assignment('<?php echo $assignment['assignment_id']; ?>', '<?php echo $assignment['course_id'] ?>');"><?php echo get_phrase('view_submission'); ?></a></li>

		        <li><a class="dropdown-item" onclick="ajax_confirm_modal('<?php echo site_url('addons/assignment/assignment_delete/'.$assignment['assignment_id']); ?>', 'assignment_del_button_<?php echo $assignment['assignment_id']; ?>')" href="javascript:;"><?php echo get_phrase('delete'); ?></a></li>
		    </ul>
			</div>
		    <div class="w-100 text-12 text-muted">
		    	<?php echo get_phrase('deadline: '); ?>
		    	<?php echo date(' d M Y', $assignment['deadline_date']) ?>
		    	<?php echo date(', h:i A', $assignment['deadline_time']) ?>
	        <br>
		    	<?php if($expire_date <= $today_date && $expire_time <= $today_time) { ?>
              <span class="badge badge-danger"><?php echo get_phrase('expired'); ?></span>
          <?php } else if($expire_date >= $today_date && $expire_time >= $today_time) { ?>
              <?php if($assignment['status'] == 'active') { ?>
                  <span class="badge badge-success"><?php echo get_phrase('active'); ?></span>
              <?php } else { ?>
                  <span class="badge badge-warning"><?php echo get_phrase('draft'); ?></span>
              <?php } ?>
          <?php } ?>
		    </div>
		</div>
	</div>
<?php endforeach; ?>
<?php if (count($assignment_list) == 0): ?>
    <div class="img-fluid w-100 text-center">
      <img width="100px" src="<?php echo base_url('assets/backend/images/file-search.svg'); ?>"><br>
      <?php echo get_phrase('no_data_found'); ?>
    </div>
<?php endif; ?>

