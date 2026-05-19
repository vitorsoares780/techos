<?php
require_once __DIR__ . '/config.php';

class Database {
    private $host = "mysql";
    private $db_name = "sistema_ordens_servico";
    private $username = "root";
    private $password = "asdf1234";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        $tentativas = 5;
        $delay = 2; // segundos

        for ($i = 0; $i < $tentativas; $i++) {
            try {
                $this->conn = new PDO(
                    "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );

                $this->conn->exec("SET time_zone = '-03:00'");

                return $this->conn; // sucesso

            } catch (PDOException $exception) {
                error_log("Tentativa " . ($i+1) . " falhou: " . $exception->getMessage());
                sleep($delay);
            }
        }

        // Se chegou aqui, falhou tudo
        echo "<pre>Erro final de conexão: não conseguiu conectar ao MySQL</pre>";
        return null;
    }
}
?>