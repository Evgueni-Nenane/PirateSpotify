<?php 
require_once 'connection.php';
require_once 'Editora.php';

class EditoraDAO {
    public function inserir($editora) {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO Editora (Nome_Editora, Email_Editora, Contacto_Editora, Endereco) VALUES (?, ?, ?, ?)";
        $ps = $conn->prepare($sql);
        $NomeEditora = $editora->getNomeEditora();
        $EmailEditora = $editora->getEmailEditora();
        $ContactoEditora = $editora->getContactoEditora();
        $Endereco = $editora->getEndereco();
        $ps->bind_param("ssss", $NomeEditora, $EmailEditora, $ContactoEditora, $Endereco);
        if ($ps->execute()) {
            return $conn->insert_id;
        }
        return -1;
    }
           
    public function listarTodos() {
        $editoras = [];
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Editora";
        $result = $conn->prepare($sql);
        $result->execute();
        $rows = $result->get_result();
        while ($row = $rows->fetch_assoc()) {
            $editoras[] = new Editora(
                $row['Codigo_Editora'],
                $row['Nome_Editora'],
                $row['Contacto_Editora'],
                $row['Email_Editora'],
                $row['Endereco']
            );
        }
        return $editoras;
    }
    public function buscarPorCodigo($codigoEditora) {
        $conn = connection::connectionDB();
		$sql = "SELECT * FROM Editora "
            . "WHERE Codigo_Editora = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoEditora);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();
        if (!$row) {
            return null;
        }
        return new Editora(
            $row['Codigo_Editora'],
            $row['Nome_Editora'],
            $row['Contacto_Editora'],
            $row['Email_Editora'],
            $row['Endereco']
        );
    }   
    public function atualizar($editora) {
        $conn = connection::connectionDB();
    	$sql = "UPDATE Editora SET email_editora = ?, contacto_editora = ?, endereco = ? "
    			+ "WHERE Codigo_Editora = ? ";
                $ps = $conn->prepare($sql);
    	            $email = $editora->getEmailEditora();
                    $contacto = $editora->getContactoEditora();
                    $endereco = $editora->getEndereco();
                    $codigo = $editora->getCodigoEditora();
                    $ps->bind_param("sssi", $email, $contacto, $endereco, $codigo);
                    return $ps->execute();
    }
    public function temRelacionamento($codigo) {
        $conn = connection::connectionDB();
    	$sql = "SELECT COUNT(*) AS Quantidade"
    			+ " FROM Edicao "
    			+ " WHERE Codigo_editora = ?";
    	        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();
        return $row['Quantidade'] > 0;
    }
    
    public function remover($codigo) {
    	if ($this->temRelacionamento($codigo)) {
    		return false;
    	}
        $conn = connection::connectionDB();
    	$sql = "DELETE FROM Editora WHERE codigo_editora = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
         $ps->execute();
         return $ps->affected_rows > 0;
    	
    }
    
    
    public function listarPorCodigo($codigoDisco) {
        $conn = connection::connectionDB();
        $sql = "SELECT e.* FROM Editora e INNER JOIN Edicao ed"
            . " ON e.Codigo_Editora = ed.Codigo_Editora"
            . " WHERE ed.Codigo_Disco = ?";
            $ps = $conn->prepare($sql);
            $ps->bind_param("i", $codigoDisco);
            $ps->execute();
            $rows = $ps->get_result()->fetch_assoc();
            if(!$rows) {
                return null;
            }
            return new Editora(
                $rows['Codigo_Editora'],
                $rows['Nome_Editora'],
                $rows['Contacto_Editora'],
                $rows['Email_Editora'],
                $rows['Endereco']
            );
    }
}