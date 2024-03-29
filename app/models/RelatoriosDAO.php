<?php
class RelatoriosDAO extends DAO{
	public function getParticipantes($filtros=array()){
		$whereClause = "";
		foreach ($filtros as $key => $value) {
			if($value != "" && $value > 0 ){
				$whereClause.="$key='$value' AND ";
			}
		}

		if(empty($whereClause)){
			$whereClause = 1;
		}else{
			$whereClause = substr($whereClause, 0, (strlen($whereClause)-4));
		}

		$sql="SELECT r.participante, p.nome, timestampdiff(YEAR, nascimento,now()) as idade,p.email,genero,universidades_id universidade,curso_id curso, 
					semestre, periodo_curso, tipo_ensino, etnia, intencao_academica, minhas_notas
					FROM participantes p 
					LEFT JOIN convidados cc ON p.email = cc.email
					JOIN respostas r on p.uid = r.participante JOIN questoes q on r.questao_id = q.id 
					JOIN universidades u ON p.universidades_id = u.id JOIN cursos c ON p.curso_id = c.id WHERE $whereClause group by r.participante";

		return $this->getAll($sql);
	}

	public function getparticipantesFinal(){
		$sql="SELECT SELECT uid, p.nome, timestampdiff(YEAR, nascimento,now()) as idade,p.email,
				genero,universidades_id universidade,curso_id curso, semestre, periodo_curso, tipo_ensino, 
				etnia, intencao_academica, minhas_notas 
				FROM participantes p 
				right JOIN analise_problemas cc ON p.email = cc.email 
				JOIN universidades u ON p.universidades_id = u.id 
				JOIN cursos c ON p.curso_id = c.id order by nome;";

		return $this->getAll($sql);
	}

	public function getEstatisticas($questionario,$participante){
		$sql= "SELECT q.ordem,a.ordem alternativa, a.alternativa texto FROM respostas r JOIN alternativas a ON r.alternativa_id = a.id JOIN 
			    questoes q on r.questao_id = q.id JOIN questionarios qr on q.questionarios_id = qr.id 
			    WHERE qr.id='$questionario' and r.participante='$participante'";	
		return $this->getAll($sql);
	}

	public function getRelatorioHeader($id){
		$sql = "SELECT q.titulo,qr.ordem from questionarios q JOIN questoes qr ON q.id = qr.questionarios_id WHERE q.id='$id' order by qr.ordem";

		return $this->getAll($sql);
	}
}