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
			$titulo = $this->f3->get('POST.titulo');
			$autores = $this->f3->get('POST.autores');
			$tradutores = $this->f3->get('POST.tradutores');
			$descricao = $this->f3->get('POST.descricao');
			$tipo = $this->f3->get('POST.tipo');

			//var_dump($tradutores);
			$queryLista = "INSERT INTO listas (questionarios) VALUES (?)";
			
			try {
				$mapper = new \DB\SQL\Mapper($this->db, 'questionarios');
				$mapper->set('titulo',$titulo);
				$mapper->set('autores',$autores);
				$mapper->set('tradutores',$tradutores);
				$mapper->set('descricao',$descricao);
				$mapper->set('tipo',$tipo);
				var_dump($mapper);
				$mapper->insert();
				
				$idQ = $mapper->get('_id');
				foreach ($alternativas as $chave=>$alternativa) {
					$queryAlts = "INSERT INTO alternativas (alternativa,questionarios_id, ordem) VALUES (:alternativa,:idQ,:ordem)";
					$this->db->exec($queryAlts,array(':alternativa'=>$alternativa,':idQ'=>$idQ,':ordem'=>$chave+1));
				}
				$this->f3->reroute("/admin/questionarios/questoes/$idQ");
			} catch (Exception $e) {
				$this->f3->set('error',$e->getMessage());
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
		if (isset($questoes)) {

			foreach ($questoes as $chave => $questao) {
				$query = "INSERT INTO questoes (questao, questionarios_id, ordem) VALUES (?, ?, ?)";
				$this->db->exec($query,array($questao, $idquestionario, $chave+1));

			}
			$this->f3->reroute('/admin/questionarios');
		}
		$q = $this->f3->get('PARAMS.id');
		$result = $this->db->exec("SELECT id,titulo FROM questionarios WHERE id=?",array($q));
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
			var_dump($dadosL);

			$this->f3->set('label','Editar');
			$this->f3->set('content','admin/formQuestionarios.html');
			echo \Template::instance()->render('tela.htm');	
		}
	}

	function mostrarQuestionario(){

		$idQuestionario = $this->f3->get('PARAMS.questionario');
		$questionarios = unserialize($this->f3->get('COOKIE.questionarios'));
		// var_dump($idQuestionario);
		//$randKey = array_rand($questionarios);
		$idQuestionario = (empty($idQuestionario) ? $questionarios[0] : $idQuestionario);
		$result = $this->db->exec("SELECT * FROM questionarios WHERE id=?",array($idQuestionario));

		if(count($result) > 0){
			
			$this->f3->set('questionario',$result[0]);
			$alternativas = $this->db->exec("SELECT id,alternativa,ordem FROM alternativas WHERE questionarios_id=? ORDER BY ordem",array($idQuestionario));
			$questoes = $this->db->exec("SELECT id,questao,ordem FROM questoes WHERE questionarios_id=? ORDER BY ordem",array($idQuestionario));

			$this->f3->set('alternativas',$alternativas);
			//$this->f3->set('questionario',$idQuestionario);
			$this->f3->set('questoes',$questoes);
			//var_dump($questoes);
			$this->f3->set('content',"questionario-tipo-".$result[0]["tipo"].".html");
			echo \Template::instance()->render('tela.htm');
		}

	}

	function processarRespostas(){
		$participante = $this->f3->get('POST.participante');
		if($this->f3->get('POST.participante')){
			$questoes = $this->f3->get('POST.questoes');
			$arrayRespostas = array();
			$str="";
			var_dump($questoes);
			foreach ($questoes as $id) {
				$resposta = $this->f3->get('POST.questao'.$id);
				$arrayRespostas[$id] = $resposta;
				$queryAnswer = "INSERT INTO respostas (participante,questao_id,alternativa_id) VALUES(?,?,?)";
				$this->db->exec($queryAnswer, array($participante,$id,$resposta));

			}

			//apos inserir respostas na base
			//decrementar questionarios faltantes da base de dados
			//ir para o proximo questionário
			$questionarios = unserialize($this->f3->get('COOKIE.questionarios'));

			array_shift($questionarios);
			var_dump($questionarios);
				//reroute pagina retornar
			$this->f3->set('COOKIE.questionarios',serialize($questionarios));
			$estadoAcesso = serialize($this->f3->get('COOKIE'));
			//var_dump($estadoAcesso);
			//die();
			$this->db->exec("UPDATE participantes SET estadoAcesso=? WHERE uid=?",array($estadoAcesso,$participante));
			$this->f3->reroute('/retornar/'.md5($this->f3->get('SESSION.mail')));
			var_dump($questionarios);			
		}


	}

}