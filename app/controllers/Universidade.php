<?php

class Universidade extends Controller{
	function home(){
		if($this->f3->get('SESSION.idusr') == 1){
			pass;
		}else{
			$this->f3->reroute('/admin/login');
		}
		$getUniversidades = "SELECT * FROM universidades";
		$result = "";
		try {
		 	$result = $this->db->exec($getUniversidades);

		 } catch (PDOException $e) {
		 	die($e->getMessage());
		}
		$this->f3->set('SESSION.admin',"Logado");
		$this->f3->set('universidades',$result);
		$this->f3->set('content','homeUniversidades.html');
		echo \Template::instance()->render('tela.htm');
		//ao inves do form, renderizar aqui uma tabela com todas as universidades cadastradas, juntamente com as opções de incluir uma nova, e editar/remover uma existente
	}

	function nova(){
		if ($this->f3->get('POST.universidade')) {
			echo 'chegou';
			$universidade   = array();
			$universidade[] = $this->f3->get('POST.universidade');
			$universidade[] = $this->f3->get('POST.responsavel','');
			$universidade[]	= $this->f3->get('POST.endereco','');
			$universidade[] = $this->f3->get('POST.complemento','');
			$universidade[]	= $this->f3->get('POST.telefone','');
			$universidade[]	= $this->f3->get('POST.cep','');
			$universidade[] = $this->f3->get('POST.estado','');
			$universidade[]	= $this->f3->get('POST.cidade','');
			$query = "INSERT into universidades (nome,responsavel,endereco,complemento,telefone,cep,estado,cidade) VALUES(?,?,?,?,?,?,?,?)";
			
			try {
				$this->db->exec($query, $universidade);
				$this->f3->reroute('/universidades');
			} catch (PDOException $e) {
				die($e->getMessage());
			}


			
		}
	}

	function formulario() {
		$this->f3->set('content','universidades.html');
		echo \Template::instance()->render('tela.htm');
	}

	function editar($params){
		$query = "SELECT * FROM universidades WHERE id=?";
		$dados = array();
		$id = ($this->f3->get('PARAMS.id'));
		try {
			$dados = $this->db->exec($query, array($id));
			$this->f3->set('uni',$dados[0]);
		} catch (Exception $e) {
			$this->f3->set('error',$e->getMessage());
		}

		$this->f3->set('content','universidades.html');
		echo \Template::instance()->render('tela.htm');		

	}

	function deletar($params){


	}
}