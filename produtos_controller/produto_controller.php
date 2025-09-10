<?php
require_once '../data/conexao.php';

function getTagsProduto($conexao, $idProduto) {
    // Busca pelas tags na tabela de relacionamento
    $sql = "SELECT t.tag 
            FROM produto_categoria pt
            JOIN tag t ON pt.categoria_id = t.idTag
            WHERE pt.produto_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $idProduto);
    $stmt->execute();
    $result = $stmt->get_result();
    $tags = [];
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['tag'];
    }

    // Se não encontrou tags, tenta buscar pelo campo antigo (idTag na tabela produto)
    if (empty($tags)) {
        $sqlAntigo = "SELECT t.tag 
                      FROM produto p
                      JOIN tag t ON p.idTag = t.idTag
                      WHERE p.idProduto = ?";
        $stmtAntigo = $conexao->prepare($sqlAntigo);
        $stmtAntigo->bind_param("i", $idProduto);
        $stmtAntigo->execute();
        $resultAntigo = $stmtAntigo->get_result();
        while ($row = $resultAntigo->fetch_assoc()) {
            $tags[] = $row['tag'];
        }
    }

    return $tags;
}

function getProdutos($conexao, $pagina = 1, $itensPorPagina = 6) {
    $offset = ($pagina - 1) * $itensPorPagina;
    $where = [];
    $params = [];
    $types = '';

    // Filtro por nome do produto
    if (!empty($_GET['nome_produto'])) {
        $where[] = "p.nomeProd LIKE ?";
        $params[] = '%' . $_GET['nome_produto'] . '%';
        $types .= 's';
    }

    // Filtro por marca
    if (!empty($_GET['marca'])) {
        $where[] = "m.marca LIKE ?";
        $params[] = '%' . $_GET['marca'] . '%';
        $types .= 's';
    }

    // Filtro por categoria (tag)
    if (!empty($_GET['categoria'])) {
        $where[] = "EXISTS (
            SELECT 1 FROM produto_categoria pc
            JOIN tag t ON pc.categoria_id = t.idTag
            WHERE pc.produto_id = p.idProduto AND t.tag LIKE ?
        )";
        $params[] = '%' . $_GET['categoria'] . '%';
        $types .= 's';
    }

    // Filtro de preço mínimo
    if (isset($_GET['preco_min']) && $_GET['preco_min'] !== '') {
        $where[] = "p.valorProd >= ?";
        $params[] = floatval($_GET['preco_min']);
        $types .= 'd';
    }

    // Filtro de preço máximo
    if (isset($_GET['preco_max']) && $_GET['preco_max'] !== '') {
        $where[] = "p.valorProd <= ?";
        $params[] = floatval($_GET['preco_max']);
        $types .= 'd';
    }

    // Ordenação
    $orderBy = "ORDER BY p.nomeProd";
    if (!empty($_GET['ordenar'])) {
        if ($_GET['ordenar'] == 'menor-preco') {
            $orderBy = "ORDER BY p.valorProd ASC";
        } elseif ($_GET['ordenar'] == 'maior-preco') {
            $orderBy = "ORDER BY p.valorProd DESC";
        }
    }

    $sql = "SELECT 
                p.*, 
                m.marca AS marca_nome,
                g.genero AS genero_nome
            FROM produto p
            LEFT JOIN marca m ON p.idMarca = m.idMarca
            LEFT JOIN genero g ON p.idGenero = g.idGenero";
    if ($where) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $sql .= " $orderBy LIMIT ?, ?";

    $stmt = $conexao->prepare($sql);
    $types .= 'ii';
    $params[] = $offset;
    $params[] = $itensPorPagina;
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $produtosPagina = $result->fetch_all(MYSQLI_ASSOC);

    // Buscar todas as tags para cada produto
    foreach ($produtosPagina as &$produto) {
        $produto['tag'] = getTagsProduto($conexao, $produto['idProduto']);
    }

    // Query para contar o total de produtos (sem paginação)
    $sqlCount = "SELECT COUNT(*) as total FROM produto p
                 LEFT JOIN marca m ON p.idMarca = m.idMarca";
    if ($where) {
        $sqlCount .= " WHERE " . implode(' AND ', $where);
    }
    $stmtCount = $conexao->prepare($sqlCount);
    if ($where) {
        $stmtCount->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
    }
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalProdutos = $resultCount->fetch_assoc()['total'];
    $totalPaginas = ceil($totalProdutos / $itensPorPagina);

    return [
        'produtos' => $produtosPagina,
        'totalPaginas' => $totalPaginas,
        'paginaAtual' => $pagina,
        'totalProdutos' => $totalProdutos
    ];
}
?>
