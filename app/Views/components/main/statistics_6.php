<?php

$db = \Config\Database::connect();
if (!isset($all_students)) {
    $all_students = $db->table('users')->where(['role_id !=' => 1])->get();
}
if (!isset($all_instructor)) {
    $all_instructor = $db->table('users')->where(['is_instructor' => 1])->get();
}
?>
<!-- Start Counter -->
<section class="pb-110 wow animate__animated  animate__fadeInUp " data-wow-duration="1000" data-wow-delay="500">
  <div class="container">
    <div class="counter-6">
      <div class="item">
        <h4 class="title"><?php echo nice_number($all_students->getNumRows()); ?><span>+</span></h4>
        <p class="info"><?php echo get_phrase('Happy Student') ?></p>
      </div>
      <div class="item">
        <h4 class="title"><?php echo nice_number($all_instructor->getNumRows()); ?><span>+</span></h4>
        <p class="info"><?php echo get_phrase('Quality Educators') ?></p>
      </div>

      <?php
        $premium_course = $db->table('course')->where(['status' => 'active', 'is_free_course' => null])->get()->getNumRows();
        $free_course = $db->table('course')->where(['status' => 'active', 'is_free_course' => 1])->get()->getNumRows();
      ?>
      <div class="item">
        <h4 class="title"><?php echo nice_number($premium_course); ?><span>+</span></h4>
        <p class="info"><?php echo get_phrase('Premium Courses') ?></p>
      </div>
      <div class="item">
        <h4 class="title"><?php echo nice_number($free_course); ?><span>+</span></h4>
        <p class="info"><?php echo get_phrase('Cost-free course') ?></p>
      </div>
    </div>
  </div>
</section>
<!-- End Counter -->

