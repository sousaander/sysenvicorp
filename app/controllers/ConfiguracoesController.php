<?php

namespace App\Controllers;

/**
 * Controlador para a seção de Configurações do sistema.
 */
class ConfiguracoesController extends BaseController
{
    /**
     * Garante que o construtor da classe pai (BaseController) seja chamado.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Exibe a página principal de configurações.
     */
    public function index()
    {
        $data = [
            'pageTitle' => 'Configurações Gerais',
        ];

        $this->renderView('configuracoes/index', $data);
    }
}
