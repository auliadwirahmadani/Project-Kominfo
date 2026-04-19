@extends('layouts.app')

@section('title', 'Tentang Kami - Geoportal Bengkulu')

@section('content')

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* =========================
       🎨 DESIGN SYSTEM
    ========================= */
    :root {
        --primary: #ef4444;
        --primary-dark: #dc2626;
        --primary-light: #fecaca;
        --primary-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --secondary: #1f2937;
        --accent: #fbbf24;
        --glass: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(239, 68, 68, 0.2);
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        --shadow-lg: 0 8px 32px rgba(0,0,0,0.15);
        --shadow-xl: 0 12px 48px rgba(239, 68, 68, 0.2);
        --radius: 20px;
        --radius-lg: 28px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .work-sans { font-family: 'Work Sans', sans-serif; }
    
    /* Smooth scroll */
    html { scroll-behavior: smooth; }

    /* =========================
       🌟 HERO SECTION
    ========================= */
    .hero {
        position: relative;
        padding: 2rem 1rem 2rem;
        background: linear-gradient(135deg, #fef2f2 0%, #fff 50%, #fef2f2 100%);
        overflow: hidden;
        min-height: auto;
        display: flex;
        align-items: center;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 70%;
        height: 150%;
        background: radial-gradient(ellipse at center, rgba(239, 68, 68, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        animation: pulse 8s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .hero-grid {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: center;
    }

    @media (min-width: 1024px) {
        .hero-grid { grid-template-columns: 1.1fr 0.9fr; }
    }

    /* Content Card */
    .hero-card {
        background: var(--glass);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-lg);
        padding: 1.5rem 2rem;
        box-shadow: var(--shadow-xl);
        position: relative;
        z-index: 2;
        animation: slideInLeft 0.6s ease-out;
    }

    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(-30px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 50px;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
    }

    .hero-badge svg { width: 1rem; height: 1rem; }

    .hero-title {
        font-size: clamp(1.75rem, 4vw, 2.75rem);
        font-weight: 800;
        color: var(--secondary);
        line-height: 1.1;
        margin-bottom: 0.75rem;
        background: linear-gradient(135deg, var(--secondary) 0%, var(--primary-dark) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-desc {
        font-size: clamp(0.95rem, 2vw, 1rem);
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        max-width: 600px;
    }

    /* Feature Items */
    .feature-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .feature-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: white;
        border: 1px solid var(--primary-light);
        border-radius: 14px;
        transition: var(--transition);
        cursor: default;
    }

    .feature-item:hover {
        border-color: var(--primary);
        box-shadow: 0 8px 24px -5px rgba(239, 68, 68, 0.25);
        transform: translateX(4px);
    }

    .feature-icon {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, var(--primary-light), #fff);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.25rem;
    }

    .feature-content h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--secondary);
        margin-bottom: 0.25rem;
    }

    .feature-content p {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.5;
        margin: 0;
    }

    /* CTA Buttons */
    .hero-cta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.875rem 1.75rem;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        border: none;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.5);
    }

    .btn-outline {
        background: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
    }

    .btn-outline:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .btn svg { width: 1.125rem; height: 1.125rem; }

    /* Hero Image */
    .hero-image {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        animation: slideInRight 0.6s ease-out;
    }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .hero-image-wrapper {
        position: relative;
        width: 100%;
        max-width: 400px;
    }

    .hero-image-wrapper::before {
        content: '';
        position: absolute;
        inset: -20px;
        background: linear-gradient(135deg, var(--primary-light), transparent);
        border-radius: var(--radius-lg);
        filter: blur(20px);
        opacity: 0.5;
        z-index: -1;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-10px) rotate(1deg); }
    }

    .hero-img {
        width: 100%;
        height: auto;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        animation: float 4s ease-in-out infinite;
    }

    /* Floating Elements */
    .floating-badge {
        position: absolute;
        padding: 0.75rem 1.25rem;
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: float 5s ease-in-out infinite;
        z-index: 3;
    }

    .floating-badge:nth-child(1) { top: 10%; right: -10%; animation-delay: 0s; }
    .floating-badge:nth-child(2) { bottom: 15%; left: -5%; animation-delay: 1s; }
    .floating-badge:nth-child(3) { top: 50%; right: -15%; animation-delay: 2s; }

    .floating-badge svg { color: var(--primary); width: 1.25rem; height: 1.25rem; }

    @media (max-width: 1024px) {
        .floating-badge { display: none; }
    }

    /* =========================
       🎯 VISION & MISSION
    ========================= */
    .vision-section {
        padding: 5rem 1rem;
        background: white;
        position: relative;
    }

    .vision-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 1200px;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
    }

    .section-header {
        text-align: center;
        max-width: 700px;
        margin: 0 auto 4rem;
    }

    .section-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 50px;
        margin-bottom: 1rem;
    }

    .section-title {
        font-size: clamp(1.75rem, 4vw, 2.5rem);
        font-weight: 800;
        color: var(--secondary);
        margin-bottom: 1rem;
    }

    .section-desc {
        color: #6b7280;
        font-size: 1.125rem;
        line-height: 1.6;
    }

    .vision-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    @media (min-width: 768px) {
        .vision-grid { grid-template-columns: repeat(2, 1fr); }
    }

    .vision-card {
        background: var(--glass);
        border: 1px solid var(--primary-light);
        border-radius: var(--radius);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .vision-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
        opacity: 0;
        transition: var(--transition);
    }

    .vision-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.3);
        border-color: var(--primary);
    }

    .vision-card:hover::before { opacity: 1; }

    .vision-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .vision-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .vision-card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--secondary);
        margin: 0;
    }

    .vision-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.875rem;
    }

    .vision-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        color: #4b5563;
        font-size: 1rem;
        line-height: 1.6;
    }

    .vision-list li::before {
        content: '✓';
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        background: var(--primary-light);
        color: var(--primary-dark);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        margin-top: 2px;
    }

    .vision-quote {
        background: linear-gradient(135deg, var(--primary-light), #fff);
        border-left: 4px solid var(--primary);
        padding: 1.5rem;
        border-radius: 0 12px 12px 0;
        margin-top: 1rem;
    }

    .vision-quote p {
        font-size: 1.125rem;
        font-style: italic;
        color: var(--secondary);
        line-height: 1.7;
        margin: 0;
        position: relative;
    }

    .vision-quote p::before,
    .vision-quote p::after {
        content: '"';
        color: var(--primary);
        font-size: 2rem;
        font-weight: 800;
        opacity: 0.3;
        position: absolute;
    }
    .vision-quote p::before { top: -10px; left: -5px; }
    .vision-quote p::after { bottom: -25px; right: -5px; transform: rotate(180deg); }

    /* =========================
       👥 TEAM SECTION
    ========================= */
    .team-section {
        padding: 5rem 1rem;
        background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .team-card {
        background: white;
        border-radius: var(--radius);
        padding: 2rem;
        text-align: center;
        border: 1px solid var(--primary-light);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .team-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary-gradient);
        transform: scaleX(0);
        transition: var(--transition);
        transform-origin: left;
    }

    .team-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.3);
        border-color: var(--primary);
    }

    .team-card:hover::before { transform: scaleX(1); }

    .team-avatar {
        width: 100px;
        height: 100px;
        margin: 0 auto 1.25rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-light), #fff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        border: 4px solid white;
        box-shadow: var(--shadow-md);
        position: relative;
    }

    .team-avatar::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        background: var(--primary-gradient);
        opacity: 0;
        transition: var(--transition);
        z-index: -1;
    }

    .team-card:hover .team-avatar::after { opacity: 0.2; }

    .team-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--secondary);
        margin-bottom: 0.25rem;
    }

    .team-role {
        color: var(--primary);
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
    }

    .team-desc {
        color: #6b7280;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .team-social {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .team-social a {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f3f4f6;
        color: var(--secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        text-decoration: none;
    }

    .team-social a:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .team-social svg { width: 1rem; height: 1rem; }

    /* =========================
       📊 STATS SECTION
    ========================= */
    .stats-section {
        padding: 4rem 1rem;
        background: white;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        max-width: 1000px;
        margin: 0 auto;
    }

    @media (min-width: 768px) {
        .stats-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .stat-card {
        text-align: center;
        padding: 1.5rem;
    }

    .stat-value {
        font-size: clamp(2rem, 5vw, 2.75rem);
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
        margin-bottom: 0.5rem;
        display: block;
    }

    .stat-label {
        color: #4b5563;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, var(--primary-light), #fff);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.5rem;
    }

    /* =========================
       🎬 ANIMATIONS
    ========================= */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-on-scroll {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .animate-on-scroll.visible {
        opacity: 1;
        transform: translateY(0);
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
        .animate-on-scroll { opacity: 1 !important; transform: none !important; }
    }

    /* =========================
       📱 RESPONSIVE
    ========================= */
    @media (max-width: 768px) {
        .hero { padding: 3rem 1rem 4rem; min-height: auto; }
        .hero-card { padding: 1.75rem; }
        .hero-cta { flex-direction: column; }
        .btn { width: 100%; }
        .vision-card { padding: 1.5rem; }
        .team-card { padding: 1.5rem; }
        .floating-badge { display: none; }
    }

    /* =========================
       ♿ ACCESSIBILITY
    ========================= */
    .btn:focus-visible,
    .team-social a:focus-visible {
        outline: 3px solid var(--accent);
        outline-offset: 2px;
    }

    @media (prefers-contrast: high) {
        :root {
            --glass-border: var(--primary);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.4);
        }
    }

    /* =========================
       🏛️ PRODUSEN SECTION
    ========================= */
    .produsen-section {
        padding: 5rem 1rem;
        background: white;
        position: relative;
    }

    .produsen-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 1200px;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
    }

    .produsen-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .produsen-card {
        background: white;
        border-radius: var(--radius);
        padding: 2rem 1.5rem;
        text-align: center;
        border: 1px solid var(--primary-light);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .produsen-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--primary-gradient);
        transform: scaleX(0);
        transition: var(--transition);
        transform-origin: left;
    }

    .produsen-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px -5px rgba(239, 68, 68, 0.28);
        border-color: var(--primary);
    }

    .produsen-card:hover::before { transform: scaleX(1); }

    .produsen-avatar {
        width: 90px;
        height: 90px;
        margin: 0 auto 1.25rem;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid white;
        box-shadow: 0 4px 16px rgba(239,68,68,0.2);
        position: relative;
        flex-shrink: 0;
    }

    .produsen-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .produsen-avatar-fallback {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        margin: 0 auto 1.25rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: white;
        border: 4px solid white;
        box-shadow: 0 4px 16px rgba(239,68,68,0.2);
    }

    .produsen-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--secondary);
        margin-bottom: 0.25rem;
    }

    .produsen-instansi {
        color: var(--primary);
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }

    .produsen-bio {
        color: #6b7280;
        font-size: 0.875rem;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .produsen-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.75rem;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 50px;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .produsen-empty {
        text-align: center;
        padding: 3rem;
        color: #9ca3af;
    }

    .produsen-empty-icon {
        margin-bottom: 1.25rem;
        opacity: 0.4;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* SVG sizing in icon containers */
    .section-badge svg   { width: 1rem;   height: 1rem;   flex-shrink: 0; }
    .feature-icon svg    { width: 22px;   height: 22px; }
    .vision-icon svg     { width: 28px;   height: 28px;   color: white; }
    .stat-icon svg       { width: 28px;   height: 28px; }
    .produsen-badge svg  { width: 11px;   height: 11px;   flex-shrink: 0; }
    .produsen-empty-icon svg { width: 60px; height: 60px; opacity: 0.35; }
    .team-social svg     { width: 1rem;   height: 1rem; }
</style>

<!-- =========================
   🌟 HERO SECTION
========================= -->
<section class="hero">
    <div class="hero-grid">
        
        <!-- Content -->
        <div class="hero-card">
            <span class="hero-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Profil Resmi
            </span>
            
            <h1 class="hero-title">Tentang Geoportal Bengkulu</h1>
            
            <p class="hero-desc">
                Platform resmi penyedia informasi geospasial Provinsi Bengkulu. 
                Kami berkomitmen mendukung perencanaan pembangunan, transparansi data, 
                dan pengambilan keputusan berbasis peta yang akurat dan terpercaya.
            </p>
            
            <!-- Features -->
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div class="feature-content">
                        <h4>Tim Profesional</h4>
                        <p>Didukung ahli GIS dan teknologi untuk kualitas data terbaik.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <div class="feature-content">
                        <h4>Terintegrasi</h4>
                        <p>Data dari 25+ instansi dalam satu platform yang mudah diakses.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="feature-content">
                        <h4>Akses Publik</h4>
                        <p>Gratis untuk masyarakat, akademisi, dan pemerintah daerah.</p>
                    </div>
                </div>
            </div>
            
            <!-- CTA Buttons -->
            <div class="hero-cta">
                <a href="#contact" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Hubungi Kami
                </a>
                <a href="{{ route('geo') }}" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Jelajahi Peta
                </a>
            </div>
        </div>
        
        <!-- Image with Floating Elements -->
        <div class="hero-image">
            <div class="hero-image-wrapper">
                <img 
                    src="{{ asset('ilustrasi about.png') }}" 
                    alt="Visualisasi Geoportal Bengkulu" 
                    class="hero-img"
                    loading="lazy"
                >
                
                <div class="floating-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $totalLayers ?? 0 }} Layer Data
                </div>
                <div class="floating-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Provinsi Bengkulu
                </div>
                <div class="floating-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Update Real-time
                </div>
            </div>
        </div>
        
    </div>
</section>


<!-- =========================
   🎯 VISION & MISSION
========================= -->
<section class="vision-section">
    <div class="container mx-auto px-4">
        
        <div class="section-header animate-on-scroll">
            <span class="section-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Arah Kami
            </span>
            <h2 class="section-title">Visi & Misi</h2>
            <p class="section-desc">
                Komitmen strategis kami dalam mengembangkan ekosistem data geospasial 
                yang bermanfaat bagi kemajuan Provinsi Bengkulu.
            </p>
        </div>
        
        <div class="vision-grid">
            
            <!-- Misi -->
            <div class="vision-card animate-on-scroll">
                <div class="vision-card-header">
                    <div class="vision-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="vision-card-title">Misi Kami</h3>
                </div>
                <ul class="vision-list">
                    <li>Menyediakan data geospasial yang akurat, terkini, dan terstandarisasi</li>
                    <li>Mendorong transparansi informasi publik melalui akses data terbuka</li>
                    <li>Mempermudah akses data bagi masyarakat, akademisi, dan pemerintah</li>
                    <li>Memperkuat kolaborasi dan interoperabilitas antar instansi</li>
                    <li>Mendukung pengambilan keputusan berbasis bukti spasial</li>
                </ul>
            </div>
            
            <!-- Visi -->
            <div class="vision-card animate-on-scroll">
                <div class="vision-card-header">
                    <div class="vision-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <h3 class="vision-card-title">Visi Kami</h3>
                </div>
                <div class="vision-quote">
                    <p>
                        Menjadi platform geospasial terdepan yang mendukung pembangunan 
                        berkelanjutan, inovasi digital, dan keterbukaan informasi 
                        di Provinsi Bengkulu.
                    </p>
                </div>
                <p style="margin-top: 1.5rem; color: #6b7280; line-height: 1.6;">
                    Kami envision Bengkulu sebagai provinsi yang cerdas secara spasial, 
                    di mana setiap keputusan pembangunan didasarkan pada data yang 
                    akurat, dapat diakses, dan bermanfaat bagi seluruh pemangku kepentingan.
                </p>
            </div>
            
        </div>
        
    </div>
</section>


<!-- =========================
   👥 TEAM SECTION
========================= -->
<section class="team-section">
    <div class="container mx-auto px-4">
        
        <div class="section-header animate-on-scroll">
            <span class="section-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Tim Kami
            </span>
            <h2 class="section-title">Pengelola Geoportal</h2>
            <p class="section-desc">
                Tim multidisiplin yang berdedikasi mengelola dan mengembangkan 
                platform Geoportal Bengkulu dengan standar profesional.
            </p>
        </div>
        
        <div class="team-grid">
            
            <!-- Member 3 -->
            <div class="team-card animate-on-scroll">
                <div class="team-avatar">AD</div>
                <h4 class="team-name">Aulia Dwi Rahmadani</h4>
                <p class="team-role">Fullstack Developer</p>
                <p class="team-desc">
                    Full-stack developer yang fokus pada pengembangan aplikasi web 
                    geospasial yang cepat, aman, dan user-friendly.
                </p>
                <div class="team-social">
                    <a href="#" aria-label="Email">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </a>
                    <a href="#" aria-label="GitHub">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </a>
                </div>
            </div>
            
        </div>
        
    </div>
</section>



<!-- =========================
   🏛️ PRODUSEN DATA SECTION
========================= -->
<section class="produsen-section">
    <div class="container mx-auto px-4">

        <div class="section-header animate-on-scroll">
            <span class="section-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Kontributor
            </span>
            <h2 class="section-title">Produsen Data Geospasial</h2>
            <p class="section-desc">
                Instansi-instansi pemerintah Provinsi Bengkulu yang berkontribusi
                menghasilkan dan menyediakan data geospasial di platform ini.
            </p>
        </div>

        @if(isset($producens) && $producens->count() > 0)
            <div class="produsen-grid">
                @foreach($producens as $produsen)
                    @php
                        $pProfile  = $produsen->profile;
                        $photoPath = $pProfile?->photo;
                        $hasPhoto  = $photoPath && file_exists(public_path('storage/' . $photoPath));
                        $initial   = strtoupper(substr($produsen->name ?? 'P', 0, 1));
                    @endphp
                    <div class="produsen-card animate-on-scroll">

                        {{-- Avatar --}}
                        @if($hasPhoto)
                            <div class="produsen-avatar">
                                <img
                                    src="{{ asset('storage/' . $photoPath) }}"
                                    alt="{{ $produsen->name }}"
                                    loading="lazy"
                                >
                            </div>
                        @else
                            <div class="produsen-avatar-fallback">{{ $initial }}</div>
                        @endif

                        {{-- Badge role --}}
                        <span class="produsen-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Produsen Data
                        </span>

                        {{-- Nama --}}
                        <h3 class="produsen-name">{{ $produsen->name }}</h3>

                        {{-- Instansi --}}
                        @if($pProfile?->instansi)
                            <p class="produsen-instansi">
                                <svg xmlns="http://www.w3.org/2000/svg" style="display:inline;width:14px;height:14px;vertical-align:-2px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                {{ $pProfile->instansi }}
                            </p>
                        @else
                            <p class="produsen-instansi" style="color:#9ca3af;font-style:italic;">Instansi belum diisi</p>
                        @endif

                        {{-- Bio --}}
                        @if($pProfile?->bio)
                            <p class="produsen-bio">{{ $pProfile->bio }}</p>
                        @else
                            <p class="produsen-bio" style="font-style:italic;color:#d1d5db;">Belum ada deskripsi.</p>
                        @endif

                    </div>
                @endforeach
            </div>
        @else
            <div class="produsen-empty animate-on-scroll">
                <div class="produsen-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <p style="font-size:1rem;font-weight:600;">Belum ada produsen data yang terdaftar.</p>
            </div>
        @endif

    </div>
</section>


<!-- =========================
   📊 STATS SECTION
========================= -->
<section class="stats-section">
    <div class="container mx-auto px-4">
        
        <div class="stats-grid">
            
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
                <span class="stat-value" data-target="{{ $totalLayers ?? 0 }}">0</span>
                <span class="stat-label">Layer Data Aktif</span>
            </div>
            
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <span class="stat-value" data-target="{{ $totalKategori ?? 0 }}">0</span>
                <span class="stat-label">Kategori Data</span>
            </div>
            
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="stat-value" data-target="{{ $totalPengguna ?? 0 }}">0</span>
                <span class="stat-label">Pengguna Terdaftar</span>
            </div>
            
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="stat-value" data-target="{{ $totalMetadata ?? 0 }}">0</span>
                <span class="stat-label">Metadata Tersedia</span>
            </div>
            
        </div>
        
    </div>
</section>


<!-- =========================
   🧠 JAVASCRIPT
========================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // =========================
    // 🎬 SCROLL ANIMATIONS
    // =========================
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    
    animateElements.forEach(el => observer.observe(el));
    
    
    // =========================
    // 📊 COUNTER ANIMATION
    // =========================
    function animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const step = () => {
            current += increment;
            if(current < target) {
                element.textContent = Math.floor(current).toLocaleString('id-ID');
                requestAnimationFrame(step);
            } else {
                element.textContent = target.toLocaleString('id-ID');
            }
        };
        step();
    }
    
    // Trigger counter when stats section is visible
    const statsSection = document.querySelector('.stats-section');
    const statValues = document.querySelectorAll('.stat-value');
    let countersAnimated = false;
    
    const statsObserver = new IntersectionObserver((entries) => {
        if(entries[0].isIntersecting && !countersAnimated) {
            countersAnimated = true;
            statValues.forEach(el => {
                const target = parseInt(el.dataset.target);
                animateCounter(el, target);
            });
            statsObserver.unobserve(statsSection);
        }
    }, { threshold: 0.3 });
    
    if(statsSection) statsObserver.observe(statsSection);
    
    
    // =========================
    // 🔗 BUTTON INTERACTIONS
    // =========================
    // Hubungi Kami button
    document.querySelector('.btn-primary')?.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Smooth scroll to contact section or show modal
        const contactSection = document.getElementById('contact');
        if(contactSection) {
            contactSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            // Fallback: show notification
            showNotification('📧 Form kontak akan segera tersedia!', 'info');
        }
    });
    
    // Jelajahi Peta button
    document.querySelector('.btn-outline')?.addEventListener('click', function(e) {
        // Let default link behavior work, or add analytics
        showNotification('🗺️ Membuka peta interaktif...', 'success');
    });
    
    
    // =========================
    // 🔔 NOTIFICATION HELPER
    // =========================
    function showNotification(message, type = 'info') {
        document.getElementById('about-notif')?.remove();
        
        const colors = { success: '#22c55e', error: '#ef4444', info: '#374151' };
        const icons = { success: '✅', error: '⚠️', info: 'ℹ️' };
        
        const notif = document.createElement('div');
        notif.id = 'about-notif';
        notif.style.cssText = `
            position: fixed; bottom: 24px; right: 24px; 
            padding: 12px 20px; border-radius: 12px; 
            background: ${colors[type]}; color: white; 
            font-weight: 500; font-size: 0.95rem; 
            box-shadow: 0 8px 24px rgba(0,0,0,0.2); 
            z-index: 10000; display: flex; align-items: center; gap: 10px;
            animation: slideIn 0.3s ease;
        `;
        notif.innerHTML = `<span style="font-size:1.25rem">${icons[type]}</span><span>${message}</span>`;
        
        document.body.appendChild(notif);
        
        setTimeout(() => {
            notif.style.opacity = '0';
            notif.style.transform = 'translateY(10px)';
            notif.style.transition = 'all 0.3s ease';
            setTimeout(() => notif.remove(), 300);
        }, 3000);
    }
    
    // Add keyframes if not exists
    if(!document.querySelector('#about-anim-styles')) {
        const style = document.createElement('style');
        style.id = 'about-anim-styles';
        style.textContent = `@keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); }}`;
        document.head.appendChild(style);
    }
    
    
    // =========================
    // ♿ KEYBOARD SHORTCUTS
    // =========================
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + C to focus contact
        if((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'c') {
            e.preventDefault();
            document.querySelector('.btn-primary')?.click();
        }
        // Ctrl/Cmd + M to open map
        if((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'm') {
            e.preventDefault();
            document.querySelector('.btn-outline')?.click();
        }
    });
    
});
</script>

@endsection