<?php 
$db = \Config\Database::connect();
$quizes = $db->orderBy('order', 'asc')->get_where('lesson', ['course_id' => $course_details['id'], 'lesson_type' => 'quiz'])->getResultArray(); ?>
<div class="row">
	<?php foreach($quizes as $key => $quiz): ?>
		<div class="col-md-12 border-bottom pb-2 mt-2">
			<p class="w-100 mb-0 fw-bold">
				<b>Q<?php echo ++$key.'. '; ?></b>
				<span class="d-inline-block float-right"><?php echo get_phrase('Total Marks') ?>: <b><?php echo json_decode($quiz['attachment'])->total_marks; ?></b></span>
			</p>
			<p><?php echo $quiz['title']; ?></p>


			<?php $quiz_results = $db->orderBy('quiz_result_id', 'asc')->where('quiz_id', $quiz['id'])->where('user_id', $student_id)->get('quiz_results'); ?>

			<p class="my-0"><?php echo get_phrase('Total attempts') ?>: <?php echo $quiz_results->getNumRows(); ?></p>
			<p class="my-0">
				<?php echo get_phrase('Obtained Marks of all attempts') ?>:
				<?php foreach($quiz_results->getResultArray() as $rKey => $quiz_result): ?>
					<?php
						++$rKey;
						if($quiz_results->getNumRows() > $rKey){
							echo $quiz_result['total_obtained_marks'].', ';
						}else{
							echo $quiz_result['total_obtained_marks'];
						}
					?>
				<?php endforeach; ?>
			</p>

			<a class="btn btn-primary btn-sm mt-3" href="<?php echo site_url('home/lesson/'.slugify($course_details['title']).'/'.$course_details['id'].'/'.$quiz['id'].'?student_id='.$student_id); ?>" target="_blank"><?php echo get_phrase('Go to answer sheet'); ?></a>
		</div>
	<?php endforeach; ?>
</div>

