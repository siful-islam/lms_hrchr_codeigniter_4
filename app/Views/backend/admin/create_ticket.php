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
        <div class="row justify-content-center">
            <div class="col-md-10">
                <form action="<?= site_url('addons/customer_support/add_support_ticket'); ?>" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h4><?= get_phrase('ticket_form'); ?></h4>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="code"><?php echo get_phrase('ticket_code'); ?></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="code" name = "code" value="<?php echo substr(rand(500000, 1000000), 0, 6); ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="ticket_title"><?php echo get_phrase('title'); ?> <span class="required">*</span> </label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="ticket_title" name = "title" placeholder="<?php echo get_phrase('create_ticket_title'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="select_support_categories"><?php echo get_phrase('select_category_type'); ?><span class="required">*</span></label>
                        <div class="col-md-9">   
                            <select class="form-control select2" data-toggle="select2" name="category_id" id="category_id" required>
                                <option value=""><?php echo get_phrase('select_a_category'); ?></option>
                                <?php $categories = $customerSupportModel->get_support_categories($category['id'])->getResultArray(); 
                                    foreach ($categories as $category): ?>
                                        <?php if($category['status'] == 'active'): ?>
                                            <option value="<?php echo $category['id']; ?>"><?php echo $category['title']; ?></option>
                                        <?php endif; ?>
                              <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="user_id"><?php echo get_phrase('user'); ?><span class="required">*</span> </label>
                        <div class="col-md-9">
                            <select class="form-control select2" data-toggle="select2" name="user_id" id="user_id" required>
                                <option value=""><?php echo get_phrase('select_a_user'); ?></option>
                                <?php $user_list = $userModel->get_user()->getResultArray();
                                    foreach ($user_list as $user):?>
                                    <option value="<?php echo $user['id'] ?>"><?php echo $user['first_name'].' '.$user['last_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="priority"><?php echo get_phrase('priority'); ?></label>
                        <div class="col-md-9">
                            <select class="form-control select2" data-toggle="select2" name="priority" id="priority">
                                <option value="low"><?php echo get_phrase('low'); ?></option>
                                <option value="medium"><?php echo get_phrase('medium'); ?></option>
                                <option value="high"><?php echo get_phrase('high'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="description"><?php echo get_phrase('descrption'); ?></label>
                        <div class="col-md-9">
                            <textarea name="description" id = "description" class="form-control" rows="8"></textarea>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="file"><?php echo get_phrase('select_file'); ?></label>
                        <div class="input-group col-md-9">
                            <div class="custom-file">
                                <input type="file" name="support_file" class="custom-file-input" id="inputGroupFile04" onchange="changeTitleOfImageUploader(this)">
                                <label class="custom-file-label" for="inputGroupFile04"><?php echo get_phrase('choose_file'); ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary float-right"><?= get_phrase('submit_support_ticket'); ?></button>
                    </div>
                </form>
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
</div>

