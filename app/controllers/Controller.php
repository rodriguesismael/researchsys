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
	function __construct() {
		
		$f3=Base::instance();
		$this->f3=$f3;
	    $db=new DB\SQL(
	        $f3->get('DATABASE'),
	        $f3->get('DBUSER')//,$f3->get('DBPASS')
	    );	    
	    $this->db=$db;
	    $mapper = new \DB\SQL\Mapper($this->db, 'listas');
	    $this->mapper = $mapper;
	}	
}