<?php

namespace App\Core;

use PDO;

/**
 * Classe Model base.
 * Cada model da aplicação deve herdar desta classe.
 * Ela lida com a conexão com o banco de dados.
 */
abstract class Model
{
    protected $db;

    public function __construct()
    {
        // Pega a instância da conexão PDO da classe Connection
        $this->db = Connection::getInstance();
    }
}