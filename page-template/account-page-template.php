<?php /*Template Name: Somity Account Page Template*/ ?>
<?php
if (!is_user_logged_in()) {
    wp_redirect(home_url());
    exit();
} else {
    $user = wp_get_current_user(); 
    $roles = ( array ) $user->roles;
    if(!sizeof($roles)) {        
        wp_redirect(home_url());
        exit();
    }
}

if (!function_exists('wp_generate_attachment_metadata')){
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');
}

global $wpdb;
$table_mos_deposits = $wpdb->prefix.'mos_deposits';
$table_mos_skim_user = $wpdb->prefix.'mos_skim_user';
$current_user_id = get_current_user_id();
$mos_skim_user = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mos_skim_user WHERE user_id = {$current_user_id}"); 
$mos_skim_user_active = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mos_skim_user WHERE user_id = {$current_user_id} AND status = 'active'"); 

$mos_deposits = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mos_deposits WHERE user_id = {$current_user_id}"); 


$nominee_verified = carbon_get_user_meta( $current_user_id, 'mos-somity-nominee-verified' );


if (isset( $_POST['mos_somity_edit_profile_field'] ) && wp_verify_nonce( $_POST['mos_somity_edit_profile_field'], 'mos_somity_edit_profile_action' ) ) {
    $err = 0;

    $user_image_file = $_FILES['user_image'];
    if ($user_image_file) {
        //var_dump($user_image_file);
        if($user_image_file['type'] != 'image/jpeg' && $user_image_file['type'] != 'image/png' && $user_image_file['type'] != 'image/gif') {
            $err++;
        }
        if ($user_image_file["size"] > 5000000) {
            $err++;
        }
        if (!$err) {
            $movefile = handle_upload('user_image');
        }
    }
    //var_dump($_POST);
    if ($_POST['first_name']) update_user_meta( $current_user_id, 'first_name', $_POST['first_name'] );
    if ($_POST['last_name']) update_user_meta( $current_user_id, 'last_name', $_POST['last_name'] );
    if ($_POST['user_nid']) update_user_meta( $current_user_id, '_mos-somity-user-nid', $_POST['user_nid'] );
    if ($_POST['user_passport']) update_user_meta( $current_user_id, '_mos-somity-user-passport', $_POST['user_passport'] );
    if ($_POST['user_address']) update_user_meta( $current_user_id, '_mos-somity-user-address', $_POST['user_address'] );
    if (@$movefile["url"]) update_user_meta( $current_user_id, '_mos-somity-user-image', $movefile["url"] );
}

if (isset( $_POST['mos_somity_edit_nominee_profile_field'] ) && wp_verify_nonce( $_POST['mos_somity_edit_nominee_profile_field'], 'mos_somity_edit_nominee_profile_action' ) && $nominee_verified == 'no') {
    //var_dump($_POST);
    
    $err = 0;

    $nominee_image_file = $_FILES['nominee_image'];
    if ($nominee_image_file) {
        //var_dump($user_image_file);
        if($nominee_image_file['type'] != 'image/jpeg' && $nominee_image_file['type'] != 'image/png' && $nominee_image_file['type'] != 'image/gif') {
            $err++;
        }
        if ($nominee_image_file["size"] > 5000000) {
            $err++;
        }
        if (!$err) {
            $movefile = handle_upload('nominee_image');
        }
    }
    if ($_POST['nominee_name']) update_user_meta( $current_user_id, '_mos-somity-nominee-name', $_POST['nominee_name'] );
    if ($_POST['nominee_nid']) update_user_meta( $current_user_id, '_mos-somity-nominee-nid', $_POST['nominee_nid'] );
    if ($_POST['nominee_passport']) update_user_meta( $current_user_id, '_mos-somity-nominee-passport', $_POST['nominee_passport'] );
    if ($_POST['nominee_address']) update_user_meta( $current_user_id, '_mos-somity-nominee-address', $_POST['nominee_address'] );
    if (@$movefile["url"]) update_user_meta( $current_user_id, '_mos-somity-nominee-image', $movefile["url"] );
}
if (isset( $_POST['mos_somity_add_deposit_field'] ) && wp_verify_nonce( $_POST['mos_somity_add_deposit_field'], 'mos_somity_add_deposit_action' ) ) {
    // var_dump($_POST);
    $err = 0;

    $uploadedfile = $_FILES['image'];
    if ($uploadedfile) {
        //var_dump($uploadedfile);
        if($uploadedfile['type'] != 'image/jpeg' && $uploadedfile['type'] != 'image/png' && $uploadedfile['type'] != 'image/gif') {
            $err++;
        }
        if ($uploadedfile["size"] > 5000000) {
            $err++;
        }
        if (!$err) {
            $movefile = handle_upload('image');
        }
    } else {
        $err++;
    }
    if (!$err) {
        //$movefile["url"]
        $wpdb->insert(
            $table_mos_deposits,
            array(
                'user_id' => $current_user_id,
                'skim_id' => $_POST['skim'],
                'photo' => $movefile["url"],
                'source' => $_POST["source"],
                'amount' => $_POST["amount"],
                'apply_date' => date('Y-m-d'),
                'comment' => $_POST["comment"],
                'status' => 'pending',
            )
        );
        $msg = 'Your request has been received, admin will approve your request.';
    }
}
		


$msg = '';
$p = (@$_GET['p'])?$_GET['p']:'';
$action = (@$_GET['action'])?$_GET['action']:'';
if ($action == 'add_skim') {
    //echo 'WOrking';
    $title = (@$_GET['title'])?$_GET['title']:'';
    $amount = (@$_GET['amount'])?$_GET['amount']:'0';
    $rate = (@$_GET['rate'])?$_GET['rate']:'';
    $time = (@$_GET['time'])?$_GET['time']:'';
    $penalty = (@$_GET['penalty'])?$_GET['penalty']:'';

    $data = array("title"=>$title, "amount"=>$amount, "rate"=>$rate, "time"=> $time, "penalty" => $penalty);    
    $wpdb->insert(
        $table_mos_skim_user,
        array(
            'user_id' => $current_user_id,
            'status' => 'pending',
            'skim_details' => json_encode($data),
            'apply_date' => date('Y-m-d'),
        )
    );
    $msg = 'Your request has been received, admin will approve your request.';
}
$mos_somity_account_page = carbon_get_theme_option('mos_somity_account_page');
$mos_somity_source = carbon_get_theme_option('mos_somity_source');
$mos_somity_skim = carbon_get_theme_option('mos_somity_skim');
$mos_somity_notiece = carbon_get_theme_option('mos_somity_notiece');



$first_name = get_user_meta( $current_user_id, 'first_name', true ); 
$last_name = get_user_meta( $current_user_id, 'last_name', true ); 

$address = carbon_get_user_meta( $current_user_id, 'mos-somity-user-address' );
$nid = carbon_get_user_meta( $current_user_id, 'mos-somity-user-nid' );
$passport = carbon_get_user_meta( $current_user_id, 'mos-somity-user-passport' );
$image = carbon_get_user_meta( $current_user_id, 'mos-somity-user-image' );

$nominee_name = carbon_get_user_meta( $current_user_id, 'mos-somity-nominee-name' );
$nominee_nid = carbon_get_user_meta( $current_user_id, 'mos-somity-nominee-nid' );
$nominee_address = carbon_get_user_meta( $current_user_id, 'mos-somity-nominee-address' );
$nominee_passport = carbon_get_user_meta( $current_user_id, 'mos-somity-nominee-passport' );
$nominee_image = carbon_get_user_meta( $current_user_id, 'mos-somity-nominee-image' );

?>
<?php get_header() ?>
<section class="somity-account-wrap">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <ul class="somity-account-menu">
                    <li><a class="link-underline link-underline-opacity-0" href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>">Dashboard</a></li>

                    <li <?php echo ($p == 'deposits' || $p == 'add-deposit')?'class="menu-open"':''  ?>>
                        <a class="link-underline link-underline-opacity-0" href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=deposits">Deposits</a>
                        <ul>
                            <li <?php echo ($p == 'deposits')?'class="menu-active"':''  ?>><a class="link-underline link-underline-opacity-0" href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=deposits">All Deposit</a></li>
                            <li <?php echo ($p == 'add-deposit')?'class="menu-active"':''  ?>><a class="link-underline link-underline-opacity-0" href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=add-deposit">Add Deposit</a></li>
                        </ul>
                    </li>
                    <li <?php echo ($p == 'skims' || $p == 'add-skim')?'class="menu-open"':''  ?>>
                        <a class="link-underline link-underline-opacity-0" href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=skims">Skims</a>
                        <ul>
                            <li <?php echo ($p == 'skims')?'class="menu-active"':''  ?>><a class="link-underline link-underline-opacity-0" href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=skims">All Skim</a></li>
                            <li <?php echo ($p == 'add-skim')?'class="menu-active"':''  ?>><a class="link-underline link-underline-opacity-0" href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=add-skim">Add Skim</a></li>
                        </ul>
                    </li>
                    <li><a class="link-underline link-underline-opacity-0" href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=edit-profile">Edit Profile</a></li>
                    <li><a class="link-underline link-underline-opacity-0" href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=nominee-profile">Nominee Profile</a></li>
                    <li><a class="link-underline link-underline-opacity-0" href="<?php echo wp_logout_url( home_url() ); ?>">Logout</a></li>
                </ul>
            </div>
            <div class="col-lg-8">
                <?php if (@$mos_somity_notiece) : ?>
                <div class="somity-account-notiece"><?php echo $mos_somity_notiece ?></div>
                <?php endif?>
                <?php if (@$msg) : ?>
                <div class="somity-account-notiece"><?php echo $msg ?></div>
                <?php endif?>

                <?php if($p == 'deposits') : ?>

                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Apply Date</th>
                            <th scope="col">Approved_date</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mos_deposits as $deposit) : ?>
                        <tr>
                            <td><?php echo $deposit->ID ?></td>
                            <td><?php echo $deposit->amount ?></td>
                            <td><?php echo $deposit->apply_date?></td>
                            <td><?php echo $deposit->approved_date?></td>
                            <td><?php echo $deposit->status?></td>
                        </tr>
                        <?php endforeach?>
                    </tbody>
                </table>
                <?php elseif($p == 'add-deposit') : ?>
                <form class="needs-validation" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'mos_somity_add_deposit_action', 'mos_somity_add_deposit_field' ); ?>
                    <div class="mb-3">
                        <label for="skim" class="form-label">Select Skim</label>
                        <select name="skim" class="form-select" id="skim" required>
                            <option value="">Choose...</option>
                            <?php foreach($mos_skim_user as $skim) : ?>
                            <?php $skim_details = json_decode($skim->skim_details)?>
                            <option value="<?php echo $skim->ID ?>"><?php echo $skim_details->title?>(ID: <?php echo $skim->ID ?>)</option>
                            <?php endforeach?>
                        </select>
                        <div class="invalid-feedback">
                            Please select a valid state.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="source" class="form-label">Source</label>
                        <select name="source" class="form-select" id="source" required>
                            <option value="">Choose...</option>
                            <?php foreach($mos_somity_source as $source) : ?>
                            <option><?php echo $source['title'] ?>(<?php echo $source['number'] ?>)</option>
                            <?php endforeach?>
                        </select>
                        <div class="invalid-feedback">
                            Please select a valid state.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input name="amount" type="number" class="form-control" required>
                        <div class="invalid-feedback">Example invalid form file feedback</div>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input name="image" type="file" class="form-control" aria-label="file example" required accept="image/png, image/gif, image/jpeg">
                        <div class="invalid-feedback">Example invalid form file feedback</div>
                    </div>
                    <div class="mb-3">
                        <label for="comment" class="form-label">Textarea</label>
                        <textarea name="comment" class="form-control" id="comment" placeholder="Comment"></textarea>
                    </div>
                    <input type="hidden" name="skim_id" value="">
                    <button class="btn btn-primary" type="submit">Submit form</button>
                </form>
                <?php elseif($p == 'skims') : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Title</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Rate</th>
                            <th scope="col">Time (Month)</th>
                            <th scope="col">Penalty</th>
                            <th scope="col">Apply Date</th>
                            <th scope="col">End Date</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mos_skim_user as $skim) : ?>
                        <?php $skim_details = json_decode($skim->skim_details)?>
                        <tr>
                            <td><?php echo $skim->ID ?></td>
                            <td><?php echo $skim_details->title ?></td>
                            <td><?php echo $skim_details->amount ?></td>
                            <td><?php echo $skim_details->rate ?></td>
                            <td><?php echo $skim_details->time ?></td>
                            <td><?php echo $skim_details->penalty ?></td>
                            <td><?php echo $skim->apply_date?></td>
                            <td>End Date</td>
                            <td><?php echo $skim->status ?></td>
                            <td><a href="#">Close</a></td>
                        </tr>
                        <?php endforeach?>
                    </tbody>
                </table>
                <?php elseif($p == 'add-skim') : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Rate</th>
                            <th scope="col">Time (Month)</th>
                            <th scope="col">Penalty</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mos_somity_skim as $skim) : ?>
                        <tr>
                            <td><?php echo $skim['title'] ?></td>
                            <td><?php echo $skim['amount'] ?></td>
                            <td><?php echo $skim['rate'] ?></td>
                            <td><?php echo $skim['time'] ?></td>
                            <td><?php echo $skim['penalty'] ?></td>
                            <td><a href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=add-skim&action=add_skim&title=<?php echo $skim['title'] ?>&amount=<?php echo $skim['amount'] ?>&rate=<?php echo $skim['rate'] ?>&time=<?php echo $skim['time'] ?>&penalty=<?php echo $skim['penalty'] ?>">Apply</a></td>
                        </tr>
                        <?php endforeach?>
                    </tbody>
                </table>
                <?php elseif($p == 'edit-profile') : ?>
                <form class="needs-validation mb-4" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'mos_somity_edit_profile_action', 'mos_somity_edit_profile_field' ); ?>
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Personal Info</h3>
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input id="first_name" name="first_name" type="text" class="form-control" value="<?php echo @$first_name ?>" required>
                                <div class="invalid-feedback">First name is required</div>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input id="last_name" name="last_name" type="text" class="form-control" value="<?php echo @
                            $last_name ?>">
                            </div>
                            <div class="mb-3">
                                <label for="user_nid" class="form-label">National ID</label>
                                <input id="user_nid" name="user_nid" type="text" class="form-control" value="<?php echo @$nid ?>">
                            </div>
                            <div class="mb-3">
                                <label for="user_passport" class="form-label">Passport</label>
                                <input id="user_passport" name="user_passport" type="text" class="form-control" value="<?php echo @$passport ?>">
                            </div>
                            <div class="mb-3">
                                <label for="user_address" class="form-label">Address</label>
                                <textarea id="user_address" name="user_address" class="form-control"><?php echo @$address ?></textarea>
                            </div>
                            <div class="mb-3 upload-image">
                                <label for="user_image" class="form-label">Image</label>
                                <input name="user_image" id="user_image" type="file" class="form-control" aria-label="file example" accept="image/png, image/gif, image/jpeg">
                                <?php if (@$image) : ?>
                                <div class="mt-2 preview-image" style="max-width: 250px">
                                    <img src="<?php echo $image?>" alt="" class="img-fluid">
                                </div>
                                <?php endif?>
                            </div>
                            <input type="hidden" name="skim_id" value="">
                            <button class="btn btn-primary" type="submit">Save Profile</button>
                        </div>
                    </div>
                </form>
                <?php elseif($p == 'nominee-profile') : ?>
                <form class="needs-validation" method="post" enctype="multipart/form-data">
                            <?php wp_nonce_field( 'mos_somity_edit_nominee_profile_action', 'mos_somity_edit_nominee_profile_field' ); ?>
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">Nominee Info</h3>
                                    <div class="mb-3">
                                        <label for="nominee_name" class="form-label">Name</label>
                                        <input id="nominee_name" name="nominee_name" type="text" class="form-control" value="<?php echo @$nominee_name ?>" required>
                                        <div class="invalid-feedback">Name is required</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nominee_nid" class="form-label">National ID</label>
                                        <input id="nominee_nid" name="nominee_nid" type="text" class="form-control" value="<?php echo @$nominee_nid ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="nominee_passport" class="form-label">Passport</label>
                                        <input id="nominee_passport" name="nominee_passport" type="text" class="form-control" value="<?php echo @$nominee_passport ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="nominee_address" class="form-label">Address</label>
                                        <textarea id="nominee_address" name="nominee_address" class="form-control"><?php echo @$nominee_address ?></textarea>
                                    </div>
                                    <div class="mb-3 upload-image">
                                        <label for="nominee_image" class="form-label">Image</label>
                                        <input name="nominee_image" id="nominee_image" type="file" class="form-control" aria-label="file example" accept="image/png, image/gif, image/jpeg">
                                        <?php if (@$nominee_image) : ?>
                                        <div class="mt-2 preview-image" style="max-width: 250px">
                                            <img src="<?php echo $nominee_image?>" alt="" class="img-fluid">
                                        </div>
                                        <?php endif?>
                                    </div>
                                    <input type="hidden" name="skim_id" value="">
                                    <button class="btn btn-primary" type="submit">Save Profile</button>
                                </div>
                            </div>
                        </form>
                <?php else : ?>
                Dashboard
                <?php echo $current_user_id ?>
                <?php endif?>
            </div>
        </div>
    </div>
</section>
<?php the_content() ?>
<?php get_footer() ?>
