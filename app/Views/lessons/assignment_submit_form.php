<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <h5 class="header-title my-1 text-16"><?php 
$db = \Config\Database::connect();
echo get_phrase('submit_assignment_form'); ?></h5>
                    </div>
                    <div class="col-md-5">
                        <a class="btn btn-outline-primary btn-rounded my-1 text-16" href="javascript:;" onclick="load_course_assignments('<?= $course_id; ?>')"> <i class="fas fa-arrow-left"></i> <?php echo get_phrase('back_to_assignment_list'); ?></a>
                    </div>
                </div>

                <?php
                $db->where('assignment_id', $assignment_id);
                $assignment = $db->table('assignment')->get()->getRowArray(); 
                ?>

                <div class="col">
                    <div class="col-md-6">
                        <h6 class="header-title my-1"><?php echo get_phrase('questions:'); ?></h6>
                    </div>

                    <div class="col-md-6 pt-2">
                        <div>
                            <?php echo htmlspecialchars_decode($assignment['questions']); ?>
                        </div>
                    </div>
                    <?php if($assignment['question_file'] != "") { ?>
                        <div class="col-md-6 pt-2 pb-2">
                            <div>
                                <?php echo get_phrase('download_attached_file:'); ?>
                            </div>
                            <a href="<?php echo site_url('uploads/assignment_files/assignments/'.$assignment['question_file']); ?>" download class="btn-outline-primary"> <?php echo $assignment['question_file']; ?> </a>
                        </div>
                    <?php } ?>  
                </div>

                <form class="ajaxForm" action="<?= site_url('addons/assignment/submit_assignment'); ?>" enctype="multipart/form-data" method="post">
                    
                    <div class="col mb-3">
                        <label class="col-md-2 col-form-label" for="answer"><?php echo get_phrase('answer'); ?> <span class="required">*</span> </label>
                        <div class="col-md-11">
                            <textarea class="form-control author-answer-editor" name = "answer" id="answer"></textarea>
                        </div>
                    </div>

                    <div class="col mb-3">
                        <label class="col-md-4 col-form-label" for="answer_file"><?php echo get_phrase('upload_file')." "."(optional)"; ?></label>
                        <div class="col-md-11 input-group ">
                            <div class="custom-file">
                                <input type="file" name="answer_file" class="custom-file-input" id="inputGroupFile04" onchange="changeTitleOfImageUploader(this)">
                            </div>
                        </div>
                    </div>

                    <div class="col mb-3">
                        <label class="col-md-6 col-form-label" for="note"><?php echo get_phrase('enter_your_private_note')." "."(optional)"; ?></label>
                        <div class="col-md-11">
                            <textarea name="note" id = "note" class="form-control"></textarea>
                        </div>
                    </div>

                    <div>
                        <input type="hidden" name="assignment_id" id="assignment_id" value="<?php echo $assignment_id;?>" class="form-field" Placeholder="assignment_id">
                    </div>
                    <div>
                        <input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id;?>" class="form-field" Placeholder="course_id">
                    </div>


                    <button class="btn btn-primary border-5-info mt-4 px-5 float-right" type="submit"><?= site_phrase('submit'); ?>
                    </button>

                </form>

            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
<!-- end row-->

<script type="text/javascript">
    $(function() {
        //tinymce editor
        if ($(".author-answer-editor")[0]) {
            tinymce.init({
                selector: ".author-answer-editor",
                menubar: false,
                statusbar: false,
                branding: false,
                toolbar: "bold  italic",
            });
        }
    });
    $(function() {
        //The form of submission to RailTeam js is defined here.(Form class or ID)
        $('.ajaxForm').ajaxForm({
            beforeSend: function() {
            },
            uploadProgress: function(event, position, total, percentComplete) {
                var percentVal = percentComplete + '%';
            },
            complete: function(xhr) {
                var jsonResponse = JSON.parse(xhr.responseText);


                if(jsonResponse.status == 'error'){
                    toastr.error(jsonResponse.message);
                } else{

                    toastr.success(jsonResponse.message);
                    load_course_assignments('<?= $course_id; ?>');

                }
            },
            error: function()
            {
                //You can write here your js error message
            }
        });
    });

</script>


