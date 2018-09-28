<?php
class UniversidadeDAO extends DAO{
	public function save($id=0, $values){
		if($id == 0){
			$sql = "INSERT into universidades (nome,responsavel,descricao_responsavel,endereco,complemento,telefone,cep,estado,cidade)
					VALUES(:nome,:responsavel,:desc_resp,:endereco,:complemento,:telefone,:cep,:estado,:cidade)";
		}else{
			var_dump($values);
			$sql = "UPDATE universidades SET nome=:nome,responsavel=:responsavel,descricao_responsavel=:desc_resp,endereco=:endereco,complemento=:complemento,telefone=:telefone,
					cep=:cep,estado=:estado,cidade=:cidade WHERE id=:id";
		}

		$q = $this->db->prepare($sql);
		if ($id > 0) {
			$q->bindParam(":id", $id, PDO::PARAM_INT);	
		}
		$q->bindParam(":nome", $values['nome'], PDO::PARAM_STR);
		$q->bindParam(":responsavel", $values['responsavel'], PDO::PARAM_STR);
		$q->bindParam(":desc_resp", $values['desc_resp'], PDO::PARAM_STR);
		$q->bindParam(":endereco", $values['endereco'], PDO::PARAM_STR);
		$q->bindParam(":complemento", $values['complemento'], PDO::PARAM_STR);
		$q->bindParam(":telefone", $values['telefone'], PDO::PARAM_STR);
		$q->bindParam(":cep", $values['cep'], PDO::PARAM_STR);
		$q->bindParam(":estado", $values['estado'], PDO::PARAM_STR);
		$q->bindParam(":cidade", $values['cidade'], PDO::PARAM_INT);
		try {
			$r = $q->execute();
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
		return $r;

	}

	public function getList(){
		//$sql = "SELECT u.*,c.nome as city FROM universidades u JOIN cidades c ON u.cidade = c.id ORDER BY u.nome";
		$sql = "SELECT u.* FROM universidades u ORDER BY u.nome";
		return $this->getAll($sql);
	}

	public function getById($id){
		//$sql = "SELECT u.*,c.nome as city FROM universidades u JOIN cidades c ON u.cidade = c.id WHERE u.id=$id";
		$sql = "SELECT u.* FROM universidades u WHERE u.id=$id";

		return $this->getAll($sql);
	}

	public function getCursos(){
		$sql = "SELECT id,nome FROM cursos ORDER BY nome";
		return $this->getAll($sql);
	}


}