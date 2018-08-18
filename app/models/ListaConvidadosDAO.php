<?php
class ListaConvidadosDAO extends DAO{
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

	public function getConvidadoByEmail($email){
		$sql = "SELECT * FROM convidados WHERE email='$email'";

		return $this->getAll($sql);
	}	

}