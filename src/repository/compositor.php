<?php 
require_once 'Compositor.php';
class CompositorDAO {

    public function inserir($compositor) {
        $conn = connection::connectionDB(); //COLOQUEM O METODO DA CLASSE Q FAZ CONECT COM A BD EM TODOS OS METODOS
        $sql = "INSERT INTO Compositor (Nome_Compositor, Apelido_Compositor, "
            . "Contacto_Compositor, Email_Compositor) VALUES (?, ?, ?, ?)";
            $ps = $conn->prepare($sql);
            $NomeCompositor = $compositor->getNomeCompositor();
            $ApelidoCompositor = $compositor->getApelidoCompositor();
            $ContactoCompositor = $compositor->getContactoCompositor();
            $EmailCompositor = $compositor->getEmailCompositor();
            $ps->bind_param("ssss", $NomeCompositor, $ApelidoCompositor, $ContactoCompositor, $EmailCompositor);
             if ($ps->execute()) {
            return $conn->insert_id;
        }
        return -1;
      
    }
    public function listarTodos() {
        $compositores=[];
        $conn = connection::connectionDB(); 
        $sql = "SELECT * FROM Compositor";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $compositor = new Compositor(
                $row['Codigo_Compositor'],
                $row['Nome_Compositor'],
                $row['Apelido_Compositor'],
                $row['Contacto_Compositor'],
                $row['Email_Compositor']
            );
            $compositores[] = $compositor;
        }
        return $compositores;
    }
      
    
    public function listarPorCodigo($codigoDisco) {
        $conn = connection::connectionDB();
        $sql = "SELECT c.* FROM Compositor c INNER JOIN Compositor_DC cd"
        		. " ON c.Codigo_Compositor = cd.Codigo_Compositor WHERE cd.Codigo_DC = ?";
                $ps = $conn->prepare($sql);
                $ps->bind_param("i", $codigoDisco);
                $result = $ps->execute();
                $row = $ps->get_result()->fetch_assoc();
                if (!$row) {
                    return null;
                }
                return new Compositor(
                    $row['Codigo_Compositor'],
                    $row['Nome_Compositor'],
                    $row['Apelido_Compositor'],
                    $row['Contacto_Compositor'],
                    $row['Email_Compositor']
                );
    }
    
    
    public function buscarPorCodigo($codigoCompositor) {
        $conn = connection::connectionDB();
    		$sql = "SELECT * FROM Compositor "
    				. "WHERE Codigo_Compositor = ?";
                     $ps = $conn->prepare($sql);
                     $ps->bind_param("i", $codigoCompositor);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();
        if (!$row) {
            return null;
        }
        return new Compositor(
            $row['Codigo_Compositor'],
            $row['Nome_Compositor'],
            $row['Apelido_Compositor'],
            $row['Contacto_Compositor'],
            $row['Email_Compositor']
        );
    }
      
     
    public function atualizar($compositor) {
        $conn = connection::connectionDB();
    	$sql = "UPDATE Compositor SET email_compositor = ?, contacto_compositor = ? "
    			. "WHERE Codigo_compositor = ? ";
                $ps = $conn->prepare($sql);
                $email = $compositor->getEmailCompositor();
                $contacto = $compositor->getContactoCompositor();
                $codigo = $compositor->getCodigoCompositor();
                $ps->bind_param("ssi", $email, $contacto, $codigo);
                return $ps->execute();
    }

    
    public function temRelacionamento($codigo) {
        $conn = connection::connectionDB();
    		$sql = "SELECT COUNT(*) AS Quantidade"
    			+ " FROM Compositor_DC "
    			+ " WHERE Codigo_Compositor = ?";
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
	    	$sql = "DELETE FROM Compositor WHERE codigo_compositor = ?";
	    	$ps = $conn->prepare($sql);
	    	$ps->bind_param("i", $codigo);
	    	return $ps->execute();
            return $ps->affected_rows > 0;  
    }
    public function inserRelacaoDiscoCompositor($codigoDisco, $codigoCompositor) {
        $conn = connection::connectionDB();
	    	$sql = "INSERT INTO Compositor_DC (Codigo_Compositor, Codigo_DC) VALUES (?,?)";
	    	$ps = $conn->prepare($sql);
	    	$ps->bind_param("ii", $codigoCompositor, $codigoDisco);
	    	return $ps->execute();
    }
}
