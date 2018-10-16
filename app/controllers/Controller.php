<?php

class Controller{
	protected $f3;
    protected $db;
    protected $mapper;
	function beforeroute(){
		//echo 'Before routing - ';
	}
	function afterroute(){
		//echo '- After routing';
	}
	public function isAdmin(){
		if($this->f3->get('SESSION.idusr') == 1){
			return true;
		}else{
			$this->f3->reroute('/admin/login');
		}		
	}	
	function __construct() {
		//require 'vendor/autoload.php';
		$f3=Base::instance();
		$this->f3=$f3;
	    $db=new DB\SQL(
	        $f3->get('DATABASE'),
	        $f3->get('DBUSER'),
	        $f3->get('DBPASS')
	    );	    
	    $this->db=$db;
	    $mapper = new \DB\SQL\Mapper($this->db, 'listas');
	    $this->mapper = $mapper;
	}	
}