<?php
class CidadesEstadosDAO extends DAO{
	public function getCidades(){
		$sql = "SELECT * FROM cidades ORDER BY nome ASC";
		return $this->getAll($sql);
	}

	public function getEstados(){
		$sql = "SELECT * FROM estados ORDER BY nome ASC";
		return $this->getAll($sql);
	}

	public function getCidadesByEstado($uf){
		$sql = "SELECT * FROM cidades WHERE estado_uf='$uf' ORDER BY nome ASC";
		return $this->getAll($sql);
	}	
}