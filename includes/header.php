<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">
    <title>NABARD Project - <?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></title>
    
    <!-- Tailwind CSS (primary + fallback CDNs) -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"
            onerror="if(!window.__tailwindFallbackLoaded){var s=document.createElement('script');s.src='https://unpkg.com/@tailwindcss/browser@4';document.head.appendChild(s);window.__tailwindFallbackLoaded=true;}"></script>
    
    <!-- Google Fonts: Inter and Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- jQuery and DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <style>
        /* Custom Scrollbar Styles */
        :root {
            --sb-track-color: #232E33;
            --sb-thumb-color: #404067;
            --sb-size: 10px; /* Adjusted from 14px to be slightly more subtle but useful */
        }

        /* Applying to entire body and custom scrollbar class */
        ::-webkit-scrollbar {
            width: var(--sb-size);
            height: var(--sb-size);
        }

        ::-webkit-scrollbar-track {
            background: var(--sb-track-color);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--sb-thumb-color);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569; /* slate-600 */
        }

        @supports not selector(::-webkit-scrollbar) {
            * {
                scrollbar-color: var(--sb-thumb-color) var(--sb-track-color);
                scrollbar-width: thin;
            }
        }

        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }
        /* Glassmorphism Utilities */
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        /* DataTables Dark Theme Overrides */
        .dataTables_wrapper {
            color: #94a3b8 !important; /* slate-400 */
        }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background-color: #0f172a !important; /* slate-900 */
            border: 1px solid #334155 !important; /* slate-700 */
            color: white !important;
            padding: 4px 8px !important;
            border-radius: 6px !important;
            outline: none !important;
        }
        table.dataTable {
            border-collapse: collapse !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
            margin-top: 1.5rem !important;
            margin-bottom: 1.5rem !important;
        }
        table.dataTable thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding: 12px 16px !important;
            background: rgba(255, 255, 255, 0.02) !important;
        }
        table.dataTable tbody td {
            padding: 12px 16px !important;
            border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
        }
        .dataTables_info, .dataTables_paginate {
            padding-top: 1rem !important;
            color: #64748b !important; /* slate-500 */
        }
        .dataTables_paginate .paginate_button {
            color: #94a3b8 !important;
            border: 1px solid transparent !important;
        }
        .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important; /* indigo-600 */
            color: white !important;
            border: 1px solid #4f46e5 !important;
            border-radius: 6px !important;
        }
        .dataTables_paginate .paginate_button:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border: 1px solid transparent !important;
        }
        /* Buttons */
        .dt-buttons .dt-button {
            background: rgba(79, 70, 229, 0.1) !important;
            border: 1px solid rgba(79, 70, 229, 0.2) !important;
            color: #818cf8 !important;
            border-radius: 6px !important;
            padding: 4px 12px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            margin-right: 4px !important;
            transition: all 0.2s !important;
        }
        .dt-buttons .dt-button:hover {
            background: rgba(79, 70, 229, 0.2) !important;
            border-color: rgba(79, 70, 229, 0.4) !important;
        }
        
        /* Date Picker Icon Color Fix */
        ::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">
