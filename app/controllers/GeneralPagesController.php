<?php
class GeneralPagesController extends Controller{
	public function infoPage(){
		$this->f3->set('content','informacoes.html');
		echo \Template::instance()->render('tela.htm');
	}

	public function universidadesParticipantesPage(){
		$universidade = new UniversidadeDAO();
		$universidades = $universidade->getList();
		unset($universidade);
		$this->f3->set('universidades',$universidades);
		$this->f3->set('content','sobreUniversidades.html');
		echo \Template::instance()->render('tela.htm');
	}

	public function contatoPage(){
		$this->f3->set('content','contato.html');
		echo \Template::instance()->render('tela.htm');
	}	
}