<?php
session_start();
// Verifica se o usuário está logado
if(!isset($_SESSION['adm_id'])) {
    header("Location: ../login/loginAdm.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Púrpura Admin - Cadastro de Produto</title>
    <link rel="stylesheet" href="produto.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="../../html/css/img/logo.png" type="image/x-icon">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar de navegação -->
        <nav class="sidebar">
            <div class="logo-container">
                <h1>PÚRPURA</h1>
                <p class="tagline">Área Administrativa</p>
            </div>
            <ul class="admin-menu">
                <li>
                    <a href="../dashbord/dashbord.php" class="menu-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="cadastroProduto.php" class="menu-item active">
                        <i class="fas fa-tshirt"></i>
                        <span>Produtos</span>
                    </a>
                </li>
                <li>
                    <a href="#pedidos" class="menu-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Pedidos</span>
                    </a>
                </li>
                <li>
                    <a href="#clientes" class="menu-item">
                        <i class="fas fa-users"></i>
                        <span>Clientes</span>
                    </a>
                </li>
                <li>
                    <a href="#categorias" class="menu-item">
                        <i class="fas fa-tags"></i>
                        <span>Categorias</span>
                    </a>
                </li>
                <li>
                    <a href="#relatorios" class="menu-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Relatórios</span>
                    </a>
                </li>
                <li>
                    <a href="#configuracoes" class="menu-item">
                        <i class="fas fa-cog"></i>
                        <span>Configurações</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="../logout/logoutAdm.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </div>
        </nav>

        <!-- Conteúdo principal -->
        <main class="main-content">
            <header class="admin-header">
                <div class="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="admin-search">
                    <input type="text" placeholder="Pesquisar...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                <div class="admin-profile">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="admin-info">
                        <span>Seja Bem Vindo, <?php echo htmlspecialchars($_SESSION['adm_nome']); ?></span>
                        <img src="../img/adm.jpeg" alt="" class="admin-avatar">
                    </div>
                </div>
            </header>

            <!-- Conteúdo da página -->
            <div class="content-pages">
                <div class="page-header">
                    <h2 class="page-title">Cadastrar Novo Produto</h2>
                    <div class="header-actions">
                        <button class="btn-secondary" onclick="window.history.back()">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </button>
                        <button class="btn-primary" onclick="document.getElementById('product-form').requestSubmit()">
                            <i class="fas fa-save"></i> Salvar Produto
                        </button>
                    </div>
                </div>

                <!-- Formulário de Cadastro de Produto -->
                <div class="form-container active">
                    <form id="product-form" method="POST" action="processaProduto.php" enctype="multipart/form-data">
                        <!-- Informações Básicas -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Informações Básicas
                            </h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="product-name">Nome do Produto *</label>
                                    <input type="text" id="product-name" name="nomeProd" required 
                                           placeholder="Ex: Vestido Púrpura Elegance">
                                </div>
                                <div class="form-group">
                                    <label for="product-category">Categoria/Tags *</label>
                                    <select id="product-category" name="categorias[]" multiple required style="height: 120px;">
                                        <option value="" select disabled>Selecione uma categoria</option>
                                        <?php
                                        // CONEXAO COM O BANCO PARA BUSCAR AS CATEGORIAS
                                        require_once '../../data/conexao.php';
                                        $conexao = conectarBanco();
                                        $result = mysqli_query($conexao, "SELECT idTag, tag FROM tag");
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<option value='{$row['idTag']}'>{$row['tag']}</option>";
                                        }
                                        $conexao->close();
                                        ?>
                                    </select>
                                    <small>Segure Ctrl (Windows) ou Command (Mac) para selecionar mais de uma.</small>
                                </div>
                                <div class="form-group">
                                    <label for="product-brand">Marca</label>
                                    <select id="product-brand" name="idMarca" class="form-control">
                                        <option value="" selected disabled>Selecione uma marca</option>
                                        <?php
                                        // CONEXAO COM O BANCO PARA BUSCAR AS MARCAS
                                        require_once '../../data/conexao.php';
                                        $conexao = conectarBanco();
                                        $sql = "SELECT idMarca, marca FROM marca ORDER BY marca";
                                        $resultado = $conexao->query($sql);
                                        
                                        if ($resultado->num_rows > 0) {
                                            while($row = $resultado->fetch_assoc()) {
                                                echo '<option value="' . htmlspecialchars($row['idMarca']) . '">' 
                                                    . htmlspecialchars($row['marca']) . '</option>';
                                            }
                                        }
                                        $conexao->close();
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="product-gender">Gênero *</label>
                                    <select id="product-gender" name="idGenero" required>
                                        <option value="" select disabled>Selecione um gênero</option>
                                        <?php
                                        // CONEXAO COM O BANCO PARA BUSCAR OS GENEROS
                                        require_once '../../data/conexao.php';
                                        $conexao = conectarBanco();
                                        $sql = "SELECT idGenero, genero FROM genero ORDER BY genero";
                                        $resultado = $conexao->query($sql);
                                        
                                        if ($resultado->num_rows > 0) {
                                            while($row = $resultado->fetch_assoc()) {
                                                echo '<option value="' . htmlspecialchars($row['idGenero']) . '">' 
                                                    . htmlspecialchars($row['genero']) . '</option>';
                                            }
                                        }
                                        $conexao->close();
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group full-width">
                                    <label for="product-description">Descrição do Produto *</label>
                                    <textarea id="product-description" name="descricaoProd" rows="4" required 
                                              placeholder="Descreva as características, materiais, cuidados e detalhes do produto..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Preços e Estoque -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-dollar-sign"></i>
                                Preços e Estoque
                            </h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="product-price">Preço Original (R$) *</label>
                                    <input type="number" name="valorProd" step="0.01" required 
                                           placeholder="0,00">
                                </div>
                                <div class="form-group">
                                    <label for="product-cost">Custo do Produto (R$)</label>
                                    <input type="number" name="valorCusto" step="0.01" 
                                           placeholder="0,00">
                                </div>
                                <div class="form-group">
                                    <label for="product-stock">Quantidade em Estoque *</label>
                                    <input type="number" name="quantidade" required min="0" 
                                           placeholder="0">
                                </div>
                            </div>
                        </div>

                        <!-- Especificações -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-ruler"></i>
                                Especificações
                            </h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="product-color">Cor Principal</label>
                                    <input type="text" name="cor" 
                                           placeholder="Ex: Púrpura, Roxo, Violeta">
                                </div>
                                <div class="form-group">
                                    <label for="product-material">Material</label>
                                    <input type="text" name="material" 
                                           placeholder="Ex: Algodão, Poliéster, Seda">
                                </div>
                                <div class="form-group">
                                    <label for="product-sizes">Tamanhos Disponíveis</label>
                                    <div class="checkbox-group sizes-group">
                                        <label><input type="checkbox" name="pp" value="PP"> PP</label>
                                        <label><input type="checkbox" name="p" value="P"> P</label>
                                        <label><input type="checkbox" name="m" value="M"> M</label>
                                        <label><input type="checkbox" name="g" value="G"> G</label>
                                        <label><input type="checkbox" name="gg" value="GG"> GG</label>
                                        <label><input type="checkbox" name="xgg" value="XGG"> XGG</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="product-weight">Peso (gramas)</label>
                                    <input type="number" name="peso" 
                                           placeholder="Ex: 250">
                                </div>
                            </div>
                        </div>

                   <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-images"></i>
                                Imagens do Produto
                            </h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="product-image">Imagem Principal *</label>
                                    <div class="file-upload-area">
                                        <input type="file" id="imagemProd" name="imagemProd" accept="image/*" required>
                                        <div class="upload-placeholder">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Clique para selecionar ou arraste a imagem aqui</p>
                                            <small>PNG, JPG até 5MB</small>
                                        </div>
                                    </div>
                                    <img id="preview-image" style="max-width: 200px; display: none; margin-top: 10px;" alt="Preview da imagem">
                                </div>
                            </div>
                        </div>
<!--
                                <div class="form-group">
                                    <label for="product-gallery">Galeria de Imagens</label>
                                    <div class="file-upload-area">
                                        <input type="file" id="product-gallery" name="product-gallery" accept="image/*" multiple>
                                        <div class="upload-placeholder">
                                            <i class="fas fa-images"></i>
                                            <p>Adicione até 8 imagens adicionais</p>
                                            <small>PNG, JPG até 5MB cada</small>
                                        </div>
                                    </div>
                                </div>

                                

                                    -->

                                    
                        <!-- Botões de Ação -->
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="window.history.back()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="button" class="btn-secondary">
                                <i class="fas fa-save"></i> Salvar como Rascunho
                            </button>
                            <button type="submit" class="btn-primary">
                                <!--<i class="fas fa-check"></i>--> Cadastrar Produto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- 
    
    
    
    

    <div class="modal" id="success-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle" style="color: var(--verde);"></i> Produto Cadastrado com Sucesso!</h3>
                <button class="close-modal" onclick="closeModal('success-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>O produto foi cadastrado com sucesso no sistema.</p>
                <div class="modal-actions">
                    <button class="btn-secondary" onclick="closeModal('success-modal')">Fechar</button>
                    <button class="btn-primary" onclick="window.location.reload()">Cadastrar Outro</button>
                </div>
            </div>
        </div>
    </div>
                                    
    <script>
        // Funcionalidade básica para o formulário
        document.getElementById('product-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simulação de envio
            setTimeout(() => {
                document.getElementById('success-modal').style.display = 'flex';
            }, 500);
        });

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Preview de imagens
        document.getElementById('product-image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Aqui você pode adicionar lógica para mostrar preview da imagem
                    console.log('Imagem carregada:', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Toggle sidebar para mobile
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        });
    </script>

                                    -->

    <script>
        document.getElementById('imagemProd').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview-image');
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
    </script>
</body>
</html>