<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="card">
    <div class="card-body">
        <div class="row justify-content-center">
            <div class="col-md-10">
            	<form action="<?= site_url('addons/customer_support/add_support_macro'); ?>" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h4><?= get_phrase('macro_add_form'); ?></h4>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="macro_title"><?php echo get_phrase('title'); ?> <span class="required">*</span> </label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="macro_title" name = "title" placeholder="<?php echo get_phrase('support_macro_title'); ?>" required>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="macro_description"><?php echo get_phrase('description'); ?> <span class="required">*</span> </label>
                        <div class="col-md-9">
                        	<textarea class="macro_description form-control" rows="8" name="description"></textarea>
                         </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary float-right"><?= get_phrase('create_macro'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

