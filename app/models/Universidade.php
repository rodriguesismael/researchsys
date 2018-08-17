<?php
class UniversidadeDAO extends DAO{
	public function save($values){
		$sql = "INSERT into universidades (nome,responsavel,endereco,complemento,telefone,cep,estado,cidade)
				VALUES(:nome,:responsavel,:endereco,:complemento,:telefone,:cep,:estado,:cidade)";
		$q = $this->db->prepare($sql);
		foreach ($values as $chave => $valor) {
			$q->bindParam(":".$chave,$valor);
		}

		return $q->execute();

	}

	public function getList(){
		$sql = "SELECT u.*,c.nome as city FROM universidades u JOIN cidades c ON u.cidade = c.id ORDER BY u.nome";

		return $this->execute($sql);
	}

	public function getById($id){
		$sql = "SELECT u.*,c.nome as city FROM universidades u JOIN cidades c ON u.cidade = c.id WHERE u.id=$id";

		return $this->getAll($sql);
	}


}