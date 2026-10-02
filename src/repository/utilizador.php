<?php
require_once __DIR__ . '/../model/utilizador.php';
require_once __DIR__ . '/../model/nivelacesso.php';
require_once __DIR__ . '/connection.php';

class UtilizadorDAO
{
    public function inserir($utilizador)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO Utilizador (Nome, Apelido, UserName, Genero, Perfil, "
            . "Email, Contacto, Senha, Primeiro_Acesso, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $ps = $conn->prepare($sql);

        $nome = $utilizador->getNome();
        $apelido = $utilizador->getApelido();
        $userName = $utilizador->getUser_name();
        $genero = $utilizador->getGenero();
        $perfil = $utilizador->getPerfil()->getCodigoNivel();
        $email = $utilizador->getEmail();
        $contacto = $utilizador->getContacto();
        $senha = $utilizador->getSenha();
        $primeiroAcesso = $utilizador->isPrimeiroAcesso() ? 1 : 0;
        $foto = $utilizador->getFoto();

        $ps->bind_param(
            "ssssisssis",
            $nome,
            $apelido,
            $userName,
            $genero,
            $perfil,
            $email,
            $contacto,
            $senha,
            $primeiroAcesso,
            $foto
        );

        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function atualizarUser($codigoUser, $utilizador)
    {
        $conn = connection::connectionDB();
        $sql = "UPDATE Utilizador SET foto = ?, Perfil = ?, Email = ?, Contacto = ? WHERE Codigo_User = ?";
        $ps = $conn->prepare($sql);

        $foto = $utilizador->getFoto();
        $perfil = $utilizador->getPerfil()->getCodigoNivel();
        $email = $utilizador->getEmail();
        $contacto = $utilizador->getContacto();

        $ps->bind_param("sissi", $foto, $perfil, $email, $contacto, $codigoUser);
        $ps->execute();
        return true;
    }

    public function adicionarFoto($codigoUser, $utilizador)
    {
        $conn = connection::connectionDB();
        $sql = "UPDATE Utilizador SET foto = ? WHERE Codigo_User = ?";
        $ps = $conn->prepare($sql);

        $foto = $utilizador->getFoto();
        $ps->bind_param("si", $foto, $codigoUser);
        $ps->execute();
        return true;
    }

    public function buscarFoto($codigoUser)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT foto FROM Utilizador WHERE Codigo_User = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoUser);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        return $row ? $row['foto'] : null;
    }

    public function listarTodos()
    {
        $utilizadores = [];
        $conn = connection::connectionDB();
        $sql = "SELECT Codigo_User, Nome, Apelido, UserName, Genero, Email, Contacto, NomeNivel, Utilizador.Perfil "
            . "FROM Utilizador "
            . "INNER JOIN NivelAcesso ON Utilizador.Perfil = NivelAcesso.CodigoNivel";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $perfil = new NivelAcesso($row['Perfil'], $row['NomeNivel']);
            $utilizadores[] = new Utilizador(
                codigo: $row['Codigo_User'],
                nome: $row['Nome'],
                apelido: $row['Apelido'],
                user_name: $row['UserName'],
                genero: $row['Genero'],
                perfil: $perfil,
                email: $row['Email'],
                contacto: $row['Contacto']
            );
        }
        return $utilizadores;
    }

    public function buscarPorId($codigoUser)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT Codigo_User, Nome, Apelido, UserName, Genero, Email, Contacto, Foto, NomeNivel, CodigoNivel"
            . " FROM Utilizador"
            . " INNER JOIN NivelAcesso ON Utilizador.Perfil = NivelAcesso.CodigoNivel"
            . " WHERE Codigo_User = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoUser);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $perfil = new NivelAcesso($row['CodigoNivel'], $row['NomeNivel']);
            $utilizador = new Utilizador(
                foto: $row['Foto'],
                perfil: $perfil,
                email: $row['Email'],
                contacto: $row['Contacto']
            );
            $utilizador->setNome($row['Nome']);
            $utilizador->setApelido($row['Apelido']);
            $utilizador->setUser_name($row['UserName']);
            $utilizador->setGenero($row['Genero']);
            $utilizador->setCodigo($row['Codigo_User']);
            $utilizador->setFoto($row['Foto']);
            return $utilizador;
        }
        return null;
    }

    public function remover($codigoUser)
    {
        $conn = connection::connectionDB();
        $sql = "DELETE FROM Utilizador WHERE Codigo_User = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoUser);
        $ps->execute();
        return true;
    }


    public function resetarSenha($codigoUser, $novaSenha)
{
    $conn = connection::connectionDB();
    $ps = $conn->prepare("UPDATE Utilizador SET Senha = ?, Primeiro_Acesso = 1 WHERE Codigo_User = ?");
    $ps->bind_param("si", $novaSenha, $codigoUser);
    $ps->execute();
    return $ps->affected_rows > 0;
}
}
