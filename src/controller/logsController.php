<?php 

require_once __DIR__ . '/../repository/logs.php';
require_once __DIR__ . '/../model/logs.php';

 class LogsController {
	private $logsDAO;

	public function __construct()
    { $this->logsDAO = new LogsDAO();
    
        }
	
	public function inserirLog($log) {
		return $this->logsDAO->inserir($log);
	}
	
	public function listarLogs() {
		return $this->logsDAO->listarLogs();
	}
}