<div class="row w-100">
    <div class="col-md-6">
        <h4 class="header-title my-1"><?php 
$db = \Config\Database::connect();
echo get_phrase('assignment_reviewing_form'); ?></h4>
    </div>
    <div class="col-md-6">
        <a href="javascript:;" onclick="load_submitted_assignment('<?php echo $submitted_answer['assignment_id']; ?>', '<?php echo $course_id; ?>');" class="alignToTitle btn btn-outline-secondary btn-rounded btn-sm my-1"> <i class=" mdi mdi-keyboard-backspace"></i> <?php echo get_phrase('back_to_assignment_submission_list'); ?></a>
    </div>
</div>
<div class="row">
    <div class="col-xl-4">
        <div class="row alert-info pb-1 pt-1 mx-0">
            <?php 
            $db->where('assignment_id', $submitted_answer['assignment_id']);
            $data = $db->table('assignment')->get()->getRowArray();
            ?>
            <div class="col-md-12 pt-2">
                <h4 for="questions"> <?php echo get_phrase('your_questions:'); ?> </h4>
                <div>
                    <?php echo htmlspecialchars_decode($data['questions']); ?>
                </div>
            </div>
            <div class="col-md-12 pt-2 pb-2">
                <div>
                    <h5> <?php echo get_phrase('download_question_file:'); ?> </h5> 
                </div>
                <a href="<?php echo site_url('uploads/assignment_files/assignments/'.$data['question_file']); ?>" download> <?php echo $data['question_file']; ?> </a>
            </div>    
        </div>
    </div>
    <div class="col-xl-8">
        <div class="row alert alert-info pb-1 pt-1 mx-0">
            <div class="col-md-12 pt-2">
                <h4 for="answers"> <?php echo get_phrase("student's_submission:"); ?> </h4>
                <label><?php echo get_phrase('submitted_by: '); ?> </label>
                <?php 
                $db->where('id', $submitted_answer['user_id']);
                $query=$db->table('users')->get();  
                if($query->getNumRows()>0)
                {
                    echo $query->getRow()->first_name." ".$query->getRow()->last_name;
                } ?>
            </div> 
        </div>
        <div class="row alert alert-info pb-1 pt-1 mx-0">
            <div class="col-md-12 pt-2">
                <h5 for="answers"> <?php echo get_phrase("answers:"); ?> </h5>
                <div>
                    <?php echo htmlspecialchars_decode($submitted_answer['answer']); ?>
                </div>
            </div> 
            <div class="col-md-12 pt-2 pb-2">
                <div>
                    <h5> <?php echo get_phrase('download_answer_file:'); ?> </h5>     
                </div>
                <a href="<?php echo site_url('uploads/assignment_files/submitted_answers/'.$submitted_answer['answer_file']); ?>" download> <?php echo $submitted_answer['answer_file']; ?> </a>
            </div>    
        </div>
        <div class="row alert alert-info pb-1 pt-1 mx-0">
            <div class="col-md-12">
                <form class="required-form" action="javascript:;">

                    <div>
                        <h5> <?php echo get_phrase('provide_your_assessment:'); ?> </h5>     
                    </div>

                    <div class="row">
                        <div class="form-group col-md-7">
                            <?php
                            $db->where('assignment_id', $submitted_answer['assignment_id']);
                            $query = $db->table('assignment')->get()->getRowArray();
                            ?>
                            <label for="marks"><?php echo get_phrase('marks ( out_of '); ?> <?php echo $query['total_marks']; ?> <?php echo ')' ?> <span class="required">*</span> </label>
                            <input class="form-control" name="marks" id="marks" type="number" min="0" max="<?php echo $query['total_marks'] ?>" value="<?= $submitted_answer['marks']; ?>">
                        </div>

                    </div>

                    <div class="row">
                        <div class="form-group col-md-7">
                            <label for="remarks"><?php echo get_phrase('remarks'); ?></label>
                            <input class="form-control" name="remarks" id="remarks" type="text" value="<?= $submitted_answer['remarks']; ?>">
                        </div>
                    </div>

                    <div class="form-group pt-2">
                        <button type="button" class="btn btn-success" onclick="update_mark(this, '<?php echo $submitted_answer['submission_id']; ?>', '<?php echo $submitted_answer['assignment_id']; ?>', '<?php $course_id ?>')"><?php echo get_phrase('submit'); ?></button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

