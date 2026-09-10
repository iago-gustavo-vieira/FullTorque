<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - FullTorque</title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #DD0101;
            --primary-dark: #a01e28;
        }
        
        body.theme-alemanha {
            --primary-color: #FFCE00;
            --primary-dark: #daaf03;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 40px 20px;
            transition: all 0.3s;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        body.theme-alemanha .container {
            background: #000;
            box-shadow: 0 10px 40px rgba(255,206,0,0.3);
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        body.theme-alemanha .header {
            color: #000;
        }
        
        .header-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .content {
            padding: 40px;
        }
        
        .section {
            margin-bottom: 35px;
        }
        
        .section-title {
            color: var(--primary-color);
            font-size: 1.4rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            font-size: 1.5rem;
        }
        
        .section p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        body.theme-alemanha .section p {
            color: #ccc;
        }
        
        .section ul {
            margin: 15px 0 15px 30px;
            color: #666;
        }
        
        body.theme-alemanha .section ul {
            color: #ccc;
        }
        
        .section li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .highlight-box {
            background: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        body.theme-alemanha .highlight-box {
            background: #2a2a2a;
            color: #ccc;
        }
        
        .highlight-box h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .contact-box {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-top: 40px;
        }
        
        body.theme-alemanha .contact-box {
            color: #000;
        }
        
        .contact-box h3 {
            margin-bottom: 15px;
        }
        
        .contact-box a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .contact-box a:hover {
            text-decoration: underline;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        body.theme-alemanha .back-btn {
            color: #000;
        }
        
        .back-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(221,1,1,0.3);
        }
        
        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .section-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">🔒</div>
            <h1>Política de Privacidade</h1>
            <p>Última atualização: <?php echo date('d/m/Y'); ?></p>
        </div>
        
        <div class="content">
            <button onclick="voltarAoSite()" class="back-btn" style="border: none; cursor: pointer;">
                <i class="fas fa-arrow-left"></i> Voltar ao Site
            </button>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    1. Introdução
                </h2>
                <p>A FullTorque está comprometida em proteger sua privacidade e seus dados pessoais. Esta Política de Privacidade explica como coletamos, usamos, armazenamos e protegemos suas informações em conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018).</p>
            </div>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-database"></i>
                    2. Informações que Coletamos
                </h2>
                <p>Coletamos diferentes tipos de informações para fornecer e melhorar nossos serviços:</p>
                <ul>
                    <li><strong>Dados de Cadastro:</strong> Nome, sobrenome, CPF, e-mail, telefone/celular</li>
                    <li><strong>Dados de Veículos:</strong> Marca, modelo, ano, placa, quilometragem</li>
                    <li><strong>Dados de Navegação:</strong> Endereço IP, tipo de navegador, páginas visitadas, tempo de permanência</li>
                    <li><strong>Cookies:</strong> Preferências de tema, sessão de login, histórico de navegação</li>
                    <li><strong>Dados de Agendamento:</strong> Histórico de serviços, datas, horários, valores</li>
                </ul>
            </div>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-cookie-bite"></i>
                    3. Uso de Cookies
                </h2>
                <p>Utilizamos cookies para melhorar sua experiência em nosso site:</p>
                <ul>
                    <li><strong>Cookies Essenciais:</strong> Necessários para o funcionamento básico do site (sessão, autenticação)</li>
                    <li><strong>Cookies de Preferência:</strong> Armazenam suas escolhas (tema Itália/Alemanha, idioma)</li>
                    <li><strong>Cookies de Análise:</strong> Nos ajudam a entender como você usa o site</li>
                </ul>
                <div class="highlight-box">
                    <h4>Gerenciamento de Cookies</h4>
                    <p>Os cookies expiram após 30 dias de inatividade. Você pode gerenciar ou excluir cookies através das configurações do seu navegador a qualquer momento.</p>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-bullseye"></i>
                    4. Como Usamos suas Informações
                </h2>
                <p>Utilizamos suas informações pessoais para:</p>
                <ul>
                    <li>Processar e gerenciar seus agendamentos de serviços</li>
                    <li>Enviar confirmações, lembretes e notificações importantes</li>
                    <li>Melhorar nossos serviços e experiência do usuário</li>
                    <li>Realizar análises estatísticas e de desempenho</li>
                    <li>Cumprir obrigações legais e regulatórias</li>
                    <li>Prevenir fraudes e garantir a segurança do sistema</li>
                </ul>
            </div>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-shield-alt"></i>
                    5. Segurança dos Dados
                </h2>
                <p>Implementamos medidas técnicas e organizacionais para proteger seus dados:</p>
                <ul>
                    <li>Criptografia de dados sensíveis (senhas, informações de pagamento)</li>
                    <li>Acesso restrito aos dados apenas para funcionários autorizados</li>
                    <li>Monitoramento contínuo de segurança e vulnerabilidades</li>
                    <li>Backups regulares para prevenir perda de dados</li>
                    <li>Servidores seguros com certificados SSL/TLS</li>
                </ul>
            </div>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-share-alt"></i>
                    6. Compartilhamento de Dados
                </h2>
                <p>Não vendemos, alugamos ou compartilhamos suas informações pessoais com terceiros para fins de marketing. Podemos compartilhar dados apenas nas seguintes situações:</p>
                <ul>
                    <li>Com prestadores de serviços essenciais (processamento de pagamentos, hospedagem)</li>
                    <li>Quando exigido por lei ou ordem judicial</li>
                    <li>Para proteger nossos direitos legais ou segurança</li>
                    <li>Com seu consentimento explícito</li>
                </ul>
            </div>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-user-check"></i>
                    7. Seus Direitos (LGPD)
                </h2>
                <p>De acordo com a LGPD, você tem os seguintes direitos:</p>
                <ul>
                    <li><strong>Acesso:</strong> Solicitar cópia de todos os seus dados pessoais</li>
                    <li><strong>Correção:</strong> Atualizar dados incompletos, inexatos ou desatualizados</li>
                    <li><strong>Exclusão:</strong> Solicitar a eliminação de dados desnecessários ou tratados em desconformidade</li>
                    <li><strong>Portabilidade:</strong> Receber seus dados em formato estruturado e legível</li>
                    <li><strong>Revogação:</strong> Retirar consentimento a qualquer momento</li>
                    <li><strong>Informação:</strong> Saber com quem compartilhamos seus dados</li>
                    <li><strong>Oposição:</strong> Opor-se ao tratamento de dados em certas situações</li>
                </ul>
            </div>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    8. Retenção de Dados
                </h2>
                <p>Mantemos seus dados pessoais apenas pelo tempo necessário para cumprir as finalidades descritas nesta política, salvo quando a lei exigir período maior de retenção.</p>
                <ul>
                    <li><strong>Dados de cadastro:</strong> Enquanto sua conta estiver ativa</li>
                    <li><strong>Histórico de serviços:</strong> 5 anos (conforme legislação fiscal)</li>
                    <li><strong>Cookies:</strong> 30 dias de inatividade</li>
                </ul>
            </div>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-child"></i>
                    9. Menores de Idade
                </h2>
                <p>Nossos serviços não são direcionados a menores de 18 anos. Não coletamos intencionalmente informações de menores sem o consentimento dos pais ou responsáveis legais.</p>
            </div>
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-sync-alt"></i>
                    10. Alterações nesta Política
                </h2>
                <p>Podemos atualizar esta Política de Privacidade periodicamente. Notificaremos sobre mudanças significativas através de e-mail ou aviso em nosso site. A data da última atualização está sempre indicada no topo desta página.</p>
            </div>
            
            <div class="contact-box">
                <h3><i class="fas fa-envelope"></i> Entre em Contato</h3>
                <p>Para exercer seus direitos, esclarecer dúvidas ou fazer reclamações sobre privacidade:</p>
                <p style="margin-top: 15px;">
                    <strong>E-mail:</strong> <a href="mailto:ciao@fulltorque.com.br">ciao@fulltorque.com.br</a><br>
                    <strong>Telefone:</strong> <a href="tel:+551239325190">(11) 3932-5190</a><br>
                    <strong>WhatsApp:</strong> <a href="https://wa.me/5512988211304">(12) 98821-1304</a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Aplicar tema salvo do localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'theme-alemanha') {
                document.body.classList.add('theme-alemanha');
            }
        });
        
        // Função para voltar ao site
        function voltarAoSite() {
            // Se tem histórico, volta
            if (window.history.length > 1) {
                window.history.back();
            } else {
                // Se não tem histórico (abriu em nova aba), fecha a aba ou vai para home
                window.close();
                // Se não conseguir fechar (bloqueado pelo navegador), redireciona
                setTimeout(() => {
                    window.location.href = 'home.php';
                }, 100);
            }
        }
    </script>
</body>
</html>
