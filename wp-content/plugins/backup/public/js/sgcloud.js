jQuery(document).ready( function() {
    sgBackup.initCloudSwitchButtons();
    sgBackup.initCloudFolderSettings();
});

jQuery(document).on('change', '.btn-file :file', function() {
    var input = jQuery(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [numFiles, label]);
});

sgBackup.initSFTPKeyFileSelection = function() {
    isFileSelected = false;
    jQuery('.btn-file :file').off('fileselect').on('fileselect', function(event, numFiles, label){
        var input = jQuery(this).parents('.input-group').find(':text'),
            log = numFiles > 1 ? numFiles + ' files selected' : label;

        if (input.length) {
            input.val(log);
            isFileSelected = true;
        }
        else {
            if(log) alert(log);
        }
    });
}

sgBackup.importKeyFile =  function(isFileSelected){
    jQuery('.alert').remove();
    if(!isFileSelected){
        var alert = sgBackup.alertGenerator(BG_CLOUD_STRINGS.invalidImportFile, 'alert-danger');
        jQuery('#sg-modal .modal-header').prepend(alert);
        return false;
    }

    var sguploadFile = new FormData(),
    url = "importKeyFile",
    sgAllowedFileSize = jQuery('.sg-backup-upload-input').attr('data-max-file-size'),
    sgFile = jQuery('input[name=sg-ssh-key-file]')[0].files[0];
    sguploadFile.append('sg-ssh-key-file', sgFile);
    if(sgFile.size > sgAllowedFileSize){
        var alert = sgBackup.alertGenerator(BG_CLOUD_STRINGS.invalidFileSize, 'alert-danger');
        jQuery('#sg-modal .modal-header').prepend(alert);
        return false;
    }

    var ajaxHandler = new sgRequestHandler(url, sguploadFile, {
        contentType: false,
        cache: false,
        xhr: function() {  // Custom XMLHttpRequest
            var myXhr = jQuery.ajaxSettings.xhr();
            if(myXhr.upload){ // Check if upload property exists
                myXhr.upload.addEventListener('progress', sgBackup.fileUploadProgress, false); // For handling the progress of the upload
            }
            return myXhr;
        },
        processData: false
    });

    ajaxHandler.callback = function(response, error){
        jQuery('.alert').remove();
        if(typeof response.success == 'undefined'){
            //if error
            var alert = sgBackup.alertGenerator(response, 'alert-danger');
            jQuery('#sg-modal .modal-header').prepend(alert);
        }
    };
    ajaxHandler.run();
}

sgBackup.initCloudSwitchButtons = function(){
    jQuery('.sg-switch').bootstrapSwitch();
    jQuery('.sg-switch').on('switchChange.bootstrapSwitch', function(event, state) {
        var storage = jQuery(this).attr('data-storage'),
            url = jQuery(this).attr('data-remote');
        that = jQuery(this);
        //If switch is on
        if(state) {
            jQuery('.alert').remove();
            if(storage == 'DROPBOX' || storage == 'GOOGLE_DRIVE' || storage == 'ONE_DRIVE') {
                var curlRequirementCheck = new sgRequestHandler('curlChecker', {});
                that.bootstrapSwitch('indeterminate',true);
                curlRequirementCheck.callback = function(response){
                    if(typeof response.success !== 'undefined') {
                        var isFeatureAvailable = new sgRequestHandler('isFeatureAvailable', {sgFeature: storage});
                        isFeatureAvailable.callback = function(response) {
                            if (typeof response.success !== 'undefined') {
                                jQuery(location).attr('href', getAjaxUrl(url));
                            }
                            else {
                                var alert = sgBackup.alertGenerator(response.error, 'alert-warning');
                                jQuery('.sg-cloud-container legend').after(alert);
                                that.bootstrapSwitch('state', false);
                            }
                        }

                        isFeatureAvailable.run();
                    }
                    else{
                        var alert = sgBackup.alertGenerator(response.error, 'alert-danger');
                        jQuery('.sg-cloud-container legend').after(alert);
                        that.bootstrapSwitch('state',false);
                    }
                };
                curlRequirementCheck.run();
            }
            else if (storage == 'FTP') {
                jQuery('input[data-storage=FTP]').bootstrapSwitch('indeterminate',true);
                sgBackup.isFtpConnected = false;
                jQuery('#ftp-settings').click();
            }
            else if (storage == 'AMAZON') {
                jQuery('input[data-storage=AMAZON]').bootstrapSwitch('indeterminate',true);
                sgBackup.isAmazonConnected = false;
                jQuery('#amazon-settings').click();
            }
        }
        else {
            var ajaxHandler = new sgRequestHandler(url, {cancel: true});
            ajaxHandler.callback = function(response){
                jQuery('.sg-'+storage+'-user').remove();
            };
            ajaxHandler.run();
        }
    });
};

sgBackup.storeAmazonSettings = function(){
    var error = [];
    //Validation
    jQuery('.alert').remove();
    var amazonForm = jQuery('form[data-type=storeAmazonSettings]');
    amazonForm.find('input').each(function(){
        if(jQuery(this).val()<=0){

            if(jQuery(this)[0].id == "customBucketRegion" && jQuery("#bucketType").val() != "custom"){
                return;
            }
            var errorTxt = jQuery(this).closest('div').parent().find('label').html().slice(0,-2);
            error.push(errorTxt+' field is required.');
        }
    });

    //If any error show it and abort ajax
    if(error.length){
        var alert = sgBackup.alertGenerator(error, 'alert-danger');
        jQuery('#sg-modal .modal-header').prepend(alert);
        return false;
    }

    //Before Ajax call
    jQuery('.modal-footer .btn-primary').attr('disabled','disabled');
    jQuery('.modal-footer .btn-primary').html(BG_CLOUD_STRINGS.connectionInProgress);

    //Get user credentials
    var amazonBucket = jQuery('#amazonBucket').val();
    var amazonAccessKey = jQuery('#amazonAccessKey').val();
    var amazonSecretAccessKey = jQuery('#amazonSecretAccessKey').val();
    var region = jQuery('#amazonBucketRegion').val();

    //On Success
    var ajaxHandler = new sgRequestHandler('cloudAmazon',amazonForm.serialize());
    ajaxHandler.dataIsObject = false;
    ajaxHandler.callback = function(response){
        jQuery('.alert').remove();
        if(typeof response.success !== 'undefined'){
            sgBackup.isAmazonConnected = true;
            jQuery('input[data-storage=AMAZON]').bootstrapSwitch('state',true);
            jQuery('#sg-modal').modal('hide');
        }
        else{
            //if error
            var alert = sgBackup.alertGenerator(response, 'alert-danger');
            jQuery('#sg-modal .modal-header').prepend(alert);

            //Before Ajax call
            jQuery('.modal-footer .btn-primary').removeAttr('disabled');
            jQuery('.modal-footer .btn-primary').html('Save');
        }
    };
    ajaxHandler.run();
};

sgBackup.storeFtpSettings = function(){
    var error = [];
    //Validation
    jQuery('.alert').remove();
    var ftpForm = jQuery('form[data-type=storeFtpSettings]');
    ftpForm.find('input[type=text]').each(function(){
        if(jQuery(this).val()<=0){
            if (jQuery(this).attr('name') != 'sg-key-file') {
                var errorTxt = jQuery(this).closest('div').parent().find('label').html().slice(0,-2);
            }
            else {
                var errorTxt = jQuery(this).closest('div').parent().parent().find('label').html().slice(0,-2);
            }


            if(!jQuery('#sg-connect-with-key-file').is(':checked') && errorTxt == 'Private key') {
                return true;
            }

            if(jQuery('#sg-connect-with-key-file').is(':checked') && errorTxt == 'Password') {
                return true;
            }

            error.push(errorTxt+' field is required.');
        }
    });

    //If any error show it and abort ajax
    if(error.length){
        var alert = sgBackup.alertGenerator(error, 'alert-danger');
        jQuery('#sg-modal .modal-header').prepend(alert);
        return false;
    }

    //Before Ajax call
    jQuery('.modal-footer .btn-primary').attr('disabled','disabled');
    jQuery('.modal-footer .btn-primary').html('Connecting...');

    //Get user credentials
    var ftpHost = jQuery('#ftpHost').val();
    var ftpUser = jQuery('#ftpUser').val();
    var ftpPort = jQuery('#ftpPort').val();
    var ftpString = ftpUser+'@'+ftpHost+':'+ftpPort;

    if (jQuery("#sg-connect-with-key-file").is(":checked")) {
        sgBackup.importKeyFile(isFileSelected);
    }

    //On Success
    var ajaxHandler = new sgRequestHandler('cloudFtp',ftpForm.serialize());
    ajaxHandler.dataIsObject = false;
    ajaxHandler.callback = function(response){
        jQuery('.alert').remove();
        if(typeof response.success !== 'undefined'){
            sgBackup.isFtpConnected = true;
            jQuery('input[data-storage=FTP]').bootstrapSwitch('state',true);
            jQuery('#sg-modal').modal('hide');
            sgBackup.addUserInfo(ftpString);
        }
        else{
            //if error
            var alert = sgBackup.alertGenerator(response, 'alert-danger');
            jQuery('#sg-modal .modal-header').prepend(alert);

            //Before Ajax call
            jQuery('.modal-footer .btn-primary').removeAttr('disabled');
            jQuery('.modal-footer .btn-primary').html('Save');
        }
    };
    ajaxHandler.run();
};
sgBackup.initCloudFolderSettings = function(){
    jQuery('#cloudFolder').on('input', function(){
        jQuery('#sg-save-cloud-folder').fadeIn();
    });
    jQuery('#sg-save-cloud-folder').click(function(){
        jQuery('.alert').remove();
        var cloudFolderName = jQuery('#cloudFolder').val(),
            cloundFolderRequest = new sgRequestHandler('saveCloudFolder',{cloudFolder: cloudFolderName}),
            saveBtn = jQuery(this);
        var alert = sgBackup.alertGenerator(BG_CLOUD_STRINGS.invalidDestinationFolder,'alert-danger');
        if(cloudFolderName.length<=0)
        {
            jQuery('.sg-cloud-container legend').after(alert);
            return;
        }
        saveBtn.attr('disabled','disabled');
        saveBtn.html('Saving...');
        cloundFolderRequest.callback = function(response){
            if(typeof response.success !== 'undefined'){
                var successAlert = sgBackup.alertGenerator(BG_CLOUD_STRINGS.successMessage,'alert-success');
                jQuery('.sg-cloud-container legend').after(successAlert);
                saveBtn.fadeOut();
            }
            else{
                jQuery('.sg-cloud-container legend').after(alert);
            }
            saveBtn.removeAttr('disabled');
            saveBtn.html('Save');
        };

        var isFeatureAvailable = new sgRequestHandler('isFeatureAvailable', {sgFeature: 'SUBDIRECTORIES'});
        isFeatureAvailable.callback = function(response) {
            if (typeof response.success !== 'undefined') {
                cloundFolderRequest.run();
            }
            else {
                var alert = sgBackup.alertGenerator(response.error, 'alert-warning');
                jQuery('.sg-cloud-container legend').after(alert);
                saveBtn.fadeOut();
            }
        }

        isFeatureAvailable.run();
    });
};

sgBackup.addUserInfo = function(info){
    jQuery('.sg-user-info .sg-helper-block').remove();
    jQuery('.sg-user-info br').remove();
    jQuery('.sg-user-info').append('<br/><span class="text-muted sg-user-email sg-helper-block">'+info+'</span>');
};
