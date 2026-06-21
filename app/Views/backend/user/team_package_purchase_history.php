<!-- start page title -->
<div class="row ">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-body">
        <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php 
$db = \Config\Database::connect();
echo get_phrase('purchase_history'); ?></h4>
      </div> <!-- end card body-->
    </div> <!-- end card -->
  </div><!-- end col-->
</div>

<div class="card">
  <div class="card-body">
    <table id="basic-datatable" class="table table-striped table-centered mb-0">
      <thead>
        <tr>
          <th><?php echo get_phrase('user'); ?></th>
          <th><?php echo get_phrase('package'); ?></th>
          <th><?php echo get_phrase('paid_amount'); ?></th>
          <th><?php echo get_phrase('payment_method'); ?></th>
          <th><?php echo get_phrase('purchased_date'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($purchase_history as $purchase) :
          $user_data = $db->table('users')->where(array('id' => $purchase['user_id']))->get()->getRowArray();
          $course_data = $db->table('course')->where(array('id' => $purchase['course_id']))->get()->getRowArray(); ?>
          <tr class="gradeU">
            <td>
              <?php echo $user_data['first_name'] . ' ' . $user_data['last_name']; ?><br>
              <small class="badge badge-light"><?php echo $user_data['email']; ?></small>
            </td>
            <td>
              <!-- Add tooltip to the package title -->
              <span data-toggle="tooltip" data-placement="right" title="<?php echo get_phrase('package_course') . ': ' . $course_data['title']; ?>">
                <?php echo $purchase['title']; ?>
              </span>
            </td>
            <td>
              <?php echo currency($purchase['paid_amount']); ?><br>
            </td>
            <td><?php echo ucfirst($purchase['payment_method']); ?></td>
            <td><?php echo date('D, d-M-Y', $purchase['date_added']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

