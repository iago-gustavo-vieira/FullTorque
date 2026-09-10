<?php
/**
 * MOBILE META TAGS - FULLTORQUE
 * Meta tags otimizadas para dispositivos móveis
 */
// Não fechar a tag PHP para evitar espaços em branco
?><!-- Viewport otimizado para mobile -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">

<!-- Meta tags para PWA -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="<?php echo SISTEMA_NOME ?? 'FullTorque'; ?>">

<!-- Meta tags para Android -->
<meta name="theme-color" content="#CE2B37">
<meta name="msapplication-navbutton-color" content="#CE2B37">
<meta name="msapplication-TileColor" content="#CE2B37">

<!-- Meta tags para iOS -->
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="apple-touch-icon" href="logo.png">
<link rel="apple-touch-icon" sizes="152x152" href="logo.png">
<link rel="apple-touch-icon" sizes="180x180" href="logo.png">
<link rel="apple-touch-icon" sizes="167x167" href="logo.png">

<!-- Meta tags para performance -->
<meta name="format-detection" content="telephone=no">
<meta name="format-detection" content="date=no">
<meta name="format-detection" content="address=no">
<meta name="format-detection" content="email=no">

<!-- Preload de recursos críticos -->
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"></noscript>

<!-- DNS Prefetch para recursos externos -->
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">

<!-- Manifest para PWA -->
<link rel="manifest" href="manifest.json">

<script>
// Detectar e configurar viewport height para mobile
function setVH() {
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}

// Executar no carregamento e redimensionamento
setVH();
window.addEventListener('resize', setVH);
window.addEventListener('orientationchange', () => {
    setTimeout(setVH, 100);
});

// Detectar tema do sistema
if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.setAttribute('data-theme', 'dark');
}

// Detectar preferência de movimento reduzido
if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.documentElement.setAttribute('data-reduced-motion', 'true');
}
</script>

<style>
/* CSS crítico inline para evitar FOUC */
html {
    -webkit-text-size-adjust: 100%;
    -ms-text-size-adjust: 100%;
    text-size-adjust: 100%;
}

body {
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    overflow-x: hidden;
}

/* Prevenir zoom em inputs no iOS */
@media screen and (-webkit-min-device-pixel-ratio: 0) {
    select,
    textarea,
    input[type="text"],
    input[type="password"],
    input[type="datetime"],
    input[type="datetime-local"],
    input[type="date"],
    input[type="month"],
    input[type="time"],
    input[type="week"],
    input[type="number"],
    input[type="email"],
    input[type="url"],
    input[type="search"],
    input[type="tel"],
    input[type="color"] {
        font-size: 16px !important;
    }
}

/* Loading spinner para melhor UX */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Skeleton loading para cards */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Otimizações de performance */
.will-change-transform {
    will-change: transform;
}

.will-change-scroll {
    will-change: scroll-position;
}

/* Safe area para dispositivos com notch */
.safe-area-top {
    padding-top: env(safe-area-inset-top);
}

.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}

.safe-area-left {
    padding-left: env(safe-area-inset-left);
}

.safe-area-right {
    padding-right: env(safe-area-inset-right);
}
</style>