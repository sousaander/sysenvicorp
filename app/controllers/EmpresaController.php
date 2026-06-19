<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\EmpresaModel;

class EmpresaController extends BaseController
{
    private $empresaModel;

    public function __construct()
    {
        parent::__construct();
        $this->empresaModel = new EmpresaModel();
    }

    /**
     * Exibe o formulário com os dados da empresa.
     */
    public function index()
    {
        $empresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'pageTitle' => 'Dados da Empresa',
            'empresa' => $empresa,
        ];

        $this->renderView('empresa/index', $data);
    }

    /**
     * Salva os dados da empresa e o certificado digital.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/empresa');
            exit();
        }

        $dados = $_POST;
        $certificado = $_FILES['certificado_digital'] ?? null;
        $caminhoCertificado = null;

        // Garante que o CNPJ seja salvo apenas com números
        if (isset($dados['cnpj'])) {
            $dados['cnpj'] = preg_replace('/\D/', '', $dados['cnpj']);
        }

        // Lógica para upload da logomarca
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileInfo = pathinfo($_FILES['logo']['name']);
            $extension = strtolower($fileInfo['extension']);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($extension, $allowedExtensions)) {
                // Busca a config atual para poder deletar o arquivo antigo
                $configAtual = $this->empresaModel->getDadosEmpresa();
                
                $newFilename = 'logo_' . time() . '.' . $extension;
                $destination = $uploadDir . $newFilename;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $destination)) {
                    // Adiciona o nome do arquivo ao array de dados que será salvo no JSON
                    $dados['logo_path'] = $newFilename;

                    // Remove o arquivo físico da logo anterior, se existir para evitar acúmulo de lixo
                    if (!empty($configAtual['logo_path'])) {
                        $oldFile = $uploadDir . $configAtual['logo_path'];
                        if (file_exists($oldFile)) @unlink($oldFile);
                    }
                }
            } else {
                $this->setFlashMessage('error', 'Formato de imagem inválido para a logo. Use JPG, PNG ou WEBP.');
            }
        }

        // Lógica para upload do certificado
        if ($certificado && $certificado['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/storage/certificados/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $nomeArquivo = 'certificado_empresa.pfx'; // Nome fixo para sobrescrever
            $caminhoDestino = $uploadDir . $nomeArquivo;

            if (move_uploaded_file($certificado['tmp_name'], $caminhoDestino)) {
                $caminhoCertificado = $caminhoDestino;
            } else {
                $this->setFlashMessage('error', 'Falha ao fazer upload do certificado.');
            }
        }

        if ($this->empresaModel->salvarDadosEmpresa($dados, $caminhoCertificado)) {
            $this->setFlashMessage('success', 'Dados da empresa atualizados com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar os dados da empresa.');
        }

        header('Location: ' . BASE_URL . '/empresa');
        exit();
    }
}
