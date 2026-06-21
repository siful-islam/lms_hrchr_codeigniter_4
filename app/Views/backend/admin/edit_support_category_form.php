<form action="<?= site_url('addons/customer_support/update_support_category/'.$category['id']); ?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label class="" for="category_title"><?php echo get_phrase('title'); ?> <span class="required">*</span> </label>
        <!--for Delete previews image-->
        <input type="text" class="form-control" id="category_title" value="<?= $category['title']; ?>" name = "title" placeholder="<?php echo get_phrase('support_category_title'); ?>" required>
    </div>
    <div class="form-group">
        <button class="btn btn-primary float-right"><?= get_phrase('update_category'); ?></button>
    </div>
</form>


