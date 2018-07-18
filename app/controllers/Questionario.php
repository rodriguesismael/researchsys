<?php

class Questionario extends Controller{
	function regulacaoMotivacao(){
		$this->f3->set('content','berm.html');
		echo \Template::instance()->render('tela.htm');		
	}

	function concepcoesInteligencia(){
		$this->f3->set('content','eci.html');
		echo \Template::instance()->render('tela.htm');		
	}
	function adiamentoRecompensa(){
		$this->f3->set('content','eara.html');
		echo \Template::instance()->render('tela.htm');
	}
	function autoEficaciaAprendizagem(){
		$this->f3->set('content','escala-autoeficacia.html');
		echo \Template::instance()->render('tela.htm');		
	}
	function estrategiasAutoPrejudiciais(){
		$this->f3->set('content','escala-autoprejudiciais.html');
		echo \Template::instance()->render('tela.htm');		
	}
	function estrategiasRegulacaoMotivacao(){
		$this->f3->set('content','escala-regulacao.html');
		echo \Template::instance()->render('tela.htm');		
	}
	function autorregulacaoControle(){
		$this->f3->set('content','qeacd.html');
		echo \Template::instance()->render('tela.htm');		
	}
	function slri(){
		$this->f3->set('content','slri.html');
		echo \Template::instance()->render('tela.htm');		
	}
}