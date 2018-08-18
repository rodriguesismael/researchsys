<?php
class UniversidadeDAO extends DAO{
	public function save($values){
		$sql = "INSERT into universidades (nome,responsavel,endereco,complemento,telefone,cep,estado,cidade)
				VALUES(:nome,:responsavel,:endereco,:complemento,:telefone,:cep,:estado,:cidade)";
		$q = $this->db->prepare($sql);
		
		$q->bindParam(":nome", $values['nome'], PDO::PARAM_STR);
		$q->bindParam(":responsavel", $values['responsavel'], PDO::PARAM_STR);
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
		$sql = "SELECT u.*,c.nome as city FROM universidades u JOIN cidades c ON u.cidade = c.id ORDER BY u.nome";

		return $this->getAll($sql);
	}

	public function getById($id){
		$sql = "SELECT u.*,c.nome as city FROM universidades u JOIN cidades c ON u.cidade = c.id WHERE u.id=$id";

		return $this->getAll($sql);
	}

	public function getCursos(){
		$sql = "SELECT id,nome FROM cursos ORDER BY nome";
		return $this->getAll($sql);
	}


}