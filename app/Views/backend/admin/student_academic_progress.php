<style>
.badge{
	color:#150404 !important;
} 
</style>
 <div class="mb-3 d-flex justify-content-between align-items-end">

	<form id="progressFilterForm" class="d-flex align-items-end gap-3" >
	<input type="hidden" name="course_id" value="<?php 
$crudModel = new \App\Models\Crud_model();
$userModel = new \App\Models\User_model();
$db = \Config\Database::connect();
echo $course_details['id']; ?>">
		<div style="width:300px;">
			<label for="company_name"><?php echo get_phrase('company_name'); ?></label>
			<input class="form-control w-100"
       type="text"
       name="company_name"
       id="company_name"
       value="<?php echo html_escape($company_name); ?>">
				 
		</div>
		<div style="width:300px;">
			<label for="progress"><?php echo get_phrase('progress'); ?></label>
			<select class="form-control select2 w-100" name="progress_status" id="progress_status">
				<option value="all" <?php if($progress_status == 'all') echo 'selected'; ?>>All</option>
				<option value="not_started" <?php if($progress_status == 'not_started') echo 'selected'; ?>>Not Started</option>
				<option value="in_progress" <?php if($progress_status == 'in_progress') echo 'selected'; ?>>In Progress</option>
				<option value="completed" <?php if($progress_status == 'completed') echo 'selected'; ?>>Completed</option>
			</select>
		</div>

		<div>
			<button type="submit" class="btn btn-primary">
				<?php echo get_phrase('filter'); ?>
			</button>
		</div>

	</form>

	<div>
		 
		<a href="<?php 
			echo site_url('admin/export_student_progress_csv/' . $course_details['id'] .
				'?company_name=' . urlencode($company_name) .
				'&progress_status=' . urlencode($progress_status)
			); ?>" 
		   class="btn btn-info">
			<?php echo get_phrase('Export CSV'); ?>
		</a>
	</div>

</div>  
<div class="table-responsive">
  <table class="studentAcademicProgress table table-striped table-centered mb-4">
    <thead>
      <tr>
		 <th><?php echo get_phrase('SL'); ?></th>
        <th><?php echo get_phrase('Student'); ?></th>
		 <th><?php echo get_phrase('company_name'); ?></th>
        <th><?php echo get_phrase('Date') ?></th>
        <th><?php echo get_phrase('Progress'); ?></th>
        <th class="text-center"><?php echo get_phrase('Actions'); ?></th>
      </tr> 
    </thead>
	 <?php 
	//$enrolments = $db->where('course_id', $course_details['id'])->get('enrol')->getResultArray();
	
	$db->select('enrol.*, users.first_name, users.last_name, users.email, users.company_name, watch_histories.course_progress, watch_histories.completed_date, watch_histories.date_updated, watch_histories.completed_lesson,watch_histories.downloaded_number');
	$db->from('enrol');
	$db->join('users', 'users.id = enrol.user_id');
	$db->join('watch_histories', 'watch_histories.student_id = enrol.user_id AND watch_histories.course_id = enrol.course_id', 'left');
	$db->where('enrol.course_id', $course_details['id']);


	// ✅ Company Filter
	if (!empty($company_name)) {
		$db->like('users.company_name', $company_name);
	}

	// ✅ Progress Filter
	if (!empty($progress_status) && $progress_status != 'all') {
		if ($progress_status == 'not_started') {
			$db->where('(watch_histories.course_progress IS NULL OR watch_histories.course_progress = 0)');
		}
		elseif ($progress_status == 'in_progress') {
			$db->where('watch_histories.course_progress >', 0);
			$db->where('watch_histories.course_progress <', 100);
		}
		elseif ($progress_status == 'completed') {
			$db->where('watch_histories.course_progress', 100);
		}
	}

	$enrolments = $db->get()->getResultArray();


	?>
    <?php $lessons = $crudModel->get_lessons('course', $course_details['id']); ?>
    <?php $total_lesson = $lessons->getNumRows(); ?>
    <tbody>
      <?php
	  $j=1;
      foreach($enrolments as $enrolment):
        //$student = $userModel->get_all_user($enrolment['user_id'])->getRowArray();
        //$watch_history = $db->where('course_id', $course_details['id'])->where('student_id', $enrolment['user_id'])->get('watch_histories')->getRowArray();
        //$completed_lesson_arr = isset($watch_history['completed_lesson']) ? json_decode($watch_history['completed_lesson'], true) : [];
		$course_progress = $enrolment['course_progress'] ?? 0;						  
		$completed_lesson_arr = json_decode($enrolment['completed_lesson'] ?? '[]', true);
		$completed_lesson = is_array($completed_lesson_arr) ? $completed_lesson_arr : [];
		$date_updated = !empty($enrolment['date_updated']) 
						? date('d M Y, H:i a', $enrolment['date_updated']) 
						: get_phrase('Not started yet');
		$completed_date = !empty($enrolment['completed_date']) 
						? date('d M Y', $enrolment['completed_date']) 
						: get_phrase('Not completed yet');
        ?>
        <tr>
          <td>
             <p class="my-0"><?php echo $j++; ?></p>
             
          </td>
          <td>
			   <p class="my-0"><?php echo @$enrolment['first_name'].' '.@$enrolment['last_name']; ?></p>
            <span class="badge badge-light"><?php echo @$enrolment['email']; ?></span>
          </td>
		  <td>
            <p class="my-0"><?php echo @$enrolment['company_name']; ?></p>
             
          </td>
          <td> 
            <p class="my-0"><b><?php echo get_phrase('Enrolled from'); ?>-</b> <?php echo date('d M Y', $enrolment['date_added']); ?></p>

            <p class="my-0"><b><?php echo get_phrase('last seen on'); ?>-</b> <?php echo $date_updated; ?></p>

            <p class="my-0"><b><?php echo get_phrase('Completed on'); ?>-</b> <?php echo $completed_date; ?></p>
          </td>
          <td>
            <div class="progress <?php echo ($course_progress == 0) ? 'bg-danger' : ''; ?>">
			<?php
				// Decide progress bar color
				if ($course_progress == 100) {
					$progress_class = 'bg-success';        // Green
				} elseif ($course_progress > 0) {
					$progress_class = 'bg-warning';        // Yellow
				} else {
					$progress_class = 'bg-danger';         // Red
				}
				?>

				 
					<div class="progress-bar <?php echo $progress_class; ?>" 
						 role="progressbar"
						 style="width: <?php echo $course_progress; ?>%;"
						 aria-valuenow="<?php echo $course_progress; ?>"
						 aria-valuemin="0" 
						 aria-valuemax="100">
						<?php echo $course_progress; ?>%
					</div> 
            </div>
            <p class="my-0 mt-1">- <?php echo get_phrase('Completed lesson').' '.count($completed_lesson).' '.get_phrase('out of').' '.$total_lesson; ?></p>

            <?php
              $total_watched_duration = 0; //seconds
              $watched_durations = $db->table('watched_duration')->where(['watched_student_id' => $enrolment['user_id'], 'watched_course_id' => $course_details['id']])->get();
              foreach($watched_durations->getResultArray() as $watched_duration){
                $total_watched_duration += count(json_decode($watched_duration['watched_counter'], true))*5;
              }
            ?>

            <p class="my-0">- <?php echo get_phrase('Watched duration').'- <b>'.seconds_to_time_format($total_watched_duration); ?></b></p>

            
            
          </td>
          <td class="text-center">
            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
              <a href="javascript:;" onclick="showLargeModal('<?php echo site_url('admin/student_academic_quiz_result/'.$course_details['id'].'/'.$enrolment['user_id']); ?>', '<?php echo get_phrase('Quiz results'); ?>')" class="btn btn-light cursor-pointer" data-toggle="tooltip" title="<?php echo get_phrase('Quiz results'); ?>"><i class="far fa-address-card"></i></a>

              <?php if(addon_status('certificate')): ?>
                <a href="<?php echo site_url('admin/student_certificate/'.$enrolment['user_id'].'/'.$course_details['id']); ?>" target="_blank" class="btn btn-light cursor-pointer" data-toggle="tooltip" title="<?php echo get_phrase('Certificate'); ?>">
                  <i class="fas fa-graduation-cap"></i>
					<span class="badge badge-pill badge-info ml-1" style="font-size: 0.8rem;" 
							 data-toggle="tooltip" 
							 title="<?php echo get_phrase('Certificate Downloaded').' '.$enrolment['downloaded_number'].' times'; ?>">
						   <?php echo intval($enrolment['downloaded_number']); ?>
					   </span>
					</a> 
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script>
$('#progressFilterForm').on('submit', function(e){
    e.preventDefault();

    var formData = $(this).serialize();
    var course_id = $('input[name="course_id"]').val();

    $('.ajax_loader').addClass('start_ajax_loading');

    $.ajax({
        url: '<?php echo site_url('admin/student_academic_progress/'); ?>' + course_id,
        type: 'GET',
        data: formData,
        success: function(response){
            $('#academic_progress').html(response);
            $('.ajax_loader').removeClass('start_ajax_loading');
        }
    });
});
</script>		 
<script type="text/javascript">
  $('[data-toggle=tooltip]').tooltip();
</script>

