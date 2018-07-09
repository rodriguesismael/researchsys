<?php

class Universidade extends Controller{
	function home(){
		$this->f3->set('content','universidades.html');
		echo View::instance()->render('tela.htm');
		//ao inves do form, renderizar aqui uma tabela com todas as universidades cadastradas, juntamente com as opções de incluir uma nova, e editar/remover uma existente
	}

	function nova(){
		if ($this->f3->get('POST.universidade')) {
			echo 'chegou';
			$universidade=$this->f3->get('POST.universidade');
			$responsavel=$this->f3->get('POST.responsavel');
			$query = "INSERT into universidades (nome,responsavel) VALUES(?,?)";
			
			try {
				$this->db->exec($query,array(1=>$universidade,2=>$responsavel));
				$this->f3->reroute('/universidades');
			} catch (PDOException $e) {
				die($e->getMessage());
			}


			
		}
	}

	function editar(){
		/**
		passar o id como parametro
		renderizar mesmo form da inclusão, setando o value dos inputs


		**/


	}
}