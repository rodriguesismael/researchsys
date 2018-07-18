<?php

class Administrator extends Controller{

	function isLogged(){
		return true;
		//return isset($this->f3->get('SESSION.admin'));
	}
	function home(){
		$this::checkIsLogged();
		$this->f3->set('content','home.htm');
		echo \Template::instance()->render('tela.htm');
	}

	function login(){
		if ($this->f3->get('POST.user')) {
			$usuario = $this->f3->get('POST.user');
			$senha = $this->f3->get('POST.pass');

			$userData = $this->userAuth($usuario,md5($senha));
			if (!empty($userData[0])) {
				//die('man');
				$this->f3->set('SESSION.nomeusr',$userData[0]['nome']);
				$this->f3->set('SESSION.papelusr',$userData[0]['papel']);
				$this->f3->set('SESSION.idusr',$userData[0]['id']);
				$this->f3->reroute('/admin');
			}else{
				$this->f3->set('error','Dados inválidos! Tente novamente');
			}
		}
		$this->f3->set('content','login.html');
		echo \Template::instance()->render('tela.htm');
	}

	function userAuth($user,$pass){
		$query="SELECT id,login,nome,papel FROM usuarios WHERE login= ? AND senha= ?";
		$userResult = array();
		try {
			$userResult = $this->db->exec($query,array($user,$pass));
		} catch (PDOException $e) {
			echo $e->getMessage();
		}

		return $userResult;
	}

	public function instance(){
		return $this;
	}

	function logout() {
		$this->f3->set('SESSION.nomeusr', '');
		$this->f3->set('SESSION.papelusr', '');
		$this->f3->set('SESSION.idusr', '');
		$this->f3->reroute('/admin');	
	}

	public function checkIsLogged(){
		if($this->f3->get('SESSION.idusr') == 1){
			return true;
		}else{
			$this->f3->reroute('/admin/login');
		}
	}
}