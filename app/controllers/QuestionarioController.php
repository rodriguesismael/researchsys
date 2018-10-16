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
		$this->f3->set('action','/admin/questionarios/adicionar');
		$this->f3->set('submit_button','Salvar e Incluir Questões');
		$this->f3->set('content','admin/formQuestionarios.html');
		echo \Template::instance()->render('tela.htm');	

	}

	function addQuestoes(){
		$this->isAdmin();
		$questoes = $this->f3->get('POST.questoes');
		$idquestionario = $this->f3->get('POST.questionario');
		$questionario = new QuestionariosDAO();
		if (isset($questoes)) {
			foreach ($questoes as $key => $questao) {
				$camposQuestao = array();
				$camposQuestao['questionario'] = $idquestionario;
				$camposQuestao['questao'] = $questao;
				$camposQuestao['ordem'] = $key + 1;
				$questionario->saveQuestao($camposQuestao);
			}
			//$questionario->saveQuestoes($questoes,$idquestionario);
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
		if($this->f3->get('POST.id')){
			$campos = array();
			$id = $this->f3->get('POST.id');
			$campos['questao'] = $this->f3->get('POST.questao');
			$questionario = new QuestionariosDAO();
			$questionario->saveQuestao($campos,$id);
			//var_dump($this->f3->get('POST.questao'));
			echo "Questão Atualizada";
			return;
		}
		$q = $this->f3->get('PARAMS.id');
		$questionario = new QuestionariosDAO();
		$questoes = $questionario->getQuestoes($q);
		$resultQuestionario = $questionario->getQuestById($q);
		if(isset($questoes)){
			$this->f3->set('questoes',$questoes);
			$this->f3->set('questionario',$resultQuestionario[0]);
			$this->f3->set('content','admin/formQuestoes.html');
			echo \Template::instance()->render('tela.htm');	
		} else{
			echo "404 Questionario";
		}		
	}
	function editar($params){
		$this->isAdmin();
		if($this->f3->get('POST.questionario')){
			$id = $this->f3->get('POST.questionario');
			$camposQuestionario = array();
			$camposQuestionario['titulo'] = $this->f3->get('POST.titulo');
			$camposQuestionario['autores'] = $this->f3->get('POST.autores');
			$camposQuestionario['tradutores'] = $this->f3->get('POST.tradutores');
			$camposQuestionario['descricao'] = $this->f3->get('POST.descricao');

			$questionario = new QuestionariosDAO();
			$r = $questionario->save($camposQuestionario,$id);
			if($r){
				unset($questionario);
				$this->f3->reroute('/admin/questionarios');	
			}			
		}
		if ($this->f3->get('PARAMS.id')) {
			$id = $this->f3->get('PARAMS.id');
			$questionario = new QuestionariosDAO();
			$result = $questionario->getQuestById($id);
			$alternativas = $questionario->getAlternativas($id);
			$this->f3->set('questionario',$result[0]);
			$this->f3->set('alternativas',$alternativas);
			$this->f3->set('label','Editar');
			$this->f3->set('action','/admin/questionarios/editar/'.$id);
			$this->f3->set('submit_button','Atualizar');
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
			//var_dump(nl2br($result[0]['descricao']));
			$result[0]['descricao']=nl2br($result[0]['descricao']);
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
				$this->f3->reroute('/retornar/'.$this->f3->get('SESSION.participante'));
				//var_dump($questionarios);
			}
			// $this->db->exec("UPDATE participantes SET estadoAcesso=? WHERE uid=?",array($estadoAcesso,$participante));
		}
	}
}