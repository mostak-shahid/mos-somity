<?php /*Template Name: Somity Account Page Template*/ ?>
<?php
$mos_somity_notiece = carbon_get_theme_option('mos_somity_notiece');
?>
<?php get_header() ?>
<section class="somity-account-wrap">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <ul class="somity-account-menu">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">All Deposit</a></li>
                    <li><a href="#">Add Deposit</a></li>
                    <li><a href="#">Add Skim</a></li>
                    <li><a href="#">Edit Profile</a></li>
                </ul>
            </div>
            <div class="col-lg-8">
                <?php if ($mos_somity_notiece) : ?>
                    <div class="somity-account-notiece"><?php echo $mos_somity_notiece ?></div>
                <?php endif?>
            </div>
        </div>
    </div>
</section>
<?php the_content() ?>
<?php get_footer() ?>