<?php

class Administrator extends Controller{
	function home(){
		$this->f3->set('content','home.htm');
		echo View::instance()->render('tela.htm');
	}
}