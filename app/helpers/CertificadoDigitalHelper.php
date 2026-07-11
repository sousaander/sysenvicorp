<?php

namespace App\Helpers;

use NFePHP\Common\Certificate;

class CertificadoDigitalHelper
{
    /**
     * Lê um certificado A1 (.pfx/.p12) e retorna os dados extraídos.
     *
     * @param string $pfxContent Conteúdo binário do arquivo .pfx
     * @param string $password   Senha do certificado
     * @return array{success: bool, data?: array, error?: string}
     */
    public static function lerCertificado(string $pfxContent, string $password): array
    {
        try {
            $cert = Certificate::readPfx($pfxContent, $password);

            $certRaw = $cert->getCertificate();
            $certInfo = openssl_x509_parse($certRaw);

            $subject = $certInfo['subject'] ?? [];
            $issuer  = $certInfo['issuer'] ?? [];

            // Extrai CN (nome do titular)
            $nome = $subject['CN'] ?? $subject['commonName'] ?? '';

            // Extrai CPF ou CNPJ (ICP-Brasil: CPF vem como serialNumber no subject)
            $cpf = $cert->getCpf();
            $cnpj = $cert->getCnpj();
            $documento = $cpf ?: $cnpj;

            // Empresa
            $empresa = $cert->getCompanyName();

            // Validade
            $validadeDe = $cert->getValidFrom();
            $validadeAte = $cert->getValidTo();
            $expirado = $cert->isExpired();

            // ICP-Brasil?
            $icp = $cert->getICP();

            // Emissor
            $emissorNome = $issuer['CN'] ?? $issuer['organizationName'] ?? '';

            return [
                'success' => true,
                'data' => [
                    'nome' => $nome,
                    'cpf' => $cpf,
                    'cnpj' => $cnpj,
                    'documento' => $documento,
                    'empresa' => $empresa,
                    'validade_de' => $validadeDe ? $validadeDe->format('Y-m-d') : null,
                    'validade_ate' => $validadeAte ? $validadeAte->format('Y-m-d') : null,
                    'expirado' => $expirado,
                    'icp_brasil' => $icp,
                    'emissor' => $emissorNome,
                    'dados_raw' => $certInfo,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Assina digitalmente um PDF usando certificado A1 via TCPDF.
     *
     * @param string $pdfContent Conteúdo do PDF a ser assinado
     * @param string $pfxContent Conteúdo binário do .pfx
     * @param string $password   Senha do certificado
     * @param array  $appearance Dados da aparência da assinatura [
     *                           'name' => 'Nome do signatário',
     *                           'reason' => 'Razão',
     *                           'location' => 'Local',
     *                           'info' => 'Info adicional'
     *                           ]
     * @return string PDF assinado
     */
    public static function assinarPdf(
        string $pdfContent,
        string $pfxContent,
        string $password,
        array $appearance = []
    ): string {
        $cert = Certificate::readPfx($pfxContent, $password);

        $certPem = $cert->getCertificate();
        $privateKey = $cert->getPrivateKey();

        $name = $appearance['name'] ?? $cert->getCompanyName() ?: 'Signatário';
        $reason = $appearance['reason'] ?? 'Assinatura Digital de Proposta';
        $location = $appearance['location'] ?? 'Brasil';

        // Salva o PDF original em arquivo temporário
        $inputPath = tempnam(sys_get_temp_dir(), 'pdf_original_');
        file_put_contents($inputPath, $pdfContent);

        try {
            // Cria TCPDF com FPDI para importar páginas
            $pdf = new \setasign\Fpdi\TcpdfFpdi();

            // Configura metadados
            $pdf->SetCreator('SysEnviCorp');
            $pdf->SetAuthor($name);

            // Configura a assinatura digital
            $pdf->setSignature(
                $certPem,
                $privateKey,
                $password,
                '',
                [],
                'A',
                ['Name' => $name, 'Reason' => $reason, 'Location' => $location]
            );

            // Adiciona um selo/appearance de assinatura na última página
            $pdf->setSignatureAppearance(-1, -1, 0, 0); // Aparecerá no rodapé da última página

            // Importa todas as páginas do PDF original
            $pageCount = $pdf->setSourceFile($inputPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);

                // Na última página, adiciona o retângulo da assinatura
                if ($i === $pageCount) {
                    $sigW = 120;
                    $sigH = 30;
                    $sigX = $size['width'] - $sigW - 20;
                    $sigY = $size['height'] - $sigH - 20;

                    // Define a aparência no rodapé
                    $pdf->setSignatureAppearance($sigX, $sigY, $sigW, $sigH);
                }
            }

            // Fecha e gera o PDF assinado
            $signedContent = $pdf->Output('', 'S');
        } finally {
            if (file_exists($inputPath)) {
                @unlink($inputPath);
            }
        }

        return $signedContent;
    }

    /**
     * Retorna o diretório de upload para certificados.
     */
    public static function getUploadDir(): string
    {
        $dir = ROOT_PATH . '/public/uploads/certificados';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }
}
