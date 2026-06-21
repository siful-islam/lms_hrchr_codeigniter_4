<?php
    
$userModel = new \App\Models\User_model();
$db = \Config\Database::connect();
$system_name = \Config\Database::connect()->table('settings')->where(array('key'=>'system_name'))->get()->getRow()->value;
    $system_title = \Config\Database::connect()->table('settings')->where(array('key'=>'system_title'))->get()->getRow()->value;
    $user_details = $userModel->get_all_user(session()->get('user_id'))->getRowArray();
    $text_align     = \Config\Database::connect()->table('settings')->where(array('key' => 'text_align'))->get()->getRow()->value;
    $logged_in_user_role = strtolower(session()->get('role'));
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo get_phrase($page_title); ?> | <?php echo $system_title; ?></title>
    <!-- all the meta tags -->
    <?php include 'metas.php'; ?>
    <!-- all the css files -->
    <?php include 'includes_top.php'; ?>
</head>
<body data-layout="detached">
    <!-- HEADER -->
    <?php include 'header.php'; ?>
    <div class="container-fluid">
        <div class="wrapper">
            <!-- BEGIN CONTENT -->
            <!-- SIDEBAR -->
            <?php include $logged_in_user_role.'/'.'navigation.php' ?>
            <!-- PAGE CONTAINER-->
            <div class="content-page">
                <div class="content">
                    <!-- BEGIN PlACE PAGE CONTENT HERE -->
                    <?php include $logged_in_user_role.'/'.$page_name.'.php';?>
                    <!-- END PLACE PAGE CONTENT HERE -->
                </div>
            </div>
            <!-- END CONTENT -->
        </div>
    </div>
    <!-- all the js files -->
    <?php include 'includes_bottom.php'; ?>
    <?php include 'modal.php'; ?>
    <?php include 'common_scripts.php'; ?>
	 <?php include 'rightbar.php'; ?>
</body>
</html>


