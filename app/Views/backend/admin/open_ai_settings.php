<?php
	$open_ai_settings = get_settings('open_ai', true);

	if($open_ai_settings['model'] == 'text-davinci-003'){
		$max_tokens = 4000;
	}else{
		$max_tokens = 2048;
	}
?>

<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-play-protected-content title_icon"></i> <?php echo get_phrase('open_ai_settings'); ?>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row">
	<div class="col-lg-7">
		<div class="card">
			<div class="card-body">
				<h3 class="mb-3 header-title"><?php echo get_phrase('manage_your_open_ai_settings'); ?></h3>
				<form action="<?php echo site_url('admin/open_ai_settings/update'); ?>" method="post">

					<div class="form-group">
						<label for=""><?php echo get_phrase('select_ai_model'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="model" onchange="max_tokes(this.value)" required>
							<option value="gpt-3.5-turbo-0125" <?php if($open_ai_settings['model'] == 'gpt-3.5-turbo-0125') echo 'selected'; ?>>gpt-3.5-turbo-0125</option>
							<option value="gpt-4-0125-preview" <?php if($open_ai_settings['model'] == 'gpt-4-0125-preview') echo 'selected'; ?>>gpt-4-0125-preview (<?php echo get_phrase('Required Premium Account'); ?>)</option>
						</select>
					</div>

					<div class="form-group">
						<label for="max_tokens"><?php echo get_phrase('max_tokens'); ?></label>
						<input class="form-control" type="number" id="max_tokens" value="<?php echo $open_ai_settings['max_tokens'] ?>" name="max_tokens" min="20" max="<?php echo $max_tokens; ?>" required>
					</div>

					<div class="form-group">
						<label for="number_of_image_creation"><?php echo get_phrase('number_of_image_creation'); ?></label>
						<input class="form-control" type="number" id="number_of_image_creation" value="<?php echo $open_ai_settings['number_of_image_creation'] ?>" name="number_of_image_creation" min="1" max="50" required>
					</div>
					
					<div class="form-group">
						<label for="ai_secret_key"><?php echo get_phrase('secret_key'); ?></label>
						<input class="form-control" type="text" id="ai_secret_key" value="<?php echo $open_ai_settings['ai_secret_key'] ?>" name="ai_secret_key" required>
					</div>

                    <div class="form-group">
                    	<button type="submit" class="btn btn-primary"><?php echo get_phrase('save_changes'); ?></button>
                    </div>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	"Use strict";
	
	function max_tokes(model){
		if(model == 'text-davinci-003'){
			$('#max_tokens').attr("max", "4000");
		}else{
			$('#max_tokens').attr("max", "2048");
		}
	}
</script>

