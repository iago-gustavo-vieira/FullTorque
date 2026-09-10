<?php
// Define o título da página
$titulo = "Configurações de Tema";

// Define os botões do cabeçalho
$botoes_header = [
    [
        'url' => 'perfil.php',
        'icone' => 'fas fa-arrow-left',
        'texto' => 'Voltar',
        'classe' => 'btn-outline'
    ]
];

// Inclui o cabeçalho
require_once 'header.php';

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém as preferências de tema
    $cor_primaria = limparDados($_POST['cor_primaria']);
    $cor_secundaria = limparDados($_POST['cor_secundaria']);
    $modo_escuro = isset($_POST['modo_escuro']) ? 1 : 0;
    
    // Salva as preferências no banco de dados
    $conexao = conectarBD();
    $usuario_id = $_SESSION['usuario_id'];
    
    // Verifica se já existem preferências para o usuário
    $stmt = $conexao->prepare("SELECT id FROM preferencias_usuario WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        // Atualiza as preferências existentes
        $stmt = $conexao->prepare("UPDATE preferencias_usuario SET cor_primaria = ?, cor_secundaria = ?, modo_escuro = ? WHERE usuario_id = ?");
        $stmt->bind_param("ssii", $cor_primaria, $cor_secundaria, $modo_escuro, $usuario_id);
    } else {
        // Insere novas preferências
        $stmt = $conexao->prepare("INSERT INTO preferencias_usuario (usuario_id, cor_primaria, cor_secundaria, modo_escuro) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $usuario_id, $cor_primaria, $cor_secundaria, $modo_escuro);
    }
    
    if ($stmt->execute()) {
        exibirAlerta('success', 'Preferências de tema salvas com sucesso!');
    } else {
        exibirAlerta('danger', 'Erro ao salvar as preferências: ' . $conexao->error);
    }
    
    $conexao->close();
}

// Busca as preferências atuais do usuário
$conexao = conectarBD();
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conexao->prepare("SELECT * FROM preferencias_usuario WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$preferencias = $stmt->get_result()->fetch_assoc();

$conexao->close();

// Define valores padrão se não houver preferências
$cor_primaria = $preferencias['cor_primaria'] ?? COR_PRIMARIA;
$cor_secundaria = $preferencias['cor_secundaria'] ?? COR_SECUNDARIA;
$modo_escuro = $preferencias['modo_escuro'] ?? 0;
?>

<div class="page-header">
    <h2>Configurações de Tema</h2>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-palette"></i> Personalização</h2>
    </div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="cor_primaria">Cor Primária</label>
                <div class="color-picker-container">
                    <input type="color" id="cor_primaria" name="cor_primaria" value="<?php echo $cor_primaria; ?>">
                    <input type="text" id="cor_primaria_text" value="<?php echo $cor_primaria; ?>" readonly>
                </div>
                <div class="color-preview" style="background-color: <?php echo $cor_primaria; ?>"></div>
            </div>
            
            <div class="form-group">
                <label for="cor_secundaria">Cor Secundária</label>
                <div class="color-picker-container">
                    <input type="color" id="cor_secundaria" name="cor_secundaria" value="<?php echo $cor_secundaria; ?>">
                    <input type="text" id="cor_secundaria_text" value="<?php echo $cor_secundaria; ?>" readonly>
                </div>
                <div class="color-preview" style="background-color: <?php echo $cor_secundaria; ?>"></div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="modo_escuro" name="modo_escuro" <?php echo $modo_escuro ? 'checked' : ''; ?>>
                    <span class="checkbox-custom"></span>
                    Ativar modo escuro por padrão
                </label>
            </div>
            
            <div class="theme-preview">
                <h3>Visualização</h3>
                <div class="preview-container" id="preview-container">
                    <div class="preview-header" id="preview-header">
                        <div class="preview-title">Título da Página</div>
                        <div class="preview-actions">
                            <div class="preview-button">Botão</div>
                        </div>
                    </div>
                    <div class="preview-content">
                        <div class="preview-card">
                            <div class="preview-card-header">
                                <div class="preview-card-title">Título do Card</div>
                                <div class="preview-card-icon"></div>
                            </div>
                            <div class="preview-card-body">
                                <div class="preview-text">Conteúdo do card</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Salvar Preferências
            </button>
        </form>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 30px;
}

.page-header h2 {
    font-size: 1.8rem;
    color: var(--secondary-color);
}

.color-picker-container {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.color-picker-container input[type="color"] {
    width: 50px;
    height: 50px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-right: 10px;
}

.color-picker-container input[type="text"] {
    width: 100px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-family: monospace;
    text-align: center;
}

.color-preview {
    width: 100%;
    height: 30px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.checkbox-label input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkbox-custom {
    position: relative;
    display: inline-block;
    width: 20px;
    height: 20px;
    background-color: #fff;
    border: 2px solid #ddd;
    border-radius: 4px;
    margin-right: 10px;
    transition: all 0.3s;
}

.checkbox-label:hover .checkbox-custom {
    border-color: var(--primary-color);
}

.checkbox-label input:checked ~ .checkbox-custom {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.checkbox-custom:after {
    content: "";
    position: absolute;
    display: none;
    left: 6px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.checkbox-label input:checked ~ .checkbox-custom:after {
    display: block;
}

.theme-preview {
    margin: 30px 0;
}

.theme-preview h3 {
    margin-bottom: 15px;
    color: var(--secondary-color);
}

.preview-container {
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s;
}

.preview-container.dark {
    background-color: #1a1a1a;
    color: #f5f5f5;
}

.preview-header {
    background-color: #f9f9f9;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    transition: all 0.3s;
}

.preview-container.dark .preview-header {
    background-color: #333;
    border-color: #444;
}

.preview-title {
    font-weight: 600;
    color: var(--secondary-color);
    transition: all 0.3s;
}

.preview-container.dark .preview-title {
    color: #f5f5f5;
}

.preview-button {
    background-color: var(--primary-color);
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    font-size: 14px;
    transition: all 0.3s;
}

.preview-content {
    padding: 15px;
    transition: all 0.3s;
}

.preview-card {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: all 0.3s;
}

.preview-container.dark .preview-card {
    background-color: #2a2a2a;
}

.preview-card-header {
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    transition: all 0.3s;
}

.preview-container.dark .preview-card-header {
    border-color: #444;
}

.preview-card-title {
    font-weight: 600;
    color: var(--secondary-color);
    transition: all 0.3s;
}

.preview-container.dark .preview-card-title {
    color: #f5f5f5;
}

.preview-card-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: var(--primary-color);
    transition: all 0.3s;
}

.preview-card-body {
    padding: 15px;
    transition: all 0.3s;
}

.preview-text {
    color: #666;
    transition: all 0.3s;
}

.preview-container.dark .preview-text {
    color: #ccc;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Atualiza o texto do input quando a cor é alterada
    document.getElementById('cor_primaria').addEventListener('input', function() {
        document.getElementById('cor_primaria_text').value = this.value;
        updatePreview();
    });
    
    document.getElementById('cor_secundaria').addEventListener('input', function() {
        document.getElementById('cor_secundaria_text').value = this.value;
        updatePreview();
    });
    
    // Atualiza a visualização quando o modo escuro é alterado
    document.getElementById('modo_escuro').addEventListener('change', function() {
        updatePreview();
    });
    
    // Função para atualizar a visualização
    function updatePreview() {
        const corPrimaria = document.getElementById('cor_primaria').value;
        const corSecundaria = document.getElementById('cor_secundaria').value;
        const modoEscuro = document.getElementById('modo_escuro').checked;
        
        const previewContainer = document.getElementById('preview-container');
        const previewHeader = document.getElementById('preview-header');
        const previewButton = document.querySelector('.preview-button');
        const previewTitle = document.querySelector('.preview-title');
        const previewCardTitle = document.querySelector('.preview-card-title');
        const previewCardIcon = document.querySelector('.preview-card-icon');
        
        // Aplica as cores
        previewButton.style.backgroundColor = corPrimaria;
        previewTitle.style.color = corSecundaria;
        previewCardTitle.style.color = corSecundaria;
        previewCardIcon.style.backgroundColor = corPrimaria;
        
        // Aplica o modo escuro
        if (modoEscuro) {
            previewContainer.classList.add('dark');
        } else {
            previewContainer.classList.remove('dark');
        }
    }
    
    // Inicializa a visualização
    updatePreview();
});
</script>

<?php
// Inclui o rodapé
require_once 'footer.php';
?>