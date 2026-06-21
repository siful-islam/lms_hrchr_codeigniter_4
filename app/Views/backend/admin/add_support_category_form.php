<form action="<?= site_url('addons/customer_support/add_support_category'); ?>" method="post" enctype="multipart/form-data">
    <div class="row mb-3">
        <div class="col-md-12">
            <h4><?= get_phrase('category_add_form'); ?></h4>
        </div>
    </div>
    <div class="form-group">
        <label for="category_title"><?php echo get_phrase('title'); ?> <span class="required">*</span> </label>
        <input type="text" class="form-control" id="category_title" name = "title" placeholder="<?php echo get_phrase('support_category_title'); ?>" required>
    </div>
    <div class="form-group">
        <button class="btn btn-primary float-right"><?= get_phrase('create_category'); ?></button>
    </div>
</form>

