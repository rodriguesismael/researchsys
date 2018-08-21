<?php
class ListaConvidadosDAO extends DAO{

	protected $mapper;
	protected $listId;
	public function __construct(){
		parent::__construct();
	    $this->mapper = new \DB\SQL\Mapper($this->db, 'listas');
	}

	public function saveLista($values){
		$this->mapper->set('titulo',$values['titulo']);
		$this->mapper->set('questionarios',$values['questionarios']);
		$this->mapper->insert();
		$this->listId = $this->mapper->get('_id');
		var_dump($this->listId);

	}

	public function saveConvidados($convidados){
		$sql = "INSERT INTO convidados (idlista,email) VALUES (:idLista,:email)";
		$statement = $this->db->prepare($sql);
		$r = true;
		foreach ($convidados as $email) {
			$statement->bindParam(':idLista',$this->listId,PDO::PARAM_INT);
			$statement->bindParam(':email',$email,PDO::PARAM_STR);
			try {
				$statement->execute();
			} catch (PDOException $e) {
				$this->exception = $e;
				$r=false;
				echo $e->getMessage();
			}
		}
		return $r;
	}
	public function getListas(){
		$sql = "SELECT * FROM listas";

		return $this->getAll($sql);
	}

	public function getListaById($id){
		$sql = "SELECT * FROM listas WHERE id=$id";

		return $this->getAll($sql);
	}

	public function getConvidados(){
		$sql = "SELECT * FROM convidados";

		return $this->getAll($sql);
	}

	public function getConvidadosByLista($lista){
		$sql = "SELECT * FROM convidados WHERE idlista=$lista";

		return $this->getAll($sql);
	}

	public function getConvidadoByEmail($email,$crypt=false){
		$strField="email";
		if ($crypt) {
			$strField="md5(email)";
		}
		$sql = "SELECT * FROM convidados WHERE $strField='$email'";

		return $this->getAll($sql);
	}	

}