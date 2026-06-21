<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('macros'); ?>
                  <a href="<?php echo site_url('addons/customer_support/add_support_macro_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('add_new_macro'); ?></a>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="card">
    <div class="card-body">
    	<div class="row">
	        <div class="col-sm-3 mb-2 mb-sm-0">
	            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
	            	<?php foreach ($support_macros->getResultArray() as $key => $macro) { ?>

	                <a class="nav-link px-2 <?php if($key == 0) echo 'active show'; ?>" id="v-pills-home-tab-<?php echo $key; ?>" data-toggle="pill" href="#v-pills-<?php echo $key; ?>" role="tab" aria-controls="v-pills-home"
	                    aria-selected="true">
	                    <i class="mdi mdi-home-variant d-lg-none d-block mr-1"></i>
	                    <span class="d-none d-lg-block"><?php echo $macro['title']; ?></span>
	                </a>
	               <?php } ?>
	            </div>
	        </div> <!-- end col-->

	        <div class="col-sm-9">
	            <div class="tab-content" >
	            	<?php foreach ($support_macros->getResultArray() as $key => $macro) { ?>
		                <div class="tab-pane fade <?php if($key == 0) echo 'active show'; ?>" id="v-pills-<?php echo $key; ?>" role="tabpanel" aria-labelledby="v-pills-home-tab-<?php echo $key; ?>">
		                	<form action="<?= site_url('addons/customer_support/update_support_macro/'.$macro['id']); ?>" method="post" enctype="multipart/form-data">
		                		<div class="form-group">
		                			<label><?php echo get_phrase('title'); ?></label>
		                			<input class="form-control" type="text" name="title" value="<?php echo $macro['title']; ?>">
		                		</div>
		                		<div class="form-group">
		                			<textarea class="macro_description form-control" rows="8" name="description"><?php echo $macro['description']; ?></textarea>
		                		</div>
		               
		                		<div class="form-group mt-3">
        							<button class="btn btn-primary float-right" type="submit"><?= get_phrase('update_macro'); ?>
        							</button>
        							<button class="btn btn-danger float-right mr-2" type="button" onclick="confirm_modal('<?php echo site_url('addons/customer_support/delete_support_macro/'.$macro['id']); ?>');"><?= get_phrase('delete_macro'); ?>
        							</button>
    							</div>
		                	</form>
		                </div>
		            <?php } ?>
	            </div> <!-- end tab-content-->
	        </div> <!-- end col-->
	    </div>
	</div>
</div>

