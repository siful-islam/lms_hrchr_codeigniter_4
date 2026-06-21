<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <h5 class="header-title my-1 text-16"><?php 
$db = \Config\Database::connect();
echo get_phrase('submitted_assignment_result'); ?></h5>
                    </div>
                    <div class="col-md-5">
                        <a class="btn btn-outline-primary btn-rounded my-1 text-16" href="javascript:;" onclick="load_course_assignments('<?= $course_id; ?>')"> <i class="fas fa-arrow-left"></i> <?php echo get_phrase('back_to_assignment_list'); ?></a>
                    </div>
                </div>

                <?php if($results != "") { ?>

                    <div class="col">
                        <div class="col-md-6">
                            <h6 class="header-title my-1"><?php echo get_phrase('results:'); ?></h6>
                        </div>
                        <div class="row pt-2">
                            <div class="col-md-3">
                                <?php echo get_phrase('student_name: ');?>
                            </div>
                            <div class="col-md-8">
                                <?php 
                                $db->where('id', $results['user_id']);
                                $query=$db->table('users')->get();  
                                if($query->getNumRows()>0)
                                {
                                    echo $query->getRow()->first_name." ".$query->getRow()->last_name;
                                } ?>
                            </div>
                        </div>
                        <?php if($results['review_status'] == 'reviewed') { ?>
                            <div class="row pt-2">
                                <div class="col-md-3">
                                    <?php echo get_phrase('marks: ');?>
                                </div>
                                <div class="col-md-8">
                                    <?php echo $results['marks']; ?>
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="col-md-3">
                                    <?php echo get_phrase('remarks: ');?>
                                </div>
                                <div class="col-md-8">
                                    <?php echo get_phrase($results['remarks']); ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="row pt-2">
                                <div class="col-md-3">
                                    <?php echo get_phrase('status: ');?>
                                </div>
                                <div class="col-md-8">
                                    <span class="badge bg-warning"><?php echo get_phrase($results['review_status']); ?></span>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                <?php } else { ?>

                    <div class="row pt-2">
                        <div class="col-md-10">
                            <?php echo get_phrase("you_haven't_submitted_the_assignment.");?>
                        </div>
                    </div>
                <?php } ?>

            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
<!-- end row-->

