<!DOCTYPE html>
<html lang="en" class="dark h-full antialiased selection:bg-cyan-500 selection:text-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LinePulse | Smart Production Performance Monitoring</title>
    <link id="app-favicon" rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2245%22 fill=%22%2306b6d4%22/><path d=%22M25 50 L40 65 L75 30%22 stroke=%22white%22 stroke-width=%2210%22 fill=%22none%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22/></svg>">

    <!-- Google Fonts: Plus Jakarta Sans, Inter & JetBrains Mono for Industrial Dashboards -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- ApexCharts CDN for high performance industrial graphs -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans overflow-x-hidden">
    <div id="app" class="min-h-full flex flex-col"></div>
</body>
</html>
