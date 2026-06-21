<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('categories'); ?>
                  <a href="javascript:;" onclick="showAjaxModal('<?php echo site_url('addons/customer_support/add_support_category_form'); ?>', '<?php echo get_phrase('add_new_category'); ?>')" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('add_new_category'); ?></a>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
<div class="card">
    <div class="card-body">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="table-responsive-sm mt-4">
                <table class="table table-striped table-centered mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th><?php echo get_phrase('title'); ?></th>
                      <th><?php echo get_phrase('status'); ?></th>    
                      <th><?php echo get_phrase('action'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                      <?php
                       foreach ($support_category->getResultArray() as $key => $category): ?>
                          <tr>
                              <td><?php echo $key+1; ?></td>
                              <td><?php echo $category['title']; ?></td>
                              <td>
                                <?php if($category['status'] == 'active') { ?>
                                  <span class="badge badge-danger"><?php echo get_phrase('active'); ?></span>
                                <?php } else { ?>
                                  <span class="badge badge-light"><?php echo get_phrase('inactive'); ?></span>
                                <?php } ?>
                                
                              </td>
                              <td>
                                  <div class="dropright dropright">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">

                                        <li><a class="dropdown-item" href="javascript:;" onclick="showAjaxModal('<?= site_url('addons/customer_support/edit_support_category_form/'.$category['id']); ?>', '<?php echo get_phrase('edit_support_category'); ?>')"><?php echo get_phrase('edit'); ?></a></li>

                                        <?php if($category['status'] == 'active') { ?>
                                          <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('addons/customer_support/change_category_status/inactive/'.$category['id']); ?>');"><?php echo get_phrase('deactivate_category'); ?></a></li>
                                        <?php } else { ?>
                                          <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('addons/customer_support/change_category_status/active/'.$category['id']); ?>');"><?php echo get_phrase('activate_category'); ?></a></li>
                                        <?php } ?>

                                        <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('addons/customer_support/delete_support_category/'.$category['id']); ?>');"><?php echo get_phrase('delete'); ?></a></li>
                                    </ul>
                                </div>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
              </div>
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
</div>

