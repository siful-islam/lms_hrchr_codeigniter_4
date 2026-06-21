
<div class="tab-content">
  <div class="tab-pane <?php 

$customerSupportModel = new \App\Models\addons\Customer_support_model();
$userModel = new \App\Models\User_model();
if($status == 'opened') echo 'show active'; ?>" id="opened">
    <div class="table-responsive-sm mt-4">
      <table class="table table-striped table-centered mb-0">
          <thead>
            <tr>
              <th><?php echo get_phrase('ticket_id'); ?></th>
              <th><?php echo get_phrase('title'); ?></th>
              <th><?php echo get_phrase('category'); ?></th>
              <th><?php echo get_phrase('user_name'); ?></th>
              <th><?php echo get_phrase('priority'); ?></th>
              <th><?php echo get_phrase('action'); ?></th>
            </tr>
          </thead>
          <tbody>
              <?php
               foreach ($tickets->getResultArray() as $key => $ticket): ?>
                <?php if($ticket['status'] == 'opened') { ?>
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
                      <?php if($ticket['priority'] == 'high') { ?>
                          <span class="badge badge-danger"><?php echo get_phrase('high'); ?></span>
                      <?php } else if($ticket['priority'] == 'medium') { ?>
                          <span class="badge badge-info"><?php echo get_phrase('medium'); ?></span>
                      <?php } else { ?>
                          <span class="badge badge-light"><?php echo get_phrase('low'); ?></span>
                      <?php } ?>
                          
                    </td>
                    <td>
                        <div class="dropright dropright">
                          <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i class="mdi mdi-dots-vertical"></i>
                          </button>
                          <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="<?php echo site_url('addons/customer_support/view_ticket/'.$ticket['code']) ?>"><?php echo get_phrase('view_ticket'); ?></a></li>
                              <?php if($ticket['status'] == 'opened') { ?>
                                  <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('addons/customer_support/change_status/closed/'.$ticket['id']); ?>');"><?php echo get_phrase('ticket_close'); ?></a></li>
                              <?php } else { ?>
                                  <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('addons/customer_support/change_status/opened/'.$ticket['id']); ?>');"><?php echo get_phrase('ticket_open'); ?></a></li>
                              <?php } ?>
                              
                              <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('addons/customer_support/delete_ticket/'.$ticket['id'].'/opened'); ?>');"><?php echo get_phrase('delete'); ?></a></li>
                          </ul>
                      </div>
                    </td>
                  </tr>
                <?php } ?>
              <?php endforeach; ?>
          </tbody>
      </table>
    </div>
  </div>
</div>

