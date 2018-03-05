jQuery(document).ready( function() {
    sgBackup.initGeneralSettingsSwitchButtons();
    AMOUNT_OF_BACKUPS_TO_KEEP = jQuery("#amount-of-backups-to-keep").val();
});

sgBackup.initGeneralSettingsSwitchButtons = function() {
    jQuery('.sg-switch').bootstrapSwitch();

    jQuery('.sg-email-switch').on('switchChange.bootstrapSwitch', function (event, state) {
        var url = jQuery(this).attr('data-remote');
        if (state) {
            var isFeatureAvailable = new sgRequestHandler('isFeatureAvailable', {sgFeature: 'NOTIFICATIONS'});
            isFeatureAvailable.callback = function(response) {
                if (typeof response.success !== 'undefined') {
                    //If switch is on
                    jQuery('.sg-general-settings').fadeToggle();
                }
                else {
                    jQuery('.alert').remove();
                    var alert = sgBackup.alertGenerator(response.error, 'alert-warning');
                    jQuery('.sg-settings-container legend').after(alert);
                    jQuery('.sg-email-switch').bootstrapSwitch('state', false);
                    return false;
                }
            }

            isFeatureAvailable.run();
        }
        else {
            var ajaxHandler = new sgRequestHandler(url, {cancel: true});
            ajaxHandler.callback = function(response){
                jQuery('.sg-user-email').remove();
                jQuery('.sg-general-settings').hide();
            };
            ajaxHandler.run();
            jQuery('#sg-user-email').remove();
            jQuery('#sg-email').val('');
        }
    });

    jQuery('.sg-switch').on('switchChange.bootstrapSwitch', function (event, state) {
        that = this;
        var feature = jQuery(this).attr('sgFeatureName');
        if (feature && feature != 'NOTIFICATIONS') {
            var isFeatureAvailable = new sgRequestHandler('isFeatureAvailable', {sgFeature: feature});
            isFeatureAvailable.callback = function(response) {
                if (state) {
                    if (typeof response.error !== 'undefined') {
                        jQuery('.alert').remove();
                        var alert = sgBackup.alertGenerator(response.error, 'alert-warning');
                        jQuery('.sg-settings-container legend').after(alert);
                        jQuery(that).bootstrapSwitch('state', false);
                        return false;
                    }
                }
            }

            isFeatureAvailable.run();
        }
    });
};

sgBackup.sgsettings = function(){
    var settingsForm = jQuery('form[data-type=sgsettings]');
    var error = [];
    //Validation
    jQuery('.alert').remove();

    if(jQuery('.sg-email-switch').is(":checked")){
        if(!isValidEmailAddress(jQuery('#sg-email').val())){
            error.push('Please enter valid email.');
        }
    }

    var backupFileName = jQuery('#backup-file-name').val();

    if (typeof amountOfBackups !== 'undefined' && !backupFileName) {
        error.push('Please enter backup file name.');
    }

    var amountOfBackups = jQuery('#amount-of-backups-to-keep').val();
    if (AMOUNT_OF_BACKUPS_TO_KEEP != amountOfBackups) {
        if (!confirm('Are you sure you want to keep the latest '+amountOfBackups+' backups? All older backups will be deleted!')) {
            return false;
        }
    }

    if (typeof amountOfBackups !== 'undefined' && !jQuery.isNumeric(amountOfBackups)) {
        error.push('Please enter a valid number of backups to keep on your server!');
    }

    //If any error show it and abort ajax
    if(error.length){
        var alert = sgBackup.alertGenerator(error, 'alert-danger');
        jQuery('.sg-settings-container legend').after(alert);
        sgBackup.scrollToElement('.alert');
        return false;
    }

    //Before sending
    var userEmail = jQuery('#sg-email').val();
    jQuery('#sg-save-settings').attr('disabled','disabled');
    jQuery('#sg-save-settings').html('Saving...');

    //On Success
    var ajaxHandler = new sgRequestHandler('settings', settingsForm.serialize());
    ajaxHandler.dataIsObject = false;
    ajaxHandler.callback = function(response){
        jQuery('.alert').remove();
        if(typeof response.success !== 'undefined'){
            var alert = sgBackup.alertGenerator('Successfully saved.', 'alert-success');
            jQuery('.sg-settings-container legend').after(alert);
            sgBackup.addUserInfo(userEmail);
            //jQuery('.sg-switch').bootstrapSwitch('state', true, true);
            jQuery('.sg-general-settings').fadeOut();
        }
        else{
            //if error
            var alert = sgBackup.alertGenerator(response, 'alert-danger');
            jQuery('.sg-settings-container legend').after(alert);
        }

        //Always
        jQuery('#sg-save-settings').removeAttr('disabled','disabled');
        jQuery('#sg-save-settings').html('Save');
        sgBackup.scrollToElement('.alert');
    };
    ajaxHandler.run();
};

sgBackup.addUserInfo = function(info){
    jQuery('.sg-user-info .sg-helper-block').remove();
    jQuery('.sg-user-info br').remove();
    jQuery('.sg-user-info').append('<br/><span class="text-muted sg-user-email sg-helper-block">'+info+'</span>');
};

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-+\s]+")|([\w-+]+(?:\.[\w-+]+)*)|("[\w-+\s]+")([\w-+]+(?:\.[\w-+]+)*))(@((?:[\w-+]+\.)*\w[\w-+]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][\d]\.|1[\d]{2}\.|[\d]{1,2}\.))((25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\.){2}(25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
};
