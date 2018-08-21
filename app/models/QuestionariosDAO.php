<?php 
class QuestionariosDAO extends DAO{
	protected $mapper;
	protected $id;
	public function __construct(){
		parent::__construct();
		$this->mapper = new \DB\SQL\Mapper($this->db, 'questionarios');
	}

	public function save($campos){
		$this->mapper->set('titulo',$campos['titulo']);
		$this->mapper->set('autores',$campos['autores']);
		$this->mapper->set('tradutores',$campos['tradutores']);
		$this->mapper->set('descricao',$campos['descricao']);
		$this->mapper->set('tipo',$campos['tipo']);
		$this->mapper->insert();
		
		$this->id = $this->mapper->get('_id');		
	}

	public function  saveAlternativas($alts){
		$sql = "INSERT INTO alternativas (alternativa,questionarios_id, ordem) VALUES (:alternativa,:idQ,:ordem)";
		$statement = $this->db->prepare($sql);
		$r = true;
		foreach ($alts as $chave=>$alternativa) {
			$statement->bindParam(':alternativa',$this->alternativa,PDO::PARAM_STR);
			$statement->bindParam(':idQ',$this->id,PDO::PARAM_INT);
			$ordem = $chave+1;
			$statement->bindParam(':ordem',$ordem,PDO::PARAM_INT);
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

	public function saveQuestoes($questoes,$questionario){
		$sql = "INSERT INTO questoes (questao, questionarios_id, ordem) VALUES (:questao, :questionario, :ordem)";
		$statement = $this->db->prepare($sql);
		$r=true;
		foreach ($questoes as $key => $questao) {
			$statement->bindParam(":questao",$questao,PDO::PARAM_STR);
			$statement->bindParam(":questionario",$questionario,PDO::PARAM_INT);
			$ordem = $chave+1;
			$statement->bindParam(":ordem",$ordem,PDO::PARAM_INT);
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


	public function saveResposta($questao,$resposta,$participante){
		$sql = "INSERT INTO respostas (participante,questao_id,alternativa_id) VALUES(:participante,:questao,:resposta)";
		$statement = $this->db->prepare($sql);
		$r=true;
		$statement->bindParam(":participante",$participante,PDO::PARAM_INT);
		$statement->bindParam(":questao",$questao,PDO::PARAM_INT);
		$statement->bindParam(":resposta",$resposta,PDO::PARAM_INT);
		try{
			$statement->execute();
		}catch(PDOException $e){
			$this->exception = $e;
			$r=false;
			echo $e->getMessage();
		}
		return $r;
	}

	public function getList(){
		$sql = "SELECT * FROM questionarios";
		return $this->getAll($sql);
	}

	public function getQuestById($id){
		$sql = "SELECT * FROM questionarios WHERE id=$id";
		return $this->getAll($sql);
	}
	public function getQuestByEmail($email,$crypt = false){
		$strEmail = "c.email";
		if($crypt){
			$strEmail = "md5(c.email)";
		}
		$sql = "SELECT questionarios FROM convidados c JOIN listas l on c.idlista = l.id WHERE $strEmail='$email'";
		return $this->getAll($sql);
	}
	public function getAlternativas($quest){
		$sql = "SELECT * FROM alternativas WHERE questionarios_id=$quest ORDER BY ordem";
		return $this->getAll($sql);
	}

	public function getQuestoes($quest){
		$sql = "SELECT * FROM questoes WHERE questionarios_id=$quest ORDER BY ordem";
		return $this->getAll($sql);
	}

	public function getId(){
		return $this->id;
	}

}