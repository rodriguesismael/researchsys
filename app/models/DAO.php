<?php
class DAO{
	protected $f3;
	protected $db;

	protected $exception;

	public function __construct(){

		$this->f3 = Base::instance();
		if(is_null($db)){
		    $this->db=new DB\SQL(
		        $this->f3->get('DATABASE'),
		        $this->f3->get('DBUSER'),
		        $this->f3->get('DBPASS'),
		        array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
		    );
		}else{
			$this->db=$db;
		}
	}

	protected function execute($sql){
		try {
			$this->db->exec($sql);
		} catch (PDOException $e) {
			$return = false;
			$this->exception = $e;
			echo $e->getMessage();
		}

		return $return;
	}

	protected function getAll($sql, $param=array()){
		$q = null;
		try {
			$q = $this->db->prepare($sql);
			$q->execute($params);
		} catch (Exception $e) {
			$this->exception = $e;
			echo $e->getMessage();
		}

		if (is_null($q)) {
			return false;
		}else{
			return $q->fetchAll(PDO::FETCH_ASSOC);
		}
	}

}