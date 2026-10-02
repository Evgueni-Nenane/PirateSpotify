<?php 
require_once __DIR__ . '/../repository/login.php';
require_once __DIR__ . '/logscontroller.php';

 class LoginController {
	private $loginDAO;
	private $logController;
	
	public function __construct()
    {
        $this->loginDAO = new LoginDAO();
        $this->logController = new LogsController();
    }


	public function login($nome, $senha) {
        return $this->loginDAO->login($nome,$senha,$this->logController);
	}
	
	public function isPrimeiroAcesso($username, $senha) {
		return $this->loginDAO->isPrimeiroAcesso($username,$senha);
	}
	
	public function atualizarSenha($username,$senhaAntiga, $senhaNova) {
		return $this->loginDAO->atualizarSenha($username,$senhaAntiga,$senhaNova);
	}
	
	public function resetarSenha($codigoUser, $senha) {
		return $this->loginDAO->resetarSenha($codigoUser,$senha);
	}
}
