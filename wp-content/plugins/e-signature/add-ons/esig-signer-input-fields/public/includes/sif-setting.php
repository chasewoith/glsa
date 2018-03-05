<?php

class esigSifSetting {

    protected static $instance = null;

    /**
     * Returns an instance of this class.
     *
     * @since     0.1
     * @return    object    A single instance of this class.
     */
    public static function instance() {

        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function uploadDir() {
        $upload_dir_list = wp_upload_dir();
        $upload_dir = $upload_dir_list['basedir'];
        if (!file_exists($upload_dir . "/esign")) {
            mkdir($upload_dir . "/esign", 0777);
        }
        return $upload_dir . "/esign/";
    }

    public function recordEvent($userId, $docId, $uploadUrl) {

        $docType = WP_E_Sig()->document->getDocumenttype($docId);
        $eventText = $fileSize = $fileCreateTime = '';
        $fileName = basename($uploadUrl);
        $path = $this->uploadDir() . $fileName;
        if ($fileName && file_exists($this->uploadDir() . $fileName)) {
            $fileSize = $this->formatSizeUnits(filesize($path));
            $fileCreateTime = date(get_option('date_format'). " " . get_option('time_format') , filemtime($path));
        }
        if ($docType == 'stand_alone') {
            $signer = WP_E_Sig()->user->getUserdetails($userId, $docId);
            $emailAddress = sanitize_email(ESIG_POST('esig-sad-email'));
            if (is_email($emailAddress) && $emailAddress == $signer->user_email) {
                $eventText = sprintf(__("%s : Uploaded %s %s %s", 'esig'), $signer->first_name, $fileName, $fileSize, $fileCreateTime);
            }
        } elseif ($docType == 'normal') {
            $signer = WP_E_Sig()->user->getUserdetails($userId, $docId);
            $inviteHash = sanitize_text_field(ESIG_POST('invite_hash'));
            $invite = WP_E_Sig()->invite->get_Invite_Hash($signer->user_id, $docId);
            if ($invite == $inviteHash) {
                $eventText = sprintf(__("%s : Uploaded %s %s %s", 'esig'), $signer->first_name, $fileName, $fileSize, $fileCreateTime);
            }
        }

        WP_E_Sig()->document->recordEvent($docId, 'sif_upload', $eventText, $date = null, esig_get_ip());
    }

    private function formatSizeUnits($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' kB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

}
