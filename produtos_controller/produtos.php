<?php
session_start();
require_once 'produto_controller.php';
require_once '../data/conexao.php';

$conexao = conectarBanco();

// Configuração da paginação
$paginaAtual = isset($_GET['pagina']) && $_GET['pagina'] > 0 ? (int)$_GET['pagina'] : 1;
$dadosProdutos = getProdutos($conexao, $paginaAtual);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Púrpura - Loja de Roupas</title>
    <link rel="stylesheet" href="../html/css/style.css">
    <link rel="stylesheet" href="css/produtos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="../html/css/img/logo.png" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="produto.js"></script>
</head>
<body>
    <header>
        <div class="logo-container">
            <h1><a href="../html/index.php">PÚRPURA</a></h1>
            <p class="tagline">Brilhe em Púrpura</p>
        </div>
        <nav>
            <ul class="menu">
                <li><a href="../html/index.php">Início</a></li>
                <li><a href="../feminina_controller/femenina.php">Mulher</a></li>
                <li><a href="../homem_controller/homem.php">Homem</a></li>
                <li><a href="../acessorios_controller/acessorio.php">Acessórios</a></li>
                <li><a href="../produtos_controller/produtos.php" class="active">Produtos</a></li>
                <li><a href="../html/sobre.php">Sobre</a></li>
            </ul>
        </nav>
        <div class="icons">
            <a href="../produtos_controller/produtos.php" class="icon"><i class="fas fa-search"></i></a>
            <a href="../conta_controller/conta.php" class="icon"><i class="fas fa-user"></i></a>
            <a href="../favoritos_controller/favoritos.php" class="icon"><i class="fas fa-heart"></i></a>
            <a href="../carrinho_controller/carrinho.php" class="icon cart-icon"><i class="fas fa-shopping-bag"></i><span class="cart-count">0</span></a>
        </div>

        <div class="user-info">
            <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
            <form action="../login/login.php" method="post">
                <a href="../logout/logout.php" class="logout-btn">Sair</a>
            </form>
        </div>
    </header>

    <section class="produtos-banner">
        <div class="banner-content">
            <h1>Nossos Produtos</h1>
            <p>Explore nossa coleção exclusiva</p>
        </div>
    </section>

    <section class="produtos-container" id="produtos-container">
        <div class="filtro-busca">
                    <form method="get" class="filtro-busca" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 24px;">
                        <div style="flex: 1; min-width: 180px;">
                            <label for="nome_produto" style="font-weight: bold; color: #7e57c2;">Nome do produto</label>
                            <input type="text" id="nome_produto" name="nome_produto" placeholder="Ex: Camiseta" value="<?= htmlspecialchars($_GET['nome_produto'] ?? '') ?>" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
                        </div>
                        <div style="flex: 1; min-width: 140px;">
                            <label for="marca" style="font-weight: bold; color: #7e57c2;">Marca</label>
                            <input type="text" id="marca" name="marca" placeholder="Ex: Nike" value="<?= htmlspecialchars($_GET['marca'] ?? '') ?>" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
                        </div>
                        <div style="flex: 1; min-width: 140px;">
                            <label for="categoria" style="font-weight: bold; color: #7e57c2;">Categoria</label>
                            <input type="text" id="categoria" name="categoria" placeholder="Ex: Tênis" value="<?= htmlspecialchars($_GET['categoria'] ?? '') ?>" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
                        </div>
                        <div>
                            <label for="preco_min" style="font-weight: bold; color: #7e57c2;">Preço mín.</label>
                            <input type="number" id="preco_min" name="preco_min" step="0.01" min="0" placeholder="R$" value="<?= htmlspecialchars($_GET['preco_min'] ?? '') ?>" style="width: 90px; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
                        </div>
                        <div>
                            <label for="preco_max" style="font-weight: bold; color: #7e57c2;">Preço máx.</label>
                            <input type="number" id="preco_max" name="preco_max" step="0.01" min="0" placeholder="R$" value="<?= htmlspecialchars($_GET['preco_max'] ?? '') ?>" style="width: 90px; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
                        </div>
                        <div>
                            <label for="ordenar" style="font-weight: bold; color: #7e57c2;">Ordenar</label>
                            <select id="ordenar" name="ordenar" style="padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
                                <option value="">Ordenar por</option>
                                <option value="menor-preco" <?= (($_GET['ordenar'] ?? '') == 'menor-preco') ? 'selected' : '' ?>>Menor preço</option>
                                <option value="maior-preco" <?= (($_GET['ordenar'] ?? '') == 'maior-preco') ? 'selected' : '' ?>>Maior preço</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" style="background: #7e57c2; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-weight: bold; cursor: pointer;">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </form>
            </div>
        </div>

        <div class="produtos-resultados">
            <div class="resultados-header">
                <p>Exibindo <span id="produtos-exibindo"><?= count($dadosProdutos['produtos']) ?></span> de <span id="total-produtos"><?= $dadosProdutos['totalProdutos'] ?></span> produtos</p>
                <div class="view-options">
                    <button class="view-btn active"><i class="fas fa-th"></i></button>
                    <button class="view-btn"><i class="fas fa-list"></i></button>
                </div>
            </div>
            
        <div class="produtos-grid">
                <?php
                if (empty($dadosProdutos['produtos'])) {
                    echo '<div class="sem-produtos">Nenhum produto encontrado.</div>';
                } else {
                    foreach ($dadosProdutos['produtos'] as $produto) {
                        echo '<div class="product-card">';
                        echo '<div class="product-image">';
                        if (!empty($produto['imagemProd'])) {
                            echo '<img src="data:image/jpeg;base64,' . base64_encode($produto['imagemProd']) . '" alt="' . htmlspecialchars($produto['nomeProd']) . '">';
                        } else {
                            echo '<img src="../html/css/img/semImagem.jpeg" alt="Falha ao carregar imagem">';
                        }
                        echo '</div>';
                        echo '<div class="product-info">';
                        echo '<h3>'.htmlspecialchars($produto['nomeProd']).'</h3>';
                        if (!empty($produto['tag'])) {
                            echo '<div class="product-tags">';
                            foreach ($produto['tag'] as $tag) {
                                echo '<span class="product-tag">'.htmlspecialchars($tag).'</span>';
                            }
                            echo '</div>';
                        }
                        echo '<p class="product-price">R$ '.number_format($produto['valorProd'], 2, ',', '.').'</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
            
            <div class="paginacao" id="paginacao">
                <?php if ($dadosProdutos['totalPaginas'] > 1): ?>
                    <div class="paginacao-container">
                        <?php if ($dadosProdutos['paginaAtual'] > 1): ?>
                            <a href="?pagina=<?= $dadosProdutos['paginaAtual'] - 1 ?>" class="pagina-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="pagina-link disabled">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $dadosProdutos['totalPaginas']; $i++): ?>
                            <?php if ($i == $dadosProdutos['paginaAtual']): ?>
                                <span class="pagina-link active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <?php
                                $active = '';
                                $params = [
                                    'pagina' => $i,
                                    'nome_produto' => urlencode($_GET['nome_produto'] ?? ''),
                                    'marca' => urlencode($_GET['marca'] ?? ''),
                                    'categoria' => urlencode($_GET['categoria'] ?? ''),
                                    'preco_min' => urlencode($_GET['preco_min'] ?? ''),
                                    'preco_max' => urlencode($_GET['preco_max'] ?? ''),
                                    'ordenar' => urlencode($_GET['ordenar'] ?? ''),
                                ];
                                $queryString = http_build_query($params);
                                echo '<a href="?' . $queryString . '" class="pagina-link ' . $active . '">' . $i . '</a>';
                                ?>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($dadosProdutos['paginaAtual'] < $dadosProdutos['totalPaginas']): ?>
                            <a href="?pagina=<?= $dadosProdutos['paginaAtual'] + 1 ?>" class="pagina-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="pagina-link disabled">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="newsletter">
        <div class="newsletter-content">
            <h2>Inscreva-se na nossa newsletter</h2>
            <p>Receba as novidades, promoções exclusivas e dicas de moda em primeira mão</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Seu e-mail" required>
                <button type="submit" class="btn-primary">Inscrever-se</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>Púrpura</h3>
                <p>Sua essência em cores, moda que reflete sua personalidade.</p>
                <div class="social-media">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Links Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="#">Início</a></li>
                    <li><a href="#">Produtos</a></li>
                    <li><a href="#">Sobre nós</a></li>
                    <li><a href="#">Contato</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Ajuda</h3>
                <ul class="footer-links">
                    <li><a href="#">Dúvidas Frequentes</a></li>
                    <li><a href="#">Envios e Entregas</a></li>
                    <li><a href="#">Política de Devoluções</a></li>
                    <li><a href="#">Termos e Condições</a></li>
                    <li><a href="#">Política de Privacidade</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contato</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i> Av. Principal, 1000</li>
                    <li><i class="fas fa-phone"></i> (11) 99999-9999</li>
                    <li><i class="fas fa-envelope"></i> contato@purpura.com.br</li>
                </ul>
                <div class="payment-methods">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fab fa-cc-apple-pay"></i>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 Púrpura - Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>