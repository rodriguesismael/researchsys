<?php

class ParticipantesController extends Controller{

	function home(){
		$this->isAdmin();
		$listaObj = new ListaConvidadosDAO();
		$listas = $listaObj->getListas();
		unset($listaObj);
		$this->f3->set('listas',$listas);
		$this->f3->set('content','admin/homeParticipantes.html');
		echo \Template::instance()->render('tela.htm');
	}

	function nova(){
		$this->isAdmin();
		if ($this->f3->get('POST.maillist')) {		
			$camposLista['titulo'] = $this->f3->get('POST.turma');
			$emails = $this->f3->get('POST.maillist');
			$quests = $this->f3->get('POST.questionarios');
			$camposLista['questionarios'] = serialize($quests);
			$listaObj = new ListaConvidadosDAO();
			$listaObj->saveLista($camposLista);
			if($listaObj->saveConvidados($emails)){
				unset($listaObj);
				$this->f3->reroute('/admin/participantes');
			}


			// try {
			// 	$this->mapper->set('titulo',$titulo);
			// 	$this->mapper->set('questionarios',serialize($quests));
			// 	$this->mapper->insert();
			// 	$idLista = $this->mapper->get('_id');
			// 	foreach ($emails as $mail) {
			// 		$queryEmails = "INSERT INTO convidados (idlista,email) VALUES (:idLista,:email)";
			// 		$this->db->exec($queryEmails,array(':idLista'=>$idLista,':email'=>$mail));
			// 	}
			// 	$this->f3->reroute('/admin/participantes');
			// } catch (Exception $e) {
			// 	$this->f3->set('error',$e->getMessage());
			// }
		}
		$questionario = new QuestionariosDAO();
		$questionarios = $questionario->getList();
		unset($questionario);
		$this->f3->set('questionarios',$questionarios);
		$this->f3->set('label','Nova');
		$this->f3->set('content','admin/formParticipantes.html');
		echo \Template::instance()->render('tela.htm');	

	}

	function editar($params){
		$this->isAdmin();
		if ($this->f3->get('PARAMS.id')) {
			$selected=array();
			$listaObj = new ListaConvidadosDAO();
			$lista = array();
			$dadosAux = array();
			$id = ($this->f3->get('PARAMS.id'));
			$dadosAux = $listaObj->getListaById($id);
			$convidados = $listaObj->getConvidadosByLista($id);
			$lista['titulo'] = $dadosAux[0]['titulo'];
			$questConvidado = unserialize($dadosAux[0]['questionarios']);
			foreach ($questConvidado as $questionario) {
				$selected[(int)$questionario]="selected";
			}
			$lista['questionario'] = $selected;
			$this->f3->set('lista',$lista);
			$this->f3->set('convidados',$convidados);
			unset($listaObj);
			$questionario = new QuestionariosDAO();
			$questionarios = $questionario->getList();
			unset($questionario);
			$this->f3->set('questionarios',$questionarios);
			$this->f3->set('label','Editar');
			$this->f3->set('content','admin/formParticipantes.html');
			echo \Template::instance()->render('tela.htm');	
		}
	}

	function convidar(){
		$this->isAdmin();
		$obs="<strong>OBSERVAÇÃO: O sistema está em período de testes. Ao preencher os dados demográficos, favor acrescentar 'Teste' por útimo no campo nome. 
				Após finalizar o processo, favor elaborar um texto com impressões e/ou problemas encontrados bem como sugestões de melhorias. Encaminhar para isrodrigues5@gmail.com 
				com o assunto \"Testes Autorregular\".</strong>";
		$link="";
		$msgEmail = "<h4>Prezado(a) aluno(a),</h4>
		<p style='text-ident:2em;text-align:justify;'>
		Meu nome é Evely Boruchovitch. Sou professora da Faculdade de Educação da Universidade Estadual de 
		Campinas – UNICAMP. Gostaria de convidá-lo(a) a participar de uma pesquisa nacional que visa conhecer 
		melhor os fatores que favorecem ou dificultam a aprendizagem de estudantes universitários brasileiros. 
		Ao participar, você será solicitado a manifestar o seu aceite em um termo de consentimento. Na sequência, 
		você será direcionado a algumas perguntas rápidas sobre você e a dois questionários com questões de 
		múltipla escolha. Assim que terminar de respondê-los, você receberá, por e-mail, resultados e informações 
		importantes, em linhas gerais, sobre como você aprende. Receberá também orientações utéis para melhorar a 
		sua aprendizagem. As suas respostas são confidenciais e asseguro o sigilo da sua identificação.o. Os dados 
		a serem obtidos contribuirão para o desenvolvimento de melhores práticas educativas e para o fortalecimento 
		da capacidade de aprender dos alunos do Ensino Superior.</p>
		<p>
			Sua colaboração é muito valiosa! Para participar da pesquisa, por favor, acesse o
			seguinte link: <a href='REPLACE'>REPLACE</a>
		</p>
		<p>Muito obrigada!</p><br/>$obs";

		$smtp = new SMTP ( 'smtp.gmail.com', '587', 'tls', $this->f3->get('SMTP_MAIL'), $this->f3->get('SMTP_PASS') );

		$smtp->set('MIME-Version', '1.0');
		$smtp->set('Content-type', 'text/html;charset=UTF-8');
		$smtp->set('From', '"NoReply" <isrmdevonly@gmail.com>');
		$smtp->set('Subject', 'Convite');

		if ($this->f3->get('GET.lista')) {
			$listaObj = new ListaConvidadosDAO();
			$response="";
			$dados = $listaObj->getConvidadosByLista($this->f3->get('GET.lista'));
			if(empty($dados)){
				$response = json_encode(array('code'=>0,'msg'=>"Não há emails para esta lista!"));
				echo $response;
			}else{
				$sendTo =array();
				foreach ($dados as $emails) {
					$smtp->set('To', $emails['email']);
					$sendTo[] = $emails['email'];
					$link= $this->f3->get('BASE_URL')."/participar/".$this->encryptMail($emails['email']);
					//$msgEmail = str_replace('REPLACE', $link, $msgEmail);
					$smtp->send(str_replace('REPLACE', $link, $msgEmail)) or die("ERRO ".$smtp->log());
				}
				$response = json_encode($sendTo);
				echo $response;

			}
		}

	}

	function participar(){
		$hash = $this->f3->get('PARAMS.hash');
		$listaObj = new ListaConvidadosDAO();
		$result = $listaObj->getConvidadoByEmail($hash,true);
		//var_dump($result);
		if(count($result) == 0){
			$this->f3->error(404);
		}
		if ($this->f3->get('POST.aceito')) {
			$this->f3->set('COOKIE.termo',true);
		}
		if($this->f3->get('COOKIE.termo')){
			$this->f3->reroute('/cadastrar/'.$this->f3->get('PARAMS.hash'));
		}else{
			$this->f3->set('who',$this->f3->get('PARAMS.hash'));
			$this->f3->set('content','termo.html');
			echo \Template::instance()->render('tela.htm');			
		}
	}
	function cadastrar(){
		$email = $this->f3->get('PARAMS.email');

		if($this->f3->get('POST.nome')){
			$camposParticipante = array();
			$camposParticipante['nome'] = $this->f3->get('POST.nome');
			$camposParticipante['ra'] = $this->f3->get('POST.ra');
			$camposParticipante['genero'] = $this->f3->get('POST.genero');
			$camposParticipante['email'] = $this->f3->get('POST.email');
			$camposParticipante['nasc'] = $this->f3->get('POST.nasc');
			$camposParticipante['universidade'] = $this->f3->get('POST.universidade');
			$camposParticipante['curso'] = $this->f3->get('POST.curso');
			$camposParticipante['periodo'] = $this->f3->get('POST.periodo');
			$camposParticipante['tipoEnsino'] = $this->f3->get('POST.tipoEnsino');
			$camposParticipante['semestre'] = $this->f3->get('POST.semestre');
			$this->f3->set('COOKIE.cadastro',true);
			//$queryEstado = "SELECT questionarios FROM convidados c JOIN listas l on c.idlista = l.id WHERE c.email=?";
			$questionario = new QuestionariosDAO();
			$result = $questionario->getQuestByEmail($camposParticipante['email'],false);
			//$result = $this->db->exec($queryEstado,$camposParticipante['email']);
			unset($questionario);
			$this->f3->set('COOKIE.questionarios',$result[0]['questionarios']);			
			$estadoAtual = $this->f3->get('COOKIE');
			$camposParticipante['estadoAcesso'] = serialize($estadoAtual);
			$participante = new ParticipantesDAO();
			if($participante->save($camposParticipante)){
				$nomePartes = explode(" ", $camposParticipante['nome']);
				$firstName = array_shift($nomePartes);
				$lastName = implode(" ", $nomePartes);
				$uniObj = new UniversidadeDAO();
				$uni = $uniObj->getById($camposParticipante['universidade']);
				$queryString = "?invnum=80010&ak=brazil&u=gyxc&p=wxk&requiredFirstName=$firstName&requiredLastName=$lastName&";
				$queryString.= "school=".str_replace(" ", "", $uni[0]['nome'])."&idnum=".$this->f3->get('PARAMS.email')."&email=".$camposParticipante['email']."&check_box=yes";
				echo "<br>Redirecting to https://ssl.collegelassi.com/portuguese/lassi.html$queryString";
				unset($participante);
				unset($uniObj);
				$this->f3->reroute("https://ssl.collegelassi.com/portuguese/lassi.html$queryString");
			}
		}
		
		if($this->f3->get('COOKIE.termo')){
			$result = array();
			$listaObj = new ListaConvidadosDAO();
			$universidade = new UniversidadeDAO();
			$universidades = $universidade->getList();
			$cursos = $universidade->getCursos();
			$result = $listaObj->getConvidadoByEmail($email,true);
			unset($universidade);
			unset($listaObj);
			if(count($result) > 0){
				$this->f3->set('email',$result[0]['email']);
				$this->f3->set('universidades',$universidades);
				$this->f3->set('cursos',$cursos);
				$this->f3->set('content','demograficos.html');
				echo \Template::instance()->render('tela.htm');
			}

		}else{
			$this->f3->reroute('/participar/'.$this->f3->get('PARAMS.email'));
		}
	}

	function retornar(){
		$email = $this->f3->get('PARAMS.usuario');
		$participante = new ParticipantesDAO();

		$result = $participante->getEstadoAcesso($email,true);
		unset($participante);
		$cookie = unserialize($result[0]["estadoAcesso"]);
		foreach ($cookie as $key => $value) {
			$this->f3->set("COOKIE.$key",$value);
		}
		$this->f3->set('SESSION.participante',$result[0]['uid']);
		$this->f3->set('SESSION.mail',$result[0]['email']);
		//var_dump($this->f3->get("COOKIE"));
		$questionarios = unserialize($this->f3->get('COOKIE.questionarios')); 
		if(empty($questionarios)){
			//não há mais questionários para reponder
			$this->f3->reroute('/finalizar/'.md5($this->f3->get('SESSION.mail')));
		}
		$this->f3->call("QuestionarioController->mostrarQuestionario");
	}

	function finalizar(){
		//atualizar estado no banco como finalizou = true
		//destruir cookie e sessao
		$this->f3->set('SESSION.participante','');
		$this->f3->set('SESSION.mail','');
		$this->f3->set('COOKIE','');
		$header = "Final da Pesquisa";
		$texto = "Muito obrigada(o) por participar da pesquisa! A sua contribuição será muito
					valiosa para a continuidade dos estudos sobre os fatores que favorecem a aprendizagem
					de estudantes universitários brasileiros. Muito sucesso para você!";
		if ($this->f3->get("POST.naoaceito")) {
			$header = "Prezado estudante,";
			$texto = "Agradecemos a sua atenção. Esperamos poder contar com a sua participação em outro momento.";
			$this->f3->set('cordialmente',"Cordialmente,");	
		}

		$this->f3->set('header',$header);
		$this->f3->set('texto',$texto);
		$this->f3->set('assinado',"Evely Boruchovitch e equipe.");
		$this->f3->set('content','agradecimento.html');
		echo \Template::instance()->render('tela.htm');

	}

	function encryptMail($mail){
		return md5($mail);
	}

	function checkSum($valA,$valB){
		return ($valA == md5($valB)); 
	}

}