<?php

class ParticipantesController extends Controller{

	function home(){
		$this->isAdmin();
		$query = "SELECT * FROM listas";
		$listaParticipantes = $this->db->exec($query);
		$this->f3->set('listas',$listaParticipantes);
		$this->f3->set('content','admin/homeParticipantes.html');
		echo \Template::instance()->render('tela.htm');
	}

	function nova(){
		$this->isAdmin();
		if ($this->f3->get('POST.maillist')) {		
			$emails = $this->f3->get('POST.maillist');
			$quests = $this->f3->get('POST.escalas');

			$queryLista = "INSERT INTO listas (questionarios) VALUES (?)";
			
			try {
				//$this->db->exec($queryLista,array(serialize($quests)));
				$this->mapper->set('questionarios',serialize($quests));
				$this->mapper->insert();
				$idLista = $this->mapper->get('_id');
				$this->mapper->set('titulo','Lista de Participantes #'.$idLista);
				$this->mapper->update();
				foreach ($emails as $mail) {
					$queryEmails = "INSERT INTO convidados (idlista,email) VALUES (:idLista,:email)";
					$this->db->exec($queryEmails,array(':idLista'=>$idLista,':email'=>$mail));
				}
				
			} catch (Exception $e) {
				$this->f3->set('error',$e->getMessage());
			}
		}
		$this->f3->set('label','Nova');
		$this->f3->set('content','admin/formParticipantes.html');
		echo \Template::instance()->render('tela.htm');	

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
			$this->f3->set('content','admin/formParticipantes.html');
			echo \Template::instance()->render('tela.htm');	
		}
	}

	function convidar(){
		$this->isAdmin();
		/*
		$msgEmail = "<p><Prezado(a) aluno(a),</p>
		<p style='text-ident:2em;text-align:justify;'>
		Meu nome é Evely Boruchovitch. Sou professora da Faculdade de Educação da Universidade
		Estadual de Campinas – UNICAMP. Gostaria de convidá-lo (a) a participar de uma pesquisa
		nacional que visa conhecer melhor os fatores que favorecem ou dificultam a aprendizagem de
		estudantes universitários brasileiros. Ao participar, você receberá, por e-mail, resultados e
		informações que poderão lhe dar uma ideia geral de como você está como estudante. Os
		dados a serem obtidos contribuirão para o desenvolvimento de melhores práticas educativas e
		para o fortalecimento da capacidade de aprender dos alunos do Ensino Superior. As suas
		respostas são confidenciais e asseguro o sigilo da sua identificação pessoal.
		</p>
		<p>
			Sua colaboração é muito valiosa! Para participar da pesquisa, por favor, acesse o
			seguinte link
		</p>
		<p>Muito obrigada!</p>";*/

		$smtp = new SMTP ( 'smtp.gmail.com', '587', 'tls', 'isrmdevonly@gmail.com', 'devaccountqwepoi32@' );

		$smtp->set('MIME-Version', '1.0');
		$smtp->set('Content-type', 'text/html;charset=UTF-8');
		$smtp->set('From', '"Dev" <isrmdevonly@gmail.com>');
		$smtp->set('To', '"Ismael IC" <ismael@ic.unicamp.br>');
		$smtp->set('Subject', 'Message from F3');

		if ($this->f3->get('GET.lista')) {
			$query = "SELECT id,email FROM convidados WHERE idlista=?";
			try {
				$response="";
				$dados = $this->db->exec($query,array($this->f3->get('GET.lista')));
				if(empty($dados)){
					$response = json_encode(array('code'=>0,'msg'=>"Não há emails para esta lista!"));
					echo $response;
				}else{
					$sendTo =array();
					foreach ($dados as $emails) {
						$sendTo[] = $emails['email'];
						$smtp->send($msgEmail) or die("ERRO ".$smtp->log());
					}
					$response = json_encode($sendTo);
					echo $response;

				}
			} catch (PDOException $e) {
				
			}
		}

	}

	function participar(){
		if ($this->f3->get('POST.aceito')) {
			$this->f3->set('COOKIE.termo',true);
		}
		if($this->f3->get('COOKIE.termo')){
			$this->f3->reroute('/cadastrar/'.$this->f3->get('PARAMS.email'));
		}else{
			$this->f3->set('content','termo.html');
			echo \Template::instance()->render('tela.htm');			
		}
	}
	function cadastrar(){
		$email = $this->f3->get('PARAMS.email');

		if($this->f3->get('POST.nome')){
			//var_dump($this->f3->get('POST'));
			$nome = $this->f3->get('POST.nome');
			$ra = $this->f3->get('POST.ra');
			$genero = $this->f3->get('POST.genero');
			$email = $this->f3->get('POST.email');
			$nasc = $this->f3->get('POST.nasc');
			$universidade = $this->f3->get('POST.universidade');
			//$curso
			$periodo = $this->f3->get('POST.periodo');
			$tipoEnsino = $this->f3->get('POST.tipoEnsino');
			$semestre = $this->f3->get('POST.semestre');
			$columns = "(uid,nome,email,genero,nascimento,tipo_ensino,universidades_id,periodo_curso,semestre,estadoAcesso)";
			$fields = "(:ra,:nome,:email,:genero,:nasc,:tipoEnsino,:universidade,:periodo,:semestre,:estado)";
			$this->f3->set('COOKIE.cadastro',true);
			$estadoAtual = $this->f3->get('COOKIE'); 
			$insertFields = array(
									":ra"=>$ra,":nome"=>$nome,":email"=>$email,":genero"=>$genero,":nasc"=>$nasc,
									":tipoEnsino"=>$tipoEnsino,":universidade"=>$universidade,":periodo"=>$periodo,
									":semestre"=>$semestre,":estado"=>serialize($estadoAtual)
								);
			$query = "INSERT INTO participantes $columns VALUES $fields";
			try {
				$this->db->exec($query, $insertFields);			
				//recuperar questionários para o aluno responder
				//a cada questionario respondido, atualiza o cookie e a base de dados
				echo "<br>Redirecting to LASSI...";
				die();
			} catch (Exception $e) {
				$this->f3->set('error',$e->getMessage());
			}

		}
		
		if($this->f3->get('COOKIE.termo')){
			$queryEmail = "SELECT email FROM convidados WHERE email=?";
			$result = array();
			$queryUnis = "SELECT id,nome FROM universidades";
			$universidades = $this->db->exec($queryUnis);
			$result = $this->db->exec($queryEmail, array($email));
			if(count($result) > 0){
				$this->f3->set('email',$email);
				$this->f3->set('universidades',$universidades);
				$this->f3->set('content','demograficos.html');
				echo \Template::instance()->render('tela.htm');
			}

		}else{
			$this->f3->reroute('/participar/'.$this->f3->get('PARAMS.email'));
		}
	}

}