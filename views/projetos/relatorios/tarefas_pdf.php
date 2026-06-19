<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Tarefas - <?php echo htmlspecialchars($projeto['nome']); ?></title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 5px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 14px; color: #555; margin-bottom: 20px; text-align: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .header-info { margin-bottom: 30px; font-size: 12px; }
        .status-group { margin-bottom: 20px; page-break-inside: avoid; }
        .status-title { background-color: #f3f4f6; padding: 8px; font-weight: bold; font-size: 14px; border-left: 5px solid #7c3aed; margin-bottom: 10px; }
        .responsavel-group { margin-left: 0px; margin-bottom: 15px; }
        .responsavel-title { font-weight: bold; color: #4b5563; margin-bottom: 5px; padding-left: 5px; border-bottom: 1px solid #e5e7eb; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background-color: #f9fafb; font-weight: 600; color: #374151; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .badge-Urgente { color: #991b1b; background-color: #fee2e2; }
        .badge-Alta { color: #9a3412; background-color: #ffedd5; }
        .badge-Media { color: #1e40af; background-color: #dbeafe; }
        .badge-Baixa { color: #374151; background-color: #f3f4f6; }
        .text-right { text-align: right; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 10px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header-info">
        <h1>Relatório de Tarefas do Projeto</h1>
        <h2><?php echo htmlspecialchars($projeto['nome']); ?></h2>
        <table style="border: none;">
            <tr>
                <td style="border: none; width: 50%;"><strong>Cliente:</strong> <?php echo htmlspecialchars($projeto['cliente_nome'] ?? 'N/A'); ?></td>
                <td style="border: none; width: 50%; text-align: right;"><strong>Gerado em:</strong> <?php echo $dataGeracao; ?></td>
            </tr>
        </table>
    </div>

    <?php if (empty($tarefasAgrupadas)): ?>
        <p style="text-align: center; color: #6b7280; margin-top: 50px;">Nenhuma tarefa encontrada para este projeto.</p>
    <?php else: ?>
        <?php foreach ($tarefasAgrupadas as $status => $responsaveis): ?>
            <div class="status-group">
                <div class="status-title">STATUS: <?php echo mb_strtoupper(htmlspecialchars($status)); ?></div>
                
                <?php foreach ($responsaveis as $responsavel => $listaTarefas): ?>
                    <div class="responsavel-group">
                        <div class="responsavel-title">Responsável: <?php echo htmlspecialchars($responsavel); ?></div>
                        <table>
                            <thead>
                                <tr>
                                    <th width="45%">Título / Descrição</th>
                                    <th width="15%">Prioridade</th>
                                    <th width="15%">Início</th>
                                    <th width="15%">Prazo</th>
                                    <th width="10%">Dias Rest.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaTarefas as $tarefa): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($tarefa['titulo']); ?></strong>
                                            <?php if (!empty($tarefa['descricao'])): ?>
                                                <br><span style="color: #6b7280; font-size: 10px;"><?php echo nl2br(htmlspecialchars(substr($tarefa['descricao'], 0, 100))) . (strlen($tarefa['descricao']) > 100 ? '...' : ''); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo str_replace(['é', 'á'], ['e', 'a'], $tarefa['prioridade']); ?>">
                                                <?php echo htmlspecialchars($tarefa['prioridade']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $tarefa['data_inicio'] ? date('d/m/Y', strtotime($tarefa['data_inicio'])) : '-'; ?></td>
                                        <td><?php echo $tarefa['data_fim'] ? date('d/m/Y', strtotime($tarefa['data_fim'])) : '-'; ?></td>
                                        <td>
                                            <?php 
                                            if ($tarefa['data_fim'] && $tarefa['status'] != 'Concluída' && $tarefa['status'] != 'Cancelada') {
                                                $hoje = new DateTime();
                                                $fim = new DateTime($tarefa['data_fim']);
                                                $diff = $hoje->diff($fim);
                                                $dias = $diff->days;
                                                if ($fim < $hoje) {
                                                    echo "<span style='color: red; font-weight: bold;'>-" . $dias . "</span>";
                                                } else {
                                                    echo $dias;
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="footer">
        SysEnviCorp - Sistema de Gestão Ambiental | Página <span class="page-number"></span>
    </div>
</body>
</html>
