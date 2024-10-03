
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor de Texto</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>


<h1>Editor de Texto</h1>
    
    <!-- Área de edição -->
    <div id="editor" contenteditable="true" class="editor">
        Escreva seu texto aqui...
    </div>

    <!-- Selecionar extensão do arquivo -->
    <label for="fileType">Escolha a extensão do arquivo:</label>
    <select id="fileType">
        <option value="txt">.txt</option>
        <option value="html">.html</option>
        <option value="json">.json</option>
    </select>

    <!-- Botão para baixar como arquivo -->
    <button id="downloadBtn">Baixar como Arquivo</button>

    <script>
document.getElementById("downloadBtn").addEventListener("click", function() {
    // Pega o conteúdo do editor
    var editorContent = document.getElementById("editor").innerHTML;

    // Pega a extensão escolhida pelo usuário
    var fileType = document.getElementById("fileType").value;

    // Define o tipo MIME com base na extensão escolhida
    var mimeType;
    switch (fileType) {
        case 'txt':
            mimeType = 'text/plain';
            break;
        case 'html':
            mimeType = 'text/html';
            break;
        case 'json':
            mimeType = 'application/json';
            editorContent = JSON.stringify({ content: editorContent }); // Exemplo de formatação JSON
            break;
        default:
            mimeType = 'text/plain';
    }

    // Converte o conteúdo em um blob (objeto de dados)
    var blob = new Blob([editorContent], { type: mimeType });

    // Cria um link temporário para fazer o download
    var link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "documento." + fileType;  // Nome do arquivo com extensão

    // Aciona o clique no link para baixar
    link.click();
});


    </script>
</body>

</html>