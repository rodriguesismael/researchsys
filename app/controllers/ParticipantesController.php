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
			$titulo = $this->f3->get('POST.turma');
			$emails = $this->f3->get('POST.maillist');
			$quests = $this->f3->get('POST.questionarios');
			//var_dump($this->f3->get('POST'));die();
			$queryLista = "INSERT INTO listas (titulo,questionarios) VALUES (?,?)";
			
			try {
				$this->mapper->set('titulo',$titulo);
				$this->mapper->set('questionarios',serialize($quests));
				$this->mapper->insert();
				$idLista = $this->mapper->get('_id');
				foreach ($emails as $mail) {
					$queryEmails = "INSERT INTO convidados (idlista,email) VALUES (:idLista,:email)";
					$this->db->exec($queryEmails,array(':idLista'=>$idLista,':email'=>$mail));
				}
				$this->f3->reroute('/admin/participantes');
			} catch (Exception $e) {
				$this->f3->set('error',$e->getMessage());
			}
		}
		$questionarios = $this->db->exec("SELECT id,titulo FROM questionarios");
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
			$questionarios = $this->db->exec("SELECT id,titulo FROM questionarios");
			$this->f3->set('questionarios',$questionarios);
			$this->f3->set('label','Editar');
			$this->f3->set('content','admin/formParticipantes.html');
			echo \Template::instance()->render('tela.htm');	
		}
	}

	function convidar(){
		$this->isAdmin();
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
		<p>Muito obrigada!</p>";

		$smtp = new SMTP ( 'smtp.gmail.com', '587', 'tls', $this->f3->get('SMTP_MAIL'), $this->f3->get('SMTP_PASS') );

		$smtp->set('MIME-Version', '1.0');
		$smtp->set('Content-type', 'text/html;charset=UTF-8');
		$smtp->set('From', '"NoReply" <isrmdevonly@gmail.com>');
		$smtp->set('Subject', 'Convite');

		if ($this->f3->get('GET.lista')) {
			$listaObj = new ListaConvidadosDAO();
			$query = "SELECT id,email FROM convidados WHERE idlista=?";

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
			$nome = $this->f3->get('POST.nome');
			$ra = $this->f3->get('POST.ra');
			$genero = $this->f3->get('POST.genero');
			$email = $this->f3->get('POST.email');
			$nasc = $this->f3->get('POST.nasc');
			$universidade = $this->f3->get('POST.universidade');
			$curso = $this->f3->get('POST.curso');
			$periodo = $this->f3->get('POST.periodo');
			$tipoEnsino = $this->f3->get('POST.tipoEnsino');
			$semestre = $this->f3->get('POST.semestre');
			$columns = "(uid,nome,email,genero,nascimento,tipo_ensino,universidades_id,curso_id,periodo_curso,semestre,estadoAcesso)";
			$fields = "(:ra,:nome,:email,:genero,:nasc,:tipoEnsino,:universidade,:curso,:periodo,:semestre,:estado)";
			$this->f3->set('COOKIE.cadastro',true);
			
			$insertFields = array(
									":ra"=>$ra,":nome"=>$nome,":email"=>$email,":genero"=>$genero,":nasc"=>$nasc,
									":tipoEnsino"=>$tipoEnsino,":universidade"=>$universidade,":curso"=>$curso,":periodo"=>$periodo,
									":semestre"=>$semestre,":estado"=>serialize($estadoAtual)
								);
			$query = "INSERT INTO participantes $columns VALUES $fields";
			try {
				$this->db->exec($query, $insertFields);
				$queryEstado = "SELECT questionarios FROM participantes p JOIN convidados c ON p.email= c.email JOIN listas l on c.idlista = l.id WHERE p.uid=?";
				$result = $this->db->exec($queryEstado,array($ra));
				$this->f3->set('COOKIE.questionarios',$result[0]['questionarios']);
				$estadoAtual = $this->f3->get('COOKIE'); 
				$this->db->exec("UPDATE participantes SET estadoAcesso = ? WHERE uid=?",array(serialize($estadoAtual),$ra));	
				//recuperar questionários para o aluno responder
				//a cada questionario respondido, atualiza o cookie e a base de dados
				
				$nomePartes = explode(" ", $nome);
				$uniObj = new UniversidadeDAO();
				$uni = $uniObj->getById($universidade);
				$queryString = "?invnum=80010&ak=brazil&u=gyxc&p=wxk&requiredFirstName=$nomePartes[0]&requiredLastName=$nomePartes[1]&";
				$queryString.= "school=".str_replace(" ", "", $uni[0]['nome'])."&idnum=".$this->f3->get('PARAMS.email')."&email=".$email."&check_box=yes";
				echo "<br>Redirecting to https://ssl.collegelassi.com/portuguese/lassi.html$queryString";
				//die();
				$this->f3->reroute("https://ssl.collegelassi.com/portuguese/lassi.html$queryString");
			} catch (Exception $e) {
				$this->f3->set('error',$e->getMessage());
			}

		}
		
		if($this->f3->get('COOKIE.termo')){
			$result = array();
			$listaObj = new ListaConvidadosDAO();
			$universidade = new UniversidadeDAO();
			$universidades = $universidade->getList();
			$cursos = $universidade->getCursos();
			$result = $listaObj->getConvidadoByEmail($email);
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
		$result = $this->db->exec("SELECT p.email,p.uid,p.estadoAcesso,l.questionarios FROM participantes p JOIN 
			convidados c on p.email= c.email JOIN listas l on c.idlista = l.id WHERE md5(p.email)=?",array($email));
		$cookie = unserialize($result[0]["estadoAcesso"]);
		var_dump($result);
		//die();
		foreach ($cookie as $key => $value) {
			$this->f3->set("COOKIE.$key",$value);
		}
		$this->f3->set('SESSION.participante',$result[0]['uid']);
		$this->f3->set('SESSION.mail',$result[0]['email']);
		var_dump($this->f3->get("COOKIE"));
		$questionarios = unserialize($this->f3->get('COOKIE.questionarios')); 
		if(empty($questionarios)){
			//reroute finalziar pq acabou
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
		echo "Agradecemos sua participação";

	}

	function encryptMail($mail){
		return md5($mail);
	}

	function checkSum($valA,$valB){
		return ($valA == md5($valB)); 
	}

}