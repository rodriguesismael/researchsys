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
			$statement->bindParam(':random',rand(),PDO::PARAM_STR);
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
	public function arquivarLista($id,$arquivar){
		$sql = "UPDATE listas SET arquivado = :arquivar WHERE id=:id";
		$statement = $this->db->prepare($sql);
		$statement->bindParam(":id",$id,PDO::PARAM_INT);
		$statement->bindParam(":arquivar",$arquivar,PDO::PARAM_INT);

		try {
			$r = $statement->execute();
		} catch (PDOException $e) {
			$this->exception = $e;
			$r=false;
			echo $e->getMessage();
		}

		return $r;
	}
	public function getListas($filter=""){
		switch ($filter) {
			case 0://apenas listas ativas
				$clause = " WHERE arquivado = 0";
				break;
			case 1://apenas listas arquivadas
				$clause = " WHERE arquivado = 1";
				break;			
			default://todas as listas
				$clause = "";
				break;
		}
		$sql = "SELECT * FROM listas$clause";

		return $this->getAll($sql);
	}

	public function getListaById($id,$random=false,$onlyActive=false){
		$column = "id";
		$filter = "";
		if($random){
			$column = "id_aleatorio";
		}
		if($onlyActive){
			$filter = " AND arquivado=0";
		}
		$sql = "SELECT * FROM listas WHERE $column=$id$filter";

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