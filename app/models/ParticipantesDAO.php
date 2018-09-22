<?php
class ParticipantesDAO extends DAO{
	public function save($campos){
		$sql = "INSERT INTO participantes (uid,ra,nome,email,genero,nascimento,tipo_ensino,universidades_id,curso_id,periodo_curso,semestre,etnia,intencao_academica,minhas_notas,estadoAcesso)
				 VALUES (:uid,:ra,:nome,:email,:genero,:nasc,:tipoEnsino,:universidade,:curso,:periodo,:semestre,:etnia,:intencao,:notas,:estado)";
		$statement = $this->db->prepare($sql);
		$statement->bindParam(":uid",$campos['uid'], PDO::PARAM_STR);
		$statement->bindParam(":ra",$campos['ra'], PDO::PARAM_INT);
		$statement->bindParam(":nome",$campos['nome'], PDO::PARAM_STR);
		$statement->bindParam(":email",$campos['email'], PDO::PARAM_STR);
		$statement->bindParam(":genero",$campos['genero'], PDO::PARAM_STR);
		$statement->bindParam(":nasc",$campos['nasc'], PDO::PARAM_STR);
		$statement->bindParam(":tipoEnsino",$campos['tipoEnsino'], PDO::PARAM_INT);
		$statement->bindParam(":universidade",$campos['universidade'], PDO::PARAM_INT);
		$statement->bindParam(":curso",$campos['curso'], PDO::PARAM_INT);
		$statement->bindParam(":periodo",$campos['periodo'], PDO::PARAM_STR);
		$statement->bindParam(":semestre",$campos['semestre'], PDO::PARAM_INT);
		$statement->bindParam(":etnia",$campos['etnia'], PDO::PARAM_INT);
		$statement->bindParam(":intencao",$campos['intencao'], PDO::PARAM_INT);
		$statement->bindParam(":notas",$campos['notas'], PDO::PARAM_INT);
		$statement->bindParam(":estado",$campos['estadoAcesso'], PDO::PARAM_STR);
		try {
			$r = $statement->execute();
		} catch (PDOException $e) {
			$this->exception = $e;
			$r=false;
			echo $e->getMessage();
		}
		return $r;		

	}

	public function getEstadoAcesso($usuario){
		$sql = "SELECT p.email,p.uid,p.estadoAcesso,l.questionarios FROM participantes p JOIN 
			convidados c on p.email= c.email JOIN listas l on c.idlista = l.id WHERE uid='$usuario'";
		return $this->getAll($sql);

	}

	public function updateEstadoAcesso($estado,$participante){
		$sql = "UPDATE participantes SET estadoAcesso=:estado WHERE uid=:participante";
		$statement = $this->db->prepare($sql);
		$statement->bindParam(":estado",$estado,PDO::PARAM_STR);
		$statement->bindParam(":participante",$participante,PDO::PARAM_INT);
		try {
			$r = $statement->execute();
		} catch (PDOException $e) {
			$this->exception = $e;
			$r = false;
			echo $e->getMessage();
		}
		return $r;
	}
}