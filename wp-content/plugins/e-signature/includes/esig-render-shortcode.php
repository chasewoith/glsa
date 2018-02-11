<?php

/*
 *  Render e-sigature shortcode content on signing and save it to database. 
 *  
 */

function esig_render_shortcode($doc_id) {

    $docType = WP_E_Sig()->document->getDocumentType($doc_id);
    if ($docType != "stand_alone") {
        return false;
    }
    $documentContentUnfilter = WP_E_Sig()->document->esig_do_shortcode($doc_id);

    $document_content = WP_E_Sig()->signature->encrypt(ENCRYPTION_KEY, $documentContentUnfilter);
    $document_checksum = sha1($doc_id . $documentContentUnfilter);
    Esign_Query::_update("documents", array("document_content" => $document_content, "document_checksum" => $document_checksum), array("document_id" => $doc_id), array("%s", "%s"), array("%d"));
    //Esign_Query::_update("documents",array("document_content"=>$document_content),array("document_id"=>$doc_id),array("%s"),array("%d"));
}

//add_action("esig_agreement_cloned_from_stand_alone", "esig_render_shortcode", 1, 1);


