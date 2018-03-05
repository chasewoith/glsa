<?php
    $page = $_GET['page'];

    $isDisabelAdsEnabled = SGConfig::get('SG_DISABLE_ADS');
    $showUpgradeButton = SGBoot::isFeatureAvailable('SHOW_UPGRADE_PAGE');
    $buttonText = 'Buy now!';
    $upgradeText = 'Website migration, Backup to cloud, automatization, mail notifications, and more in our PRO package!';
    $buttonUrl = SG_BACKUP_SITE_URL;

    $isProVersion = SGBoot::isFeatureAvailable('STORAGE');
    if ($isProVersion) {
        $buttonText = 'Upgrade to ';
        $buttonUrl = SG_BACKUP_PRODUCTS_URL;

        $isGold = SGBoot::isFeatureAvailable('CUSTOM_BACKUP_NAME');
        if ($isGold) {
            $upgradeTo = 'Platinum';
        }
        else {
            $upgradeTo = 'Gold';
        }

        $upgradeText = $buttonText.$upgradeTo.' by paying only difference between plans.';
        $buttonText = $buttonText.$upgradeTo;
    }
?>
<div id="sg-sidebar-wrapper" class="metro">
    <nav class="sidebar dark">
        <ul>
            <li class="title">
                <a class="sg-site-url" target="_blank" href="<?php echo SG_BACKUP_SITE_URL;?>"></a>
            </li>
            <li class="<?php echo strpos($page,'backups')?'active':''?>">
                <a href="<?php echo network_admin_url('admin.php?page=backup_guard_backups'); ?>">
                    <span class="glyphicon glyphicon-hdd"></span>Backups
                </a>
            </li>
            <li class="<?php echo strpos($page,'cloud')?'active':''?>">
                <a href="<?php echo network_admin_url('admin.php?page=backup_guard_cloud'); ?>">
                    <span class="glyphicon glyphicon-cloud" aria-hidden="true"></span>Cloud
                </a>
            </li>
            <li class="<?php echo strpos($page,'schedule')?'active':''?>">
                <a href="<?php echo network_admin_url('admin.php?page=backup_guard_schedule'); ?>">
                    <span class="glyphicon glyphicon-time" aria-hidden="true"></span>Schedule
                </a>
            </li>
            <li class="<?php echo strpos($page,'settings')?'active':''?>">
                <a href="<?php echo network_admin_url('admin.php?page=backup_guard_settings'); ?>">
                    <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>Settings
                </a>
            </li>
            <li class="<?php echo strpos($page,'services')?'active':''?>">
                <a href="<?php echo network_admin_url('admin.php?page=backup_guard_services'); ?>">
                    <span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span>Services
                    <span class="badge badge-info">New</span>
                </a>
            </li>
            <li class="<?php echo strpos($page,'support')?'active':''?>">
                <a href="<?php echo network_admin_url('admin.php?page=backup_guard_support'); ?>">
                    <span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>Support
                </a>
            </li>
            <?php if (SGBoot::isFeatureAvailable('SHOW_UPGRADE_PAGE')):?>
                <li class="<?php echo strpos($page,'pro_features')?'active':''?>">
                    <a href="<?php echo network_admin_url('admin.php?page=backup_guard_pro_features'); ?>">
                        <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>Why upgrade?
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php if ($showUpgradeButton && !$isDisabelAdsEnabled):?>
        <div class="sg-alert-pro">
            <p>
                <?php _backupGuardT($upgradeText); ?>
            </p>
            <p>
                <a class="btn btn-primary" target="_blank" href="<?php echo $buttonUrl; ?>">
                    <?php _backupGuardT($buttonText); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
</div>
