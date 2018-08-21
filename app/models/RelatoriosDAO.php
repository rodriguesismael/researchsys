<?php
class RelatoriosDAO extends DAO{
	public function getParticipantes($filtros=array()){
		$whereClause = "";
		foreach ($filtros as $key => $value) {
			if($value != ""){
				$whereClause.="$key='$value' AND ";
			}
		}

		if(empty($whereClause)){
			$whereClause = 1;
		}else{
			$whereClause = substr($whereClause, 0, (strlen($whereClause)-4);
		}

		$sql="SELECT r.participante, p.nome, timestampdiff(YEAR, nascimento,now()) as idade,email,genero,u.nome universidade,c.nome curso, 
					semestre, periodo_curso, tipo_ensino FROM participantes p JOIN respostas r on p.uid = r.participante JOIN questoes q on r.questao_id = q.id 
					JOIN universidades u ON p.universidades_id = u.id JOIN cursos c ON p.curso_id = c.id WHERE $whereClause group by r.participante";

		return $this->getAll($sql);
	}
}