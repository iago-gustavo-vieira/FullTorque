<!-- Banner de Cookies LGPD -->
<div id="cookieBanner" class="cookie-banner">
    <div class="cookie-banner-content">
        <div class="cookie-banner-icon">🍪</div>
        <div class="cookie-banner-text">
            <p><strong>Este site utiliza cookies</strong> para melhorar sua experiência e analisar nosso tráfego. Ao continuar navegando, você concorda com nossa <a href="politica-privacidade.php" target="_blank">Política de Privacidade</a> conforme LGPD.</p>
        </div>
        <div class="cookie-banner-actions">
            <button class="cookie-btn-reject" onclick="rejectCookies()">Recusar</button>
            <button class="cookie-btn-accept" onclick="acceptCookies()">Aceitar</button>
        </div>
    </div>
</div>

<style>
/* Banner de Cookies */
.cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #DD0101 0%, #a01e28 100%);
    color: white;
    padding: 20px;
    box-shadow: 0 -5px 20px rgba(0,0,0,0.3);
    z-index: 10000;
    transform: translateY(100%);
    transition: transform 0.4s ease;
    display: none;
}

.cookie-banner.show {
    transform: translateY(0);
    display: block;
}

.theme-alemanha .cookie-banner {
    background: linear-gradient(135deg, #FFCE00 0%, #daaf03 100%);
    color: #000;
}

.cookie-banner-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 20px;
}

.cookie-banner-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
}

.cookie-banner-text {
    flex: 1;
}

.cookie-banner-text p {
    margin: 0;
    line-height: 1.6;
    font-size: 14px;
}

.cookie-banner-text a {
    color: white;
    text-decoration: underline;
    font-weight: 600;
}

.theme-alemanha .cookie-banner-text a {
    color: #000;
}

.cookie-banner-actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}

.cookie-btn-accept,
.cookie-btn-reject {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
}

.cookie-btn-accept {
    background: white;
    color: #DD0101;
}

.theme-alemanha .cookie-btn-accept {
    background: #000;
    color: #FFCE00;
}

.cookie-btn-accept:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.cookie-btn-reject {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 2px solid rgba(255,255,255,0.5);
}

.theme-alemanha .cookie-btn-reject {
    background: rgba(0,0,0,0.2);
    color: #000;
    border-color: rgba(0,0,0,0.3);
}

.cookie-btn-reject:hover {
    background: rgba(255,255,255,0.3);
}

@media (max-width: 768px) {
    .cookie-banner {
        padding: 15px;
    }
    
    .cookie-banner-content {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .cookie-banner-icon {
        font-size: 2rem;
    }
    
    .cookie-banner-text p {
        font-size: 13px;
    }
    
    .cookie-banner-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .cookie-btn-accept,
    .cookie-btn-reject {
        width: 100%;
    }
}
</style>

<!-- Script de Gerenciamento de Cookies -->
<script src="cookie-consent.js"></script>
