<?php
require_once '../../data/conexao.php';

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conexao = conectarBanco();
        
        // Validação dos campos obrigatórios
        if (empty($_POST['nomeProd'])) {
            throw new Exception("O nome do produto é obrigatório");
        }
        
        if (empty($_POST['categorias']) || !is_array($_POST['categorias'])) {
            throw new Exception("Selecione ao menos uma categoria/tag");
        }
        
        if (empty($_POST['idGenero'])) {
            throw new Exception("O gênero é obrigatório");
        }
        
        if (!isset($_POST['valorProd']) || $_POST['valorProd'] <= 0) {
            throw new Exception("O preço deve ser maior que zero");
        }

        // Validação da imagem
        if (!isset($_FILES['imagemProd']) || $_FILES['imagemProd']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("A imagem do produto é obrigatória");
        }

        // Verifica o tipo da imagem
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $fileType = $_FILES['imagemProd']['type'];
        
        if (!array_key_exists($fileType, $allowedTypes)) {
            throw new Exception("Tipo de arquivo não suportado. Use apenas JPEG, PNG ou GIF.");
        }
        
        // Verifica o tamanho da imagem (máximo 100MB)
        $maxSize = 100 * 1024 * 1024;
        if ($_FILES['imagemProd']['size'] > $maxSize) {
            throw new Exception("O tamanho da imagem não pode exceder 100MB.");
        }
        
        // Lê o conteúdo da imagem
        $imagemProd = file_get_contents($_FILES['imagemProd']['tmp_name']);

        // Prepara os dados
        $nomeProd = trim($_POST['nomeProd']);
        $idMarca = !empty($_POST['idMarca']) ? (int)$_POST['idMarca'] : null;
        $idGenero = (int)$_POST['idGenero'];
        $descricaoProd = trim($_POST['descricaoProd']);
        $valorProd = (float)str_replace(',', '.', $_POST['valorProd']);
        $valorCusto = !empty($_POST['valorCusto']) ? (float)str_replace(',', '.', $_POST['valorCusto']) : 0;
        $quantidade = !empty($_POST['quantidade']) ? (int)$_POST['quantidade'] : 0;
        $cor = trim($_POST['cor'] ?? '');
        $material = trim($_POST['material'] ?? '');
        
        // Processa tamanhos
        $tamanhosDisponiveis = ['PP', 'P', 'M', 'G', 'GG', 'XGG'];
        $tamanhosSelecionados = [];
        
        foreach ($tamanhosDisponiveis as $tamanho) {
            if (isset($_POST[strtolower($tamanho)])) {
                $tamanhosSelecionados[] = $tamanho;
            }
        }
        
        $tamanhoDisp = !empty($tamanhosSelecionados) ? implode(',', $tamanhosSelecionados) : '';
        $peso = !empty($_POST['peso']) ? trim($_POST['peso']) : '';

        // Prepara e executa a query (agora removendo idTag, pois será salvo na tabela de relacionamento)
        $sql = "INSERT INTO produto (
            nomeProd, idMarca, idGenero, descricaoProd, 
            valorProd, valorCusto, quantidade, cor, material, 
            tamanhoDisp, peso, imagemProd
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexao->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar a query: " . $conexao->error);
        }
        
        $null = null;
        $stmt->bind_param(
            'siisddissssb',
            $nomeProd, $idMarca, $idGenero, $descricaoProd,
            $valorProd, $valorCusto, $quantidade, $cor, $material,
            $tamanhoDisp, $peso, $null
        );

        $stmt->send_long_data(11, $imagemProd);
        
        if ($stmt->execute()) {
            $idProduto = $conexao->insert_id;

            // Salva as categorias/tags selecionadas na tabela de relacionamento
            $categorias = $_POST['categorias'];
            $stmtCat = $conexao->prepare("INSERT INTO produto_categoria (produto_id, categoria_id, nome_categoria) VALUES (?, ?, ?)");

            foreach ($categorias as $categoria_id) {
                $categoria_id = (int)$categoria_id;

                // Buscar o nome da categoria pelo ID
                $stmtNome = $conexao->prepare("SELECT tag FROM tag WHERE idTag = ?");
                $stmtNome->bind_param('i', $categoria_id);
                $stmtNome->execute();
                $stmtNome->bind_result($nome_categoria);
                $stmtNome->fetch();
                $stmtNome->close();

                $stmtCat->bind_param('iis', $idProduto, $categoria_id, $nome_categoria);
                $stmtCat->execute();
            }
            $stmtCat->close();

            header('Location: cadastroProduto.php?status=success&id=' . $idProduto);
            exit();
        } else {
            throw new Exception("Erro ao cadastrar produto: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        header('Location: cadastroProduto.php?status=error&message=' . urlencode($e->getMessage()));
        exit();
    } finally {
        if (isset($conexao)) {
            $conexao->close();
        }
    }
} else {
    header('Location: cadastroProduto.php');
    exit();
}