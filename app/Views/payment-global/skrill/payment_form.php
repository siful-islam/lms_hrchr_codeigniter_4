<?php

$userModel = new \App\Models\User_model();
//start common code of all payment gateway
if($payment_details['is_instructor_payout_user_id'] > 0){
    $instructor_details = $userModel->get_all_user($payment_details['is_instructor_payout_user_id'])->getRowArray();
    $keys = json_decode($instructor_details['payment_keys'], true);
    $keys = $keys[$payment_gateway['identifier']];
}else{
    $keys = json_decode($payment_gateway['keys'], true);
}
$test_mode = $payment_gateway['enabled_test_mode'];
//ended common code of all payment gateway
$user = $userModel->get_user(session()->get("user_id"))->getRow();
?>
<form method="post" action="https://pay.skrill.com">
    <input type="hidden" name="pay_to_email" value="<?php echo $keys["skrill_merchant_email"];?>">
    <input type="hidden" name="merchant_fields" value="customer_number">
    <input type="hidden" name="customer_number" value="<?php echo session()->get('user_id');?>">
    <input type="hidden" name="firstname" value="<?php echo $user->first_name;?>">
    <input type="hidden" name="lastname" value="<?php echo $user->last_name;?>">
    <input type="hidden" name="recipient_description" value="<?php echo $keys["skrill_merchant_email"];?>">
    <input type="hidden" name="status_url" value="<?= site_url('payment/skrill_ipn'); ?>">
    <input type="hidden" name="amount" value="<?php echo $payment_details["total_payable_amount"];?>">
    <input type="hidden" name="currency" value="<?php echo $payment_gateway["currency"];?>">
    <input type="hidden" name="transaction_id" value="<?php echo "SKR_TXN_".uniqid();?>">
    <input type="hidden" name="return_url" value="<?php echo $payment_details["success_url"].'/'.$payment_gateway['identifier'];?>">
    <input type="hidden" name="cancel_url" value="<?php echo $payment_details["cancel_url"];?>">
    <button type="submit" class="payment-button float-end gateway <?php echo $payment_gateway['identifier']; ?>-gateway" id="skrill-button1"><?php echo get_phrase('pay_by_skrill'); ?></button>
</form>


