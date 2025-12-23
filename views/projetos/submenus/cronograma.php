<!-- Incluindo a biblioteca Frappe Gantt via CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<?php
// Bloco de dados de exemplo.
// Em uma aplicação real, estes dados viriam do banco de dados,
// passados pelo controller para esta view.
$tasks = [
    [
        'id' => 'task1',
        'name' => 'Análise de Requisitos',
        'start' => '2024-08-01',
        'end' => '2024-08-05',
        'progress' => 100,
    ],
    [
        'id' => 'task2',
        'name' => 'Desenvolvimento do Módulo A',
        'start' => '2024-08-06',
        'end' => '2024-08-15',
        'progress' => 75,
        'dependencies' => 'task1' // Esta tarefa depende da task1
    ],
    [
        'id' => 'task3',
        'name' => 'Testes de Integração',
        'start' => '2024-08-16',
        'end' => '2024-08-20',
        'progress' => 50,
        'dependencies' => 'task2'
    ],
    [
        'id' => 'task4',
        'name' => 'Deploy em Homologação',
        'start' => '2024-08-21',
        'end' => '2024-08-22',
        'progress' => 10,
        'dependencies' => 'task3'
    ],
];

// Convertendo o array PHP para um objeto JSON para o JavaScript
$tasks_json = json_encode($tasks);
?>

<!-- Card de Ações e Filtros Rápidos -->
<div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <h3 class="text-lg font-semibold mb-2">Ações e Filtros Rápidos</h3>
    <button id="export-pdf-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Exportar para PDF
    </button>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-semibold mb-4">Cronograma do Projeto (Gantt)</h3>
    <!-- O gráfico de Gantt será renderizado neste elemento SVG -->
    <div id="gantt"></div>
</div>

<script>
    // Pegando os dados das tarefas do PHP
    console.log("Dados das tarefas (tasks):", <?php echo $tasks_json; ?>);
    // Verifique no console do navegador se este log mostra um array de objetos válido.

    const tasks = <?php echo $tasks_json; ?>;

    // Inicializando o gráfico de Gantt
    const gantt = new Gantt("#gantt", tasks, {
        header_height: 50,
        column_width: 30,
        step: 24,
        view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
        bar_height: 20,
        bar_corner_radius: 3,
        arrow_curve: 5,
        padding: 18,
        view_mode: 'Week', // Modo de visualização inicial
        date_format: 'YYYY-MM-DD',
        language: 'en', // O padrão é 'en'. Removendo 'pt' para evitar erro de tradução.
        // Evento disparado ao clicar em uma tarefa
        on_click: function(task) {
            alert("Tarefa clicada: " + task.name);
            // Aqui você pode abrir um modal para editar a tarefa, por exemplo.
        }
    });

    // Ativação da funcionalidade de exportar para PDF
    document.getElementById('export-pdf-btn').addEventListener('click', function() {
        const svg = document.querySelector("#gantt svg");
        if (!svg) {
            alert("Gráfico Gantt não encontrado para exportação.");
            return;
        }

        // 1. Clonar o SVG original para não modificar o que está na tela
        const svgClone = svg.cloneNode(true);

        // 2. Definir explicitamente a largura e altura para o PDF
        const {
            width,
            height
        } = svg.getBoundingClientRect();
        svgClone.setAttribute('width', width);
        svgClone.setAttribute('height', height);

        // 3. Criar um elemento <style> com os estilos do Frappe-Gantt
        // Isso garante que as cores e fontes sejam incluídas no PDF
        const style = document.createElement('style');
        style.textContent = `
            .gantt .bar-wrapper { fill: #b8c2cc; }
            .gantt .bar-progress { fill: #a3a3ff; }
            .gantt .bar-label { fill: #000; font-weight: bold; font-size: 12px; }
            .gantt .grid-header, .gantt .grid-row { fill: #f5f5f5; }
            .gantt .grid-body .grid-row:nth-child(odd) { fill: #ffffff; }
            .gantt .tick { stroke: #e0e0e0; stroke-width: 0.2; }
            .gantt .today-highlight { fill: #fcf8e3; }
            .gantt .arrow { fill: #999; }
            .gantt .bar-group.has-children .bar { fill: #d1d1d1; }
        `;
        svgClone.insertBefore(style, svgClone.firstChild);

        // 4. Serializar o SVG clonado e estilizado para uma string
        const svgString = new XMLSerializer().serializeToString(svgClone);
        const svgBlob = new Blob([svgString], {
            type: "image/svg+xml;charset=utf-8"
        });
        const url = URL.createObjectURL(svgBlob);

        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const context = canvas.getContext('2d');
            context.fillStyle = '#FFFFFF'; // Fundo branco para evitar transparência
            context.fillRect(0, 0, width, height);
            context.drawImage(img, 0, 0);

            const imgData = canvas.toDataURL('image/png');
            const {
                jsPDF
            } = window.jspdf;

            // Criar PDF em modo paisagem ('l' = landscape) e unidades em 'pt' (pontos)
            const pdf = new jsPDF('l', 'pt', 'a4');

            // Calcular as dimensões da imagem para caber na página A4
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const ratio = width / height;
            const imgWidth = pdfWidth - 20; // Deixar uma pequena margem
            const imgHeight = imgWidth / ratio;

            pdf.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
            pdf.save("cronograma-projeto.pdf");
            URL.revokeObjectURL(url); // Limpar o objeto URL
        };
        img.src = url;
    });
</script>