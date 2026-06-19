<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\UsuarioModel;

class AuditLogController extends BaseController
{
    private $model;
    private $usuarioModel;

    protected $requiredPermissions = [
        'index' => 'config_audit_view',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = new AuditLogModel();
        $this->usuarioModel = new UsuarioModel();
    }

    public function index()
    {
        // Filtros
        $filtros = [
            'user_id' => filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT),
            'module' => filter_input(INPUT_GET, 'module', FILTER_SANITIZE_SPECIAL_CHARS),
            'action' => filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 20;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca logs e usuários
        $logs = $this->model->getLogs($filtros, $itensPorPagina, $offset);
        $usuarios = $this->usuarioModel->getListaUsuarios();

        // Verifica se há mais logs para a próxima página (lógica simples)
        $temMais = count($logs) >= $itensPorPagina;

        $data = [
            'pageTitle' => 'Logs de Auditoria',
            'logs' => $logs,
            'usuarios' => $usuarios,
            'filtros' => $filtros,
            'paginaAtual' => $paginaAtual,
            'temMais' => $temMais,
        ];

        $this->renderView('audit_logs/index', $data);
    }
}
