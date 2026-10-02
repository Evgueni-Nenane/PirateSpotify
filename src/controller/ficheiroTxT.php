<?php 
require_once __DIR__ . '/../model/utilizador.php';

class FicheiroTxt
{
    /**
     * Guarda as credenciais de um novo utilizador num ficheiro .txt,
     * na pasta "Documentos/Credenciais" do PC onde o servidor está a correr.
     * Como estamos em XAMPP local, isso é o teu próprio PC.
     */
    public static function guardarTxt($u)
    {
        // getenv() lê variáveis de ambiente do sistema operativo.
        // No Windows, "USERPROFILE" é o caminho da pasta do utilizador
        // (ex: C:\Users\kenne). É o equivalente PHP ao
        // System.getProperty("user.home") do Java.
        // O "?: getenv('HOME')" é só uma alternativa para caso estejamos
        // em Linux/Mac, onde a variável se chama "HOME" em vez de "USERPROFILE".
        $userHome = getenv('USERPROFILE') ?: getenv('HOME');

        // Monta dois caminhos possíveis para a pasta onde vamos guardar o ficheiro:
        // 1) dentro da pasta OneDrive (se a pessoa usar OneDrive para sincronizar Documentos)
        // 2) a pasta Documentos normal, sem OneDrive
        // DIRECTORY_SEPARATOR é "\" no Windows e "/" no Linux — usar esta
        // constante evita escrever barras à mão, que podem falhar consoante o SO.
        $documentosOneDrive = $userHome . DIRECTORY_SEPARATOR . 'OneDrive' . DIRECTORY_SEPARATOR . 'Documentos' . DIRECTORY_SEPARATOR . 'Credenciais';
        $documentosNormal = $userHome . DIRECTORY_SEPARATOR . 'Documentos' . DIRECTORY_SEPARATOR . 'Credenciais';

        // Decide qual dos dois caminhos usar:
        // dirname($documentosOneDrive) dá-nos a pasta ACIMA de "Credenciais",
        // ou seja, "OneDrive/Documentos". Se essa pasta existir no PC,
        // quer dizer que a pessoa tem OneDrive instalado, e usamos esse caminho.
        // Senão, usamos a pasta Documentos normal.
        // is_dir() verifica se um caminho existe E é mesmo uma pasta (não um ficheiro).
        $dirFinal = is_dir(dirname($documentosOneDrive)) ? $documentosOneDrive : $documentosNormal;

        // Se a pasta "Credenciais" ainda não existir dentro de Documentos,
        // tentamos criá-la agora.
        if (!is_dir($dirFinal)) {
            // mkdir() cria uma pasta. O segundo argumento (0777) são as
            // permissões da pasta (no Windows isto é ignorado, só importa
            // em Linux/Mac). O terceiro argumento (true) diz para criar
            // também pastas "pai" em falta, caso seja preciso (equivalente
            // ao mkdirs() do Java, que cria toda a árvore de pastas; o
            // mkdir() simples do Java só cria uma pasta de cada vez).
            if (!mkdir($dirFinal, 0777, true)) {
                // Se a criação falhar (ex: falta de permissões), avisamos
                // e saímos da função sem continuar.
                echo "Directório não encontrado!";
                return;
            }
        }

        // Monta o nome final do ficheiro, juntando o caminho da pasta
        // com "NomeApelido_Credenciais.txt" — ex: "JoaoSilva_Credenciais.txt"
        $nomeFicheiro = $dirFinal . DIRECTORY_SEPARATOR . $u->getNome() . $u->getApelido() . '_Credenciais.txt';

        // fopen() abre (ou cria, se não existir) o ficheiro para escrita.
        // O 'a' significa "append" — escreve no FIM do ficheiro, sem apagar
        // o que já lá estava. É o equivalente ao "true" que o FileWriter
        // do Java recebia como segundo argumento (também significava "append").
        $fp = fopen($nomeFicheiro, 'a');

        // fwrite() escreve texto no ficheiro que acabámos de abrir.
        // (string) $u converte o objeto Utilizador em texto, chamando
        // automaticamente o método __toString() que já existe na classe
        // Utilizador — é o equivalente ao u.toString() do Java.
        // O "\n\n" no fim adiciona duas linhas em branco, para separar
        // bem cada registo dentro do ficheiro.
        fwrite($fp, (string) $u . "\n\n");

        // fclose() fecha o ficheiro depois de escrever — importante para
        // garantir que os dados ficam mesmo gravados e o ficheiro não
        // fica "preso"/bloqueado pelo PHP.
        fclose($fp);
    }

    /**
     * Faz exatamente o mesmo que guardarTxt(), mas para quando uma
     * password é reposta (reset) — grava num ficheiro/pasta diferente,
     * "Credenciais_Resets", em vez de "Credenciais".
     */
    public static function guardarResetTxt($u)
    {
        $userHome = getenv('USERPROFILE') ?: getenv('HOME');

        $documentosOneDrive = $userHome . DIRECTORY_SEPARATOR . 'OneDrive' . DIRECTORY_SEPARATOR . 'Documentos' . DIRECTORY_SEPARATOR . 'Credenciais_Resets';
        $documentosNormal = $userHome . DIRECTORY_SEPARATOR . 'Documentos' . DIRECTORY_SEPARATOR . 'Credenciais_Resets';

        $dirFinal = is_dir(dirname($documentosOneDrive)) ? $documentosOneDrive : $documentosNormal;

        if (!is_dir($dirFinal)) {
            if (!mkdir($dirFinal, 0777, true)) {
                echo "Directório não encontrado!";
                return;
            }
        }

        // Aqui o nome do ficheiro usa getNomeCompleto() em vez de
        // Nome+Apelido separados, e o sufixo é "Reset_Cred" em vez de
        // "_Credenciais" — só uma diferença de nomenclatura entre os dois métodos.
        $nomeFicheiro = $dirFinal . DIRECTORY_SEPARATOR . $u->getNomeCompleto() . 'Reset_Cred.txt';

        $fp = fopen($nomeFicheiro, 'a');

        // Aqui chamamos toStringReset() em vez do __toString() automático,
        // porque queremos um texto diferente (mais resumido) para o
        // ficheiro de resets — o Utilizador.php já tem este método definido.
        fwrite($fp, $u->toStringReset() . "\n\n");

        fclose($fp);
    }
}