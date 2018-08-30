<?php
/**
 *  @author abu shoaib
 *  @since 1.3.0
 */
class WP_E_Search extends WP_E_Model
{
    
	public function __construct(){
		parent::__construct();	
                $this->table = $this->table_prefix . "documents";
		$this->usertable = $this->table_prefix . "document_users";
	}
        
        public function get_search_user_id()
        {
                $esig_all_sender = isset($_GET['esig_all_sender'])?$_GET['esig_all_sender']:null;
                
                if($esig_all_sender)
                {
                   
                    return $esig_all_sender ;
                }
                else
                {
                    if(!is_esig_super_admin())
                    {
                       return  get_current_user_id() ;
                    }
                }
                
                return false;
        }
        
        public function is_sa_search()
        {
                 $esig_all_sender = isset($_GET['esig_all_sender'])?$_GET['esig_all_sender']:null;
                 
                if($esig_all_sender == "All Sender")
                {
                    return true;
                }
                
                if($esig_all_sender)
                {
                   
                    return false ;
                }
                else
                {
                    if(!is_esig_super_admin())
                    {
                       return  false ;
                    }
                }
                
                return true;
        }
 
        public function fetchAllOnSearch($esig_document_search)
        {
		$search = '%'. $this->esc_sql($esig_document_search) . '%';
               
                //pagination settings 
		$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
                
                $document_status= isset( $_GET['document_status'] ) ?  $_GET['document_status'] : "awaiting";
		
		$limit = 20;
		$offset = ( $pagenum - 1 ) * $limit;
           
                if($this->is_sa_search())
                {
                   
                      /*$docs=$this->wpdb->get_results($this->wpdb->prepare("SELECT ". $this->table .".document_id,". $this->table .".user_id,".$this->table .".document_title,". $this->table .".document_status,". $this->table .".document_type,". $this->table .".date_created,". $this->table .".last_modified FROM " . $this->table . " LEFT JOIN ". $this->usertable ." ON ". $this->table .".document_id =". $this->usertable .".document_id "
                              . "WHERE ". $this->usertable .".signer_name LIKE '%s' and ". $this->table .".document_status='%s' "
                              . "OR ". $this->table .".document_status ='%s' and ". $this->table .".document_title LIKE '%s' LIMIT %d,%d",$search,$document_status,$document_status,$search,$offset,$limit));
                       */
                      $docs=$this->wpdb->get_results($this->wpdb->prepare("SELECT ". $this->table .".document_id,". $this->table .".user_id,".$this->table .".document_title,". $this->table .".document_status,". $this->table .".document_type,". $this->table .".date_created,". $this->table .".last_modified FROM " . $this->table . " LEFT JOIN ". $this->usertable ." ON ". $this->table .".document_id =". $this->usertable .".document_id "
                              . "WHERE ". $this->usertable .".signer_name LIKE '%s' and ". $this->table .".document_status='%s' "
                              . "OR ". $this->table .".document_status ='%s' and ". $this->table .".document_title LIKE '%s' GROUP BY ". $this->table .".document_id LIMIT %d,%d",$search,$document_status,$document_status,$search,$offset,$limit));
                     
                }
                else
                {
                   
                     $user_id = $this->get_search_user_id();
                  // echo $this->wpdb->prepare("SELECT * FROM " . $this->table . " INNER JOIN ". $this->usertable ." ON ". $this->table .".document_id =". $this->usertable .".document_id WHERE ". $this->table .".user_id=%d and ".$this->table.".document_status=%s and ".$this->table.".document_title LIKE %s OR ".$this->table.".user_id=%d and ".$this->table.".document_status=%s and ". $this->usertable .".signer_name LIKE %s LIMIT %d,%d",$user_id,$document_status,$search,$user_id,$document_status, $search,$offset,$limit);
                  
                     //$docs=$this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM " . $this->table . " INNER JOIN ". $this->usertable ." ON ". $this->table .".document_id =". $this->usertable .".document_id WHERE ". $this->table .".user_id=%d   and  ".$this->table.".document_title LIKE %s OR ".$this->table.".user_id=%d and ". $this->usertable .".signer_name LIKE %s LIMIT %d,%d",$user_id,$search,$user_id, $search,$offset,$limit));
                     $docs=$this->wpdb->get_results($this->wpdb->prepare("SELECT ". $this->table .".document_id,". $this->table .".user_id,".$this->table .".document_title,". $this->table .".document_status,". $this->table .".document_type,". $this->table .".date_created,". $this->table .".last_modified FROM " . $this->table . " LEFT OUTER JOIN ". $this->usertable ." ON ". $this->table .".document_id =". $this->usertable .".document_id WHERE ". $this->table .".user_id=%d and ".$this->table.".document_status=%s and ".$this->table.".document_title LIKE %s OR ".$this->table.".user_id=%d and ".$this->table.".document_status=%s and ". $this->usertable .".signer_name LIKE %s LIMIT %d,%d",$user_id,$document_status,$search,$user_id,$document_status, $search,$offset,$limit));
                }
               
		//$docs=apply_filters('esig-search-document-filter',$docs,$esig_document_search);
              
		return $docs ;	
	}
        
        public function esc_sql($searchWord){
          return $this->wpdb->esc_like(esc_sql($searchWord)); 
        }
        
        public function search_document_total($esig_document_search)
        {
                $search = '%'. $this->esc_sql($esig_document_search)  . '%';
                
                $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
                
		$document_status= isset( $_GET['document_status'] ) ?  $_GET['document_status'] : "awaiting";
		
                if($this->is_sa_search())
                {
                     $docs=$this->wpdb->get_results($this->wpdb->prepare("SELECT ". $this->table .".document_id,".$this->table .".document_title FROM " . $this->table . " LEFT JOIN ". $this->usertable ." ON ". $this->table .".document_id =". $this->usertable .".document_id "
                              . "WHERE ". $this->usertable .".signer_name LIKE %s and ". $this->table .".document_status=%s "
                              . "OR ". $this->table .".document_status =%s and ". $this->table .".document_title LIKE %s GROUP BY ". $this->table .".document_id",$search,$document_status,$document_status,$search));
                    
                }
                else
                {
                    $user_id = $this->get_search_user_id();
                     $docs=$this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM " . $this->table . " INNER JOIN ". $this->usertable ." ON ". $this->table .".document_id =". $this->usertable .".document_id WHERE ". $this->table .".user_id=%d and ".$this->table.".document_status=%s and ".$this->table.".document_title LIKE %s OR ".$this->table.".user_id=%d and ".$this->table.".document_status=%s and ". $this->usertable .".signer_name LIKE %s",$user_id,$document_status,$search,$user_id,$document_status, $search));
                }
          
                 return count($docs);
        }
        
         /**
	* 
	* 
	* @return
	*/
	public function pagination()
	{
		$status = isset($_GET['document_status']) ? sanitize_text_field($_GET['document_status']) : 'awaiting';
		
		$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
		
                $esig_document_search = ESIG_SEARCH_GET('esig_document_search');
                
                if($esig_document_search)
                {
                   $total = $this->search_document_total($esig_document_search);
                }
                else
                {   
                    $doc_obj= new WP_E_Document();
                    $total =  $doc_obj->getDocumentsTotal($status);
                }
                
		$num_of_pages = ceil( $total / 20 );
		
		$page_links = paginate_links( array(
					'base' => add_query_arg( 'pagenum', '%#%' ),
					'format' => '',
					'prev_text' => __( '&laquo;', 'aag' ),
					'next_text' => __( '&raquo;', 'aag' ),
					'total' => $num_of_pages,
					'current' => $pagenum
				) );

			$page_text = "";
			if ( $page_links ) {
				$page_text='<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
			}
			
			return $page_text ; 		
	}
   
}