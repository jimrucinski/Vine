<?php
include_once'PmaTable.class.php';
class PmaTicketObj extends PmaTable
{
    var $first_name=null;
    var $last_name=null;
    var $email=null;
    var $request_title=null;
    var $due_date = null;
    var $request_desc=null;
    var $date_submitted = null; 
    var $request_type=null;
    var $agent=null;   
    var $id; 
    var $current_agent_full_name;
    var $table = "pma_tickets";
    
    var $recs=null;
    
    
    public function testing()
    {
        echo "here";
    }
    
    public function set_table($_table){
        $this->table=$_table;
	}
}

