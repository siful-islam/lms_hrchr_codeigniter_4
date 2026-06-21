<?php

$db = \Config\Database::connect();
$addon_details = $db->table('addons')->where(array('id' => $param2))->get()->getRowArray();
?>
<h5 class="mt-0"><?php echo $addon_details['name']; ?></h5>
<p><?php echo $addon_details['about']; ?></p>
<p><?php echo get_phrase('for_more_details_check_out_our'); ?> <a href="http://academy-lms.com/" target="_blank"><?php echo get_phrase('website'); ?></a> </p>


