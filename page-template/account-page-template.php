<?php /*Template Name: Somity Account Page Template*/ ?>
<?php
global $wpdb;
$table_mos_deposits = $wpdb->prefix.'mos_deposits';
$table_mos_skim_user = $wpdb->prefix.'mos_skim_user';
$current_user_id = get_current_user_id();
$mos_skim_user = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mos_skim_user WHERE user_id = {$current_user_id}"); 
$mos_skim_user_active = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mos_skim_user WHERE user_id = {$current_user_id} AND status = 'active'"); 

$mos_deposits = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mos_deposits WHERE user_id = {$current_user_id}"); 

if (isset( $_POST['mos_somity_add_deposit_field'] ) && wp_verify_nonce( $_POST['mos_somity_add_deposit_field'], 'mos_somity_add_deposit_action' ) ) {
    // var_dump($_POST);
    $err = 0;
    if ( ! function_exists( 'wp_handle_upload' ) ) 
    require_once( ABSPATH . 'wp-admin/includes/file.php' );

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
            /*["name"]=> string(9) "job_1.png" 
            ["full_path"]=> string(9) "job_1.png" 
            ["type"]=> string(9) "image/png" 
            ["tmp_name"]=> string(44) "C:\Users\User\AppData\Local\Temp\phpE100.tmp" 
            ["error"]=> int(0) 
            ["size"]=> int(357677)
            */

            $upload_overrides = array( 'test_form' => false );
            $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

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
?>
<?php get_header() ?>
<section class="somity-account-wrap">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <ul class="somity-account-menu">
                    <li><a href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>">Dashboard</a></li>
                    
                    <li <?php echo ($p == 'deposits' || $p == 'add-deposit')?'class="menu-open"':''  ?>>
                        <a href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=deposits">Deposits</a>
                        <ul>
                            <li <?php echo ($p == 'deposits')?'class="menu-active"':''  ?>><a href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=deposits">All Deposit</a></li>
                            <li <?php echo ($p == 'add-deposit')?'class="menu-active"':''  ?>><a href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=add-deposit">Add Deposit</a></li>
                        </ul>
                    </li>
                    <li <?php echo ($p == 'skims' || $p == 'add-skim')?'class="menu-open"':''  ?>>
                        <a href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=skims">Skims</a>
                        <ul>
                            <li <?php echo ($p == 'skims')?'class="menu-active"':''  ?>><a href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=skims">All Skim</a></li>
                            <li <?php echo ($p == 'add-skim')?'class="menu-active"':''  ?>><a href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=add-skim">Add Skim</a></li>
                        </ul>
                    </li>
                    <li><a href="<?php echo get_the_permalink($mos_somity_account_page[0]['id']) ?>?p=edit-profile">Edit Profile</a></li>
                    <li><a href="<?php echo wp_logout_url( home_url() ); ?>">Logout</a></li>
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
                    edit profile
                
                <?php else : ?>
                    Dashboard
                <?php endif?>
            </div>
        </div>
    </div>
</section>
<?php the_content() ?>
<?php get_footer() ?>