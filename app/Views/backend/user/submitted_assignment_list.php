<div class="row w-100">
    <div class="col-xl-12">
        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-3 header-title"><?php 
$db = \Config\Database::connect();
echo get_phrase('submitted_assignment_table'); ?></h4>
            </div>
            <div class="col-md-6">
                <a href="javascript:;" onclick="load_assignment();" class="alignToTitle btn btn-outline-secondary btn-rounded btn-sm my-1"> <i class=" mdi mdi-keyboard-backspace"></i> <?php echo get_phrase('back_to_assignment_list'); ?></a>
            </div>
        </div>
        <div class="table-responsive-sm mt-4">
            <table id="basic-datatable" class="table table-striped dt-responsive nowrap dataTable no-footer dtr-inline collapsed">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo get_phrase('student'); ?></th>
                        <th><?php echo get_phrase('marks'); ?></th>
                        <th><?php echo get_phrase('status'); ?></th>
                        <th><?php echo get_phrase('action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                   <?php foreach ($submitted_assignments as $key => $assignment) : ?>
                        <tr>
                            <td><?php echo ++$key; ?></td>
                            
                            <td>
                                <?php 
                                $db->where('id', $assignment['user_id']);
                                $query=$db->table('users')->get();  
                                if($query->getNumRows()>0)
                                {
                                    echo $query->getRow()->first_name." ".$query->getRow()->last_name;
                                } ?>
                            </td>
                            
                            <td>
                                <?php if($assignment['marks'] != "") { ?>
                                    <?php echo $assignment['marks']; ?>
                                <?php } else { ?>
                                    <span class="badge badge-info"><?php echo get_phrase('pending'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php  if($assignment['review_status'] == 'reviewed') { ?>
                                    <span class="badge badge-success"><?php echo get_phrase('reviewed'); ?></span>
                                <?php } else { ?>
                                    <span class="badge badge-info"><?php echo get_phrase('pending'); ?></span>
                                <?php } ?>
                                
                            </td>
                            <td>
                                <?php if($assignment['review_status'] == 'pending') { ?>
                                    <a href="javascript:;" onclick="view_answer('<?php echo $assignment['submission_id']; ?>', '<?php $course_id ?>');" class="btn btn-outline-secondary btn-rounded btn-sm my-1"> <?php echo get_phrase('view_submission'); ?></a>
                                <?php } else { ?>
                                    <a href="javascript:;" onclick="view_answer('<?php echo $assignment['submission_id']; ?>', '<?php $course_id ?>');" class="btn btn-outline-secondary btn-rounded btn-sm my-1"> <?php echo get_phrase('review'); ?></a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($assignments->getNumRows() == 0): ?>
                <div class="img-fluid w-100 text-center">
                  <img width="100px" src="<?php echo base_url('assets/backend/images/file-search.svg'); ?>"><br>
                  <?php echo get_phrase('no_data_found'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div><!-- end col-->
</div>

