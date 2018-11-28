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
		$this->mapper->set('id_aleatorio',$values['random_id']);
		$this->mapper->set('link_assinatura',$values['link']);
		$this->mapper->insert();
		$this->listId = $this->mapper->get('_id');
		//var_dump($this->listId);

	}

	public function saveConvidados($convidados,$lista=0){
		$sql = "INSERT INTO convidados (idlista,email,randomid) VALUES (:idLista,:email,:random)";
		$statement = $this->db->prepare($sql);
		$r = true;

		if ($lista == 0) {
			$param = $this->listId;
		}else{
			$param = $lista;
		}
		foreach ($convidados as $email) {
			$statement->bindParam(':idLista',$param,PDO::PARAM_INT);
			$statement->bindParam(':email',$email,PDO::PARAM_STR);
			//this random value will compose the participant id in the participantes table
			$statement->bindParam(':random',uniqid(rand()),PDO::PARAM_STR);
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

	public function getListaById($id,$random=false){
		$column = "id";
		if($random){
			$column = "id_aleatorio";
		}
		$sql = "SELECT * FROM listas WHERE $column=$id";

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

	public function getConvidadoByMailAndId($email,$randomid){
		$sql = "SELECT * FROM convidados WHERE email LIKE '$email%' AND randomid='$randomid'";

		return $this->getAll($sql);
	}

}