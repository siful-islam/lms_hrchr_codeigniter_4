<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php 
$customerSupportModel = new \App\Models\addons\Customer_support_model();
echo $page_title; ?></h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
<div class="card">
  <div class="card-body">
    <?php $number_of_opened_tickets = $customerSupportModel->get_tickets_by_status('opened')->getNumRows(); ?>
    <?php $number_of_closed_tickets = $customerSupportModel->get_tickets_by_status('closed')->getNumRows(); ?>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a href="<?php echo site_url('addons/customer_support/tickets/opened') ?>" class="nav-link <?php if($status == 'opened') echo 'active'; ?>">
                <i class="mdi mdi-home-variant d-lg-none d-block mr-1"></i>
                <span class="d-none d-lg-block"><?php echo get_phrase('opened').' ('.$number_of_opened_tickets.')'; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo site_url('addons/customer_support/tickets/closed') ?>" class="nav-link <?php if($status == 'closed') echo 'active'; ?>">
                <i class="mdi mdi-account-circle d-lg-none d-block mr-1"></i>
                <span class="d-none d-lg-block"><?php echo get_phrase('closed').' ('.$number_of_closed_tickets.')'; ?></span>
            </a>
        </li>
    </ul>
    <?php include $status."_tickets.php"; ?>
    
  </div>
</div>

