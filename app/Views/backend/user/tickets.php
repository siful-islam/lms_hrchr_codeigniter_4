<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php 

$customerSupportModel = new \App\Models\addons\Customer_support_model();
$userModel = new \App\Models\User_model();
echo $page_title; ?></h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
<div class="card">
  <div class="card-body">
    <div class="table-responsive-sm mt-4">
        <table class="table table-striped table-centered mb-0">
          <thead>
            <tr> 
              <th><?php echo get_phrase('ticket_id'); ?></th>
              <th><?php echo get_phrase('title'); ?></th>
              <th><?php echo get_phrase('category'); ?></th>
              <th><?php echo get_phrase('user_name'); ?></th>
              <th><?php echo get_phrase('status'); ?></th>
              <th><?php echo get_phrase('priority'); ?></th>
            </tr>
          </thead>
          <tbody>
              <?php foreach ($tickets->getResultArray() as $key => $ticket): ?>
                <tr>
                  <td><?php echo $ticket['code']; ?></td>
                  <td>
                    <a href="<?php echo site_url('addons/customer_support/view_ticket/'.$ticket['code']) ?>"><?php echo $ticket['title']; ?></a>
                    <p><span class="text-muted text-12"><?php echo date('d M Y', $ticket['date']); ?></span></p>
                  </td>
                  <td>
                    <?php echo $customerSupportModel->get_support_categories($ticket['category_id'])->getRow()->title; ?>
                  </td>
                  <td>
                     <?php $user = $userModel->get_all_user($ticket['user_id'])->getRowArray(); ?>
                     <?php echo $user['first_name'].' '.$user['last_name']; ?>
                  </td>

                  <td>
                    <?php if($ticket['status'] == 'opened') { ?>
                        <span class="badge badge-danger"><?php echo get_phrase('opened'); ?></span>
                    <?php } else { ?>
                        <span class="badge badge-light"><?php echo get_phrase('closed'); ?></span>
                    <?php } ?>
                        
                  </td>
                
                  <td>
                    <?php if($ticket['priority'] == 'high') { ?>
                        <span class="badge badge-danger"><?php echo get_phrase('high'); ?></span>
                    <?php } else if($ticket['priority'] == 'medium') { ?>
                        <span class="badge badge-info"><?php echo get_phrase('medium'); ?></span>
                    <?php } else { ?>
                        <span class="badge badge-light"><?php echo get_phrase('low'); ?></span>
                    <?php } ?>
                        
                  </td>
                </tr>
              <?php endforeach; ?>
          </tbody>
        </table>
        <?php if ($tickets->getNumRows() == 0): ?>
            <div class="img-fluid w-100 text-center">
              <img width="100px" src="<?php echo base_url('assets/backend/images/file-search.svg'); ?>"><br>
              <?php echo get_phrase('no_data_found'); ?>
            </div>
        <?php endif; ?>
    </div>
    
  </div>
</div>

