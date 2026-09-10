<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <?php include 'mobile-meta.php'; ?>
    <title>FullTorque - Serviços Automotivos</title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="testeQ/themes.css">
    <link rel="stylesheet" href="testeQ/responsive.css">
    <link rel="stylesheet" href="testeQ/mobile-specific.css">
    <style>
        :root {
            --primary-color: <?php echo COR_PRIMARIA; ?>;
            --secondary-color: <?php echo COR_SECUNDARIA; ?>;
            --tertiary-color: <?php echo COR_TERCIARIA; ?>;
            --accent-color: <?php echo COR_DESTAQUE; ?>;
            --success-color: <?php echo COR_SUCESSO; ?>;
            --warning-color: <?php echo COR_ALERTA; ?>;
            --error-color: <?php echo COR_ERRO; ?>;
            --text-color: <?php echo COR_TEXTO; ?>;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        *, *::before, *::after {
            box-sizing: border-box;
        }
        
        body {
            background-color: #FFFFFF;
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        html {
            overflow-x: hidden;
            max-width: 100vw;
            width: 100%;
            position: relative;
        }
        
        body, html {
            width: 100%;
            position: relative;
        }
        
        body > * {
            max-width: 100vw;
            overflow-x: hidden;
        }
        
        /* Tema Itália (padrão) */
        .header {
            background-color: rgba(0, 0, 0, 0.23);
            backdrop-filter: blur(10px);
        }
        /* Tema Alemanha */
        .theme-alemanha body {
            background-color: #000000;
            color: white;
        }
        
        .theme-alemanha .header {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .header {
            color: white;
            padding: 2px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            overflow-x: hidden;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-shrink: 0;
        }
        
        .logo img {
            height: 100px;
            width: auto;
        }
        
        .logo i {
            font-size: 2rem;
            margin-right: 10px;
            color: #CE2B37;
        }
        
        .theme-alemanha .logo i {
            color: #FFCE00;
        }
        
        .logo h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 25px;
            flex: 1;
            justify-content: center;
        }
        
        .nav-menu li {
            margin: 0;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        .nav-menu a i {
            margin-right: 5px;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #CE2B37;
        }
        
        .theme-alemanha .nav-menu a:hover {
            color: #FFCE00;
        }
        
        .btn {
            background-color: #CE2B37;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .theme-alemanha .btn {
            background-color: #FFCE00;
            color: black;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .btn:hover {
            background-color: #a01e28;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(206, 43, 55, 0.3);
        }
        
        .theme-alemanha .btn:hover {
            background-color: #daaf03ff;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
        }
        
        .btn-outline {
            background-color: transparent;
            color: #CE2B37;
            border: 1px solid #CE2B37;
        }
        
        .theme-alemanha .btn-outline {
            color: #FFCE00;
            border-color: #FFCE00;
        }
        
        .btn-outline:hover {
            background-color: #CE2B37;
            color: white;
        }
        
        .theme-alemanha .btn-outline:hover {
            background-color: #FFCE00;
            color: black;
        }
        
        .theme-alemanha .btn-outline {
            color: #000000 !important;
            border-color: #FFCE00 !important;
            background-color: #FFCE00 !important;
        }
        
        .theme-alemanha .hero .btn-outline {
            color: #000000 !important;
            border-color: #FFCE00 !important;
            background-color: #FFCE00 !important;
        }
        
        .hero {
            background-image: url('fundo-italia.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
            position: relative;
        }
        
        .theme-alemanha .hero {
            background-image: url('fundo-alemanha.jpg');
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                max-height: 85vh;
                padding: 2px;
            }
            
            .modal-content::before {
                inset: 2px;
                bottom: 2px;
            }
            
            .modal-header {
                padding: 20px 20px 0;
                margin-bottom: 15px;
            }
            
            .modal-header h2 {
                font-size: 1.4rem;
            }
            
            .modal-tabs {
                padding: 0 20px;
                margin-bottom: 15px;
            }
            
            .modal-tab {
                padding: 10px;
                font-size: 14px;
            }
            
            .modal-form {
                padding: 0 20px 20px;
            }
            
            .form-group {
                margin-bottom: 12px;
            }
            
            .form-group label {
                font-size: 13px;
                margin-bottom: 4px;
            }
            
            .form-group input {
                padding: 9px;
                font-size: 13px;
            }
            
            .btn-submit {
                padding: 11px;
                font-size: 14px;
            }
            
            .modal-close {
                top: 10px;
                right: 10px;
                font-size: 20px;
            }
            
            .header {
                padding: 5px 0;
            }
            
            .container {
                width: 100%;
                max-width: 100%;
                padding: 0 10px !important;
                margin: 0 !important;
            }
            
            .header-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .logo {
                order: 2;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .logo img {
                height: 50px;
                margin: 0 !important;
                padding: 0 !important;
                display: block;
            }
            
            .nav-menu {
                display: none;
            }
            
            .header-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .btn-account {
                order: 1;
                padding: 8px;
                font-size: 18px;
                min-width: 40px;
                justify-content: center;
            }
            
            .btn-account i {
                display: block !important;
                margin: 0 !important;
            }
            
            .btn-account .btn-text {
                display: none;
            }
            
            .theme-controls {
                order: 3;
            }
            
            .theme-toggle-wrapper {
                padding: 4px 6px;
            }
            
            .theme-switch {
                width: 40px;
                height: 22px;
            }
            
            .slider:before {
                height: 16px;
                width: 16px;
                left: 3px;
                bottom: 3px;
            }
            
            input:checked + .slider:before {
                transform: translateX(18px);
            }
            
            .flag-icon {
                width: 16px;
                height: 12px;
            }
            
            .mobile-menu-btn {
                display: none !important;
            }
            
            .hero {
                background-image: url('home_italia.jpg');
                background-size: cover;
                background-position: center center;
                min-height: 100vh;
                height: auto;
            }
            
            .theme-alemanha .hero {
                background-image: url('home_alemanha.jpg');
            }
        }
        
        @media (max-width: 480px) {
            .hero {
                background-size: cover;
                background-position: center;
            }
        }
        
        @media (max-width: 360px) {
            .hero {
                background-size: cover;
            }
        }
        

        
        .hero-content {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        

        
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .hero-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 32px;
            border-radius: 50px;
            font-size: 17px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .hero-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.5s ease, height 0.5s ease;
        }
        
        .hero-btn:hover::after {
            width: 300px;
            height: 300px;
        }
        
        .hero-btn-primary {
            background: linear-gradient(135deg, #CE2B37 0%, #a01e28 100%);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .theme-alemanha .hero-btn-primary {
            background: linear-gradient(135deg, #FFCE00 0%, #daaf03 100%);
            color: black;
        }
        
        .hero-btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            backdrop-filter: blur(15px);
            border-color: rgba(255, 255, 255, 0.4);
        }
        
        .hero-btn:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }
        
        .hero-btn-primary:hover {
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .hero-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        .btn-icon {
            position: relative;
            z-index: 1;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .hero-btn:hover .btn-icon {
            transform: rotate(10deg) scale(1.1);
            background: rgba(255, 255, 255, 0.3);
        }
        
        .btn-text {
            position: relative;
            z-index: 1;
            letter-spacing: 0.5px;
        }
        
        .section {
            padding: 80px 0;
            overflow-x: hidden;
            width: 100%;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .theme-alemanha .section-title h2 {
            color: white;
        }
        
        .theme-alemanha .section-title p {
            color: #ffffffb0;
        }
        
        .section-title p {
            color: #777;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .services {
            background-color: #f8f9fa;
        }
        
        .theme-alemanha .services {
            background-color: #1a1a1a;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .service-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .theme-alemanha .service-card {
            background-color: #000000;
            box-shadow: 0 8px 25px rgba(255, 206, 0, 0.3), 0 4px 10px rgba(255, 206, 0, 0.15);
            color: white;
        }
        
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12), 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        
        .theme-alemanha .service-card:hover {
            box-shadow: 0 15px 40px rgba(255, 206, 0, 0.4), 0 8px 20px rgba(255, 206, 0, 0.25);
        }
        
        .service-image {
            height: 200px;
            overflow: hidden;
        }
        
        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .service-card:hover .service-image img {
            transform: scale(1.1);
        }
        
        .service-content {
            padding: 20px;
        }
        
        .service-content h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .theme-alemanha .service-content h3 {
            color: white;
        }
        
        .service-content p {
            color: #777;
            margin-bottom: 15px;
        }
        
        .theme-alemanha .service-content p {
            color: #ffffffb0;
        }
        
        .service-price {
            font-weight: 600;
            color: #CE2B37;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .theme-alemanha .service-price {
            color: #000000;
            background-color: #FFCE00;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }
        
        .about {
            background-color: white;
        }
        
        .theme-alemanha .about {
            background-color: #000000;
        }
        
        .theme-alemanha .about-text p {
            color: #ffffffb0;
        }
        
        .about-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }
        
        .about-image {
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .about-text {
            flex: 1;
        }
        
        .about-text h3 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .theme-alemanha .about-text h3 {
            color: white !important;
        }
        
        .theme-alemanha .about-text p {
            color: #cccccc !important;
        }
        
        .about-text p {
            margin-bottom: 15px;
            color: #666;
        }
        
        .feature-list {
            list-style: none;
            margin-top: 20px;
        }
        
        .feature-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .theme-alemanha .feature-list li {
            color: #ffffff !important;
        }
        
        .feature-list i {
            color: #009246;
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .theme-alemanha .feature-list i {
            color: #FFCE00 !important;
        }
        
        .testimonials {
            background-color: #f8f9fa;
        }
        
        .theme-alemanha .testimonials {
            background-color: #1a1a1a;
        }
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .testimonial-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .theme-alemanha .testimonial-card {
            background-color: #000000;
            box-shadow: 0 8px 25px rgba(255, 206, 0, 0.3), 0 4px 10px rgba(255, 206, 0, 0.15);
            color: white;
        }
        
        .testimonial-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12), 0 6px 15px rgba(0, 0, 0, 0.08);
        }
        
        .theme-alemanha .testimonial-card:hover {
            box-shadow: 0 12px 35px rgba(255, 206, 0, 0.4), 0 6px 15px rgba(255, 206, 0, 0.25);
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            color: #555;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .author-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .author-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .author-info h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .theme-alemanha .author-info h4 {
            color: white;
        }
        
        .author-info p {
            font-size: 0.9rem;
            color: #777;
        }
        
        .cta {
            background: linear-gradient(135deg, #009246 0%, #009246 33%, #ffffff 33%, #ffffff 66%, #CE2B37 66%, #CE2B37 100%);
            color: white;
            text-align: center;
            position: relative;
        }
        
        .theme-alemanha .cta {
            background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
        }
        
        .cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        
        .cta .container {
            position: relative;
            z-index: 2;
        }
        
        .cta h3 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .cta p {
            margin-bottom: 30px;
            opacity: 0.9;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta .btn {
            background-color: white;
            color: #CE2B37;
            font-size: 1.1rem;
            padding: 12px 30px;
        }
        
        .theme-alemanha .cta .btn {
            background-color: #000000;
            color: #FFCE00;
        }
        
        .cta .btn:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .theme-alemanha .cta .btn:hover {
            background-color: #1a1a1a;
        }
        
        .footer {
            background-color: #009246;
            color: white;
            padding: 60px 0 20px;
        }
        
        .theme-alemanha .footer {
            background-color: #000000;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 2px;
            background-color: #CE2B37;
        }
        
        .theme-alemanha .footer-column h3::after {
            background-color: #FFCE00;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: #CE2B37;
        }
        
        .theme-alemanha .footer-links a:hover {
            color: #FFCE00;
        }
        
        .contact-info {
            list-style: none;
        }
        
        .contact-info li {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .contact-info i {
            margin-right: 10px;
            color: #CE2B37;
            font-size: 1.1rem;
            margin-top: 3px;
        }
        
        .theme-alemanha .contact-info i {
            color: #FFCE00;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background-color: #CE2B37;
            transform: translateY(-3px);
        }
        
        .theme-alemanha .social-links a:hover {
            background-color: #FFCE00;
            color: black;
        }
        
        /* Controles de Tema */
        .theme-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .theme-toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .theme-alemanha .theme-toggle-wrapper {
            background: rgba(255, 206, 0, 0.1);
            border-color: rgba(255, 206, 0, 0.3);
        }
        
        .theme-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }
        
        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #109349;
            transition: .4s;
            border-radius: 30px;
        }
        
        .theme-alemanha .slider {
            background-color: #FFCE00;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #000000;
        }
        
        input:checked + .slider:before {
            transform: translateX(24px);
        }
        
        .flag-icon {
            display: inline-block;
            width: 20px;
            height: 14px;
            border-radius: 2px;
        }
        
        .italy-flag {
            background: linear-gradient(to right, #009246 33%, #ffffff 33%, #ffffff 66%, #ce2b37 66%);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .germany-flag {
            background: linear-gradient(to bottom, #000000 0%, #000000 33%, #dd0000 33%, #dd0000 66%, #ffce00 66%, #ffce00 100%);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
        }
        
        .btn-account {
            background-color: #CE2B37;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .theme-alemanha .btn-account {
            background-color: #FFCE00;
            color: black;
        }
        
        .btn-account:hover {
            background-color: #a01e28;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(206, 43, 55, 0.3);
        }
        
        .theme-alemanha .btn-account:hover {
            background-color: #daaf03ff;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 450px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }
        
        .theme-alemanha .modal-content {
            background: #1a1a1a;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0,0,0,0.05);
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            transition: all 0.3s;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .theme-alemanha .modal-close {
            background: rgba(255,206,0,0.1);
            color: #FFCE00;
        }
        
        .modal-close:hover {
            background: #DD0101;
            color: white;
            transform: rotate(90deg) scale(1.1);
        }
        
        .theme-alemanha .modal-close:hover {
            background: #FFCE00;
            color: #000;
        }
        
        .modal-header {
            text-align: center;
            padding: 40px 30px 20px;
            background: linear-gradient(135deg, #DD0101 0%, #a01e28 100%);
            border-radius: 20px 20px 0 0;
            color: white;
        }
        
        .theme-alemanha .modal-header {
            background: linear-gradient(135deg, #FFCE00 0%, #daaf03 100%);
        }
        
        .modal-header h2 {
            font-size: 1.8rem;
            color: white;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .theme-alemanha .modal-header h2 {
            color: #000;
        }
        
        .modal-header p {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
        }
        
        .theme-alemanha .modal-header p {
            color: rgba(0,0,0,0.7);
        }
        
        .modal-tabs {
            display: flex;
            gap: 0;
            padding: 0 30px;
            margin-top: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .theme-alemanha .modal-tabs {
            border-bottom-color: #333;
        }
        
        .modal-tab {
            flex: 1;
            padding: 15px;
            background-color: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            color: #999;
            font-size: 15px;
        }
        
        .theme-alemanha .modal-tab {
            color: #666;
        }
        
        .modal-tab.active {
            color: #DD0101;
            border-bottom-color: #DD0101;
            background: transparent;
        }
        
        .theme-alemanha .modal-tab.active {
            color: #FFCE00;
            border-bottom-color: #FFCE00;
        }
        
        .modal-form {
            display: none;
            padding: 30px;
        }
        
        .modal-form.active {
            display: block;
        }
        

        
        .form-group {
            margin-bottom: 15px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .theme-alemanha .form-group label {
            color: #FFCE00;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            box-sizing: border-box;
            background: #f8f9fa;
            color: #333;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 18px;
            transition: color 0.3s;
            margin-top: 16px;
        }
        
        .password-toggle:hover {
            color: #CE2B37;
        }
        
        .theme-alemanha .password-toggle:hover {
            color: #FFCE00;
        }
        
        .theme-alemanha .form-group input {
            background-color: #2a2a2a;
            border-color: #444;
            color: #fff;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #DD0101;
            background: white;
            color: #333;
            box-shadow: 0 0 0 4px rgba(221, 1, 1, 0.1);
        }
        
        .theme-alemanha .form-group input:focus {
            border-color: #FFCE00;
            background: #2a2a2a;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(255, 206, 0, 0.1);
        }
        
        .theme-alemanha #loginErroMensagem {
            background: rgba(221, 1, 0, 0.2) !important;
            color: #FFCE00 !important;
            border-left-color: #DD0100 !important;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #DD0101;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }
        
        .theme-alemanha .btn-submit {
            background: #FFCE00;
            color: #000;
        }
        
        .btn-submit:hover {
            background: #b00101;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(221, 1, 1, 0.3);
        }
        
        .theme-alemanha .btn-submit:hover {
            background: #daaf03;
            box-shadow: 0 8px 20px rgba(255, 206, 0, 0.3);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            margin-left: 15px;
        }
        
        /* Overlay para menu mobile */
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .mobile-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            color: #aaa;
        }
        
        /* Seção de Promoções */
        .promocoes-section {
            padding: 60px 0;
            background-color: #f8f9fa;
        }
        
        .theme-alemanha .promocoes-section {
            background-color: #1a1a1a;
        }
        
        .promocoes-carousel-container {
            margin-bottom: 40px;
            position: relative;
        }
        
        .carousel-title {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .theme-alemanha .carousel-title {
            color: white;
        }
        
        .promocoes-carousel {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 100%;
            height: 500px;
            margin: 0 auto;
        }
        
        @media (max-width: 768px) {
            .promocoes-carousel {
                height: 450px;
            }
        }
        
        .carousel-track {
            display: flex;
            transition: transform 0.5s ease;
            will-change: transform;
        }
        
        .carousel-slide {
            min-width: 100%;
            position: relative;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .carousel-slide:hover {
            transform: scale(1.02);
        }
        
        .promo-slide-card {
            color: white;
            padding: 60px;
            height: 500px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .promo-slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            z-index: 1;
        }
        
        .promo-slide-content {
            position: relative;
            z-index: 2;
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .promo-slide-title {
            font-size: 2rem;
            font-weight: 900;
            margin: 0 0 10px 0;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
            color: #fff;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .promo-slide-desc {
            font-size: 1rem;
            margin-bottom: 15px;
            line-height: 1.4;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.4);
            font-weight: 500;
        }
        
        .promo-slide-discount {
            margin-bottom: 15px;
        }
        
        .discount-badge {
            background: #FFCE00;
            color: #000;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 1.5rem;
            font-weight: 900;
            display: inline-block;
            box-shadow: 0 5px 20px rgba(255,206,0,0.5);
        }
        
        .theme-alemanha .discount-badge {
            background: #fff;
            color: #DD0100;
        }
        
        .promo-slide-cupom {
            margin-bottom: 15px;
        }
        
        .cupom-code {
            background: rgba(255,255,255,0.2);
            color: #fff;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 0.95rem;
            border: 2px dashed rgba(255,255,255,0.6);
            display: inline-block;
            letter-spacing: 1px;
            backdrop-filter: blur(10px);
        }
        
        .promo-slide-validity {
            font-size: 0.9rem;
            color: #fff;
            padding: 6px 12px;
            border-radius: 5px;
            background: rgba(0,0,0,0.5);
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .btn-promo {
            background: #fff;
            color: #DD0101;
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 0.9rem;
            display: inline-block;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transition: all 0.3s;
        }
        
        .btn-promo:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }
        
        .theme-alemanha .btn-promo {
            background: #FFCE00;
            color: #000;
        }
        
        .promo-logo {
            position: absolute;
            top: 20px;
            right: 30px;
            max-width: 150px;
            height: auto;
            z-index: 3;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.4));
        }
        
        .promo-bottom-left {
            position: relative;
            z-index: 3;
            margin-bottom: 20px;
        }
        
        .promo-bottom-actions {
            position: relative;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }
        
        .carousel-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-50%) scale(1.1);
        }
        
        .carousel-prev {
            left: 20px;
        }
        
        .carousel-next {
            right: 20px;
        }
        
        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .carousel-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(0,0,0,0.3);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .carousel-dot.active {
            background: #667eea;
            transform: scale(1.3);
        }
        
        .theme-alemanha .carousel-dot.active {
            background: #FFCE00;
        }
        
        /* Seção de Estatísticas */
        .stats-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #009246 0%, #009246 33%, #ffffff 33%, #ffffff 66%, #CE2B37 66%, #CE2B37 100%);
            position: relative;
        }
        
        .theme-alemanha .stats-section {
            background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
        }
        
        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        
        .stats-section .container {
            position: relative;
            z-index: 2;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .stat-card {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            color: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }
        
        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1);
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .stat-label {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 0.9;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            color: white;
        }
        
        .theme-alemanha .stat-label {
            color: #FFCE00;
        }
        
        /* Animação de contagem */
        @keyframes countUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-ready {
            animation: countUp 0.8s ease-out;
        }
        
        .service-features {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .feature {
            background-color: rgba(206, 43, 55, 0.1);
            color: #CE2B37;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .theme-alemanha .feature {
            background-color: rgba(255, 206, 0, 0.2);
            color: #FFCE00;
        }
        
        /* Seção de Diferenciais */
        .differentials-section {
            padding: 80px 0;
            background-color: white;
        }
        
        .theme-alemanha .differentials-section {
            background-color: #000000;
        }
        
        .differentials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .differential-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 15px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .theme-alemanha .differential-card {
            background-color: #1a1a1a;
            color: white;
        }
        
        .differential-card:hover {
            transform: translateY(-5px);
            border-color: #CE2B37;
            box-shadow: 0 10px 30px rgba(206, 43, 55, 0.1);
        }
        
        .theme-alemanha .differential-card:hover {
            border-color: #FFCE00;
            box-shadow: 0 10px 30px rgba(255, 206, 0, 0.2);
        }
        
        .differential-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #CE2B37;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            transition: all 0.3s ease;
        }
        
        .theme-alemanha .differential-icon {
            background-color: #FFCE00;
            color: black;
        }
        
        .differential-card:hover .differential-icon {
            transform: scale(1.1);
        }
        
        .differential-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .theme-alemanha .differential-card h3 {
            color: white;
        }
        
        .differential-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .theme-alemanha .differential-card p {
            color: #ffffffb0;
        }
        
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
        }
        
        .cookie-banner.show {
            transform: translateY(0);
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
        
        /* Seção de FAQ */
        .faq-section {
            padding: 80px 0;
            background-color: #f8f9fa;
        }
        
        .theme-alemanha .faq-section {
            background-color: #1a1a1a;
        }
        
        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .faq-item {
            background-color: white;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .theme-alemanha .faq-item {
            background-color: #000000;
            box-shadow: 0 2px 10px rgba(255, 206, 0, 0.1);
        }
        
        .faq-question {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background-color: #f8f9fa;
        }
        
        .theme-alemanha .faq-question:hover {
            background-color: #1a1a1a;
        }
        
        .faq-question h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #2c3e50;
        }
        
        .theme-alemanha .faq-question h3 {
            color: white;
        }
        
        .faq-question i {
            color: #CE2B37;
            transition: transform 0.3s ease;
        }
        
        .theme-alemanha .faq-question i {
            color: #FFCE00;
        }
        
        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-item.active .faq-answer {
            padding: 0 20px 20px;
            max-height: 200px;
        }
        
        .faq-answer p {
            margin: 0;
            color: #666;
            line-height: 1.6;
        }
        
        .theme-alemanha .faq-answer p {
            color: #ffffffb0;
        }
        
        .testimonial-rating {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        .testimonial-rating i {
            color: #ffd700;
            font-size: 1.2rem;
        }
        
        .testimonial-rating {
            display: flex;
            justify-content: center;
            gap: 3px;
            margin-bottom: 15px;
        }
        
        .testimonial-rating i {
            color: #FFCE00;
            font-size: 1rem;
        }
        
        .theme-alemanha .testimonial-rating i {
            color: #DD0100;
        }
        
        .cta-benefits {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        
        .benefit {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 15px 20px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .benefit:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .benefit i {
            font-size: 1.2rem;
            color: #FFCE00;
        }
        
        .theme-alemanha .benefit i {
            color: #DD0100;
        }
        
        @media (max-width: 768px) {
            .section {
                padding: 40px 0;
            }
            
            .hero-btn {
                padding: 12px 24px;
                font-size: 14px;
            }
            
            .btn-icon {
                width: 28px;
                height: 28px;
                font-size: 16px;
            }
            
            .hero-stats {
                gap: 15px;
            }
            
            .hero-stat {
                min-width: 90px;
                padding: 12px;
            }
            
            .cta-benefits {
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }
            
            .about-content {
                flex-direction: column;
            }
            
            .section-title h2 {
                font-size: 1.6rem;
                margin-bottom: 15px;
            }
            
            .section-title p {
                font-size: 0.9rem;
            }
            
            .service-card {
                margin-bottom: 15px;
            }
            
            .service-content {
                padding: 15px;
            }
            
            .service-content h3 {
                font-size: 1.2rem;
            }
            
            .service-content p {
                font-size: 0.85rem;
            }
            
            .about-text h3 {
                font-size: 1.5rem;
            }
            
            .about-text p {
                font-size: 0.9rem;
            }
            
            .feature-list li {
                font-size: 0.85rem;
                margin-bottom: 10px;
            }
            
            .testimonial-card {
                padding: 20px;
            }
            
            .testimonial-text {
                font-size: 0.9rem;
            }
            
            .cta h3 {
                font-size: 1.8rem;
            }
            
            .cta p {
                font-size: 0.9rem;
            }
            
            .footer-content {
                gap: 20px;
            }
            
            .footer-column h3 {
                font-size: 1rem;
            }
            
            .footer-links li, .contact-info li {
                font-size: 0.85rem;
            }
            
            .nav-menu {
                position: fixed;
                top: 70px;
                right: -100%;
                width: 300px;
                height: calc(100vh - 70px);
                background-color: #109349;
                flex-direction: column;
                padding: 20px;
                transition: right 0.3s ease;
                z-index: 999;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            }
            
            .nav-menu {
                background: linear-gradient(135deg, #009246 0%, #009246 33%, #ffffff 33%, #ffffff 66%, #CE2B37 66%, #CE2B37 100%);
            }
            
            .theme-alemanha .nav-menu {
                background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
            }
            
            .nav-menu.active {
                right: 0;
            }
            
            .nav-menu li {
                margin: 15px 0;
            }
            
            .nav-menu a {
                padding: 15px;
                border-radius: 8px;
                transition: all 0.3s;
            }
            
            .nav-menu a:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }
            

            
            .promocoes-carousel {
                height: auto;
                border-radius: 15px;
            }
            
            .carousel-track {
                display: flex;
                gap: 15px;
                padding: 10px;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }
            
            .carousel-track::-webkit-scrollbar {
                display: none;
            }
            
            .carousel-slide {
                min-width: 85%;
                scroll-snap-align: center;
            }
            
            .promo-slide-card {
                background: white !important;
                border-radius: 15px;
                padding: 25px 20px;
                height: auto;
                min-height: 320px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
                text-align: center;
            }
            
            .theme-alemanha .promo-slide-card {
                background: #2a2a2a !important;
            }
            
            .promo-slide-card::after {
                display: none;
            }
            
            .promo-slide-overlay {
                display: none;
            }
            
            .promo-logo {
                display: none;
            }
            
            .promo-slide-content {
                position: relative;
                z-index: 2;
                width: 100%;
                margin-bottom: 20px;
            }
            
            .promo-slide-title {
                font-size: 1.5rem;
                color: #DD0101;
                margin-bottom: 10px;
            }
            
            .theme-alemanha .promo-slide-title {
                color: #FFCE00;
            }
            
            .promo-slide-desc {
                font-size: 1rem;
                color: #666;
                margin-bottom: 15px;
            }
            
            .theme-alemanha .promo-slide-desc {
                color: #ccc;
            }
            
            .promo-bottom-left {
                position: relative;
                margin-bottom: 15px;
            }
            
            .discount-badge {
                background: #DD0101;
                color: white;
                font-size: 1.3rem;
                padding: 10px 20px;
                border-radius: 10px;
            }
            
            .theme-alemanha .discount-badge {
                background: #FFCE00;
                color: #000;
            }
            
            .promo-bottom-actions {
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .cupom-code {
                background: #f8f9fa;
                color: #333;
                border: 2px solid #DD0101;
                font-size: 0.9rem;
                padding: 10px;
                border-radius: 8px;
                font-weight: bold;
            }
            
            .theme-alemanha .cupom-code {
                background: #1a1a1a;
                color: #FFCE00;
                border-color: #FFCE00;
            }
            
            .btn-promo {
                background: #DD0101;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                font-weight: bold;
                font-size: 0.95rem;
                width: 100%;
            }
            
            .theme-alemanha .btn-promo {
                background: #FFCE00;
                color: #000;
            }
            
            .carousel-btn {
                display: none;
            }
            
            .carousel-dots {
                margin-top: 15px;
            }
            
            .carousel-btn {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .stat-label {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Animações de entrada para a seção bem-vindo */
        @keyframes welcomeFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .welcome-section .welcome-header {
            animation: welcomeFadeIn 0.8s ease-out;
        }
        
        .welcome-section .welcome-card:nth-child(1) {
            animation: welcomeFadeIn 1s ease-out 0.2s both;
        }
        
        .welcome-section .welcome-card:nth-child(2) {
            animation: welcomeFadeIn 1s ease-out 0.4s both;
        }
        
        .welcome-section .welcome-stats {
            animation: welcomeFadeIn 1s ease-out 0.6s both;
        }
        
        .welcome-section .welcome-cta {
            animation: welcomeFadeIn 1s ease-out 0.8s both;
        }
        
        /* Efeitos especiais para a seção bem-vindo */
        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        
        .welcome-card:hover::before {
            transform: translateX(100%);
        }
        
        /* Feedback Section */
        .feedback-section {
            background-color: white;
        }
        
        .theme-alemanha .feedback-section {
            background-color: #000000 !important;
        }
        
        .theme-alemanha .feedback-section .section-title {
            background: transparent !important;
        }
        
        .theme-alemanha .feedback-section .section-title h2 {
            color: #ffffff !important;
        }
        
        .theme-alemanha .feedback-section .section-title p {
            color: #cccccc !important;
        }
        
        .theme-alemanha .feedback-section > div > div {
            background: #2a2a2a !important;
        }
        
        .theme-alemanha .feedback-section .form-group label {
            color: #FFCE00 !important;
        }
        
        .theme-alemanha .feedback-section .form-group input,
        .theme-alemanha .feedback-section .form-group select,
        .theme-alemanha .feedback-section .form-group textarea {
            background-color: #1a1a1a !important;
            color: #ffffff !important;
            border: 2px solid #444 !important;
        }
        
        .theme-alemanha .feedback-section .form-group input:focus,
        .theme-alemanha .feedback-section .form-group select:focus,
        .theme-alemanha .feedback-section .form-group textarea:focus {
            border-color: #FFCE00 !important;
            box-shadow: 0 0 0 3px rgba(255, 206, 0, 0.1) !important;
        }
        
        /* Seção Bem-Vindo */
        .welcome-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }
        
        .theme-alemanha .welcome-section {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23000" opacity="0.02"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            pointer-events: none;
        }
        
        .welcome-content {
            position: relative;
            z-index: 2;
        }
        
        .welcome-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .welcome-title {
            font-size: 3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .theme-alemanha .welcome-title {
            color: white;
        }
        
        .welcome-icon {
            font-size: 2.5rem;
            animation: flagWave 3s ease-in-out infinite;
        }
        
        @keyframes flagWave {
            0%, 100% { transform: rotate(-5deg) scale(1); }
            50% { transform: rotate(5deg) scale(1.1); }
        }
        
        .welcome-subtitle {
            font-size: 1.3rem;
            color: #666;
            font-style: italic;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .theme-alemanha .welcome-subtitle {
            color: #ccc;
        }
        
        .welcome-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 40px;
            margin-bottom: 60px;
        }
        
        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            border: 3px solid transparent;
        }
        
        .italia-card {
            background: linear-gradient(135deg, rgba(0, 146, 70, 0.05), rgba(255, 255, 255, 1), rgba(206, 43, 55, 0.05));
            border-image: linear-gradient(135deg, #009246, #ffffff, #CE2B37) 1;
        }
        
        .alemanha-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.05), rgba(221, 1, 0, 0.05), rgba(255, 206, 0, 0.05));
            border-image: linear-gradient(135deg, #000000, #DD0100, #FFCE00) 1;
        }
        
        .theme-alemanha .welcome-card {
            background: #2a2a2a;
            color: white;
        }
        
        .theme-alemanha .italia-card {
            background: linear-gradient(135deg, rgba(0, 146, 70, 0.1), rgba(42, 42, 42, 1), rgba(206, 43, 55, 0.1));
        }
        
        .theme-alemanha .alemanha-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.3), rgba(221, 1, 0, 0.1), rgba(255, 206, 0, 0.1));
        }
        
        .welcome-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        }
        
        .card-flag {
            font-size: 4rem;
            text-align: center;
            margin-bottom: 20px;
            animation: flagFloat 4s ease-in-out infinite;
        }
        
        @keyframes flagFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .welcome-card h3 {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .theme-alemanha .welcome-card h3 {
            color: white;
        }
        
        .welcome-card p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #666;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .theme-alemanha .welcome-card p {
            color: #ccc;
        }
        
        .card-features {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .feature-badge {
            background: rgba(206, 43, 55, 0.1);
            color: #CE2B37;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid rgba(206, 43, 55, 0.2);
        }
        
        .alemanha-card .feature-badge {
            background: rgba(255, 206, 0, 0.1);
            color: #FFCE00;
            border-color: rgba(255, 206, 0, 0.2);
        }
        
        .theme-alemanha .feature-badge {
            background: rgba(255, 206, 0, 0.2);
            color: #FFCE00;
            border-color: rgba(255, 206, 0, 0.3);
        }
        
        .welcome-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .stat-item {
            text-align: center;
            padding: 30px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 5px solid #009246;
        }
        
        .theme-alemanha .stat-item {
            background: #2a2a2a;
            color: white;
            border-left-color: #FFCE00;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            color: #009246;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .theme-alemanha .stat-number {
            color: #FFCE00;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #666;
            font-weight: 500;
        }
        
        .theme-alemanha .stat-label {
            color: #FFCE00;
        }
        
        .welcome-cta {
            text-align: center;
            background: linear-gradient(135deg, #009246 0%, #009246 33%, #ffffff 33%, #ffffff 66%, #CE2B37 66%, #CE2B37 100%);
            color: white;
            padding: 50px;
            border-radius: 25px;
            position: relative;
            overflow: hidden;
        }
        
        .theme-alemanha .welcome-cta {
            background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
        }
        
        .welcome-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.2);
            z-index: 1;
        }
        
        .welcome-cta > * {
            position: relative;
            z-index: 2;
        }
        
        .cta-text {
            font-size: 1.5rem;
            font-style: italic;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .cta-btn.primary {
            background: white;
            color: #009246;
        }
        
        .cta-btn.secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }
        
        .theme-alemanha .cta-btn.primary {
            background: #000000;
            color: #FFCE00;
        }
        
        .cta-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 35px rgba(0,0,0,0.3);
        }
        
        .cta-btn.primary:hover {
            background: #f8f9fa;
        }
        
        .cta-btn.secondary:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
        }
        
        /* Responsividade da seção bem-vindo */
        @media (max-width: 768px) {
            .welcome-section {
                padding: 60px 0;
            }
            
            .welcome-title {
                font-size: 2.2rem;
                flex-direction: column;
                gap: 10px;
            }
            
            .welcome-icon {
                font-size: 2rem;
            }
            
            .welcome-subtitle {
                font-size: 1.1rem;
            }
            
            .welcome-grid {
                grid-template-columns: 1fr;
                gap: 30px;
                margin-bottom: 40px;
            }
            
            .welcome-card {
                padding: 30px 20px;
            }
            
            .card-flag {
                font-size: 3rem;
            }
            
            .welcome-card h3 {
                font-size: 1.5rem;
            }
            
            .welcome-card p {
                font-size: 1rem;
            }
            
            .welcome-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .stat-item {
                padding: 20px 15px;
            }
            
            .stat-number {
                font-size: 2.2rem;
            }
            
            .stat-label {
                font-size: 1rem;
            }
            
            .welcome-cta {
                padding: 30px 20px;
            }
            
            .cta-text {
                font-size: 1.2rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .cta-btn {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .welcome-title {
                font-size: 1.8rem;
            }
            
            .welcome-stats {
                grid-template-columns: 1fr;
            }
            
            .card-features {
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }
        }
        

    </style>
</head>
<body>
    <?php 
    $mostrarModalLogin = false;
    $emailErro = '';
    $mensagemSucesso = '';
    $abrirModalLoginRedefinicao = false;
    $emailRedefinicao = '';
    
    if (isset($_SESSION['alerta']) && $_SESSION['alerta']['tipo'] === 'error'): 
        $mostrarModalLogin = true;
        $emailErro = isset($_SESSION['email_tentativa']) ? htmlspecialchars($_SESSION['email_tentativa']) : '';
        unset($_SESSION['email_tentativa']);
        unset($_SESSION['alerta']);
    elseif (isset($_SESSION['alerta']) && $_SESSION['alerta']['tipo'] === 'success'): 
        $mensagemSucesso = $_SESSION['alerta']['mensagem'];
        unset($_SESSION['alerta']);
        unset($_SESSION['mostrar_alerta_cadastro']);
    endif;
    
    if (isset($_SESSION['abrir_modal_login']) && $_SESSION['abrir_modal_login'] === true):
        $abrirModalLoginRedefinicao = true;
        $emailRedefinicao = isset($_SESSION['email_login_redefinicao']) ? htmlspecialchars($_SESSION['email_login_redefinicao']) : '';
        $mensagemSucesso = 'Senha redefinida com sucesso! Faça login com sua nova senha.';
        unset($_SESSION['abrir_modal_login']);
        unset($_SESSION['email_login_redefinicao']);
    endif;
    
    if (!empty($mensagemSucesso)):
    ?>
    <div style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px; animation: slideIn 0.3s ease;">
        <div style="background: #28a745; color: white; padding: 20px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-check-circle" style="font-size: 24px;"></i>
            <div style="flex: 1;"><?php echo $mensagemSucesso; ?></div>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0; width: 30px; height: 30px;">&times;</button>
        </div>
    </div>
    <style>
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
    <script>
        setTimeout(function() {
            const alert = document.querySelector('[style*="position: fixed"]');
            if (alert) {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
        if (window.location.search.includes('feedback=sent')) {
            const url = new URL(window.location);
            url.searchParams.delete('feedback');
            window.history.replaceState({}, '', url);
        }
    </script>
    <?php endif; ?>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="logo.png" alt="FullTorque" style="height: 100px; width: auto;">
                </div>
                <ul class="nav-menu">
                    <li><a href="#services"><i class="fas fa-wrench"></i> Serviços</a></li>
                    <li><a href="#about"><i class="fas fa-info-circle"></i> Sobre</a></li>
                    <li><a href="#contact"><i class="fas fa-envelope"></i> Contato</a></li>
                </ul>
                <div class="header-actions">
                    <button id="accountBtn" class="btn-account">
                        <i class="fas fa-user-circle"></i>
                        <span class="btn-text">Minha Conta</span>
                    </button>
                    <div class="theme-controls">
                        <div class="theme-toggle-wrapper">
                            <span class="flag-icon italy-flag"></span>
                            <label class="theme-switch">
                                <input type="checkbox" id="themeToggle">
                                <span class="slider"></span>
                            </label>
                            <span class="flag-icon germany-flag"></span>
                        </div>
                    </div>
                    <div class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Modal de Recuperação de Senha -->
    <div id="recuperacaoModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="fecharModalRecuperacao()">&times;</button>
            <div class="modal-header">
                <h2>Recuperar Senha</h2>
                <p>Informe seu email para receber o link de recuperação</p>
            </div>
            <form id="recuperacaoForm" class="modal-form active" style="display: block;">
                <div id="recuperacaoMensagem" style="display: none; padding: 15px; border-radius: 8px; margin-bottom: 15px;"></div>
                <div class="form-group">
                    <label for="recuperacaoEmail">E-mail</label>
                    <input type="email" id="recuperacaoEmail" name="email" required placeholder="seu@email.com" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="recuperacaoCPF">Últimos 4 dígitos do CPF</label>
                    <input type="text" id="recuperacaoCPF" name="cpf" required placeholder="0000" maxlength="4" pattern="[0-9]{4}">
                </div>
                <div class="form-group">
                    <label>Verificação de Segurança</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 2px solid #e0e0e0; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <span id="captchaNum1" style="font-size: 24px; font-weight: bold; color: #CE2B37;">5</span>
                            <span style="font-size: 24px; font-weight: bold;">+</span>
                            <span id="captchaNum2" style="font-size: 24px; font-weight: bold; color: #CE2B37;">3</span>
                            <span style="font-size: 24px; font-weight: bold;">=</span>
                            <input type="number" id="captchaResposta" required placeholder="?" style="width: 60px; padding: 8px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 18px; text-align: center;">
                        </div>
                        <button type="button" onclick="gerarNovoCaptcha()" style="background: none; border: none; color: #CE2B37; cursor: pointer; font-size: 12px; text-decoration: underline;">🔄 Gerar novo desafio</button>
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Enviar Link de Recuperação
                </button>
            </form>
        </div>
    </div>

    <!-- Modal de Login/Cadastro -->
    <div id="accountModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" id="modalClose">&times;</button>
            <div class="modal-header">
                <h2>Bem-vindo à FullTorque</h2>
                <p>Acesse sua conta ou crie uma nova</p>
            </div>
            <div class="modal-tabs">
                <button class="modal-tab active" data-tab="login">Entrar</button>
                <button class="modal-tab" data-tab="register">Criar Conta</button>
            </div>
            
            <!-- Formulário de Login -->
            <form id="loginForm" class="modal-form active" action="processar_login.php" method="POST">
                <div id="loginErroMensagem" style="display: none; padding: 15px; border-radius: 8px; margin-bottom: 15px; background: #fde8e8; color: #e74c3c; border-left: 4px solid #e74c3c;">
                    <i class="fas fa-exclamation-circle"></i> <span id="loginErroTexto"></span>
                </div>
                <div class="form-group">
                    <label for="loginEmail">E-mail</label>
                    <input type="email" id="loginEmail" name="email" required placeholder="seu@email.com" value="<?php echo $emailErro; ?>" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="loginPassword">Senha</label>
                    <input type="password" id="loginPassword" name="senha" required placeholder="••••••••">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('loginPassword', this)"></i>
                </div>
                <a href="#" onclick="abrirModalRecuperacao(); return false;" style="display: block; text-align: right; margin-bottom: 15px; color: #CE2B37; text-decoration: none; font-size: 14px; transition: all 0.3s;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Esqueci minha senha</a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
            
            <!-- Formulário de Cadastro -->
            <form id="registerForm" class="modal-form" action="processar_cadastro.php" method="POST">
                <div class="form-group">
                    <label for="registerName">Nome</label>
                    <input type="text" id="registerName" name="nome" required placeholder="Seu primeiro nome" minlength="2">
                </div>
                <div class="form-group">
                    <label for="registerSobrenome">Sobrenome</label>
                    <input type="text" id="registerSobrenome" name="sobrenome" required placeholder="Seu sobrenome" minlength="2">
                </div>
                <div class="form-group">
                    <label for="registerCPF">CPF</label>
                    <input type="text" id="registerCPF" name="cpf" required placeholder="000.000.000-00" maxlength="14">
                </div>
                <div class="form-group">
                    <label for="registerCelular">Celular</label>
                    <input type="text" id="registerCelular" name="celular" required placeholder="(00) 00000-0000" maxlength="15">
                </div>
                <div class="form-group">
                    <label for="registerEmail">E-mail</label>
                    <input type="email" id="registerEmail" name="email" required placeholder="seu@email.com" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="registerPassword">Senha</label>
                    <input type="password" id="registerPassword" name="senha" required placeholder="Mínimo 6 caracteres" minlength="6">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('registerPassword', this)"></i>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Criar Conta
                </button>
            </form>
        </div>
    </div>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-buttons">
                    <a href="#" class="hero-btn hero-btn-primary" onclick="document.getElementById('accountBtn').click(); setTimeout(() => document.querySelector('[data-tab=register]').click(), 100); return false;">
                        <span class="btn-icon"><i class="fas fa-user-plus"></i></span>
                        <span class="btn-text">Cadastre-se</span>
                    </a>
                    <a href="#services" class="hero-btn hero-btn-secondary">
                        <span class="btn-icon"><i class="fas fa-wrench"></i></span>
                        <span class="btn-text">Serviços</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção de Promoções -->
    <section class="promocoes-section">
        <div class="container">
            <h2 class="carousel-title">🔥 Promoções Imperdíveis! 🔥</h2>
            <div class="promocoes-carousel">
                <div class="carousel-track" id="carouselTrack">
                    <!-- Slide 1 -->
                    <div class="carousel-slide">
                        <div class="promo-slide-card" style="background-image: linear-gradient(135deg, rgba(221,1,1,0.7) 0%, rgba(221,1,1,0.7) 100%), url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&q=80'); background-size: cover; background-position: center; background-blend-mode: overlay;">
                            <img src="logo.png" alt="Full Torque" class="promo-logo">
                            <div class="promo-slide-content">
                                <h2 class="promo-slide-title">PREÇO DE BANANA!</h2>
                                <p class="promo-slide-desc">Troca de óleo + filtro</p>
                            </div>
                            <div class="promo-bottom-left">
                                <span class="discount-badge">50% OFF</span>
                            </div>
                            <div class="promo-bottom-actions">
                                <span class="cupom-code" onclick="copiarCupom('BANANA50')" style="cursor: pointer;" title="Clique para copiar">CUPOM: BANANA50</span>
                                <span class="btn-promo" onclick="document.getElementById('accountBtn').click(); setTimeout(() => document.querySelector('[data-tab=register]').click(), 100);" style="cursor: pointer;">APROVEITAR OFERTA</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slide 2 -->
                    <div class="carousel-slide">
                        <div class="promo-slide-card" style="background-image: linear-gradient(135deg, rgba(16,147,73,0.7) 0%, rgba(16,147,73,0.7) 100%), url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800&q=80'); background-size: cover; background-position: center; background-blend-mode: overlay;">
                            <img src="logo.png" alt="Full Torque" class="promo-logo">
                            <div class="promo-slide-content">
                                <h2 class="promo-slide-title">TACA-LE PAU!</h2>
                                <p class="promo-slide-desc">Revisão completa</p>
                            </div>
                            <div class="promo-bottom-left">
                                <span class="discount-badge">40% OFF</span>
                            </div>
                            <div class="promo-bottom-actions">
                                <span class="cupom-code" onclick="copiarCupom('TACALEPAU40')" style="cursor: pointer;" title="Clique para copiar">CUPOM: TACALEPAU40</span>
                                <span class="btn-promo" onclick="document.getElementById('accountBtn').click(); setTimeout(() => document.querySelector('[data-tab=register]').click(), 100);" style="cursor: pointer;">APROVEITAR OFERTA</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slide 3 -->
                    <div class="carousel-slide">
                        <div class="promo-slide-card" style="background-image: linear-gradient(135deg, rgba(255,152,0,0.7) 0%, rgba(255,152,0,0.7) 100%), url('https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800&q=80'); background-size: cover; background-position: center; background-blend-mode: overlay;">
                            <img src="logo.png" alt="Full Torque" class="promo-logo">
                            <div class="promo-slide-content">
                                <h2 class="promo-slide-title">TÁ BARATO DEMAIS!</h2>
                                <p class="promo-slide-desc">Alinhamento + Balanceamento</p>
                            </div>
                            <div class="promo-bottom-left">
                                <span class="discount-badge">45% OFF</span>
                            </div>
                            <div class="promo-bottom-actions">
                                <span class="cupom-code" onclick="copiarCupom('BARATO45')" style="cursor: pointer;" title="Clique para copiar">CUPOM: BARATO45</span>
                                <span class="btn-promo" onclick="document.getElementById('accountBtn').click(); setTimeout(() => document.querySelector('[data-tab=register]').click(), 100);" style="cursor: pointer;">APROVEITAR OFERTA</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slide 4 -->
                    <div class="carousel-slide">
                        <div class="promo-slide-card" style="background-image: linear-gradient(135deg, rgba(33,150,243,0.7) 0%, rgba(33,150,243,0.7) 100%), url('https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=800&q=80'); background-size: cover; background-position: center; background-blend-mode: overlay;">
                            <img src="logo.png" alt="Full Torque" class="promo-logo">
                            <div class="promo-slide-content">
                                <h2 class="promo-slide-title">QUEIMA DE ESTOQUE!</h2>
                                <p class="promo-slide-desc">Pastilhas de freio</p>
                            </div>
                            <div class="promo-bottom-left">
                                <span class="discount-badge">35% OFF</span>
                            </div>
                            <div class="promo-bottom-actions">
                                <span class="cupom-code" onclick="copiarCupom('QUEIMA35')" style="cursor: pointer;" title="Clique para copiar">CUPOM: QUEIMA35</span>
                                <span class="btn-promo" onclick="document.getElementById('accountBtn').click(); setTimeout(() => document.querySelector('[data-tab=register]').click(), 100);" style="cursor: pointer;">APROVEITAR OFERTA</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slide 5 -->
                    <div class="carousel-slide">
                        <div class="promo-slide-card" style="background-image: linear-gradient(135deg, rgba(156,39,176,0.7) 0%, rgba(156,39,176,0.7) 100%), url('https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?w=800&q=80'); background-size: cover; background-position: center; background-blend-mode: overlay;">
                            <img src="logo.png" alt="Full Torque" class="promo-logo">
                            <div class="promo-slide-content">
                                <h2 class="promo-slide-title">IMPERDÍVEL!</h2>
                                <p class="promo-slide-desc">Troca de pneus</p>
                            </div>
                            <div class="promo-bottom-left">
                                <span class="discount-badge">30% OFF</span>
                            </div>
                            <div class="promo-bottom-actions">
                                <span class="cupom-code" onclick="copiarCupom('PNEU30')" style="cursor: pointer;" title="Clique para copiar">CUPOM: PNEU30</span>
                                <span class="btn-promo" onclick="document.getElementById('accountBtn').click(); setTimeout(() => document.querySelector('[data-tab=register]').click(), 100);" style="cursor: pointer;">APROVEITAR OFERTA</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button class="carousel-btn carousel-prev" onclick="moveCarousel(-1)">❮</button>
                <button class="carousel-btn carousel-next" onclick="moveCarousel(1)">❯</button>
            </div>
            
            <div class="carousel-dots" id="carouselDots"></div>
        </div>
    </section>

    <!-- Seção Bem-Vindo -->
    <section class="welcome-section">
        <div class="container">
            <div class="welcome-content">
                <div class="welcome-header">
                    <h2 class="welcome-title">
                        <span class="welcome-icon">🇮🇹</span>
                        Bem-vindo à FullTorque!
                        <span class="welcome-icon">🇩🇪</span>
                    </h2>
                    <p class="welcome-subtitle">Onde a tradição italiana encontra a precisão alemã</p>
                </div>
                
                <div class="welcome-grid">
                    <div class="welcome-card italia-card">
                        <div class="card-flag">🇮🇹</div>
                        <h3>Paixão Italiana</h3>
                        <p>Inspirados pela excelência das marcas Ferrari, Lamborghini, Maserati e Alfa Romeo, trazemos a paixão italiana para cada serviço.</p>
                        <div class="card-features">
                            <span class="feature-badge">🏎️ Performance</span>
                            <span class="feature-badge">❤️ Paixão</span>
                            <span class="feature-badge">🎨 Estilo</span>
                        </div>
                    </div>
                    
                    <div class="welcome-card alemanha-card">
                        <div class="card-flag">🇩🇪</div>
                        <h3>Precisão Alemã</h3>
                        <p>Com a engenharia de precisão da BMW, Mercedes-Benz, Audi e Volkswagen, garantimos qualidade e confiabilidade em cada reparo.</p>
                        <div class="card-features">
                            <span class="feature-badge">⚙️ Precisão</span>
                            <span class="feature-badge">🔧 Qualidade</span>
                            <span class="feature-badge">🛡️ Confiança</span>
                        </div>
                    </div>
                </div>
                
                <div class="welcome-stats">
                    <div class="stat-item">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Anos de Tradição</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">5000+</div>
                        <div class="stat-label">Clientes Satisfeitos</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Garantia de Qualidade</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Atendimento Online</div>
                    </div>
                </div>
                
                <div class="welcome-cta">
                    <p class="cta-text">"Seu veículo merece o melhor cuidado. Experimente a diferença FullTorque!"</p>
                    <div class="cta-buttons">
                        <a href="#" class="cta-btn primary" onclick="document.getElementById('accountBtn').click(); setTimeout(() => document.querySelector('[data-tab=register]').click(), 100); return false;">
                            <i class="fas fa-user-plus"></i>
                            Cadastre-se Agora
                        </a>
                        <a href="#services" class="cta-btn secondary">
                            <i class="fas fa-wrench"></i>
                            Ver Serviços
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="section services">
        <div class="container">
            <div class="section-title">
                <h2>🔧 Nossos Serviços Especializados 🔧</h2>
                <p>Com a tradição italiana em mecânica automotiva, oferecemos serviços completos com garantia de qualidade. Utilizamos equipamentos de última geração e peças originais.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Troca de Óleo">
                    </div>
                    <div class="service-content">
                        <h3>🛢️ Troca de Óleo Premium</h3>
                        <p>Troca de óleo do motor com produtos italianos de alta performance. Inclui filtro de óleo e verificação completa.</p>
                        <div class="service-features">
                            <span class="feature">✓ Óleo Sintético</span>
                            <span class="feature">✓ Filtro Original</span>
                            <span class="feature">✓ 30min</span>
                        </div>
                        <div class="service-price">A partir de R$ 120,00</div>
                        <a href="#" onclick="document.getElementById('accountBtn').click(); return false;" class="btn btn-outline">Agendar Agora</a>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1486754735734-325b5831c3ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Alinhamento">
                    </div>
                    <div class="service-content">
                        <h3>⚖️ Alinhamento de Precisão</h3>
                        <p>Alinhamento computadorizado 3D e balanceamento com tecnologia italiana de precisão.</p>
                        <div class="service-features">
                            <span class="feature">✓ Tecnologia 3D</span>
                            <span class="feature">✓ Garantia 6 meses</span>
                            <span class="feature">✓ 45min</span>
                        </div>
                        <div class="service-price">A partir de R$ 150,00</div>
                        <a href="#" onclick="document.getElementById('accountBtn').click(); return false;" class="btn btn-outline">Agendar Agora</a>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1487754180451-c456f719a1fc?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Revisão">
                    </div>
                    <div class="service-content">
                        <h3>🔍 Revisão Completa</h3>
                        <p>Inspeção completa com mais de 50 itens verificados. Diagnóstico computadorizado incluído.</p>
                        <div class="service-features">
                            <span class="feature">✓ 50+ Itens</span>
                            <span class="feature">✓ Diagnóstico</span>
                            <span class="feature">✓ Relatório</span>
                        </div>
                        <div class="service-price">A partir de R$ 350,00</div>
                        <a href="#" onclick="document.getElementById('accountBtn').click(); return false;" class="btn btn-outline">Agendar Agora</a>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1609521263047-f8f205293f24?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Freios">
                    </div>
                    <div class="service-content">
                        <h3>🛑 Sistema de Freios</h3>
                        <p>Manutenção completa do sistema de freios com pastilhas e discos de qualidade italiana.</p>
                        <div class="service-features">
                            <span class="feature">✓ Pastilhas Premium</span>
                            <span class="feature">✓ Teste Segurança</span>
                            <span class="feature">✓ 1h30min</span>
                        </div>
                        <div class="service-price">A partir de R$ 280,00</div>
                        <a href="#" onclick="document.getElementById('accountBtn').click(); return false;" class="btn btn-outline">Agendar Agora</a>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1544636331-e26879cd4d9b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Ar Condicionado">
                    </div>
                    <div class="service-content">
                        <h3>❄️ Ar-Condicionado</h3>
                        <p>Manutenção completa do ar-condicionado com gás ecológico e higienização.</p>
                        <div class="service-features">
                            <span class="feature">✓ Gás Ecológico</span>
                            <span class="feature">✓ Higienização</span>
                            <span class="feature">✓ 1h</span>
                        </div>
                        <div class="service-price">A partir de R$ 180,00</div>
                        <a href="#" onclick="document.getElementById('accountBtn').click(); return false;" class="btn btn-outline">Agendar Agora</a>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Suspensão">
                    </div>
                    <div class="service-content">
                        <h3>🏎️ Suspensão Esportiva</h3>
                        <p>Manutenção e upgrade do sistema de suspensão com peças de performance italiana.</p>
                        <div class="service-features">
                            <span class="feature">✓ Peças Sport</span>
                            <span class="feature">✓ Teste Pista</span>
                            <span class="feature">✓ 2h</span>
                        </div>
                        <div class="service-price">A partir de R$ 450,00</div>
                        <a href="#" onclick="document.getElementById('accountBtn').click(); return false;" class="btn btn-outline">Agendar Agora</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção de Estatísticas -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card animate-ready">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">5000+</div>
                        <div class="stat-label">Clientes Satisfeitos</div>
                    </div>
                </div>
                <div class="stat-card animate-ready">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Anos de Experiência</div>
                    </div>
                </div>
                <div class="stat-card animate-ready">
                    <div class="stat-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">10000+</div>
                        <div class="stat-label">Veículos Atendidos</div>
                    </div>
                </div>
                <div class="stat-card animate-ready">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">4.9</div>
                        <div class="stat-label">Avaliação Média</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="section about">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1504222490345-c075b6008014?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Sobre <?php echo SISTEMA_NOME; ?>">
                </div>
                <div class="about-text">
                    <h3>🎯 Por que Escolher a FullTorque?</h3>
                    <p>Inspirados pela excelência automotiva da Itália e Alemanha - países que criaram marcas icônicas como Ferrari, Lamborghini, BMW e Mercedes-Benz - trazemos o mesmo padrão de qualidade e precisão para o cuidado do seu veículo.</p>
                    <p>Com mais de 15 anos de experiência, nossa equipe é especializada em todas as marcas, utilizando equipamentos de diagnóstico de última geração e peças de alta qualidade. Oferecemos um atendimento diferenciado que combina tecnologia, transparência e compromisso com a satisfação total do cliente.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-calendar-check"></i> Agendamento online 24/7 com confirmação instantânea via WhatsApp</li>
                        <li><i class="fas fa-user-cog"></i> Mecânicos certificados e especializados em múltiplas marcas</li>
                        <li><i class="fas fa-shield-alt"></i> Garantia estendida de até 12 meses em todos os serviços</li>
                        <li><i class="fas fa-laptop-code"></i> Diagnóstico computadorizado avançado incluso em todas as revisões</li>
                        <li><i class="fas fa-hand-holding-usd"></i> Preços transparentes e fixos - sem surpresas no orçamento</li>
                        <li><i class="fas fa-car-side"></i> Veículo reserva disponível para serviços prolongados</li>
                        <li><i class="fas fa-mobile-alt"></i> Acompanhamento em tempo real pelo app com fotos e vídeos</li>
                        <li><i class="fas fa-award"></i> Programa de fidelidade VIP com descontos exclusivos</li>
                        <li><i class="fas fa-tools"></i> Equipamentos de precisão para alinhamento 3D e balanceamento</li>
                        <li><i class="fas fa-clock"></i> Atendimento ágil - cumprimos rigorosamente os prazos prometidos</li>
                    </ul>
                    <a href="#" class="btn" onclick="document.getElementById('accountBtn').click(); setTimeout(() => document.querySelector('[data-tab=register]').click(), 100); return false;">Seja nosso cliente</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção de Diferenciais -->
    <section class="differentials-section">
        <div class="container">
            <div class="section-title">
                <h2>🏆 Nossos Diferenciais Exclusivos</h2>
                <p>Descubra por que somos a escolha preferida dos apaixonados por automóveis</p>
            </div>
            <div class="differentials-grid">
                <div class="differential-card">
                    <div class="differential-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>🕰️ Agendamento Online 24h</h3>
                    <p>Sistema de agendamento inteligente disponível 24 horas por dia. Escolha o melhor horário para você com confirmação instantânea.</p>
                </div>
                <div class="differential-card">
                    <div class="differential-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>🛡️ Garantia Estendida Premium</h3>
                    <p>Garantia estendida de até 12 meses ou 20.000 km em todos os serviços. Sua tranquilidade é nossa prioridade.</p>
                </div>
                <div class="differential-card">
                    <div class="differential-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3>🔧 Tecnologia Italiana</h3>
                    <p>Equipamentos de última geração importados da Itália para diagnósticos precisos e reparos de excelência.</p>
                </div>
                <div class="differential-card">
                    <div class="differential-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>👨‍🔧 Maestri Certificati</h3>
                    <p>Mecânicos certificados com formação internacional e especialização em marcas europeias e nacionais.</p>
                </div>
                <div class="differential-card">
                    <div class="differential-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>📱 App Exclusivo</h3>
                    <p>Acompanhe seu veículo em tempo real, receba fotos do processo e aprove orçamentos pelo celular.</p>
                </div>
                <div class="differential-card">
                    <div class="differential-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>🏅 Qualidade Certificada</h3>
                    <p>Certificados ISO 9001 e selos de qualidade que garantem os mais altos padrões de atendimento.</p>
                </div>
            </div>
        </div>
    </section>



    <!-- Seção de FAQ -->
    <section class="faq-section">
        <div class="container">
            <div class="section-title">
                <h2>❓ Perguntas Frequentes</h2>
                <p>Tire suas dúvidas sobre nossos serviços especializados</p>
            </div>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Como funciona o agendamento online 24h?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Nosso sistema inteligente é simples: cadastre-se, escolha o serviço, selecione data e horário disponível, e confirme. Você receberá confirmação por email, SMS e WhatsApp com todos os detalhes.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Qual a garantia dos serviços especializados?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Oferecemos garantia premium de 6 a 12 meses ou até 20.000 km (o que ocorrer primeiro) para todos os serviços. Serviços especializados em marcas europeias têm garantia estendida de até 18 meses.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Trabalham com todas as marcas, incluindo europeias?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sim! Somos especialistas em marcas europeias (Ferrari, Maserati, Alfa Romeo, Fiat, Volkswagen, BMW, Mercedes) e também atendemos todas as marcas nacionais e asiáticas com excelência.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Como acompanho o serviço em tempo real?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Através da sua conta no sistema ou nosso app exclusivo, você acompanha em tempo real o status do seu veículo, recebe fotos do processo, aprova orçamentos e conversa diretamente com o mecânico responsável.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Oferecem serviço de busca e entrega?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sim! Oferecemos serviço de busca e entrega gratuito em um raio de 10km da oficina. Para distâncias maiores, consulte nossa tabela de preços especiais.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Aceitam cartão e oferecem parcelamento?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Aceitamos todas as formas de pagamento: dinheiro, PIX (5% desconto), cartão de débito/crédito (até 12x sem juros), e financiamento próprio para serviços acima de R$ 1.000.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção de Feedback -->
    <section class="section feedback-section">
        <div class="container">
            <div class="section-title">
                <h2>💬 Envie seu Feedback</h2>
                <p>Sua opinião é muito importante para nós! Compartilhe sua experiência.</p>
            </div>
            <div style="max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.08);">
                <form action="processar_feedback.php" method="POST">
                    <div class="form-group">
                        <label for="feedbackNome">Nome Completo</label>
                        <input type="text" id="feedbackNome" name="nome" required placeholder="Seu nome">
                    </div>
                    <div class="form-group">
                        <label for="feedbackEmail">E-mail</label>
                        <input type="email" id="feedbackEmail" name="email" required placeholder="seu@email.com">
                    </div>
                    <div class="form-group">
                        <label for="feedbackAvaliacao">Avaliação</label>
                        <select id="feedbackAvaliacao" name="avaliacao" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                            <option value="">Selecione uma avaliação</option>
                            <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                            <option value="4">⭐⭐⭐⭐ Muito Bom</option>
                            <option value="3">⭐⭐⭐ Bom</option>
                            <option value="2">⭐⭐ Regular</option>
                            <option value="1">⭐ Ruim</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="feedbackMensagem">Mensagem</label>
                        <textarea id="feedbackMensagem" name="mensagem" required placeholder="Conte-nos sobre sua experiência..." rows="5" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Enviar Feedback
                    </button>
                </form>
            </div>
        </div>
    </section>

    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>🇮🇹 Sobre Nós</h3>
                    <p>A FullTorque é a primeira oficina com certificação italiana do Brasil, trazendo a excelência europeia para o cuidado automotivo nacional desde 2009.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Links Rápidos</h3>
                    <ul class="footer-links">
                        <li><a href="#services">🔧 Nossos Serviços</a></li>
                        <li><a href="#about">🇮🇹 Sobre Nós</a></li>
                        <li><a href="#" onclick="document.getElementById('accountBtn').click(); return false;">📱 Área do Cliente</a></li>
                        <li><a href="#" onclick="document.getElementById('accountBtn').click(); setTimeout(() => document.querySelector('[data-tab=register]').click(), 100); return false;">🎁 Cadastre-se</a></li>
                        <li><a href="admin-login.php">🔐 Área Admin</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Serviços</h3>
                    <ul class="footer-links">
                        <li><a href="#">🛢️ Troca de Óleo Premium</a></li>
                        <li><a href="#">⚖️ Alinhamento de Precisão</a></li>
                        <li><a href="#">🔍 Revisão Completa</a></li>
                        <li><a href="#">🛑 Sistema de Freios</a></li>
                        <li><a href="#">❄️ Ar-Condicionado</a></li>
                        <li><a href="#">🏎️ Suspensão Esportiva</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contato</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> Urbanova SJC, 1561 - Centro Automotivo</li>
                        <li><i class="fas fa-phone"></i> (11) 3932-5190</li>
                        <li><i class="fas fa-whatsapp"></i> (12) 98821-1304</li>
                        <li><i class="fas fa-envelope"></i> ciao@fulltorque.com.br</li>
                        <li><i class="fas fa-clock"></i> Seg - Sex: 8h-18h | Sab: 8h-12h</li>
                        <li><i class="fas fa-calendar-check"></i> Agendamento 24h Online</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SISTEMA_NOME; ?> - Todos os direitos reservados</p>
            </div>
        </div>
    </footer>
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

    <script src="testeQ/theme-controller.js"></script>
    <script src="testeQ/mobile-enhancements.js"></script>
    <script>
        // Sistema de Gerenciamento de Cookies
        const COOKIE_NAME = 'fulltorque_cookies_accepted';
        const COOKIE_EXPIRY_DAYS = 30;
        
        function checkCookieConsent() {
            const consent = getCookie(COOKIE_NAME);
            const lastVisit = getCookie('fulltorque_last_visit');
            
            if (!consent || !lastVisit || isExpired(lastVisit)) {
                setTimeout(() => {
                    document.getElementById('cookieBanner').classList.add('show');
                }, 1000);
            } else {
                updateLastVisit();
            }
        }
        
        function acceptCookies() {
            setCookie(COOKIE_NAME, 'true', COOKIE_EXPIRY_DAYS);
            updateLastVisit();
            closeCookieBanner();
            showToast('✓ Preferências salvas com sucesso!');
        }
        
        function rejectCookies() {
            setCookie(COOKIE_NAME, 'false', 1);
            closeCookieBanner();
            showToast('Você pode alterar suas preferências a qualquer momento.');
        }
        
        function closeCookieBanner() {
            document.getElementById('cookieBanner').classList.remove('show');
        }
        
        function updateLastVisit() {
            const now = new Date().getTime();
            setCookie('fulltorque_last_visit', now.toString(), COOKIE_EXPIRY_DAYS);
        }
        
        function isExpired(lastVisitTimestamp) {
            const now = new Date().getTime();
            const lastVisit = parseInt(lastVisitTimestamp);
            const daysPassed = (now - lastVisit) / (1000 * 60 * 60 * 24);
            return daysPassed > COOKIE_EXPIRY_DAYS;
        }
        
        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
        }
        
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for(let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
        
        function showToast(message) {
            const toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#2ecc71;color:white;padding:15px 25px;border-radius:10px;box-shadow:0 5px 20px rgba(0,0,0,0.3);z-index:10001;font-weight:600;opacity:0;transform:translateY(20px);transition:all 0.3s';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => toast.style.opacity = '1', 100);
            setTimeout(() => toast.style.transform = 'translateY(0)', 100);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Inicializar ao carregar
        document.addEventListener('DOMContentLoaded', checkCookieConsent);
    </script>
    <script>
        // Controle de Tema e Menu Mobile
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const navMenu = document.querySelector('.nav-menu');
            const body = document.body;
            
            // Modal
            const accountBtn = document.getElementById('accountBtn');
            const accountModal = document.getElementById('accountModal');
            const modalClose = document.getElementById('modalClose');
            const modalTabs = document.querySelectorAll('.modal-tab');
            const modalForms = document.querySelectorAll('.modal-form');
            
            // Verificar se há erro de login e abrir modal
            <?php if ($mostrarModalLogin): ?>
            setTimeout(function() {
                accountModal.classList.add('active');
                body.style.overflow = 'hidden';
                const loginErroMensagem = document.getElementById('loginErroMensagem');
                const loginErroTexto = document.getElementById('loginErroTexto');
                if (loginErroMensagem && loginErroTexto) {
                    loginErroTexto.textContent = '<?php echo addslashes($_SESSION['alerta']['mensagem'] ?? 'E-mail ou senha incorretos.'); ?>';
                    loginErroMensagem.style.display = 'block';
                }
                // Limpar apenas o campo de senha e focar nele
                const senhaInput = document.getElementById('loginPassword');
                if (senhaInput) {
                    senhaInput.value = '';
                    senhaInput.focus();
                }
            }, 100);
            <?php endif; ?>
            
            // Verificar se deve abrir modal após redefinição de senha
            <?php if ($abrirModalLoginRedefinicao): ?>
            setTimeout(function() {
                accountModal.classList.add('active');
                body.style.overflow = 'hidden';
                const loginEmailInput = document.getElementById('loginEmail');
                const loginPasswordInput = document.getElementById('loginPassword');
                if (loginEmailInput) {
                    loginEmailInput.value = '<?php echo $emailRedefinicao; ?>';
                }
                if (loginPasswordInput) {
                    loginPasswordInput.value = '';
                    loginPasswordInput.focus();
                }
            }, 100);
            <?php endif; ?>
            
            // Verificar localStorage para abrir modal após redefinição
            const emailRedefinicao = localStorage.getItem('email_login_redefinicao');
            const abrirModal = localStorage.getItem('abrir_modal_login');
            if (abrirModal === 'true' && emailRedefinicao) {
                localStorage.removeItem('email_login_redefinicao');
                localStorage.removeItem('abrir_modal_login');
                setTimeout(function() {
                    accountModal.classList.add('active');
                    body.style.overflow = 'hidden';
                    const loginEmailInput = document.getElementById('loginEmail');
                    const loginPasswordInput = document.getElementById('loginPassword');
                    if (loginEmailInput) {
                        loginEmailInput.value = emailRedefinicao;
                    }
                    if (loginPasswordInput) {
                        loginPasswordInput.value = '';
                        loginPasswordInput.focus();
                    }
                }, 500);
            }
            
            // Verificar tema salvo
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                body.className = savedTheme;
            }
            
            // Alternar tema
            if (themeToggle) {
                themeToggle.addEventListener('change', function() {
                    if (this.checked) {
                        body.className = 'theme-alemanha';
                        localStorage.setItem('theme', 'theme-alemanha');
                    } else {
                        body.className = '';
                        localStorage.setItem('theme', '');
                    }
                });
                
                // Aplicar tema salvo
                if (savedTheme === 'theme-alemanha') {
                    themeToggle.checked = true;
                }
            }
            
            // Abrir modal
            if (accountBtn) {
                accountBtn.addEventListener('click', function() {
                    accountModal.classList.add('active');
                    body.style.overflow = 'hidden';
                });
            }
            
            // Fechar modal
            if (modalClose) {
                modalClose.addEventListener('click', function() {
                    accountModal.classList.remove('active');
                    body.style.overflow = '';
                    limparFormulariosModal();
                });
            }
            
            // Fechar modal ao clicar fora
            accountModal.addEventListener('click', function(e) {
                if (e.target === accountModal) {
                    accountModal.classList.remove('active');
                    body.style.overflow = '';
                    limparFormulariosModal();
                }
            });
            
            // Alternar entre tabs
            modalTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    
                    // Remover active de todas as tabs
                    modalTabs.forEach(t => t.classList.remove('active'));
                    modalForms.forEach(f => f.classList.remove('active'));
                    
                    // Adicionar active na tab clicada
                    this.classList.add('active');
                    document.getElementById(tabName + 'Form').classList.add('active');
                    
                    // Limpar mensagem de erro ao trocar de tab
                    const loginErroMensagem = document.getElementById('loginErroMensagem');
                    if (loginErroMensagem) {
                        loginErroMensagem.style.display = 'none';
                    }
                });
            });
            
            // Menu Mobile
            if (mobileMenuBtn && navMenu) {
                // Criar overlay
                const overlay = document.createElement('div');
                overlay.className = 'mobile-overlay';
                document.body.appendChild(overlay);
                
                // Alternar menu
                mobileMenuBtn.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    overlay.classList.toggle('active');
                    body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
                });
                
                // Fechar menu ao clicar no overlay
                overlay.addEventListener('click', function() {
                    navMenu.classList.remove('active');
                    overlay.classList.remove('active');
                    body.style.overflow = '';
                });
                
                // Fechar menu ao clicar em um link
                const navLinks = navMenu.querySelectorAll('a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        navMenu.classList.remove('active');
                        overlay.classList.remove('active');
                        body.style.overflow = '';
                    });
                });
            }
        });
        
        
        // Limpar formulários do modal
        function limparFormulariosModal() {
            const loginEmail = document.getElementById('loginEmail');
            const loginPassword = document.getElementById('loginPassword');
            const loginErroMensagem = document.getElementById('loginErroMensagem');
            
            if (loginEmail) loginEmail.value = '';
            if (loginPassword) loginPassword.value = '';
            if (loginErroMensagem) loginErroMensagem.style.display = 'none';
            
            document.getElementById('registerName').value = '';
            document.getElementById('registerSobrenome').value = '';
            document.getElementById('registerCPF').value = '';
            document.getElementById('registerCelular').value = '';
            document.getElementById('registerEmail').value = '';
            document.getElementById('registerPassword').value = '';
        }
        
        // Toggle senha
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Máscaras de CPF e Telefone
        document.addEventListener('DOMContentLoaded', function() {
            const cpfInput = document.getElementById('registerCPF');
            const celularInput = document.getElementById('registerCelular');
            const recuperacaoCPFInput = document.getElementById('recuperacaoCPF');
            
            if (cpfInput) {
                cpfInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.slice(0, 11);
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    e.target.value = value;
                });
            }
            
            if (celularInput) {
                celularInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.slice(0, 11);
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                    e.target.value = value;
                });
            }
            
            if (recuperacaoCPFInput) {
                recuperacaoCPFInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
                });
            }
        })
        
        // FAQ Interativo
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                question.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');
                    
                    // Fechar todos os outros
                    faqItems.forEach(otherItem => {
                        otherItem.classList.remove('active');
                    });
                    
                    // Alternar o atual
                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            });
        });
        
        // Carrossel de Promoções
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const totalSlides = slides.length;
        let autoSlideInterval;
        
        function createDots() {
            const dotsContainer = document.getElementById('carouselDots');
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('div');
                dot.className = 'carousel-dot';
                if (i === 0) dot.classList.add('active');
                dot.addEventListener('click', () => goToSlide(i));
                dotsContainer.appendChild(dot);
            }
        }
        
        function updateDots() {
            const dots = document.querySelectorAll('.carousel-dot');
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }
        
        function moveCarousel(direction) {
            currentSlide += direction;
            if (currentSlide < 0) currentSlide = totalSlides - 1;
            if (currentSlide >= totalSlides) currentSlide = 0;
            updateCarousel();
            resetAutoSlide();
        }
        
        function goToSlide(index) {
            currentSlide = index;
            updateCarousel();
            resetAutoSlide();
        }
        
        function updateCarousel() {
            const track = document.getElementById('carouselTrack');
            track.style.transform = `translateX(-${currentSlide * 100}%)`;
            updateDots();
        }
        
        function autoSlide() {
            currentSlide++;
            if (currentSlide >= totalSlides) currentSlide = 0;
            updateCarousel();
        }
        
        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(autoSlide, 5000);
        }
        
        // Inicializar carrossel
        if (slides.length > 0) {
            createDots();
            // Desabilitar auto-slide no mobile
            if (window.innerWidth > 768) {
                autoSlideInterval = setInterval(autoSlide, 5000);
            }
            
            // Atualizar dots ao fazer scroll no mobile
            const track = document.getElementById('carouselTrack');
            if (track) {
                track.addEventListener('scroll', function() {
                    if (window.innerWidth <= 768) {
                        const scrollLeft = track.scrollLeft;
                        const slideWidth = track.offsetWidth;
                        const newIndex = Math.round(scrollLeft / (slideWidth * 0.85));
                        if (newIndex !== currentSlide && newIndex >= 0 && newIndex < totalSlides) {
                            currentSlide = newIndex;
                            updateDots();
                        }
                    }
                });
            }
        }
        
        // Desabilitar auto-slide ao redimensionar para mobile
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                clearInterval(autoSlideInterval);
            } else if (window.innerWidth > 768 && !autoSlideInterval) {
                autoSlideInterval = setInterval(autoSlide, 5000);
            }
        })
        
        // CAPTCHA
        let captchaRespostaCorreta = 0;
        
        function gerarNovoCaptcha() {
            const num1 = Math.floor(Math.random() * 10) + 1;
            const num2 = Math.floor(Math.random() * 10) + 1;
            document.getElementById('captchaNum1').textContent = num1;
            document.getElementById('captchaNum2').textContent = num2;
            captchaRespostaCorreta = num1 + num2;
            document.getElementById('captchaResposta').value = '';
        }
        
        // Funções do Modal de Recuperação
        function abrirModalRecuperacao() {
            document.getElementById('accountModal').classList.remove('active');
            document.getElementById('recuperacaoModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            limparFormularioRecuperacao();
            gerarNovoCaptcha();
        }
        
        function fecharModalRecuperacao() {
            document.getElementById('recuperacaoModal').classList.remove('active');
            document.body.style.overflow = '';
            limparFormularioRecuperacao();
        }
        
        function limparFormularioRecuperacao() {
            document.getElementById('recuperacaoEmail').value = '';
            document.getElementById('recuperacaoCPF').value = '';
            document.getElementById('captchaResposta').value = '';
            document.getElementById('recuperacaoMensagem').style.display = 'none';
            document.getElementById('recuperacaoMensagem').innerHTML = '';
            gerarNovoCaptcha();
        }
        
        // Processar formulário de recuperação
        setTimeout(function() {
            const recuperacaoForm = document.getElementById('recuperacaoForm');
            if (recuperacaoForm) {
                recuperacaoForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = document.getElementById('recuperacaoEmail').value;
                    const cpf = document.getElementById('recuperacaoCPF').value;
                    const captcha = parseInt(document.getElementById('captchaResposta').value);
                    const mensagemDiv = document.getElementById('recuperacaoMensagem');
                    const btn = this.querySelector('.btn-submit');
                    
                    // Validar CAPTCHA
                    if (captcha !== captchaRespostaCorreta) {
                        mensagemDiv.style.display = 'block';
                        mensagemDiv.style.background = '#fde8e8';
                        mensagemDiv.style.color = '#e74c3c';
                        mensagemDiv.style.borderLeft = '4px solid #e74c3c';
                        mensagemDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Resposta do desafio incorreta. Tente novamente.';
                        gerarNovoCaptcha();
                        return;
                    }
                    
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
                    
                    fetch('processar_recuperacao.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'email=' + encodeURIComponent(email) + '&cpf=' + encodeURIComponent(cpf)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro HTTP: ' + response.status);
                        }
                        return response.text();
                    })
                    .then(text => {
                        console.log('Resposta do servidor:', text);
                        try {
                            return JSON.parse(text);
                        } catch(e) {
                            // Tentar extrair JSON se houver HTML antes
                            const jsonMatch = text.match(/\{.*\}/);
                            if (jsonMatch) {
                                return JSON.parse(jsonMatch[0]);
                            }
                            throw new Error('Resposta inválida do servidor');
                        }
                    })
                    .then(data => {
                        if (data.sucesso) {
                            mensagemDiv.style.display = 'block';
                            mensagemDiv.style.background = '#e6f7ef';
                            mensagemDiv.style.color = '#2ecc71';
                            mensagemDiv.style.borderLeft = '4px solid #2ecc71';
                            mensagemDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.mensagem;
                            if (data.link) {
                                const isMobile = window.innerWidth <= 768;
                                const linkUrl = isMobile ? data.link.replace('redefinir-senha.php', 'redefinir-senha-mobile.php') : data.link;
                                const targetAttr = isMobile ? '' : 'target="_blank"';
                                mensagemDiv.innerHTML += '<br><br><p style="margin: 15px 0 10px 0; font-weight: 600;">Clique no link abaixo para redefinir sua senha (expira em 1 hora).</p>';
                                mensagemDiv.innerHTML += '<a href="' + linkUrl + '" ' + targetAttr + ' onclick="limparFormularioRecuperacao()" style="display: inline-block; background: #CE2B37; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 10px; transition: all 0.3s;" onmouseover="this.style.background=\'#a01e28\'" onmouseout="this.style.background=\'#CE2B37\'">Clique aqui para redefinir sua senha</a>';
                            }
                            document.getElementById('recuperacaoEmail').value = '';
                            document.getElementById('recuperacaoCPF').value = '';
                            document.getElementById('captchaResposta').value = '';
                        } else {
                            mensagemDiv.style.display = 'block';
                            mensagemDiv.style.background = '#fde8e8';
                            mensagemDiv.style.color = '#e74c3c';
                            mensagemDiv.style.borderLeft = '4px solid #e74c3c';
                            mensagemDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.mensagem;
                        }
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Link de Recuperação';
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        mensagemDiv.style.display = 'block';
                        mensagemDiv.style.background = '#fde8e8';
                        mensagemDiv.style.color = '#e74c3c';
                        mensagemDiv.style.borderLeft = '4px solid #e74c3c';
                        mensagemDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Erro: ' + error.message;
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Link de Recuperação';
                    });
                });
            }
        }, 100);
    </script>
</body>
</html>
