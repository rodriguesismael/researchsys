<?php

class QuestionarioController extends Controller{

	function home(){
		$this->isAdmin();

		$query = "SELECT * FROM questionarios";
		$listaQuestionarios = $this->db->exec($query);
		$this->f3->set('questionarios',$listaQuestionarios);
		$this->f3->set('content','admin/homeQuestionarios.html');
		echo \Template::instance()->render('tela.htm');
	}

	function novo(){
		$this->isAdmin();
		if ($this->f3->get('POST.alternativas')) {		
			$alternativas = $this->f3->get('POST.alternativas');
			$camposQuestionario = array();
			$camposQuestionario['titulo'] = $this->f3->get('POST.titulo');
			$camposQuestionario['autores'] = $this->f3->get('POST.autores');
			$camposQuestionario['tradutores'] = $this->f3->get('POST.tradutores');
			$camposQuestionario['descricao'] = $this->f3->get('POST.descricao');
			$camposQuestionario['tipo'] = $this->f3->get('POST.tipo');

			$questionario = new QuestionariosDAO();
			$questionario->save($camposQuestionario);
			if($questionario->saveAlternativas($alternativas)){
				$idQ = $questionario->getId();
				unset($questionario);
				$this->f3->reroute("/admin/questionarios/questoes/$idQ");
			}
		}
		$this->f3->set('label','Novo');
		$this->f3->set('content','admin/formQuestionarios.html');
		echo \Template::instance()->render('tela.htm');	

	}

	function addQuestoes(){
		$this->isAdmin();
		$questoes = $this->f3->get('POST.questoes');
		$idquestionario = $this->f3->get('POST.questionario');
		$questionario = new QuestionariosDAO();
		if (isset($questoes)) {
			$questionario->saveQuestoes($questoes,$idquestionario);
			unset($questionario);
			$this->f3->reroute('/admin/questionarios');
		}
		$q = $this->f3->get('PARAMS.id');

		$result = $questionario->getQuestById($q);
		if(isset($result)){
			$this->f3->set('questionario',$result[0]);
			$this->f3->set('content','admin/formQuestoes.html');
			echo \Template::instance()->render('tela.htm');	
		} else{
			echo "404 Questionario";
		}
	}

	function editQuestoes(){
		$this->isAdmin();
		$q = $this->f3->get('PARAMS.id');
		$questoes = $this->db->exec("SELECT id,titulo FROM questoes WHERE questionarios_id=?",array($q));
		if(isset($result)){
			$this->f3->set('questoes',$questoes);
			$this->f3->set('content','admin/formQuestoes.html');
			echo \Template::instance()->render('tela.htm');	
		} else{
			echo "404 Questionario";
		}		
	}
	function editar($params){
		$this->isAdmin();
		if ($this->f3->get('PARAMS.id')) {
			$arrayChecks = array('qeacd'=>"",'eara'=>"",'berm'=>"",'slri'=>"",'eci'=>"",'autoeficacia'=>"autoprejudiciais",''=>"",'regulacao'=>"");

			$queryL = "SELECT * FROM listas WHERE id=?";
			$queryM = "SELECT id,email FROM convidados WHERE idlista=?";
			$dadosL = array();
			$dadosAux = array();
			$id = ($this->f3->get('PARAMS.id'));
			try {
				$dadosAux = $this->db->exec($queryL, array($id));
				$convidados = $this->db->exec($queryM, array($id));
				$dadosL['titulo'] = $dadosAux[0]['titulo'];
				$arrCurrentChecks = unserialize($dadosAux[0]['questionarios']);
				foreach ($arrCurrentChecks as $questionario) {
					$arrayChecks[$questionario]="checked";
				}
				$dadosL['questionario'] = $arrayChecks;
				$this->f3->set('lista',$dadosL);
				$this->f3->set('convidados',$convidados);
			} catch (Exception $e) {
				$this->f3->set('error',$e->getMessage());
			}
			//var_dump($dadosL);

			$this->f3->set('label','Editar');
			$this->f3->set('content','admin/formQuestionarios.html');
			echo \Template::instance()->render('tela.htm');	
		}
	}

	function mostrarQuestionario(){
		$idQuestionario = $this->f3->get('PARAMS.questionario');
		$questionarios = unserialize($this->f3->get('COOKIE.questionarios'));
		//$randKey = array_rand($questionarios);
		$idQuestionario = (empty($idQuestionario) ? $questionarios[0] : $idQuestionario);
		$questionario = new QuestionariosDAO();

		$result = $questionario->getQuestById($idQuestionario);
		if(count($result) > 0){
			
			$this->f3->set('questionario',$result[0]);
			$alternativas = $questionario->getAlternativas($idQuestionario);
			$questoes = $questionario->getQuestoes($idQuestionario);
			unset($questionario);
			$this->f3->set('alternativas',$alternativas);
			//$this->f3->set('questionario',$idQuestionario);
			$this->f3->set('questoes',$questoes);			
			$this->f3->set('content',"questionario-tipo-3.html");
			echo \Template::instance()->render('tela.htm');
		}

	}

	function processarRespostas(){
		$participante = $this->f3->get('POST.participante');
		if($this->f3->get('POST.participante')){
			$questoes = $this->f3->get('POST.questoes');
			$arrayRespostas = array();
			$str="";
			//var_dump($questoes);
			$questionario = new QuestionariosDAO();
			foreach ($questoes as $id) {
				$resposta = $this->f3->get('POST.questao'.$id);
				// $arrayRespostas[$id] = $resposta;
				$questionario->saveResposta($id,$resposta,$participante);
				// $queryAnswer = "INSERT INTO respostas (participante,questao_id,alternativa_id) VALUES(?,?,?)";
				// $this->db->exec($queryAnswer, array($participante,$id,$resposta));
			}
			unset($questionario);
			//apos inserir respostas na base
			//decrementar questionarios faltantes da base de dados
			//ir para o proximo questionário
			$questionarios = unserialize($this->f3->get('COOKIE.questionarios'));

			array_shift($questionarios);
			//var_dump($questionarios);
				//reroute pagina retornar
			$this->f3->set('COOKIE.questionarios',serialize($questionarios));
			$estadoAcesso = serialize($this->f3->get('COOKIE'));
			$participanteObj = new ParticipantesDAO();
			$update = $participanteObj->updateEstadoAcesso($estadoAcesso,$participante);
			if ($update) {
				$this->f3->reroute('/retornar/'.md5($this->f3->get('SESSION.mail')));
				//var_dump($questionarios);
			}
			// $this->db->exec("UPDATE participantes SET estadoAcesso=? WHERE uid=?",array($estadoAcesso,$participante));
		}
	}
}