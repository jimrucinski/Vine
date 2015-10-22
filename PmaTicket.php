<?php
class PmaTicket
{
	protected $_date_submitted;
	protected $_due_date;
	protected $_email;
	protected $_first_name;
	protected $_last_name;
	protected $_request_title;
	protected $_request_desc;
	protected $_id;
        protected $_department;


	public function get_date_submitted(){
		return $this->_date_submitted;
	}

	public function set_date_submitted($_date_submitted){
		$this->_date_submitted = $_date_submitted;
	}

	public function get_due_date(){
		return $this->_due_date;
	}

	public function set_due_date($_due_date){
		$this->_due_date = $_due_date;
	}

	public function get_email(){
		return $this->_email;
	}

	public function set_email($_email){
		$this->_email = $_email;
	}

	public function get_first_name(){
		return $this->_first_name;
	}

	public function set_first_name($_first_name){
		$this->_first_name = $_first_name;
	}

	public function get_last_name(){
		return $this->_last_name;
	}

	public function set_last_name($_last_name){
		$this->_last_name = $_last_name;
	}

	public function get_request_title(){
		return $this->_request_title;
	}

	public function set_request_title($_request_title){
		$this->_request_title = $_request_title;
	}

	public function get_request_desc(){
		return $this->_request_desc;
	}

	public function set_request_desc($_request_desc){
		$this->_request_desc = $_request_desc;
	}

	public function get_id(){
		return $this->_id;
	}

	public function set_id($_id){
		$this->_id = $_id;
	}
        public function set_department($_department){
            $this->_department = $_department;
        }
        public function get_department(){
            return $this->_department;
        }
}
?>
