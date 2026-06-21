<?php

$userModel = new \App\Models\User_model();
$application_details = $userModel->get_applications($param2, 'application')->getRowArray();
$applicant_details = $userModel->get_all_user($application_details['user_id'])->getRowArray();
?>
<div class="text-center mb-2">
    <img class="mr-2 rounded-circle" src="<?php echo $userModel->get_user_image_url($applicant_details['id']); ?>" alt="" height="80">
</div>

<div class="table-responsive-sm">
    <table class="table table-bordered table-centered mb-0">
        <tbody>
            <tr class="text-center">
                <td><strong><?php echo get_phrase('applicant'); ?></strong></td>
                <td><?php echo $applicant_details['first_name'].' '.$applicant_details['last_name']; ?></td>
            </tr>
            <tr class="text-center">
                <td><strong><?php echo get_phrase('email'); ?></strong></td>
                <td><?php echo $applicant_details['email']; ?></td>
            </tr>
            <tr class="text-center">
                <td><strong><?php echo get_phrase('phone_number'); ?></strong></td>
                <td><?php echo $application_details['phone']; ?></td>
            </tr>
            <tr class="text-center">
                <td><strong><?php echo get_phrase('address'); ?></strong></td>
                <td><?php echo $application_details['address']; ?></td>
            </tr>
            <tr class="text-center">
                <td><strong><?php echo get_phrase('message'); ?></strong></td>
                <td><?php echo $application_details['message']; ?></td>
            </tr>
            <tr class="text-center">
                <td><strong><?php echo get_phrase('status'); ?></strong></td>
                <td><?php if ($application_details['status']): ?><span class="badge badge-success"><?php echo get_phrase('approved'); ?></span> <?php else: ?><span class="badge badge-danger"><?php echo get_phrase('pending'); ?></span><?php endif; ?></td>
                </tr>
            </tbody>
        </table>
    </div>


