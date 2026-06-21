<form action="<?= site_url('addons/customer_support/change_priority/'.$tickets['id'].'/'.$tickets['code']); ?>" method="post" enctype="multipart/form-data">
    <div class="form-check">
      <input class="form-check-input" type="radio" name="priority" id="flexRadioDefault1" value="low" <?php echo ($tickets['priority'] == 'low') ?  "checked" : "" ;  ?>>
      <label class="form-check-label" for="flexRadioDefault1">
        <?php echo get_phrase('low') ?>
      </label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="priority" id="flexRadioDefault2" value="medium"<?php echo ($tickets['priority'] == 'medium') ?  "checked" : "" ;  ?>>
      <label class="form-check-label" for="flexRadioDefault2">
        <?php echo get_phrase('medium') ?>
      </label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="priority" id="flexRadioDefault3" value="high"<?php echo ($tickets['priority'] == 'high') ?  "checked" : "" ;  ?>>
      <label class="form-check-label" for="flexRadioDefault3">
        <?php echo get_phrase('high') ?>
      </label>
    </div>
    <div class="form-group">
        <button class="btn btn-primary float-right"><?= get_phrase('update_priority'); ?></button>
    </div>
</form>

