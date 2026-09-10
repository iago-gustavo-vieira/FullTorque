<?php
require_once 'config.php';

$conexao = conectarBD();
$result = $conexao->query("SELECT id, nome, email, senha, nivel_acesso FROM usuarios ORDER BY id");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Usuários - FullTorque</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #CE2B37; margin-bottom: 20px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #109349;
            color: white;
            font-weight: bold;
        }
        tr:hover { background: #f5f5f5; }
        .senha-hash {
            font-family: monospace;
            font-size: 11px;
            color: #666;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-admin { background: #dc3545; color: white; }
        .badge-mecanico { background: #ffc107; color: #000; }
        .badge-cliente { background: #28a745; color: white; }
        .info {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }
        .btn {
            background: #109349;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn:hover { background: #0d7a3a; }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
<div class="container">
    <h1>👥 Usuários Cadastrados</h1>
    
    <div class="info">
        <strong>ℹ️ Informação:</strong> Esta página mostra todos os usuários cadastrados no sistema. Use para verificar emails e níveis de acesso.
    </div>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nível</th>
                    <th>Senha Hash</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['nome']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php
                            $nivel = $user['nivel_acesso'] ?? 'cliente';
                            $badge_class = 'badge-cliente';
                            if ($nivel === 'admin') $badge_class = 'badge-admin';
                            elseif ($nivel === 'mecanico') $badge_class = 'badge-mecanico';
                            ?>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo strtoupper($nivel); ?></span>
                        </td>
                        <td>
                            <div class="senha-hash" title="<?php echo htmlspecialchars($user['senha']); ?>">
                                <?php echo htmlspecialchars(substr($user['senha'], 0, 30)) . '...'; ?>
                            </div>
                        </td>
                        <td>
                            <a href="redefinir-senha-admin.php?id=<?php echo $user['id']; ?>" class="btn">🔑 Redefinir Senha</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #999; padding: 40px;">Nenhum usuário encontrado.</p>
    <?php endif; ?>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="home.php" class="btn">← Voltar para Home</a>
        <a href="criar-admin.php" class="btn">👤 Atualizar Admin</a>
    </div>
</div>
</body>
</html>
<?php $conexao->close(); ?>
