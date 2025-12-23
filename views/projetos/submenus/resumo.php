<?php
// Garante que os dados do projeto e do resumo existam para evitar erros
$projeto = $data['projeto'] ?? [];
$summaryDetails = $data['submenuData']['summaryDetails'] ?? [
    'orcamento' => ['receita_prevista' => 0, 'despesa_prevista' => 0],
    'art_count' => 0,
    'documentos_count' => 0,
    'mapas_count' => 0,
];
$orcamento = $summaryDetails['orcamento'];

// Formata valores para exibição
$receitaPrevistaF = 'R$ ' . number_format($orcamento['receita_prevista'] ?? 0, 2, ',', '.');
$despesaPrevistaF = 'R$ ' . number_format($orcamento['despesa_prevista'] ?? 0, 2, ',', '.');
$saldoPrevisto = ($orcamento['receita_prevista'] ?? 0) - ($orcamento['despesa_prevista'] ?? 0);
$saldoPrevistoF = 'R$ ' . number_format($saldoPrevisto, 2, ',', '.');
$saldoCor = $saldoPrevisto >= 0 ? 'text-success' : 'text-danger';

$dataInicialF = !empty($projeto['data_inicial']) ? date('d/m/Y', strtotime($projeto['data_inicial'])) : 'N/D';
$dataFimPrevistaF = !empty($projeto['data_fim_prevista']) ? date('d/m/Y', strtotime($projeto['data_fim_prevista'])) : 'N/D';

?>

<div class="container-fluid">
    <!-- Cards de Resumo Rápido -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Receita Prevista</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $receitaPrevistaF ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Despesa Prevista</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $despesaPrevistaF ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Saldo Previsto</div>
                            <div class="h5 mb-0 font-weight-bold <?= $saldoCor ?>"><?= $saldoPrevistoF ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Status do Projeto</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($projeto['status'] ?? 'N/D') ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhes do Projeto e Histórico -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ℹ️ Dados Gerais do Projeto</h6>
                </div>
                <div class="card-body">
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($projeto['cliente_nome'] ?? 'Não informado') ?></p>
                    <p><strong>Responsável Elaboração:</strong> <?= htmlspecialchars($projeto['responsavel_elaboracao'] ?? 'Não informado') ?></p>
                    <p><strong>Responsável Execução:</strong> <?= htmlspecialchars($projeto['responsavel_execucao'] ?? 'Não informado') ?></p>
                    <hr>
                    <p><strong>Data de Início:</strong> <?= $dataInicialF ?></p>
                    <p><strong>Previsão de Término:</strong> <?= $dataFimPrevistaF ?></p>
                    <hr>
                    <p><strong>ARTs Vinculadas:</strong> <span class="badge badge-primary badge-pill"><?= $summaryDetails['art_count'] ?></span></p>
                    <p><strong>Documentos Enviados:</strong> <span class="badge badge-info badge-pill"><?= $summaryDetails['documentos_count'] ?></span></p>
                    <p><strong>Mapas Cadastrados:</strong> <span class="badge badge-success badge-pill"><?= $summaryDetails['mapas_count'] ?></span></p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">📝 Observações e Histórico</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Observações Gerais:</strong>
                        <p class="text-gray-800"><?= !empty($projeto['observacoes']) ? nl2br(htmlspecialchars($projeto['observacoes'])) : 'Nenhuma observação registrada.' ?></p>
                    </div>
                    <hr>
                    <h6 class="font-weight-bold">Linha do Tempo</h6>
                    <?php if (!empty($timeline)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($timeline as $evento): ?>
                                <li class="list-group-item px-0 py-2">
                                    <div class="d-flex w-100 justify-content-between">
                                        <p class="mb-1 text-sm text-gray-800"><?= htmlspecialchars($evento['descricao']) ?></p>
                                        <small class="text-muted" title="<?= date('d/m/Y H:i', strtotime($evento['data_evento'])) ?>">
                                            <?= date('d/m/Y', strtotime($evento['data_evento'])) ?>
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        Por: <?= htmlspecialchars($evento['usuario_nome'] ?? 'Sistema') ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted"><em>Nenhum evento registrado na linha do tempo deste projeto.</em></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>