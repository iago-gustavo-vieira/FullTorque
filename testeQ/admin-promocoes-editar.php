<?php
require_once 'config.php';
verificarLogin();

// Verificar se é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: index.php");
    exit;
}

$titulo = "Editar Promoção";

// Buscar promoção para editar
if (!isset($_GET['id'])) {
    header("Location: admin-promocoes.php");
    exit;
}

$conexao = conectarBD();
$id = (int)$_GET['id'];

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo_promo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $desconto_percentual = $_POST['desconto_percentual'];
    $codigo_cupom = $_POST['codigo_cupom'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Upload da nova imagem se fornecida
    $imagem_atual = $_POST['imagem_atual'];
    $imagem = $imagem_atual;
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $upload_dir = 'uploads/promocoes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Excluir imagem anterior
        if ($imagem_atual && file_exists($upload_dir . $imagem_atual)) {
            unlink($upload_dir . $imagem_atual);
        }
        
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = 'promo_' . time() . '.' . $extensao;
        $caminho_completo = $upload_dir . $nome_arquivo;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_completo)) {
            $imagem = $nome_arquivo;
        }
    }
    
    $stmt = $conexao->prepare("UPDATE promocoes_carousel SET titulo = ?, descricao = ?, desconto_percentual = ?, codigo_cupom = ?, imagem = ?, data_inicio = ?, data_fim = ?, ativo = ? WHERE id = ?");
    $stmt->bind_param("ssissssii", $titulo_promo, $descricao, $desconto_percentual, $codigo_cupom, $imagem, $data_inicio, $data_fim, $ativo, $id);
    
    if ($stmt->execute()) {
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Promoção atualizada com sucesso!'];
    } else {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao atualizar promoção!'];
    }
    
    $conexao->close();
    header("Location: admin-promocoes.php");
    exit;
}

// Buscar dados da promoção
$stmt = $conexao->prepare("SELECT * FROM promocoes_carousel WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($promocao = $result->fetch_assoc()) {
    // Promoção encontrada
} else {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Promoção não encontrada!'];
    header("Location: admin-promocoes.php");
    exit;
}

$conexao->close();
require_once 'header.php';
?>

<div class="admin-container">
    <h2>Editar Promoção</h2>
    
    <div class="card">
        <div class="card-header">
            <h3>Editar Promoção</h3>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="imagem_atual" value="<?php echo $promocao['imagem']; ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Título da Promoção</label>
                            <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($promocao['titulo']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Desconto (%)</label>
                            <input type="number" name="desconto_percentual" class="form-control" min="1" max="100" value="<?php echo $promocao['desconto_percentual']; ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3" required><?php echo htmlspecialchars($promocao['descricao']); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Código do Cupom</label>
                            <input type="text" name="codigo_cupom" class="form-control" value="<?php echo htmlspecialchars($promocao['codigo_cupom']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nova Imagem da Promoção</label>
                            <input type="file" name="imagem" class="form-control" accept="image/*">
                            <?php if ($promocao['imagem']): ?>
                                <small class="text-muted">Imagem atual: <?php echo $promocao['imagem']; ?></small>
                                <br>
                                <img src="uploads/promocoes/<?php echo $promocao['imagem']; ?>" style="width: 100px; height: 60px; object-fit: cover; margin-top: 5px; border-radius: 4px;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Data de Início</label>
                            <input type="date" name="data_inicio" class="form-control" value="<?php echo $promocao['data_inicio']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Data de Fim</label>
                            <input type="date" name="data_fim" class="form-control" value="<?php echo $promocao['data_fim']; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="ativo" class="form-check-input" id="ativo" <?php echo $promocao['ativo'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="ativo">Promoção Ativa</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Atualizar Promoção</button>
                    <a href="admin-promocoes.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 1rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin-right: 10px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.card-header {
    background: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0;
}

.card-body {
    padding: 1rem;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.5rem;
}

.col-md-6 {
    flex: 0 0 50%;
    padding: 0 0.5rem;
}

.form-actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
    }
}
</style>

<?php require_once 'footer.php'; ?>