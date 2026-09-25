<?php
require_once __DIR__ . '/../model/log.php';
require_once __DIR__ . '/../model/nivelacesso.php';
require_once __DIR__ . '/../model/utilizador.php';
require_once __DIR__ . '/../model/sessao.php';
require_once __DIR__ . '/connection.php';

class LoginDAO
{
    private static $log;

    public function login($username, $senha, $logController)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT Codigo_User, Nome, Apelido, UserName, Genero, Email, Contacto, "
            . "Foto, Codigo_Nivel, NomeNivel"
            . " FROM Utilizador u"
            . " INNER JOIN NivelAcesso n"
            . " ON u.Codigo_Nivel = n.CodigoNivel"
            . " WHERE BINARY username = ? AND BINARY senha = ?";

        $ps = $conn->prepare($sql);
        $ps->bind_param("ss", $username, $senha);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $perfil = new NivelAcesso($row['Codigo_Nivel'], $row['NomeNivel']);

            $userSessao = new Utilizador(
                foto: $row['Foto'],
                nome: $row['Nome'],
                apelido: $row['Apelido'],
                user_name: $row['UserName'],
                perfil: $perfil,
                email: $row['Email']
            );

            Sessao::iniciarSessao($userSessao);

            $horaAgora = date('Y-m-d H:i:s');

            self::$log = new Logs(
                nome: Sessao::getUtilizadorLogado()->getNome(),
                apelido: Sessao::getUtilizadorLogado()->getApelido(),
                perfil: Sessao::getUtilizadorLogado()->getPerfil()->getNome(),
                email: Sessao::getUtilizadorLogado()->getEmail(),
                accao: "Realizou login",
                dataHora: $horaAgora
            );

            $logController->inserirLog(self::$log);
            return true;
        }
        return false;
    }

    public function isPrimeiroAcesso($username, $senha)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT primeiro_acesso FROM utilizador WHERE UserName = ? AND Senha = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ss", $username, $senha);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        return $row ? (bool) $row['primeiro_acesso'] : false;
    }

    public function atualizarSenha($username, $senhaAntiga, $novaSenha)
    {
        $conn = connection::connectionDB();
        $sql = "UPDATE utilizador SET senha = ?, primeiro_acesso = 0 WHERE BINARY username = ? AND BINARY senha = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("sss", $novaSenha, $username, $senhaAntiga);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function resetarSenha($codigoUser, $senha)
    {
        $conn = connection::connectionDB();
        $sql = "UPDATE utilizador SET senha = ?, primeiro_acesso = 1 WHERE codigo_user = ?";
        $ps = $conn->prepare($sql);
        $senhaPadrao = "User258";
        $ps->bind_param("si", $senhaPadrao, $codigoUser);
        $ps->execute();
        return $ps->affected_rows > 0;
    }
}