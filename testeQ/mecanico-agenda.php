<?php
$titulo = "Minha Agenda";
require_once 'header.php';

if (!isset($_SESSION['mecanico_id']) || $_SESSION['mecanico_id'] <= 0) {
    header("Location: index.php");
    exit;
}

$conexao = conectarBD();
$mecanico_id = $_SESSION['mecanico_id'];

// Processar atualização de disponibilidade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_disponibilidade'])) {
    // Limpar disponibilidade atual
    $stmt = $conexao->prepare("DELETE FROM mecanico_disponibilidade WHERE mecanico_id = ?");
    $stmt->bind_param("i", $mecanico_id);
    $stmt->execute();
    
    // Inserir nova disponibilidade
    for ($dia = 0; $dia <= 6; $dia++) {
        if (isset($_POST["dia_$dia"])) {
            $hora_inicio = $_POST["inicio_$dia"];
            $hora_fim = $_POST["fim_$dia"];
            
            $stmt = $conexao->prepare("INSERT INTO mecanico_disponibilidade (mecanico_id, dia_semana, hora_inicio, hora_fim) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $mecanico_id, $dia, $hora_inicio, $hora_fim);
            $stmt->execute();
        }
    }
    
    exibirAlerta('success', 'Disponibilidade atualizada com sucesso!');
}

// Buscar disponibilidade atual
$stmt = $conexao->prepare("SELECT * FROM mecanico_disponibilidade WHERE mecanico_id = ? ORDER BY dia_semana");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$disponibilidade = $stmt->get_result();

$horarios_disponiveis = [];
while ($disp = $disponibilidade->fetch_assoc()) {
    $horarios_disponiveis[$disp['dia_semana']] = [
        'inicio' => $disp['hora_inicio'],
        'fim' => $disp['hora_fim']
    ];
}

// Buscar diagnósticos por data
$stmt = $conexao->prepare("
    SELECT r.*, u.nome as cliente_nome, u.telefone, v.marca, v.modelo, v.placa,
           DATE(r.data_envio) as data_agenda
    FROM relatorios_cliente r
    JOIN usuarios u ON r.usuario_id = u.id
    LEFT JOIN veiculos v ON r.veiculo_id = v.id
    WHERE r.mecanico_id = ?
    ORDER BY r.data_envio DESC
");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$diagnosticos = $stmt->get_result();

$conexao->close();
?>

<style>
    .agenda-container {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .calendar-sidebar {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        height: fit-content;
    }
    
    .calendar-sidebar h3 {
        margin-bottom: 20px;
        color: #2c3e50;
        font-size: 1.3rem;
    }
    
    .agenda-content {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .calendar {
        width: 100%;
        border-collapse: separate;
        border-spacing: 4px;
        margin-bottom: 20px;
    }
    
    .calendar th {
        padding: 12px 8px;
        text-align: center;
        background: #109349;
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
        border-radius: 6px;
    }
    
    .calendar td {
        padding: 12px 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #f8f9fa;
        border-radius: 6px;
        font-weight: 500;
    }
    
    .calendar td:hover {
        background: #e9ecef;
        transform: scale(1.05);
    }
    
    .calendar td.today {
        background: #109349;
        color: white;
        font-weight: bold;
        box-shadow: 0 3px 8px rgba(16, 147, 73, 0.3);
    }
    
    .calendar td.has-appointments {
        background: #fff3cd;
        color: #856404;
        font-weight: bold;
    }
    
    .calendar td:empty {
        background: transparent;
        cursor: default;
    }
    
    .calendar td:empty:hover {
        transform: none;
    }
    
    .agenda-header {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }
    
    .appointment-card {
        padding: 20px;
        border-bottom: 1px solid #eee;
        transition: all 0.3s;
    }
    
    .appointment-card:hover {
        background: #f8f9fa;
    }
    
    .appointment-time {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 5px;
    }
    
    .appointment-client {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .appointment-vehicle {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    
    .appointment-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-sm {
        padding: 5px 12px;
        font-size: 0.8rem;
        border-radius: 5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    
    .btn-success {
        background: #28a745;
        color: white;
    }
    
    .btn-info {
        background: #17a2b8;
        color: white;
    }
    
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #109349;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.85rem;
        color: #666;
        font-weight: 500;
    }
    
    @media (max-width: 992px) {
        .agenda-container {
            grid-template-columns: 1fr;
        }
        
        .calendar-sidebar {
            order: 2;
        }
        
        .agenda-content {
            order: 1;
        }
    }
    
    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
        }
        
        .stat-box {
            padding: 15px;
        }
        
        .stat-number {
            font-size: 1.5rem;
        }
        
        .calendar th, .calendar td {
            padding: 8px 4px;
            font-size: 0.8rem;
        }
    }
</style>

<div class="stats-row">
    <div class="stat-box">
        <div class="stat-number"><?php echo $diagnosticos->num_rows; ?></div>
        <div class="stat-label">Total Diagnósticos</div>
    </div>
    <div class="stat-box">
        <div class="stat-number">
            <?php 
            $hoje = 0;
            $diagnosticos->data_seek(0);
            while ($d = $diagnosticos->fetch_assoc()) {
                if ($d['data_agenda'] == date('Y-m-d')) $hoje++;
            }
            echo $hoje;
            ?>
        </div>
        <div class="stat-label">Hoje</div>
    </div>
    <div class="stat-box">
        <div class="stat-number">
            <?php 
            $semana = 0;
            $diagnosticos->data_seek(0);
            while ($d = $diagnosticos->fetch_assoc()) {
                if (strtotime($d['data_agenda']) >= strtotime('monday this week') && 
                    strtotime($d['data_agenda']) <= strtotime('sunday this week')) {
                    $semana++;
                }
            }
            echo $semana;
            ?>
        </div>
        <div class="stat-label">Esta Semana</div>
    </div>
</div>

<!-- Configuração de Disponibilidade -->
<div style="background: white; border-radius: 15px; padding: 25px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
    <h3><i class="fas fa-clock"></i> Configurar Disponibilidade</h3>
    <form method="post">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-top: 20px;">
            <?php 
            $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
            for ($i = 0; $i <= 6; $i++): 
                $checked = isset($horarios_disponiveis[$i]) ? 'checked' : '';
                $inicio = $horarios_disponiveis[$i]['inicio'] ?? '08:00';
                $fim = $horarios_disponiveis[$i]['fim'] ?? '18:00';
            ?>
            <div style="border: 1px solid #eee; padding: 15px; border-radius: 8px;">
                <label style="display: flex; align-items: center; margin-bottom: 10px;">
                    <input type="checkbox" name="dia_<?php echo $i; ?>" <?php echo $checked; ?> style="margin-right: 8px;">
                    <strong><?php echo $dias[$i]; ?></strong>
                </label>
                <div style="display: flex; gap: 10px;">
                    <input type="time" name="inicio_<?php echo $i; ?>" value="<?php echo $inicio; ?>" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <input type="time" name="fim_<?php echo $i; ?>" value="<?php echo $fim; ?>" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <?php endfor; ?>
        </div>
        <button type="submit" name="atualizar_disponibilidade" style="background: #109349; color: white; padding: 12px 25px; border: none; border-radius: 8px; margin-top: 20px; cursor: pointer;">
            <i class="fas fa-save"></i> Salvar Disponibilidade
        </button>
    </form>
</div>

<div class="agenda-container">
    <div class="calendar-sidebar">
        <h3><i class="fas fa-calendar"></i> Calendário</h3>
        <table class="calendar">
            <tr>
                <th>Dom</th><th>Seg</th><th>Ter</th><th>Qua</th><th>Qui</th><th>Sex</th><th>Sáb</th>
            </tr>
            <?php
            $hoje = date('j');
            $mes = date('n');
            $ano = date('Y');
            $primeiro_dia = mktime(0, 0, 0, $mes, 1, $ano);
            $dias_mes = date('t', $primeiro_dia);
            $dia_semana = date('w', $primeiro_dia);
            
            echo "<tr>";
            for ($i = 0; $i < $dia_semana; $i++) {
                echo "<td></td>";
            }
            
            for ($dia = 1; $dia <= $dias_mes; $dia++) {
                $classe = '';
                if ($dia == $hoje) $classe = 'today';
                
                echo "<td class='$classe'>$dia</td>";
                
                if (($dia + $dia_semana) % 7 == 0) {
                    echo "</tr><tr>";
                }
            }
            echo "</tr>";
            ?>
        </table>
        
        <div style="margin-top: 20px;">
            <h4>Legenda</h4>
            <div style="display: flex; align-items: center; margin: 5px 0;">
                <div style="width: 15px; height: 15px; background: #109349; margin-right: 8px;"></div>
                <span style="font-size: 0.8rem;">Hoje</span>
            </div>
            <div style="display: flex; align-items: center; margin: 5px 0;">
                <div style="width: 15px; height: 15px; background: #fff3cd; margin-right: 8px;"></div>
                <span style="font-size: 0.8rem;">Com agendamentos</span>
            </div>
        </div>
    </div>
    
    <div class="agenda-content">
        <div class="agenda-header">
            <h3><i class="fas fa-clock"></i> Diagnósticos Agendados</h3>
        </div>
        
        <?php if ($diagnosticos->num_rows > 0): ?>
            <?php 
            $diagnosticos->data_seek(0);
            while ($diagnostico = $diagnosticos->fetch_assoc()): 
            ?>
                <div class="appointment-card">
                    <div class="appointment-time">
                        <i class="fas fa-calendar"></i> 
                        <?php echo formatarData($diagnostico['data_envio'], 'd/m/Y H:i'); ?>
                    </div>
                    <div class="appointment-client">
                        <i class="fas fa-user"></i> <?php echo $diagnostico['cliente_nome']; ?>
                    </div>
                    <?php if ($diagnostico['marca']): ?>
                    <div class="appointment-vehicle">
                        <i class="fas fa-car"></i> 
                        <?php echo $diagnostico['marca'] . ' ' . $diagnostico['modelo'] . ' (' . $diagnostico['placa'] . ')'; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="appointment-actions">
                        <a href="tel:<?php echo $diagnostico['telefone']; ?>" class="btn-sm btn-success">
                            <i class="fas fa-phone"></i> Ligar
                        </a>
                        <button class="btn-sm btn-info" onclick="verDetalhes(<?php echo $diagnostico['id']; ?>)">
                            <i class="fas fa-eye"></i> Detalhes
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; color: #666;">
                <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 15px;"></i>
                <h3>Nenhum diagnóstico agendado</h3>
                <p>Você não possui diagnósticos para hoje.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function verDetalhes(id) {
    alert('Detalhes do diagnóstico ID: ' + id);
}
</script>

<?php require_once 'footer.php'; ?>