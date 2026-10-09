<?php
require_once __DIR__ . '/../model/nivelacesso.php';
require_once __DIR__ . '/connection.php';

class NivelAcessoDAO
{
    public function inserir($nome)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO NivelAcesso (NomeNivel) VALUES (?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("s", $nome);

        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function listarNiveis()
    {
        $niveis = [];
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM NivelAcesso";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $niveis[] = new NivelAcesso(
                $row['CodigoNivel'],
                $row['NomeNivel']
            );
        }
        return $niveis;
    }

    // Todas as linhas da tabela (com as permissões), para mostrar na aba Perfis
    public function listarPerfis()
    {
        $conn = connection::connectionDB();
        $result = $conn->query("SELECT * FROM NivelAcesso ORDER BY CodigoNivel");
        $perfis = [];
        while ($row = $result->fetch_assoc()) {
            $perfis[] = $row;
        }
        return $perfis;
    }

    // Uma linha só (devolve null se não existir)
    public function buscarPorCodigo($codigo)
    {
        $conn = connection::connectionDB();
        $ps = $conn->prepare("SELECT * FROM NivelAcesso WHERE CodigoNivel = ?");
        $ps->bind_param("i", $codigo);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // Permissões de um nível, no formato que a classe Permissao usa
    public function regrasDoNivel($codigo)
    {
        $r = $this->buscarPorCodigo($codigo);

        return [
            'ler'          => !empty($r['PodeLer']),
            'adicionar'    => !empty($r['PodeAdicionar']),
            'editar'       => !empty($r['PodeEditar']),
            'remover'      => !empty($r['PodeRemover']),
            'utilizadores' => !empty($r['PodeUtilizadores']),
            'logs'         => !empty($r['PodeLogs']),
        ];
    }

    public function criar($nome, $perm)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO NivelAcesso
                (NomeNivel, PodeLer, PodeAdicionar, PodeEditar, PodeRemover, PodeUtilizadores, PodeLogs)
                VALUES (?, 1, ?, ?, ?, ?, ?)";
        $ps = $conn->prepare($sql);

        $add  = !empty($perm['adicionar']) ? 1 : 0;
        $edit = !empty($perm['editar']) ? 1 : 0;
        $rem  = !empty($perm['remover']) ? 1 : 0;
        $user = !empty($perm['utilizadores']) ? 1 : 0;
        $logs = !empty($perm['logs']) ? 1 : 0;
        $ps->bind_param("siiiii", $nome, $add, $edit, $rem, $user, $logs);

        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function atualizar($codigo, $nome, $perm)
    {
        if ($codigo <= 4) return false; // perfis base não se editam

        $conn = connection::connectionDB();
        $sql = "UPDATE NivelAcesso SET NomeNivel=?, PodeAdicionar=?, PodeEditar=?,
                PodeRemover=?, PodeUtilizadores=?, PodeLogs=? WHERE CodigoNivel=?";
        $ps = $conn->prepare($sql);

        $add  = !empty($perm['adicionar']) ? 1 : 0;
        $edit = !empty($perm['editar']) ? 1 : 0;
        $rem  = !empty($perm['remover']) ? 1 : 0;
        $user = !empty($perm['utilizadores']) ? 1 : 0;
        $logs = !empty($perm['logs']) ? 1 : 0;
        $ps->bind_param("siiiiii", $nome, $add, $edit, $rem, $user, $logs, $codigo);

        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function remover($codigo)
    {
        if ($codigo <= 4) return false; // perfis base não se apagam

        $conn = connection::connectionDB();

        // Não apaga se houver utilizadores com este perfil.
        // AJUSTA o nome da coluna se na tabela utilizador for diferente de CodigoNivel.
        $ps = $conn->prepare("SELECT COUNT(*) AS total FROM utilizador WHERE CodigoNivel = ?");
        $ps->bind_param("i", $codigo);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();
        if ($row['total'] > 0) return false;

        $ps = $conn->prepare("DELETE FROM NivelAcesso WHERE CodigoNivel = ?");
        $ps->bind_param("i", $codigo);
        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}