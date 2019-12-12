<?php

class Universidade extends Controller{
	function home(){
		$this->isAdmin();
		$universidade = new UniversidadeDAO();
		$lista = $universidade->getList();
		$this->f3->set('SESSION.admin',"Logado");
		$this->f3->set('universidades',$lista);
		$this->f3->set('content','admin/homeUniversidades.html');
		echo \Template::instance()->render('tela.htm');
		//ao inves do form, renderizar aqui uma tabela com todas as universidades cadastradas, juntamente com as opções de incluir uma nova, e editar/remover uma existente
	}

	function nova(){
		$this->isAdmin();
		if ($this->f3->get('POST.universidade')) {
			//echo 'chegou';
			//var_dump($this->f3->get('POST'));
			$campos   				= array();
			$campos["nome"] 		= $this->f3->get('POST.universidade');
			$campos["responsavel"]  = $this->f3->get('POST.responsavel','');
			$campos["desc_resp"]    = $this->f3->get('POST.desc_resp','');
			$campos["endereco"]		= $this->f3->get('POST.endereco','');
			$campos["complemento"] 	= $this->f3->get('POST.complemento','');
			$campos["telefone"]		= $this->f3->get('POST.telefone','');
			$campos["cep"]			= $this->f3->get('POST.cep','');
			$campos["estado"] 		= $this->f3->get('POST.estado','');
			$campos["cidade"]		= $this->f3->get('POST.cidade','');
			
			$universidade = new UniversidadeDAO();

			$r = $universidade->save($campos);
			if($r){
				unset($universidade);
				$this->f3->reroute('/admin/universidades');	
			}
			
		}
	}

	function formulario() {
		$locais = new CidadesEstadosDAO();
		$estados = $locais->getEstados();
		unset($locais);
		$this->f3->set('action','/admin/universidades/adicionar');
		$this->f3->set('submit_button','Incluir');
		$this->f3->set('estados',$estados);
		$this->f3->set('content','/admin/formUniversidades.html');
		echo \Template::instance()->render('tela.htm');
	}

	function editar($params){
		$this->isAdmin();
		if ($this->f3->get('POST.unicode')) {
			$campos   				= array();
			$id 					= $this->f3->get('POST.unicode');
			$campos["nome"] 		= $this->f3->get('POST.universidade');
			$campos["responsavel"]  = $this->f3->get('POST.responsavel','');
			$campos["desc_resp"]    = $this->f3->get('POST.desc_resp','');
			$campos["endereco"]		= $this->f3->get('POST.endereco','');
			$campos["complemento"] 	= $this->f3->get('POST.complemento','');
			$campos["telefone"]		= $this->f3->get('POST.telefone','');
			$campos["cep"]			= $this->f3->get('POST.cep','');
			$campos["estado"] 		= $this->f3->get('POST.estado','');
			$campos["cidade"]		= $this->f3->get('POST.cidade','');
			
			$universidade = new UniversidadeDAO();

			$r = $universidade->save($campos,$id);
			if($r){
				unset($universidade);
				$this->f3->reroute('/admin/universidades');	
			}
		}
		$universidade = new UniversidadeDAO();
		$id = $this->f3->get('PARAMS.id');

		$dados = $universidade->getById($id);
		$locais = new CidadesEstadosDAO();
		$estados = $locais->getEstados();
		$cidades= $locais->getCidadesByEstado($dados[0]['estado']);
		unset($locais);
		unset($universidade);
		$this->f3->set('estados',$estados);
		$this->f3->set('cidades',$cidades);
		$this->f3->set('uni',$dados[0]);
		//var_dump($dados[0]);
		$this->f3->set('action','/admin/universidades/editar/'.$id);
		$this->f3->set('submit_button','Salvar');
		$this->f3->set('content','admin/formUniversidades.html');
		echo \Template::instance()->render('tela.htm');

	}

	function deletar($params){


	}
}