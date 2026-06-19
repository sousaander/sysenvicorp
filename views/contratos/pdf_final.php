<?php
// Prepara a logomarca em Base64 para garantir a exibição no PDF independente do ambiente (Windows/Linux)
$logoBase64 = '';
if (!empty($empresa['logo_path'])) {
    $logoPath = ROOT_PATH . '/public/uploads/logos/' . $empresa['logo_path'];
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $mime = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png';
        $logoBase64 = 'data:' . $mime . ';base64,' . $logoData;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        /* Configurações de Página ABNT */
        @page {
            margin: 3cm 2cm 2cm 3cm; /* ABNT: Superior/Esquerda 3cm, Inferior/Direita 2cm */
        }

        body {
            font-family: "Arial", sans-serif;
            font-size: 12pt; /* Tamanho padrão para corpo de texto */
            line-height: 1.5; /* Espaçamento entre linhas padrão ABNT */
            color: #000;
            text-align: justify; /* Texto sempre justificado */
            margin: 0;
            padding: 0;
        }

        .header {
            position: fixed;
            top: -2cm;
            left: 0;
            right: 0;
            height: 2cm;
            border-bottom: 1px solid #000;
            margin-bottom: 1cm;
        }

        .header table { width: 100%; border-collapse: collapse; }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .document-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 30px 0 30px 0;
            text-transform: uppercase;
        }

        .content {
            word-wrap: break-word;
        }
        
        .content p {
            text-indent: 1.25cm;
            margin-top: 0;
            margin-bottom: 10px;
            text-align: justify;
        }

        .content h3 {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            text-indent: 0;
            text-transform: uppercase;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1.5cm;
            font-size: 10pt;
            color: #000;
            border-top: 0.5pt solid #000;
            padding-top: 2px;
        }

        /* Numeração de Página no Topo Direito (Opcional ABNT) ou Rodapé */
        .page-number { text-align: right; }
        .page-number:after { content: "Página " counter(page); }

        .logo {
            max-height: 1.5cm;
            max-width: 5cm;
        }

        /* Marca d'água RASCUNHO */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0, 0, 0, 0.1); /* Cinza claro e transparente */
            z-index: -1000; /* Garante que fique atrás do conteúdo */
            white-space: nowrap;
        }

        /* Quebra de página para assinaturas */
        .signature-block {
            margin-top: 50px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho visível apenas na primeira página se não for fixed, 
         ou em todas se for fixed. Para contratos, geralmente usa-se em todas. -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 50%;">
                    <?php if ($logoBase64): ?>
                        <img src="<?php echo $logoBase64; ?>" class="logo">
                    <?php else: ?>
                        <span class="company-name"><?php echo htmlspecialchars($empresa['razao_social'] ?? 'SysEnviCorp'); ?></span>
                    <?php endif; ?>
                </td>
                <td style="width: 50%; text-align: right; vertical-align: middle;">
                    <div style="font-size: 10pt; font-weight: bold;"><?php echo htmlspecialchars($empresa['razao_social'] ?? ''); ?></div>
                    <div style="font-size: 9pt;">CNPJ: <?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?></div>
                    <?php if (!empty($contrato['projeto_nome'])): ?>
                        <div style="font-size: 8pt; margin-top: 5px; color: #555; text-transform: uppercase;">
                            Projeto: <?php echo htmlspecialchars($contrato['projeto_nome']); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="document-title">CONTRATO DE <?php echo htmlspecialchars(strtoupper($tipo)); ?></div>
    
    <?php if (!empty($contrato['numero_contrato'])): ?>
        <div style="text-align: center; margin-top: -25px; margin-bottom: 30px; font-size: 11pt; font-weight: bold;">
            INSTRUMENTO Nº <?php echo htmlspecialchars($contrato['numero_contrato']); ?>
            <?php if (!empty($contrato['numero_contrato_cliente'])): ?>
                <br><span style="font-size: 9pt; font-weight: normal; color: #333;">REF. CLIENTE: <?php echo htmlspecialchars($contrato['numero_contrato_cliente']); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($status) && $status === 'Rascunho'): ?>
        <div class="watermark">RASCUNHO</div>
    <?php endif; ?>

    <div class="content">
        <!-- Preâmbulo / Identificação das Partes -->
        <div style="margin-bottom: 30px;">
            <p style="text-indent: 0;"><strong>CONTRATANTE:</strong> 
                <?php echo htmlspecialchars($contrato['contratante_nome'] ?? ''); ?>, 
                inscrito(a) no CPF/CNPJ sob o nº <?php echo htmlspecialchars($contrato['contratante_documento'] ?? ''); ?>, 
                com sede/endereço em <?php echo htmlspecialchars($contrato['contratante_endereco'] ?? ''); ?>
                <?php echo !empty($contrato['contratante_representante']) ? ', neste ato representado(a) por ' . htmlspecialchars($contrato['contratante_representante']) : ''; ?>.
            </p>

            <p style="text-indent: 0; margin-top: 15px;"><strong>CONTRATADO:</strong> 
                <?php echo htmlspecialchars($contrato['contratado_nome'] ?? ''); ?>, 
                inscrito(a) no CPF/CNPJ sob o nº <?php echo htmlspecialchars($contrato['contratado_documento'] ?? ''); ?>, 
                com sede/endereço em <?php echo htmlspecialchars($contrato['contratado_endereco'] ?? ''); ?>
                <?php echo !empty($contrato['contratado_representante']) ? ', neste ato representado(a) por ' . htmlspecialchars($contrato['contratado_representante']) : ''; ?>.
            </p>
            <p style="text-indent: 0; margin-top: 15px;">Entre as partes nomeadas e qualificadas deste instrumento (Dados
do Contrato), doravante designadas CONTRATANTE e CONTRATADA, e, em conjunto, PARTES,
fica ajustado o presente contrato, de acordo com as Condições Gerais e as Condições Especiais a
seguir, sendo que as cláusulas das Condições Especiais completam as Condições Gerais e prevalecem
sempre sobre estas, em caso de divergência.</p>
        </div>

        <h3>1. DO OBJETO DA CONTRATAÇÃO</h3>
        <p><?php echo nl2br(htmlspecialchars($objeto)); ?></p>

        <?php if (!empty($contrato['base_referencia'])): ?>
            <p><strong>Parágrafo Único:</strong> As atividades objeto deste contrato serão executadas prioritariamente na unidade denominada <strong><?php echo htmlspecialchars($contrato['base_referencia']); ?></strong>.</p>
        <?php endif; ?>

        <?php if (!empty($contrato['data_inicio']) || !empty($contrato['vencimento']) || !empty($contrato['duracao_meses'])): ?>
            <h3>2. DOS PRAZOS E VIGÊNCIA</h3>
            <?php if (!empty($contrato['data_inicio'])): ?>
                <p><strong>Data de Início:</strong> <?php echo date('d/m/Y', strtotime($contrato['data_inicio'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($contrato['vencimento'])): ?>
                <p><strong>Data de Término:</strong> <?php echo date('d/m/Y', strtotime($contrato['vencimento'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($contrato['duracao_meses'])): ?>
                <p><strong>Duração Estimada:</strong> <?php echo htmlspecialchars($contrato['duracao_meses']); ?> meses.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['renovacao_automatica']) && $contrato['renovacao_automatica'] !== 'Não se aplica'): ?>
                <p><strong>Renovação:</strong> <?php echo htmlspecialchars($contrato['renovacao_automatica']); ?>.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($contrato['responsabilidades_contratante']) || !empty($contrato['responsabilidades_contratado'])): ?>
            <h3>3. DAS RESPONSABILIDADES DAS PARTES</h3>
            <?php if (!empty($contrato['responsabilidades_contratante'])): ?>
                <p><strong>São responsabilidades do Contratante:</strong><br><?php echo nl2br(htmlspecialchars($contrato['responsabilidades_contratante'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($contrato['responsabilidades_contratado'])): ?>
                <p><strong>São responsabilidades do Contratado:</strong><br><?php echo nl2br(htmlspecialchars($contrato['responsabilidades_contratado'])); ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($contrato['criterios_aceite'])): ?>
            <h3>4. DOS CRITÉRIOS DE ACEITE E ENTREGA</h3>
            <p><?php echo nl2br(htmlspecialchars($contrato['criterios_aceite'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($contrato['valor'])): ?>
            <h3>5. DO VALOR E DAS CONDIÇÕES DE PAGAMENTO</h3>
            <p>O preço total para a execução do presente contrato é de R$ <?php echo number_format($contrato['valor'], 2, ',', '.'); ?>.</p>
            
            <?php if (!empty($contrato['valor_sinal']) && $contrato['valor_sinal'] > 0): ?>
                <p><strong>Sinal / Entrada:</strong> R$ <?php echo number_format($contrato['valor_sinal'], 2, ',', '.'); ?></p>
            <?php endif; ?>
            <?php if (!empty($contrato['condicao_pagamento'])): ?>
                <p><strong>Condição de Pagamento:</strong> <?php echo htmlspecialchars($contrato['condicao_pagamento']); ?>
                <?php if (!empty($contrato['numero_parcelas'])) echo ' em ' . htmlspecialchars($contrato['numero_parcelas']) . ' parcela(s)'; ?>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['forma_pagamento'])): ?>
                <p><strong>Forma de Pagamento:</strong> <?php echo htmlspecialchars($contrato['forma_pagamento']); ?> 
                <?php 
                    $infoBancaria = [];
                    if (!empty($contrato['pix_tipo_chave'])) $infoBancaria[] = 'Tipo Chave: ' . htmlspecialchars($contrato['pix_tipo_chave']);
                    if (!empty($contrato['dados_bancarios'])) $infoBancaria[] = 'Chave/Dados: ' . htmlspecialchars($contrato['dados_bancarios']);
                    
                    if (!empty($infoBancaria)) echo ' - ' . implode(' | ', $infoBancaria);
                ?>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['dia_vencimento'])): ?>
                <p><strong>Dia de Vencimento:</strong> Dia <?php echo htmlspecialchars($contrato['dia_vencimento']); ?> de cada mês.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['observacoes_financeiras'])): ?>
                <p><strong>Observações Financeiras:</strong> <?php echo nl2br(htmlspecialchars($contrato['observacoes_financeiras'])); ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($contrato['multa_atraso']) || !empty($contrato['juros_mora']) || !empty($contrato['penalidade_descumprimento']) || !empty($contrato['multa_rescisao_antecipada'])): ?>
            <h3>6. DAS MULTAS E INADIMPLÊNCIA</h3>
            <?php if (!empty($contrato['prazo_carencia_multa'])): ?>
                <p><strong>Prazo de Carência:</strong> <?php echo htmlspecialchars($contrato['prazo_carencia_multa']); ?> dia(s) após o vencimento.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['multa_atraso']) && $contrato['multa_atraso'] > 0): ?>
                <p><strong>Multa por Atraso no Pagamento:</strong> <?php echo htmlspecialchars($contrato['multa_atraso']); ?>%.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['juros_mora']) && $contrato['juros_mora'] > 0): ?>
                <p><strong>Juros de Mora:</strong> <?php echo htmlspecialchars($contrato['juros_mora']); ?>% ao mês.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['correcao_monetaria']) && $contrato['correcao_monetaria'] !== 'Nenhuma'): ?>
                <p><strong>Correção Monetária:</strong> <?php echo htmlspecialchars($contrato['correcao_monetaria']); ?>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['penalidade_descumprimento'])): ?>
                <p><strong>Penalidade por Descumprimento:</strong> <?php echo nl2br(htmlspecialchars($contrato['penalidade_descumprimento'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($contrato['multa_rescisao_antecipada'])): ?>
                <p><strong>Multa por Rescisão Antecipada:</strong> <?php echo nl2br(htmlspecialchars($contrato['multa_rescisao_antecipada'])); ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($contrato['clausula_confidencialidade']) || !empty($contrato['lgpd_conformidade'])): ?>
            <h3>7. DA CONFIDENCIALIDADE E PROTEÇÃO DE DADOS (LGPD)</h3>
            <?php if (!empty($contrato['confidencialidade_tags']) && $contrato['confidencialidade_tags'] !== '[]' && $contrato['confidencialidade_tags'] !== '["Nenhum"]'): ?>
                <p><strong>Natureza dos Dados Tratados:</strong> <?php 
                    $tags = json_decode($contrato['confidencialidade_tags'], true) ?: []; 
                    echo implode(', ', array_map('htmlspecialchars', $tags)); 
                ?>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['prazo_sigilo']) && $contrato['prazo_sigilo'] !== 'Não aplicável'): ?>
                <p><strong>Prazo de Sigilo Opcional:</strong> <?php echo htmlspecialchars($contrato['prazo_sigilo']); ?>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['penalidade_violacao_sigilo'])): ?>
                <p><strong>Penalidade por Violação de Sigilo:</strong> <?php echo htmlspecialchars($contrato['penalidade_violacao_sigilo']); ?>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['clausula_confidencialidade'])): ?>
                <p><?php echo nl2br(htmlspecialchars($contrato['clausula_confidencialidade'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($contrato['lgpd_conformidade'])): ?>
                <p>Declaram as partes estar em plena conformidade com a Lei Geral de Proteção de Dados (L. 13.709/2018), 
                com base legal no Art. 7º: <strong><?php echo !empty($contrato['base_legal_lgpd']) ? htmlspecialchars($contrato['base_legal_lgpd']) : 'Execução do presente contrato'; ?></strong>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['dpo_encarregado'])): ?>
                <p><strong>Encarregado de Dados (DPO):</strong> <?php echo htmlspecialchars($contrato['dpo_encarregado']); ?>.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($contrato['causas_rescisao_imotivada']) || !empty($contrato['causas_justa_causa']) || !empty($contrato['obrigacoes_pos_encerramento'])): ?>
            <h3>8. DA RESCISÃO E ENCERRAMENTO</h3>
            <?php if (!empty($contrato['aviso_previo_rescisao'])): ?>
                <p><strong>Aviso Prévio para Rescisão:</strong> <?php echo htmlspecialchars($contrato['aviso_previo_rescisao']); ?>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['rescisao_descumprimento'])): ?>
                <p><strong>Rescisão por Descumprimento:</strong> <?php echo htmlspecialchars($contrato['rescisao_descumprimento']); ?>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['causas_rescisao_imotivada'])): ?>
                <p><strong>Rescisão Imotivada (sem penalidades):</strong><br><?php echo nl2br(htmlspecialchars($contrato['causas_rescisao_imotivada'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($contrato['causas_justa_causa'])): ?>
                <p><strong>Causas para Justa Causa:</strong><br><?php echo nl2br(htmlspecialchars($contrato['causas_justa_causa'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($contrato['indenizacao_rescisao'])): ?>
                <p><strong>Indenização Rescisória:</strong> <?php echo htmlspecialchars($contrato['indenizacao_rescisao']); ?></p>
            <?php endif; ?>
            <?php if (!empty($contrato['nao_concorrencia']) && $contrato['nao_concorrencia'] !== 'Não aplicável'): ?>
                <p><strong>Cláusula de Não Concorrência:</strong> Aplica-se pelo período de <?php echo htmlspecialchars($contrato['nao_concorrencia']); ?>.</p>
            <?php endif; ?>
            <?php if (!empty($contrato['obrigacoes_pos_encerramento'])): ?>
                <p><strong>Obrigações Pós-Encerramento:</strong><br><?php echo nl2br(htmlspecialchars($contrato['obrigacoes_pos_encerramento'])); ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($contrato['clausulas_adicionais'])): ?>
            <h3>9. CLÁUSULAS ADICIONAIS</h3>
            <p><?php echo nl2br(htmlspecialchars($contrato['clausulas_adicionais'])); ?></p>
        <?php endif; ?>

        <h3>10. DISPOSIÇÕES FINAIS E FORO</h3>
        <?php if (!empty($contrato['lei_aplicavel'])): ?>
            <p><strong>Lei Aplicável:</strong> Fica definido que o presente contrato será regido sob a <?php echo htmlspecialchars($contrato['lei_aplicavel']); ?>.</p>
        <?php endif; ?>
        <?php if (!empty($contrato['resolucao_disputas'])): ?>
            <p><strong>Resolução de Disputas:</strong> <?php echo htmlspecialchars($contrato['resolucao_disputas']); ?>.</p>
        <?php endif; ?>
        <?php if (!empty($contrato['foro_eleicao'])): ?>
            <p><strong>Do Foro:</strong> Fica eleito o foro da Comarca de <?php echo htmlspecialchars($contrato['foro_eleicao']); ?> para dirimir qualquer dúvida ou controvérsia oriunda deste instrumento, com renúncia a qualquer outro, por mais privilegiado que seja.</p>
        <?php endif; ?>
        <p>E, por estarem inteiramente justos e contratados, firmam o presente instrumento em <?php echo htmlspecialchars($contrato['numero_vias'] ?? '2 vias'); ?> de igual teor e forma.</p>
    </div>

    <!-- Seção de Assinaturas Automática -->
    <div class="signature-block">
        <p style="margin-bottom: 40px;">
            <?php 
                $cidade = !empty($contrato['local_assinatura']) ? $contrato['local_assinatura'] : '____________________';
                $meses = ["janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"];
                $dataFmt = date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y');
                echo htmlspecialchars($cidade) . ", " . $dataFmt . ".";
            ?>
        </p>

        <table style="width: 100%; margin-top: 30px;">
            <tr>
                <td style="width: 45%; border-top: 1px solid #000; text-align: left; vertical-align: top; padding-top: 5px; font-size: 10pt;">
                    <strong>CONTRATANTE</strong><br>
                    Nome: <?php echo htmlspecialchars($contrato['contratante_nome'] ?? ''); ?><br>
                    CPF/CNPJ: <?php echo htmlspecialchars($contrato['contratante_documento'] ?? ''); ?><br>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; border-top: 1px solid #000; text-align: left; vertical-align: top; padding-top: 5px; font-size: 10pt;">
                    <strong>CONTRATADO</strong><br>
                    Nome: <?php echo htmlspecialchars($contrato['contratado_nome'] ?? ''); ?><br>
                    CPF/CNPJ: <?php echo htmlspecialchars($contrato['contratado_documento'] ?? ''); ?><br>
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-top: 60px;">
            <tr>
                <td style="width: 45%; border-top: 0.5pt solid #000; text-align: left; padding-top: 5px; font-size: 10pt;">
                    <strong>Testemunha 1</strong><br>Nome: _______________________<br>CPF: _______________________
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; border-top: 0.5pt solid #000; text-align: left; padding-top: 5px; font-size: 10pt;">
                    <strong>Testemunha 2</strong><br>Nome: _______________________<br>CPF: _______________________
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table width="100%">
            <tr>
                <td style="text-align: left; font-size: 8pt;">Este documento é parte integrante do sistema SysEnviCorp. Gerado em: <?php echo $dataGeracao; ?></td>
                <td style="text-align: right;" class="page-number"></td>
            </tr>
        </table>
    </div>
</body>
</html>