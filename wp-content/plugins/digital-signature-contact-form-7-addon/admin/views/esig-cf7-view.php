<?php if (function_exists("WP_E_Sig")){ ?>
<div class="contact-form-editor-box-mail" id="wpcf7-mail">

    <h2>E-Signature</h2>
    <fieldset>
        <table class="form-table">
            <tbody>
                
                 <tr>
                    <th scope="row">
                       
                    </th>
                    <td>
                        <?php $checked=($settings_enable_esignature)?"checked":false;?>
                        <input name="settings_enable_esignature" type="checkbox" id="settings-enable-esignature"  value="1" <?php echo $checked; ?> >
                        <label>Enable E-Signature for this contract form.</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="wpcf7-mail-recipient"><?php _e('Signer Name', 'esig'); ?><font color="red">*</font></label>
                    </th>
                    <td>
                        <input name="settings_signer_name" type="text" id="settings-signer-name"  value="<?php echo $signer_name; ?>"  placeholder="<?php _e('Name or fields', 'esig'); ?>">
                        <span class="howto">Select the name field from your Contact Form 7. This field is what the signers full name will be on their WP E-Signature contract.</span>
                        <?php
                        if (ESIG_GET('signer_error') == "error") {
                            echo "<font color=red>Please input correct signer name field</font>";
                        }
                        ?>
                    </td>
                </tr>
                <!-- Signer Email Address -->
                <tr>
                    <th scope="row">
                        <label for="wpcf7-mail-sender"><?php _e('Signer Email Address', 'esig'); ?><font color="red">*</font></label>
                    </th>
                    <td>
                        <input name="settings_signer_email_address" type="text" id="settings-signer-email-address" value="<?php echo $signer_email; ?>"  placeholder="<?php _e('Email or fields', 'esig'); ?>" >
                        <span class="howto">Select the email field from your Contact Form 7. This field is what the signers email will be on their WP E-Signature contract.</span>
                        <?php
                        if (ESIG_GET('email_error') == "error") {
                            echo "<font color=red>Please input correct signer e-mail field</font>";
                        }
                        ?>
                    </td>
                </tr>

                <!-- Signing Logic -->
                <tr>
                    <th scope="row">
                        <label for="wpcf7-mail-subject"><?php _e('Signing Logic', 'esig'); ?></label>
                    </th>
                    <td>

                        <select name="signing_logic" class="gaddon-setting gaddon-select" id="signing_logic">
                            <option value="redirect" <?php
                            if ($signing_logic == "redirect") {
                                echo "selected";
                            }
                            ?>><?php _e('Redirect user to Contract/Agreement after Submission', 'esig'); ?></option>
                            <option value="email" <?php
                            if ($signing_logic == "email") {
                                echo "selected";
                            }
                            ?>><?php _e('Send User an Email Requesting their Signature after Submission', 'esig'); ?></option></select>
                        <span class="howto"><?php _e('Please select your desired signing logic once this form is submitted.', 'esig'); ?></span>
                    </td>
                </tr>

                <!-- select sad document -->
                <tr>
                    <th scope="row">
                        <label for="wpcf7-mail-subject"><?php _e('Select stand alone document', 'esig'); ?><font color="red">*</font></label>
                    </th>
                    <td>
                        <select name="select_sad" id="select_sad">
                            <?php
                            if (class_exists('esig_sad_document')) {

                                $sad = new esig_sad_document();
                                $sad_pages = $sad->esig_get_sad_pages();
                                echo'<option value=""> ' . __('Select an agreement page', 'esig') . ' </option>';
                                foreach ($sad_pages as $page) {
                                    $selected = ($page->page_id == $select_sad) ? "selected" : null;
                                    if (get_the_title($page->page_id)) {
                                        echo '<option value="' . $page->page_id . '" ' . $selected . '> ' . get_the_title($page->page_id) . ' </option>';
                                    }
                                }
                            }
                            ?></select>
                         <?php
                        if (ESIG_GET('agreement_error') == "error") {
                            echo "<font color=red>Please select an agreement.</font>";
                        }
                        ?>
                        <span class="howto"><?php _e('If you would like to can <a href="edit.php?post_type=esign&amp;page=esign-add-document&amp;esig_type=sad">create new document', 'esig-nfds'); ?></a></span>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="wpcf7-mail-subject"></label>
                    </th>
                    <td>
                        <select name="underline_data" id="settings-underline_data">
                            <option value="underline" <?php
                            if ($underline_data == "underline") {
                                echo "selected";
                            }
                            ?> ><?php _e('Underline the data That was submitted from this Contact Form', 'esig'); ?></option>
                            <option value="not_under" <?php
                            if ($underline_data == "not_under") {
                                echo "selected";
                            }
                            ?>><?php _e('Do not underline the data that was submitted from the Contact Form', 'esig'); ?></option>
                        </select>
                        
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="signing_reminder_email"><?php _e('Signing Reminder Email', 'esig'); ?></label>
                    </th>
                    <td>
                        <input name="signing_reminder_email" type="hidden" value="0"/>
                        <input type="checkbox" name="signing_reminder_email" id="settings-signing_reminder_email" value="1" <?php checked($signing_reminder_email, 1); ?> /><?php _e('Enabling signing reminder email. If/When user has not sign the document', 'esig'); ?><br><br>
                        <?php _e('Send the reminder email to the signer in ', 'esig-nfds'); ?><input type="textbox" name="reminder_email" value="<?php echo $reminder_email; ?>" style="width:40px;height:30px;"><?php _e('Days', 'esig'); ?> <?php
                        if (ESIG_GET('reminder_email') == "error") {
                            echo "<font color=red>Numeric value required</font>";
                        }
                        ?><br><br>
                        <?php _e('After the first Reminder send reminder every ', 'esig-nfds'); ?><input type="textbox" name="first_reminder_send" value="<?php echo $first_reminder_send; ?>" style="width:40px;height:30px;"> <?php _e('Days', 'esig'); ?> <?php
                        if (ESIG_GET('first_reminder_send') == "error") {
                            echo "<font color=red>Numeric value required</font>";
                        }
                        ?><br><br>
                        <?php _e('Expire reminder in ', 'esig-nfds'); ?><input type="textbox" name="expire_reminder" value="<?php echo $expire_reminder; ?>" style="width:40px;height:30px;"> Days  <?php
                        if (ESIG_GET('expire_reminder') == "error") {
                            echo "<font color=red>Numeric value required</font>";
                        }
                        ?>
                    </td>


                </tr>


            </tbody>
        </table>
    </fieldset>

</div>

<?php }


else{
 include plugin_dir_path(__FILE__) . 'core-alert.php';   
}?>











