<?php
if (!isset($pageTitle)) $pageTitle = 'Website Perhitungan Investasi Proyek Sumur Migas';
if (!isset($activePage)) $activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --color-primary: #F5A623;
            --color-primary-hover: #D88912;
            --color-primary-light: #FFD27A;
            --color-primary-pale: #FFF2D9;
            --color-accent-yellow: #F7C948;
            --color-accent-peach: #FAD9A1;
            --color-bg: #FAF9F6;
            --color-surface: #FFFFFF;
            --color-sidebar: #FFFDF8;
            --color-border: #E9E3D8;
            --color-divider: #F1ECE4;
            --color-heading: #2F2A24;
            --color-body: #5F584F;
            --color-muted: #8D857A;
            --color-success: #63C174;
            --color-danger: #E46A61;
            --shadow-card: 0 8px 24px rgba(214, 170, 94, 0.12);
            --shadow-card-hover: 0 12px 32px rgba(214, 170, 94, 0.18);
            --shadow-container: 0 20px 50px rgba(196, 160, 92, 0.10);
        }

        * { box-sizing: border-box; }

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            background: var(--color-bg);
            color: var(--color-body);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .app-bg {
            background:
                radial-gradient(circle at top left, rgba(245, 166, 35, 0.13), transparent 26rem),
                radial-gradient(circle at top right, rgba(255, 210, 122, 0.20), transparent 28rem),
                var(--color-bg);
        }

        .app-sidebar {
            flex: 0 0 16.5rem;
            width: 16.5rem;
            background: var(--color-sidebar);
            color: var(--color-heading);
            border-right: 1px solid var(--color-divider);
            box-shadow: var(--shadow-container);
        }

        .app-main {
            flex: 1 1 0;
            min-width: 0;
            width: 100%;
        }

        .app-content {
            width: 100%;
            max-width: none !important;
            margin-inline: 0 !important;
        }

        .app-content.px-4,
        .app-content.px-5,
        .app-content.px-6,
        .app-content.px-7,
        .app-content.px-8 {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }

        .app-topbar {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--color-divider);
        }

        .app-card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-card);
        }

        .app-card-hover {
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .app-card-hover:hover {
            transform: translateY(-2px);
            border-color: var(--color-accent-peach);
            box-shadow: var(--shadow-card-hover);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            border-radius: 1rem;
            padding: .85rem 1rem;
            color: var(--color-body);
            font-weight: 700;
            transition: all .2s ease;
        }

        .nav-link:hover {
            background: var(--color-primary-pale);
            color: var(--color-heading);
        }

        .nav-link-active {
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent-yellow));
            color: #fff;
            box-shadow: 0 12px 26px rgba(245, 166, 35, 0.28);
        }

        .nav-icon,
        .ui-icon {
            width: 1.1rem;
            height: 1.1rem;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        .icon-box {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: var(--color-primary-pale);
            color: var(--color-primary-hover);
        }

        .app-input {
            border: 1px solid var(--color-border);
            background: #fff;
            color: var(--color-heading);
            min-width: 0;
        }

        .app-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 4px rgba(245, 166, 35, 0.14);
        }

        .app-btn-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            color: #fff;
            box-shadow: 0 12px 24px rgba(245, 166, 35, 0.25);
        }

        .app-btn-primary:hover {
            filter: brightness(.98);
            transform: translateY(-1px);
        }

        .project-form,
        .space-y-6.max-w-6xl {
            display: grid;
            gap: 1.5rem;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
        }

        .project-form .bg-white,
        .space-y-6.max-w-6xl .bg-white,
        .project-form .shadow-sm,
        .space-y-6.max-w-6xl .shadow-sm {
            background: var(--color-surface) !important;
            border-color: var(--color-border) !important;
            box-shadow: var(--shadow-card) !important;
            color: var(--color-body) !important;
        }

        .project-form .border-slate-200,
        .space-y-6.max-w-6xl .border-slate-200 {
            border-color: var(--color-border) !important;
        }

        .project-form .text-slate-800,
        .space-y-6.max-w-6xl .text-slate-800 {
            color: var(--color-heading) !important;
        }

        .project-form .text-slate-600,
        .project-form .text-slate-500,
        .space-y-6.max-w-6xl .text-slate-600,
        .space-y-6.max-w-6xl .text-slate-500 {
            color: var(--color-muted) !important;
        }

        .project-form input,
        .project-form select,
        .project-form textarea,
        .space-y-6.max-w-6xl input,
        .space-y-6.max-w-6xl select,
        .space-y-6.max-w-6xl textarea {
            background: #fff !important;
            color: var(--color-heading) !important;
            border-color: var(--color-border) !important;
        }

        .project-form button[type="button"],
        .space-y-6.max-w-6xl button[type="button"] {
            background: rgba(245, 166, 35, 0.12) !important;
            color: var(--color-heading) !important;
            border-color: var(--color-border) !important;
        }

        .project-form button[type="button"]:hover,
        .space-y-6.max-w-6xl button[type="button"]:hover {
            background: rgba(245, 166, 35, 0.18) !important;
        }

        .project-form button[type="submit"],
        .space-y-6.max-w-6xl button[type="submit"] {
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)) !important;
            color: #fff !important;
            border-color: transparent !important;
        }

        .project-form .shadow-sm,
        .space-y-6.max-w-6xl .shadow-sm {
            box-shadow: var(--shadow-card) !important;
        }

        .project-form .bg-white,
        .space-y-6.max-w-6xl .bg-white {
            background: #fff !important;
            color: var(--color-body) !important;
        }

        .project-form input,
        .project-form select,
        .project-form textarea,
        .space-y-6.max-w-6xl input,
        .space-y-6.max-w-6xl select,
        .space-y-6.max-w-6xl textarea {
            background: #fff !important;
            color: var(--color-heading) !important;
            border-color: var(--color-border) !important;
        }

        .project-form .text-slate-800,
        .space-y-6.max-w-6xl .text-slate-800 {
            color: var(--color-heading) !important;
        }

        .project-form .text-slate-600,
        .project-form .text-slate-500,
        .space-y-6.max-w-6xl .text-slate-600,
        .space-y-6.max-w-6xl .text-slate-500 {
            color: var(--color-muted) !important;
        }

        .kpi-value {
            font-size: clamp(1.15rem, 1.45vw, 1.55rem);
            line-height: 1.18;
            overflow-wrap: anywhere;
        }

        .chart-wrap {
            position: relative;
            width: 100%;
            min-width: 0;
        }

        .table-scroll {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-scroll::-webkit-scrollbar { height: 8px; }
        .table-scroll::-webkit-scrollbar-track { background: #FFF8EB; }
        .table-scroll::-webkit-scrollbar-thumb {
            background: #E9B949;
            border-radius: 999px;
        }

        .calculation-table {
            min-width: 1180px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .calculation-table th,
        .calculation-table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        .calculation-table td {
            color: var(--color-body);
        }

        .calculation-table tr.border-t {
            background: #FFFDF8 !important;
            border-color: var(--color-divider) !important;
        }

        .calculation-table tr.border-t td.font-bold {
            color: var(--color-heading) !important;
        }

        @media (max-width: 640px) {
            .calculation-table { min-width: 1080px; }
        }
    </style>
</head>
<body class="antialiased">
<div class="min-h-screen flex app-bg">
    <aside class="hidden lg:flex lg:flex-col app-sidebar lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:overflow-y-auto">
        <div class="px-5 py-6 border-b" style="border-color: var(--color-divider);">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white" style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); box-shadow: 0 12px 24px rgba(245,166,35,.24);">
                    <svg class="ui-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 20V6.5A2.5 2.5 0 0 1 9.5 4h5A2.5 2.5 0 0 1 17 6.5V20"/><path d="M6 20h12"/><path d="M9 8h6"/><path d="M9 12h6"/><path d="M10 4V2h4v2"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-bold leading-tight truncate" style="color: var(--color-heading);">Migas Investasi</div>
                    <div class="text-xs mt-1 truncate" style="color: var(--color-muted);">Perhitungan NCF Sumur Migas</div>
                </div>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="index.php" class="nav-link <?= $activePage === 'dashboard' ? 'nav-link-active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h6V4H4z"/><path d="M14 20h6V4h-6z"/><path d="M4 20h6v-3H4z"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="proyek.php" class="nav-link <?= $activePage === 'proyek' ? 'nav-link-active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19h14"/><path d="M7 19V8l5-4 5 4v11"/><path d="M9 19v-5h6v5"/><path d="M10 9h4"/></svg>
                <span>Proyek Sumur</span>
            </a>
        </nav>
        <div class="p-4 text-xs border-t" style="color: var(--color-muted); border-color: var(--color-divider);">© 2026 Sistem Informasi Migas</div>
    </aside>

    <main class="app-main lg:ml-[16.5rem] min-w-0"">
        <header class="app-topbar px-4 sm:px-6 lg:px-8 py-4 fixed top-0 left-0 right-0 lg:left-[16.5rem] z-40">
            <div class="app-content flex flex-col gap-3">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 min-w-0">
                    <div class="min-w-0">
                        <h1 class="text-xl md:text-2xl font-bold tracking-tight truncate" style="color: var(--color-heading);"><?= e($pageTitle) ?></h1>
                        <p class="text-sm leading-relaxed" style="color: var(--color-muted);">Sistem pengelolaan proyek sumur, produksi, pendapatan, pajak, dan net cash flow.</p>
                    </div>
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full text-white flex items-center justify-center font-bold shrink-0" style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));">U</div>
                    </div>
                </div>
                <nav class="lg:hidden flex gap-2 overflow-x-auto pb-1">
                    <a href="index.php" class="nav-link text-sm shrink-0 <?= $activePage === 'dashboard' ? 'nav-link-active' : '' ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h6V4H4z"/><path d="M14 20h6V4h-6z"/><path d="M4 20h6v-3H4z"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="proyek.php" class="nav-link text-sm shrink-0 <?= $activePage === 'proyek' ? 'nav-link-active' : '' ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19h14"/><path d="M7 19V8l5-4 5 4v11"/><path d="M9 19v-5h6v5"/><path d="M10 9h4"/></svg>
                        <span>Proyek Sumur</span>
                    </a>
                </nav>
            </div>
        </header>
        <section class="app-content px-4 sm:px-5 lg:px-7 xl:px-8 pb-4 sm:pb-5 lg:pb-7 xl:pb-8 pt-44 sm:pt-40 lg:pt-28 min-w-0">
