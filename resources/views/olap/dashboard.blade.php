<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard OLAP 3D - Sistema de Gestión</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Three.js -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://unpkg.com/three-globe@2.28.0/dist/three-globe.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3@7.8.5/dist/d3.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Select2 para selects mejorados -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #5ee7df 0%, #b490ca 100%);
            --accent-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);
            --sidebar-width: 280px;
            --header-height: 70px;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            color: #2c3e50;
        }

        /* Layout Principal */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark-gradient);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            transition: transform var(--transition-speed);
            z-index: 1000;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-brand i {
            margin-right: 10px;
            font-size: 2rem;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 0 10px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: margin-left var(--transition-speed);
        }

        /* Header */
        .main-header {
            background: white;
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 15px 25px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 25px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--primary-gradient);
        }

        .stat-card:nth-child(2)::before {
            background: var(--success-gradient);
        }

        .stat-card:nth-child(3)::before {
            background: var(--warning-gradient);
        }

        .stat-card:nth-child(4)::before {
            background: var(--danger-gradient);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 500;
        }

        .stat-change {
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .stat-change.positive {
            color: #28a745;
        }

        .stat-change.negative {
            color: #dc3545;
        }

        /* 3D Visualization */
        .view-container {
            height: 500px;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .view-switcher {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1000;
        }

        .stats-panel {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 12px;
            position: absolute;
            bottom: 15px;
            left: 15px;
            z-index: 1000;
            max-width: 300px;
            backdrop-filter: blur(10px);
        }

        /* Transactions */
        .transaction-item {
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 12px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .transaction-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .transaction-item.completed {
            border-left-color: #28a745;
        }

        .transaction-item.cancelled {
            border-left-color: #dc3545;
        }

        /* Buttons */
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
        }

        .btn-success {
            background: var(--success-gradient);
        }

        .btn-warning {
            background: var(--warning-gradient);
        }

        .btn-danger {
            background: var(--danger-gradient);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Badges */
        .badge {
            border-radius: 20px;
            padding: 6px 12px;
            font-weight: 500;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f3f4;
        }

        .table tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        /* Forms */
        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Modals */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 15px 20px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease forwards;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }
        }

        /* Toggle button for mobile */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1100;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* View specific styles */
        .view-section {
            display: none;
        }

        .view-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        /* Map container */
        .map-container {
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Sucursal labels */
        .sucursal-label {
            position: absolute;
            color: white;
            background: rgba(0, 0, 0, 0.8);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            pointer-events: none;
            z-index: 100;
            backdrop-filter: blur(4px);
        }

        /* Cube controls */
        .cube-controls {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1000;
            max-width: 280px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Detail sidenav */
        .sidenav {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 2000;
            top: 0;
            right: 0;
            background: white;
            overflow-x: hidden;
            transition: 0.3s;
            padding-top: 60px;
            box-shadow: -5px 0 25px rgba(0, 0, 0, 0.1);
        }

        .sidenav.open {
            width: 400px;
        }

        .sidenav .closebtn {
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 36px;
            color: #aaa;
            text-decoration: none;
        }

        .sidenav .closebtn:hover {
            color: #000;
        }

        /* Loading animation */
        .loader {
            display: inline-block;
            width: 80px;
            height: 80px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }

        .loader:after {
            content: " ";
            display: block;
            width: 64px;
            height: 64px;
            margin: 8px;
            border-radius: 50%;
            border: 6px solid #667eea;
            border-color: #667eea transparent #667eea transparent;
            animation: loader 1.2s linear infinite;
        }

        @keyframes loader {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        /* Estilos para el motor MDX */
        .draggable-item {
            cursor: move;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.2s;
        }

        .draggable-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .drag-over {
            background: rgba(102, 126, 234, 0.1) !important;
            border: 2px dashed #667eea !important;
        }

        /* Estilos para la visualización 3D */
        .cube-legend {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 12px;
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1000;
            max-width: 250px;
            backdrop-filter: blur(10px);
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 10px;
        }

        /* Estilos para la tabla de procesos ETL */
        #tablaProcesosETL tr.running {
            background: rgba(255, 193, 7, 0.1) !important;
        }

        #tablaProcesosETL tr.running:hover {
            background: rgba(255, 193, 7, 0.2) !important;
        }

        /* Animaciones para procesos ETL */
        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .running-pulse {
            animation: pulse 2s infinite;
        }

        /* Responsive para visualización 3D */
        @media (max-width: 768px) {
            .cube-legend {
                max-width: 200px;
                font-size: 0.8rem;
            }

            .legend-item {
                margin-bottom: 5px;
            }

            .legend-color {
                width: 15px;
                height: 15px;
            }
        }

        .toast {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            margin-bottom: 10px;
            animation: toastIn 0.5s ease, toastOut 0.5s ease 2.5s forwards;
        }

        @keyframes toastIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes toastOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Añadir en la sección de estilos */
        .globe-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .globe-controls {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 12px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .globe-info-panel {
            position: absolute;
            bottom: 15px;
            right: 15px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 8px;
            max-width: 300px;
            backdrop-filter: blur(10px);
        }

        .globe-legend {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 12px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .pulse-effect {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.7;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .flight-path {
            stroke-dasharray: 5;
            animation: dash 30s linear infinite;
        }

        @keyframes dash {
            to {
                stroke-dashoffset: 1000;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="#" class="sidebar-brand">
                    <i class="fas fa-cube"></i> OLAP 3D
                </a>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="#" class="nav-link active" data-view="dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-view="sucursales">
                        <i class="fas fa-store"></i> Sucursales
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-view="productos">
                        <i class="fas fa-box"></i> Productos
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-view="inventario">
                        <i class="fas fa-warehouse"></i> Inventario
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-view="olap">
                        <i class="fas fa-cube"></i> Consultas OLAP
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-view="transacciones">
                        <i class="fas fa-exchange-alt"></i> Transacciones
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-view="reportes">
                        <i class="fas fa-chart-bar"></i> Reportes
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-view="configuracion">
                        <i class="fas fa-cog"></i> Configuración
                    </a>
                </div>
                <div class="nav-item mt-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="#" class="nav-link"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </form>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard View -->
            <div class="view-section active" id="dashboard-view">
                <div class="main-header">
                    <h1 class="header-title">Dashboard OLAP 3D</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary" id="refreshDashboard">
                            <i class="fas fa-sync-alt me-2"></i>Actualizar
                        </button>
                        <button class="btn btn-outline-primary" id="fullscreenBtn">
                            <i class="fas fa-expand me-2"></i>Pantalla Completa
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid" id="statsGrid">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                        <div class="stat-icon text-primary">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-value" id="ventasHoy">$0</div>
                        <div class="stat-label">Ventas Hoy</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> <span id="ventasChange">0%</span> desde ayer
                        </div>
                    </div>

                    <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                        <div class="stat-icon text-success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value" id="ventasMes">$0</div>
                        <div class="stat-label">Ventas Mes</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> <span id="ventasMesChange">0%</span> desde el mes pasado
                        </div>
                    </div>

                    <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                        <div class="stat-icon text-warning">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-value" id="gananciaTotal">$0</div>
                        <div class="stat-label">Ganancia Total</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> <span id="gananciaChange">0%</span> desde ayer
                        </div>
                    </div>

                    <div class="stat-card fade-in" style="animation-delay: 0.4s;">
                        <div class="stat-icon text-info">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="stat-value" id="sucursalesActivas">0/0</div>
                        <div class="stat-label">Sucursales Activas</div>
                        <div class="stat-change negative">
                            <i class="fas fa-info-circle"></i> <span id="sucursalesInactivas">0</span> en
                            mantenimiento
                        </div>
                    </div>
                </div>

                <!-- Reemplazar la sección de visualización 3D existente -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Visualización 3D de Sucursales y Transacciones</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary active" id="viewMapBtn">
                                <i class="fas fa-globe-americas me-1"></i> Mapa Mundial
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="viewCubeBtn">
                                <i class="fas fa-cube me-1"></i> Cubo OLAP
                            </button>
                            <button class="btn btn-sm btn-outline-info" id="viewMixedBtn">
                                <i class="fas fa-layer-group me-1"></i> Vista Mixta
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="view-container" id="viewContainer">
                            <div class="globe-controls">
                                <h6>Controles del Mapa</h6>
                                <div class="btn-group-vertical">
                                    <button class="btn btn-light btn-sm" id="zoomInGlobe">
                                        <i class="fas fa-search-plus"></i> Zoom +
                                    </button>
                                    <button class="btn btn-light btn-sm" id="zoomOutGlobe">
                                        <i class="fas fa-search-minus"></i> Zoom -
                                    </button>
                                    <button class="btn btn-light btn-sm" id="resetGlobeView">
                                        <i class="fas fa-expand"></i> Reset
                                    </button>
                                    <button class="btn btn-light btn-sm" id="toggleRotateGlobe">
                                        <i class="fas fa-pause me-1"></i>Pausar Rotación
                                    </button>
                                    <button class="btn btn-light btn-sm" id="toggleFlights">
                                        <i class="fas fa-plane me-1"></i>Mostrar Transacciones
                                    </button>
                                </div>
                            </div>

                            <div class="globe-info-panel">
                                <h6>Estadísticas en Tiempo Real</h6>
                                <p class="mb-1">Sucursales activas: <span class="badge bg-info"
                                        id="activeBranches">0</span></p>
                                <p class="mb-1">Transacciones hoy: <span class="badge bg-success"
                                        id="todayTransactions">0</span></p>
                                <p class="mb-1">Productos en movimiento: <span class="badge bg-warning"
                                        id="movingProducts">0</span></p>
                                <p class="mb-0">Última actualización: <span id="lastUpdate">00:00:00</span></p>
                            </div>

                            <div class="globe-legend">
                                <h6>Leyenda</h6>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #4caf50;"></div>
                                    <span>Sucursal Activa</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #f44336;"></div>
                                    <span>Sucursal Inactiva</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #2196f3;"></div>
                                    <span>Transacción en Curso</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #ff9800;"></div>
                                    <span>Alta Actividad</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Agregar después de la sección de consultas OLAP existente -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Motor de Consultas MDX</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Dimensiones Disponibles</label>
                                            <div id="dimensionesContainer" class="border p-2 rounded"
                                                style="min-height: 200px; max-height: 300px; overflow-y: auto;">
                                                <!-- Las dimensiones se cargarán dinámicamente -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Medidas Disponibles</label>
                                            <div id="medidasContainer" class="border p-2 rounded"
                                                style="min-height: 200px; max-height: 300px; overflow-y: auto;">
                                                <!-- Las medidas se cargarán dinámicamente -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Consulta MDX Generada</label>
                                            <textarea class="form-control" id="queryMDX" rows="8" readonly></textarea>
                                        </div>
                                        <button class="btn btn-primary w-100" id="ejecutarMDX">
                                            <i class="fas fa-play me-1"></i>Ejecutar MDX
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de Procesos ETL -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Monitor de Procesos ETL</h5>
                                <button class="btn btn-sm btn-success" id="nuevoProcesoETL">
                                    <i class="fas fa-plus me-1"></i>Nuevo Proceso
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="tablaProcesosETL">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th>Estado</th>
                                                <th>Última Ejecución</th>
                                                <th>Duración</th>
                                                <th>Registros</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Cargando...</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visualización 3D del Cubo OLAP -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Visualización 3D del Cubo OLAP</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="view-container" id="olap3DContainer" style="height: 600px;">
                                    <div class="view-switcher">
                                        <div class="btn-group-vertical">
                                            <button class="btn btn-light btn-sm" id="rotateXCube">
                                                <i class="fas fa-sync"></i> X
                                            </button>
                                            <button class="btn btn-light btn-sm" id="rotateYCube">
                                                <i class="fas fa-sync"></i> Y
                                            </button>
                                            <button class="btn btn-light btn-sm" id="rotateZCube">
                                                <i class="fas fa-sync"></i> Z
                                            </button>
                                            <button class="btn btn-light btn-sm" id="resetCubeView">
                                                <i class="fas fa-expand"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    <div class="cube-legend">
                                        <h6>Leyenda de Dimensiones</h6>
                                        <div id="dimensionLegend">
                                            <!-- Leyenda generada dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Charts -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Ventas por Sucursal</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Top Productos</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="productsChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Otras vistas (inicialmente ocultas) -->
            <div class="view-section" id="sucursales-view">
                <div class="main-header">
                    <h1 class="header-title">Gestión de Sucursales</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearSucursalModal">
                            <i class="fas fa-plus me-2"></i>Nueva Sucursal
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaSucursales">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Ciudad</th>
                                        <th>Estado Docker</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de Productos -->
            <div class="view-section" id="productos-view">
                <div class="main-header">
                    <h1 class="header-title">Gestión de Productos</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearProductoModal">
                            <i class="fas fa-plus me-2"></i>Nuevo Producto
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaProductos">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Categoría</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de Inventario -->
            <div class="view-section" id="inventario-view">
                <div class="main-header">
                    <h1 class="header-title">Gestión de Inventario</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#transferirInventarioModal">
                            <i class="fas fa-exchange-alt me-2"></i>Transferir
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select" id="filtroSucursal">
                                    <option value="">Todas las sucursales</option>
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="filtroAlerta">
                                    <label class="form-check-label" for="filtroAlerta">Mostrar solo alertas de
                                        stock</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="buscarProducto"
                                    placeholder="Buscar producto...">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaInventario">
                                <thead>
                                    <tr>
                                        <th>Sucursal</th>
                                        <th>Producto</th>
                                        <th>Código</th>
                                        <th>Cantidad</th>
                                        <th>Mínimo</th>
                                        <th>Ubicación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista OLAP -->
            <div class="view-section" id="olap-view">
                <div class="main-header">
                    <h1 class="header-title">Consultas ETL/OLAP Avanzadas</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary" id="nuevaConsultaBtn">
                            <i class="fas fa-plus me-2"></i>Nueva Consulta
                        </button>
                        <button class="btn btn-success" id="ejecutarConsultaBtn">
                            <i class="fas fa-play me-2"></i>Ejecutar
                        </button>
                    </div>
                </div>

                <div class="row">
                    <!-- Panel de controles OLAP -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Constructor de Consultas</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Nombre de Consulta</label>
                                    <input type="text" class="form-control" id="nombreConsulta"
                                        placeholder="Mi consulta OLAP">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Medidas</label>
                                    <select class="form-select" id="medidasSelect" multiple>
                                        <option value="ventas" selected>Ventas Totales</option>
                                        <option value="cantidad">Cantidad Vendida</option>
                                        <option value="ganancia">Ganancia</option>
                                        <option value="costo">Costo</option>
                                        <option value="margen">Margen de Ganancia</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Dimensiones</label>
                                    <select class="form-select" id="dimensionesSelect" multiple>
                                        <option value="tiempo">Tiempo</option>
                                        <option value="sucursal">Sucursal</option>
                                        <option value="producto">Producto</option>
                                        <option value="categoria">Categoría</option>
                                        <option value="region">Región</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Filtros</label>
                                    <div id="filtrosContainer">
                                        <div class="filter-group mb-2">
                                            <select class="form-select form-select-sm mb-1" name="filtroCampo">
                                                <option value="">Seleccionar campo</option>
                                                <option value="fecha">Fecha</option>
                                                <option value="sucursal">Sucursal</option>
                                                <option value="producto">Producto</option>
                                                <option value="categoria">Categoría</option>
                                            </select>
                                            <select class="form-select form-select-sm mb-1" name="filtroOperador">
                                                <option value="=">Igual a</option>
                                                <option value=">">Mayor que</option>
                                                <option value="<">Menor que</option>
                                                <option value="between">Entre</option>
                                                <option value="in">En</option>
                                            </select>
                                            <input type="text" class="form-control form-control-sm"
                                                name="filtroValor" placeholder="Valor">
                                        </div>
                                    </div>

                                    <button class="btn btn-sm btn-outline-primary" id="addFilterBtn">
                                        <i class="fas fa-plus me-1"></i>Agregar Filtro
                                    </button>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ordenamiento</label>
                                    <select class="form-select" id="ordenSelect">
                                        <option value="">Sin orden específico</option>
                                        <option value="ventas_desc">Ventas (Mayor a Menor)</option>
                                        <option value="ventas_asc">Ventas (Menor a Mayor)</option>
                                        <option value="ganancia_desc">Ganancia (Mayor a Menor)</option>
                                        <option value="fecha_desc">Fecha (Más reciente primero)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nivel de Detalle</label>
                                    <select class="form-select" id="nivelDetalleSelect">
                                        <option value="alto">Alto (Detallado)</option>
                                        <option value="medio" selected>Medio</option>
                                        <option value="bajo">Bajo (Resumido)</option>
                                    </select>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="vistaPreviaAuto" checked>
                                    <label class="form-check-label" for="vistaPreviaAuto">Vista previa
                                        automática</label>
                                </div>

                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" id="guardarConsultaBtn">
                                        <i class="fas fa-save me-1"></i>Guardar Consulta
                                    </button>
                                    <button class="btn btn-outline-secondary" id="cargarConsultaBtn">
                                        <i class="fas fa-folder-open me-1"></i>Cargar Consulta
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Panel de consultas guardadas -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Consultas Guardadas</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group" id="consultasGuardadasList">
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de resultados y visualización -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Resultados de Consulta</h5>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary active" data-display-type="table">
                                        <i class="fas fa-table"></i> Tabla
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" data-display-type="chart">
                                        <i class="fas fa-chart-bar"></i> Gráfico
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" data-display-type="pivot">
                                        <i class="fas fa-th"></i> Pivote
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 id="resultadosTitle">Resultados: 0 registros encontrados</h6>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-secondary" id="exportarCSVBtn">
                                            <i class="fas fa-file-csv"></i> CSV
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" id="exportarExcelBtn">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" id="exportarPDFBtn">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="resultadosTable">
                                        <thead>
                                            <tr>
                                                <th>Sucursal</th>
                                                <th>Producto</th>
                                                <th>Periodo</th>
                                                <th class="text-end">Ventas</th>
                                                <th class="text-end">Cantidad</th>
                                                <th class="text-end">Ganancia</th>
                                                <th class="text-end">Margen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                                    <p>Ejecuta una consulta para ver los resultados</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#">Anterior</a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">Siguiente</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>

                        <!-- Panel de visualización de gráficos (oculto inicialmente) -->
                        <div class="card mt-4 d-none" id="chartPanel">
                            <div class="card-header">
                                <h5 class="mb-0">Visualización de Datos</h5>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-secondary active" data-chart-type="bar">
                                        <i class="fas fa-chart-bar"></i> Barras
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" data-chart-type="line">
                                        <i class="fas fa-chart-line"></i> Líneas
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" data-chart-type="pie">
                                        <i class="fas fa-chart-pie"></i> Torta
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="olapChart" height="300"></canvas>
                            </div>
                        </div>

                        <!-- Panel de métricas resumen -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Métricas Resumen</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-primary" id="metricVentas">$0</h3>
                                            <p class="mb-0">Ventas Totales</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-success" id="metricGanancia">$0</h3>
                                            <p class="mb-0">Ganancia Total</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-info" id="metricMargen">0%</h3>
                                            <p class="mb-0">Margen Promedio</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-warning" id="metricUnidades">0</h3>
                                            <p class="mb-0">Unidades Vendidas</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Otras vistas (Transacciones, Reportes, Configuración) -->
            <div class="view-section" id="transacciones-view">
                <div class="main-header">
                    <h1 class="header-title">Gestión de Transacciones</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#crearTransaccionModal">
                            <i class="fas fa-plus me-2"></i>Nueva Transacción
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaTransacciones">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para editar sucursal -->
            <div class="modal fade" id="editarSucursalModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="formEditarSucursal">
                            @csrf
                            <input type="hidden" id="editar_sucursal_id" name="id">
                            <div class="modal-header">
                                <h5 class="modal-title">Editar Sucursal</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="editar_nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="editar_nombre" name="nombre"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="editar_direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="editar_direccion"
                                        name="direccion" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="editar_ciudad" class="form-label">Ciudad</label>
                                        <input type="text" class="form-control" id="editar_ciudad" name="ciudad"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editar_pais" class="form-label">País</label>
                                        <input type="text" class="form-control" id="editar_pais" name="pais"
                                            required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="editar_codigo_postal" class="form-label">Código Postal</label>
                                        <input type="text" class="form-control" id="editar_codigo_postal"
                                            name="codigo_postal">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editar_telefono" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="editar_telefono"
                                            name="telefono">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="editar_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="editar_email" name="email">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="editar_latitud" class="form-label">Latitud</label>
                                        <input type="number" step="any" class="form-control"
                                            id="editar_latitud" name="latitud">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editar_longitud" class="form-label">Longitud</label>
                                        <input type="number" step="any" class="form-control"
                                            id="editar_longitud" name="longitud">
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="editar_activa"
                                        name="activa">
                                    <label class="form-check-label" for="editar_activa">Activa</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="editar_docker_habilitado"
                                        name="docker_habilitado">
                                    <label class="form-check-label" for="editar_docker_habilitado">Habilitar
                                        contenedor Docker</label>
                                </div>
                                <div id="dockerConfigContainer" style="display: none;">
                                    <div class="mb-3">
                                        <label for="editar_docker_image" class="form-label">Imagen de Docker</label>
                                        <input type="text" class="form-control" id="editar_docker_image"
                                            name="docker_image" placeholder="ej: mongo:latest">
                                    </div>
                                    <div class="mb-3">
                                        <label for="editar_docker_ports" class="form-label">Puertos</label>
                                        <input type="text" class="form-control" id="editar_docker_ports"
                                            name="docker_ports" placeholder="ej: 27017:27017">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal para editar producto -->
            <div class="modal fade" id="editarProductoModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form id="formEditarProducto">
                            @csrf
                            <input type="hidden" id="editar_producto_id" name="id">
                            <div class="modal-header">
                                <h5 class="modal-title">Editar Producto</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_codigo" class="form-label">Código</label>
                                            <input type="text" class="form-control" id="editar_codigo"
                                                name="codigo" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_nombre" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="editar_nombre"
                                                name="nombre" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="editar_descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="editar_descripcion" name="descripcion" rows="2"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="editar_precio" class="form-label">Precio</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" class="form-control"
                                                    id="editar_precio" name="precio" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="editar_costo" class="form-label">Costo</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" class="form-control"
                                                    id="editar_costo" name="costo" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="editar_stock" class="form-label">Stock</label>
                                            <input type="number" class="form-control" id="editar_stock"
                                                name="stock" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_categoria" class="form-label">Categoría</label>
                                            <select class="form-select" id="editar_categoria" name="categoria"
                                                required>
                                                <option value="">Seleccionar categoría</option>
                                                <option value="electronica">Electrónica</option>
                                                <option value="ropa">Ropa</option>
                                                <option value="hogar">Hogar</option>
                                                <option value="deportes">Deportes</option>
                                                <option value="alimentos">Alimentos</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_marca" class="form-label">Marca</label>
                                            <input type="text" class="form-control" id="editar_marca"
                                                name="marca">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="editar_proveedor" class="form-label">Proveedor</label>
                                    <input type="text" class="form-control" id="editar_proveedor"
                                        name="proveedor">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_peso" class="form-label">Peso (kg)</label>
                                            <input type="number" step="0.01" class="form-control"
                                                id="editar_peso" name="peso">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_dimensiones" class="form-label">Dimensiones
                                                (LxAxA)</label>
                                            <input type="text" class="form-control" id="editar_dimensiones"
                                                name="dimensiones" placeholder="10x5x2">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="editar_activo"
                                        name="activo">
                                    <label class="form-check-label" for="editar_activo">Producto activo</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal para editar inventario -->
            <div class="modal fade" id="editarInventarioModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="formEditarInventario">
                            @csrf
                            <input type="hidden" id="editar_inventario_id" name="id">
                            <div class="modal-header">
                                <h5 class="modal-title">Editar Registro de Inventario</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="editar_sucursal_id" class="form-label">Sucursal</label>
                                    <select class="form-select" id="editar_sucursal_id" name="sucursal_id" required
                                        disabled>
                                        <option value="">Seleccionar sucursal</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="editar_producto_id" class="form-label">Producto</label>
                                    <select class="form-select" id="editar_producto_id" name="producto_id" required
                                        disabled>
                                        <option value="">Seleccionar producto</option>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_cantidad" class="form-label">Cantidad Actual</label>
                                            <input type="number" class="form-control" id="editar_cantidad"
                                                name="cantidad" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_minimo_stock" class="form-label">Mínimo de
                                                Stock</label>
                                            <input type="number" class="form-control" id="editar_minimo_stock"
                                                name="minimo_stock" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="editar_ubicacion" class="form-label">Ubicación en Almacén</label>
                                    <input type="text" class="form-control" id="editar_ubicacion"
                                        name="ubicacion" placeholder="Ej: Estante A-12">
                                </div>
                                <div class="mb-3">
                                    <label for="editar_lote" class="form-label">Número de Lote</label>
                                    <input type="text" class="form-control" id="editar_lote" name="lote">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_fecha_entrada" class="form-label">Fecha de
                                                Entrada</label>
                                            <input type="date" class="form-control" id="editar_fecha_entrada"
                                                name="fecha_entrada">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editar_fecha_caducidad" class="form-label">Fecha de
                                                Caducidad</label>
                                            <input type="date" class="form-control" id="editar_fecha_caducidad"
                                                name="fecha_caducidad">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="editar_bloqueado"
                                        name="bloqueado">
                                    <label class="form-check-label" for="editar_bloqueado">Stock bloqueado para
                                        ventas</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="view-section" id="reportes-view">
                <div class="main-header">
                    <h1 class="header-title">Reportes y Análisis</h1>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Módulo de reportes en desarrollo.
                </div>
            </div>

            <!-- Reemplazar el contenido actual de la vista de configuración -->
            <div class="view-section" id="configuracion-view">
                <div class="main-header">
                    <h1 class="header-title">Configuración del Sistema</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary" id="guardarConfiguracion">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </div>

                <div class="row">
                    <!-- Panel de conexiones a BD -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Conexiones a Bases de Datos</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Servidor OLAP</label>
                                    <input type="text" class="form-control" id="olapServer" value="localhost">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Base de Datos</label>
                                    <input type="text" class="form-control" id="olapDatabase"
                                        value="DataWarehouse">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usuario</label>
                                    <input type="text" class="form-control" id="olapUser" value="olap_user">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="olapPassword" value="********">
                                </div>
                                <button class="btn btn-outline-primary" id="testConnection">
                                    <i class="fas fa-plug me-1"></i>Probar Conexión
                                </button>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Configuración de Sucursales</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="autoRefreshSucursales"
                                        checked>
                                    <label class="form-check-label" for="autoRefreshSucursales">Actualización
                                        automática de estado</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Intervalo de actualización (minutos)</label>
                                    <input type="number" class="form-control" id="refreshInterval" value="5"
                                        min="1">
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="alertStockBajo" checked>
                                    <label class="form-check-label" for="alertStockBajo">Alertas de stock
                                        bajo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de usuarios y permisos -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Usuarios y Permisos</h5>
                                <button class="btn btn-sm btn-primary" id="nuevoUsuarioBtn">
                                    <i class="fas fa-plus me-1"></i>Nuevo Usuario
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="tablaUsuarios">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Nombre</th>
                                                <th>Rol</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>admin</td>
                                                <td>Administrador del Sistema</td>
                                                <td><span class="badge bg-primary">Administrador</span></td>
                                                <td><span class="badge bg-success">Activo</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>analista</td>
                                                <td>Analista Comercial</td>
                                                <td><span class="badge bg-info">Analista</span></td>
                                                <td><span class="badge bg-success">Activo</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Personalización de la Interfaz</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Tema de Color</label>
                                    <select class="form-select" id="temaColor">
                                        <option value="default" selected>Predeterminado</option>
                                        <option value="dark">Oscuro</option>
                                        <option value="light">Claro</option>
                                        <option value="blue">Azul</option>
                                        <option value="green">Verde</option>
                                    </select>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="animacionesHabilitadas"
                                        checked>
                                    <label class="form-check-label" for="animacionesHabilitadas">Animaciones
                                        habilitadas</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Densidad de la Interfaz</label>
                                    <select class="form-select" id="densidadInterfaz">
                                        <option value="compacta">Compacta</option>
                                        <option value="comfortable" selected>Confortable</option>
                                        <option value="espaciosa">Espaciosa</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de configuraciones avanzadas -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Configuraciones Avanzadas</h5>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs" id="advancedConfigTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="etl-tab" data-bs-toggle="tab"
                                            data-bs-target="#etl" type="button" role="tab">Procesos
                                            ETL</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="backup-tab" data-bs-toggle="tab"
                                            data-bs-target="#backup" type="button" role="tab">Backup</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="logs-tab" data-bs-toggle="tab"
                                            data-bs-target="#logs" type="button" role="tab">Registros del
                                            Sistema</button>
                                    </li>
                                </ul>
                                <div class="tab-content p-3" id="advancedConfigTabsContent">
                                    <div class="tab-pane fade show active" id="etl" role="tabpanel">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="etlAutomatico"
                                                checked>
                                            <label class="form-check-label" for="etlAutomatico">Ejecución automática
                                                de ETL</label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Hora de ejecución</label>
                                                <input type="time" class="form-control" id="etlTime"
                                                    value="02:00">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Frecuencia</label>
                                                <select class="form-select" id="etlFrecuencia">
                                                    <option value="daily">Diariamente</option>
                                                    <option value="weekly">Semanalmente</option>
                                                    <option value="monthly">Mensualmente</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Notificar por correo</label>
                                            <input type="email" class="form-control" id="etlEmail"
                                                placeholder="correo@ejemplo.com">
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="backup" role="tabpanel">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="backupAutomatico">
                                            <label class="form-check-label" for="backupAutomatico">Backup
                                                automático</label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Directorio de backup</label>
                                                <input type="text" class="form-control" id="backupPath"
                                                    value="/backups">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Retención (días)</label>
                                                <input type="number" class="form-control" id="backupRetention"
                                                    value="30" min="1">
                                            </div>
                                        </div>
                                        <button class="btn btn-outline-primary" id="backupNow">
                                            <i class="fas fa-database me-1"></i>Realizar Backup Ahora
                                        </button>
                                    </div>
                                    <div class="tab-pane fade" id="logs" role="tabpanel">
                                        <div class="mb-3">
                                            <label class="form-label">Nivel de registro</label>
                                            <select class="form-select" id="logLevel">
                                                <option value="error">Solo Errores</option>
                                                <option value="warn">Advertencias</option>
                                                <option value="info" selected>Información</option>
                                                <option value="debug">Depuración</option>
                                            </select>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="logToFile"
                                                checked>
                                            <label class="form-check-label" for="logToFile">Guardar en
                                                archivo</label>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tamaño máximo de archivo (MB)</label>
                                            <input type="number" class="form-control" id="logMaxSize"
                                                value="10" min="1">
                                        </div>
                                        <button class="btn btn-outline-primary" id="viewLogs">
                                            <i class="fas fa-file-alt me-1"></i>Ver Registros
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidenav for Details -->
    <div id="detailSidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" id="closeNav">&times;</a>
        <div class="px-3">
            <h4 id="detailTitle">Detalles de Sucursal</h4>
            <div id="detailContent">
                <!-- El contenido se cargará dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal para crear sucursal -->
    <div class="modal fade" id="crearSucursalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formCrearSucursal">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Nueva Sucursal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" class="form-control" id="ciudad" name="ciudad"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="pais" class="form-label">País</label>
                                <input type="text" class="form-control" id="pais" name="pais"
                                    required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="codigo_postal" class="form-label">Código Postal</label>
                                <input type="text" class="form-control" id="codigo_postal"
                                    name="codigo_postal">
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="latitud" class="form-label">Latitud</label>
                            <input type="number" step="any" class="form-control" id="latitud"
                                name="latitud">
                        </div>
                        <div class="mb-3">
                            <label for="longitud" class="form-label">Longitud</label>
                            <input type="number" step="any" class="form-control" id="longitud"
                                name="longitud">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="activa" name="activa"
                                checked>
                            <label class="form-check-label" for="activa">Activa</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para crear producto -->
    <div class="modal fade" id="crearProductoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formCrearProducto">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Nuevo Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="precio" class="form-label">Precio</label>
                                <input type="number" step="0.01" class="form-control" id="precio"
                                    name="precio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="costo" class="form-label">Costo</label>
                                <input type="number" step="0.01" class="form-control" id="costo"
                                    name="costo" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock Inicial</label>
                                <input type="number" class="form-control" id="stock" name="stock"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="categoria" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="categoria" name="categoria"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca">
                        </div>
                        <div class="mb-3">
                            <label for="sucursal_id" class="form-label">Sucursal</label>
                            <select class="form-select" id="sucursal_id" name="sucursal_id" required>
                                <option value="">Seleccionar sucursal</option>
                                <!-- Las opciones se cargarán dinámicamente -->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para transferir inventario -->
    <div class="modal fade" id="transferirInventarioModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTransferirInventario">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Transferir Inventario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="producto_id" class="form-label">Producto</label>
                            <select class="form-select" id="producto_id" name="producto_id" required>
                                <option value="">Seleccionar producto</option>
                                <!-- Las opciones se cargarán dinámicamente -->
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sucursal_origen_id" class="form-label">Sucursal Origen</label>
                                <select class="form-select" id="sucursal_origen_id" name="sucursal_origen_id"
                                    required>
                                    <option value="">Seleccionar origen</option>
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="sucursal_destino_id" class="form-label">Sucursal Destino</label>
                                <select class="form-select" id="sucursal_destino_id" name="sucursal_destino_id"
                                    required>
                                    <option value="">Seleccionar destino</option>
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad"
                                min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo (Opcional)</label>
                            <textarea class="form-control" id="motivo" name="motivo" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Transferir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para crear transacción -->
    <div class="modal fade" id="crearTransaccionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formCrearTransaccion">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Nueva Transacción</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="origen_sucursal_id" class="form-label">Sucursal Origen</label>
                                <select class="form-select" id="origen_sucursal_id" name="origen_sucursal_id"
                                    required>
                                    <option value="">Seleccionar origen</option>
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="destino_sucursal_id" class="form-label">Sucursal Destino</label>
                                <select class="form-select" id="destino_sucursal_id" name="destino_sucursal_id"
                                    required>
                                    <option value="">Seleccionar destino</option>
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Productos</label>
                            <div id="productosTransaccion">
                                <div class="producto-item row mb-2">
                                    <div class="col-md-6">
                                        <select class="form-select" name="productos[0][producto_id]" required>
                                            <option value="">Seleccionar producto</option>
                                            <!-- Las opciones se cargarán dinámicamente -->
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="productos[0][cantidad]"
                                            min="1" placeholder="Cantidad" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm quitar-producto">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                id="agregarProducto">
                                <i class="fas fa-plus me-1"></i>Agregar Producto
                            </button>
                        </div>

                        <div class="mb-3">
                            <label for="prioridad" class="form-label">Prioridad</label>
                            <select class="form-select" id="prioridad" name="prioridad" required>
                                <option value="low">Baja</option>
                                <option value="medium" selected>Media</option>
                                <option value="high">Alta</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notas" class="form-label">Notas (Opcional)</label>
                            <textarea class="form-control" id="notas" name="notas" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Transacción</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para guardar consultas -->
    <div class="modal fade" id="guardarConsultaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Guardar Consulta OLAP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombreConsultaGuardar" class="form-label">Nombre de Consulta</label>
                        <input type="text" class="form-control" id="nombreConsultaGuardar">
                    </div>
                    <div class="mb-3">
                        <label for="descripcionConsulta" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcionConsulta" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="consultaCompartida">
                        <label class="form-check-label" for="consultaCompartida">
                            Compartir con otros usuarios
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmarGuardarBtn">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cargar consultas -->
    <div class="modal fade" id="cargarConsultaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cargar Consulta Guardada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Buscar consultas...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <div class="list-group" id="listaConsultasModal">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary">Cargar Consulta</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Three.js -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/controls/OrbitControls.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Variables globales
        let scene, camera, renderer, controls;
        let sucursalObjects = [];
        let transactionLines = [];
        let olapChartInstance = null;
        let salesChartInstance = null;
        let productsChartInstance = null;
        let currentView = 'dashboard';

        // URLs de la API
        const API_BASE = '';
        const API_URLS = {
            // Dashboard
            dashboard: '/dashboard', // antes: /dashboard/data

            // Sucursales
            sucursales: '/sucursales', // antes: /api/sucursales

            // Productos
            productos: '/productos', // antes: /api/productos
            productosCategories: '/productos/categories', // crear ruta si no existe

            // Inventario
            inventario: '/inventario', // antes: /api/inventario
            transferirInventario: '/inventario/transferir',
            inventarioAlertas: '/inventario/alertas', // crear ruta si no existe

            // Transacciones
            transacciones: '/transacciones', // antes: /api/transacciones
            transaccionesRealtime: '/transacciones/realtime', // crear ruta si no existe

            // Ventas
            ventas: '/ventas', // antes: /api/ventas
            ventasMetrics: '/ventas/metrics', // crear ruta si no existe

            // ETL
            etlProcesar: '/etl/procesar', // crear ruta si no existe
            etlEstado: '/etl/estado', // crear ruta si no existe

            // OLAP
            olapCube: '/olap/cube' // crear ruta si no existe
        };

        // Configuración de AJAX con autenticación
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            xhrFields: {
                withCredentials: true
            },
            crossDomain: true
        });

        // Inicializar la aplicación
        $(document).ready(function() {
            // Verificar autenticación
            checkAuth();

            // Inicializar navegación
            initNavigation();

            // Cargar datos iniciales
            loadInitialData();

            // Configurar event listeners
            setupEventListeners();

            // Inicializar la visualización 3D
            init3DView();

            // Inicializar nuevas funcionalidades
            initOLAP3D();
            initMDXBuilder();
            loadProcesosETL();

            // Event listeners para nuevas funcionalidades
            $('#ejecutarMDX').click(ejecutarConsultaMDX);
            $('#nuevoProcesoETL').click(() => {
                mostrarNotificacion('Funcionalidad en desarrollo', 'info');
            });

            // Controles 3D
            $('#rotateXCube').click(() => rotateCube('x'));
            $('#rotateYCube').click(() => rotateCube('y'));
            $('#rotateZCube').click(() => rotateCube('z'));
            $('#resetCubeView').click(resetCubeView);
        });

        // Verificar autenticación
        function checkAuth() {
            $.ajax({
                url: '/user',
                method: 'GET',
                success: function(response) {
                    console.log('Usuario autenticado:', response);
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        window.location.href = '/login';
                    }
                }
            });
        }

        $(document).ready(function() {
            checkAuth();
        });

        // Inicializar navegación entre vistas
        function initNavigation() {
            $('.nav-link').click(function(e) {
                e.preventDefault();

                // Actualizar navegación activa
                $('.nav-link').removeClass('active');
                $(this).addClass('active');

                // Mostrar la vista correspondiente
                const viewId = $(this).data('view') + '-view';
                $('.view-section').removeClass('active');
                $('#' + viewId).addClass('active');
                currentView = $(this).data('view');

                // Cargar datos específicos de la vista
                loadViewData(currentView);

                // Cerrar sidebar en móviles
                if ($(window).width() < 992) {
                    $('#sidebar').removeClass('show');
                }
            });

            // Toggle sidebar en móviles
            $('#menuToggle').click(function() {
                $('#sidebar').toggleClass('show');
            });
        }

        // Cargar datos iniciales
        function loadInitialData() {
            // Cargar estadísticas del dashboard
            loadDashboardStats();

            // Cargar transacciones en tiempo real
            loadTransactions();

            // Cargar datos para los selectores
            loadSelectOptions();
        }

        // Cargar datos específicos de una vista
        function loadViewData(view) {
            switch (view) {
                case 'sucursales':
                    loadSucursales();
                    break;
                case 'productos':
                    loadProductos();
                    break;
                case 'inventario':
                    loadInventario();
                    break;
                case 'transacciones':
                    loadTransacciones();
                    break;
                case 'olap':
                    loadConsultasGuardadas();
                    break;
            }
        }

        // Cargar estadísticas del dashboard
        function loadDashboardStats() {
            $.ajax({
                url: API_URLS.dashboard,
                method: 'GET',
                success: function(metrics) {
                    updateDashboardMetrics(metrics);
                },
                error: function(xhr) {
                    console.error('Error al cargar estadísticas:', xhr);
                    mostrarNotificacion('Error al cargar datos del dashboard', 'danger');
                }
            });
        }

        // Actualizar estadísticas de sucursales
        function updateSucursalesStats(sucursales) {
            const totalSucursales = sucursales.length;
            const sucursalesActivas = sucursales.filter(s => s.activa).length;

            $('#totalSucursales').text(totalSucursales);
            $('#sucursalesActivas').text(`${sucursalesActivas}/${totalSucursales}`);
            $('#sucursalesInactivas').text(totalSucursales - sucursalesActivas);
        }

        // Actualizar métricas del dashboard
        function updateDashboardMetrics(metrics) {
            // Función auxiliar para formatear números de forma segura
            function formatNumber(value) {
                return Number(value ?? 0).toLocaleString();
            }

            $('#ventasHoy').text(`$${formatNumber(metrics?.ventasHoy)}`);
            $('#ventasMes').text(`$${formatNumber(metrics?.ventasMes)}`);
            $('#gananciaTotal').text(`$${formatNumber(metrics?.gananciaTotal)}`);

            $('#sucursalesActivas').text(`${metrics?.sucursalesActivas ?? 0}/${metrics?.totalSucursales ?? 0}`);
            $('#sucursalesInactivas').text((metrics?.totalSucursales ?? 0) - (metrics?.sucursalesActivas ?? 0));

            // Actualizar cambios porcentuales (simulados para la demo)
            $('#ventasChange').text('12.5%');
            $('#ventasMesChange').text('8.3%');
            $('#gananciaChange').text('10.2%');

            // Actualizar estadísticas del mapa
            $('#totalSucursales').text(metrics?.totalSucursales ?? 0);
            $('#transaccionesActivas').text('14'); // Simulado
            $('#productosMovimiento').text('327'); // Simulado

            // Actualizar gráficos solo si metrics existe
            if (metrics) {
                updateCharts(metrics);
            }
        }

        // Cargar transacciones
        function loadTransactions() {
            $.ajax({
                url: `${API_URLS.transacciones}`,
                method: 'GET',
                success: function(transacciones) {
                    renderTransactions(transacciones.data);
                }
            });
        }

        // Renderizar transacciones en el panel
        function renderTransactions(transacciones) {
            const container = $('#transactionsContainer');
            container.empty();

            if (transacciones.length === 0) {
                container.html('<p class="text-center text-muted">No hay transacciones recientes</p>');
                return;
            }

            transacciones.slice(0, 10).forEach(trans => {
                const transactionEl = $(`
                    <div class="transaction-item ${trans.estado}">
                        <div class="d-flex justify-content-between">
                            <strong>${trans.origen_sucursal_id} → ${trans.destino_sucursal_id}</strong>
                            <small class="text-muted">${new Date(trans.created_at).toLocaleTimeString()}</small>
                        </div>
                        <div>Transacción ${trans.codigo}</div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="badge bg-${getBadgeColor(trans.estado)}">
                                ${trans.estado}
                            </span>
                            <small>Prioridad: ${trans.prioridad}</small>
                        </div>
                    </div>
                `);

                container.append(transactionEl);
            });
        }

        // Obtener color del badge según el estado
        function getBadgeColor(estado) {
            switch (estado) {
                case 'completada':
                    return 'success';
                case 'pendiente':
                    return 'warning';
                case 'en_transito':
                    return 'info';
                case 'cancelada':
                    return 'danger';
                default:
                    return 'secondary';
            }
        }

        // Cargar opciones para los selectores
        function loadSelectOptions() {
            // Cargar sucursales para selectores
            $.ajax({
                url: API_URLS.sucursales,
                method: 'GET',
                success: function(sucursales) {
                    populateSucursalSelects(sucursales.data);
                }
            });

            // Cargar productos para selectores
            $.ajax({
                url: API_URLS.productos,
                method: 'GET',
                success: function(productos) {
                    populateProductoSelects(productos.data);
                }
            });
        }

        // Poblar selectores de sucursales
        function populateSucursalSelects(sucursales) {
            const selects = [
                '#filtroSucursal',
                '#sucursal_id',
                '#sucursal_origen_id',
                '#sucursal_destino_id',
                '#origen_sucursal_id',
                '#destino_sucursal_id'
            ];

            selects.forEach(selector => {
                const $select = $(selector);
                if ($select.length) {
                    $select.empty().append('<option value="">Seleccionar sucursal</option>');
                    sucursales.forEach(sucursal => {
                        $select.append(`<option value="${sucursal.id}">${sucursal.nombre}</option>`);
                    });
                }
            });
        }

        // Poblar selectores de productos
        function populateProductoSelects(productos) {
            const selects = [
                '#producto_id',
                '[name*="producto_id"]'
            ];

            selects.forEach(selector => {
                $(selector).each(function() {
                    const $select = $(this);
                    $select.empty().append('<option value="">Seleccionar producto</option>');
                    productos.forEach(producto => {
                        $select.append(
                            `<option value="${producto.id}">${producto.nombre} (${producto.codigo})</option>`
                        );
                    });
                });
            });
        }

        // Cargar sucursales para la vista de gestión
        function loadSucursales() {
            $.ajax({
                url: API_URLS.sucursales,
                method: 'GET',
                success: function(response) {
                    renderSucursalesTable(response.data);
                }
            });
        }

        // Renderizar tabla de sucursales
        function renderSucursalesTable(sucursales) {
            const $tbody = $('#tablaSucursales tbody');
            $tbody.empty();

            if (sucursales.length === 0) {
                $tbody.html('<tr><td colspan="5" class="text-center py-4">No hay sucursales registradas</td></tr>');
                return;
            }

            sucursales.forEach(sucursal => {
                const row = `
                    <tr>
                        <td>${sucursal.id}</td>
                        <td>${sucursal.nombre}</td>
                        <td>${sucursal.ciudad}</td>
                        <td>
                            <span class="badge bg-${sucursal.activa ? 'success' : 'secondary'}">
                                ${sucursal.docker_container_id ? 'Activo' : 'Inactivo'}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editarSucursal(${sucursal.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarSucursal(${sucursal.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-${sucursal.activa ? 'warning' : 'success'}" 
                                onclick="toggleSucursalEstado(${sucursal.id}, ${!sucursal.activa})">
                                <i class="fas fa-${sucursal.activa ? 'stop' : 'play'}"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(row);
            });
        }

        // Cargar productos para la vista de gestión
        function loadProductos() {
            $.ajax({
                url: API_URLS.productos,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderProductosTable(response.data);
                    } else {
                        mostrarNotificacion('Error: ' + response.message, 'danger');
                        // Cargar datos de ejemplo para desarrollo
                        loadProductosMock();
                    }
                },
                error: function(xhr) {
                    console.error('Error al cargar productos:', xhr);
                    mostrarNotificacion('Error al cargar productos. Usando datos de ejemplo.', 'warning');
                    // Cargar datos de ejemplo para desarrollo
                    loadProductosMock();
                }
            });
        }

        // Renderizar tabla de productos
        function renderProductosTable(productos) {
            const $tbody = $('#tablaProductos tbody');
            $tbody.empty();

            if (productos.length === 0) {
                $tbody.html('<tr><td colspan="6" class="text-center py-4">No hay productos registrados</td></tr>');
                return;
            }

            productos.forEach(producto => {
                const alertClass = producto.stock <= 5 ? 'danger' : producto.stock <= 10 ? 'warning' : 'success';

                const row = `
                    <tr>
                        <td>${producto.codigo}</td>
                        <td>${producto.nombre}</td>
                        <td>$${producto.precio.toFixed(2)}</td>
                        <td>
                            <span class="badge bg-${alertClass}">${producto.stock}</span>
                        </td>
                        <td>${producto.categoria}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editarProducto(${producto.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${producto.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="verMetricasProducto(${producto.id})">
                                <i class="fas fa-chart-line"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(row);
            });
        }

        // Cargar inventario para la vista de gestión
        function loadInventario() {
            $.ajax({
                url: API_URLS.inventario,
                method: 'GET',
                success: function(response) {
                    renderInventarioTable(response.data);
                }
            });
        }

        // Renderizar tabla de inventario
        function renderInventarioTable(inventario) {
            const $tbody = $('#tablaInventario tbody');
            $tbody.empty();

            if (inventario.length === 0) {
                $tbody.html('<tr><td colspan="7" class="text-center py-4">No hay registros de inventario</td></tr>');
                return;
            }

            inventario.forEach(item => {
                const alertClass = item.cantidad <= item.minimo_stock ? 'table-warning' : '';
                const badgeClass = item.cantidad <= item.minimo_stock ? 'warning' : 'success';

                const row = `
                    <tr class="${alertClass}">
                        <td>${item.sucursal?.nombre || 'N/A'}</td>
                        <td>${item.producto?.nombre || 'N/A'}</td>
                        <td>${item.producto?.codigo || 'N/A'}</td>
                        <td><span class="badge bg-${badgeClass}">${item.cantidad}</span></td>
                        <td>${item.minimo_stock}</td>
                        <td>${item.ubicacion || 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editarInventario(${item.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarInventario(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(row);
            });
        }

        // Cargar transacciones para la vista de gestión
        function loadTransacciones() {
            $.ajax({
                url: API_URLS.transacciones,
                method: 'GET',
                success: function(response) {
                    renderTransaccionesTable(response.data);
                }
            });
        }

        // Renderizar tabla de transacciones
        function renderTransaccionesTable(transacciones) {
            const $tbody = $('#tablaTransacciones tbody');
            $tbody.empty();

            if (transacciones.length === 0) {
                $tbody.html('<tr><td colspan="6" class="text-center py-4">No hay transacciones</td></tr>');
                return;
            }

            transacciones.forEach(transaccion => {
                const row = `
                    <tr>
                        <td>${transaccion.codigo}</td>
                        <td>${transaccion.origen_sucursal_id}</td>
                        <td>${transaccion.destino_sucursal_id}</td>
                        <td>
                            <span class="badge bg-${getBadgeColor(transaccion.estado)}">
                                ${transaccion.estado}
                            </span>
                        </td>
                        <td>${new Date(transaccion.created_at).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="verDetallesTransaccion(${transaccion.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="cambiarEstadoTransaccion(${transaccion.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(row);
            });
        }

        // Cargar consultas guardadas para OLAP
        function loadConsultasGuardadas() {
            // Simular carga de consultas guardadas
            setTimeout(() => {
                const consultas = [{
                        id: 1,
                        nombre: 'Top 10 Productos por Ventas',
                        descripcion: 'Ventas por producto, trimestre 2023',
                        fecha: 'Hace 3 días'
                    },
                    {
                        id: 2,
                        nombre: 'Análisis Regional',
                        descripcion: 'Comparativo de ventas por región',
                        fecha: 'Hace 1 semana'
                    },
                    {
                        id: 3,
                        nombre: 'Tendencia Mensual',
                        descripcion: 'Evolución de ventas mensuales 2023',
                        fecha: 'Hace 2 semanas'
                    }
                ];

                const $lista = $('#consultasGuardadasList');
                $lista.empty();

                consultas.forEach(consulta => {
                    $lista.append(`
                        <a href="#" class="list-group-item list-group-item-action" data-consulta-id="${consulta.id}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${consulta.nombre}</h6>
                                <small>${consulta.fecha}</small>
                            </div>
                            <p class="mb-1">${consulta.descripcion}</p>
                        </a>
                    `);
                });
            }, 1000);
        }

        // Inicializar la visualización 3D
        function init3DView() {
            // Configurar escena
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x1a1a1a);

            // Configurar cámara
            const container = document.getElementById('viewContainer');
            camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            camera.position.z = 50;
            camera.position.y = 30;

            // Configurar renderizador
            renderer = new THREE.WebGLRenderer({
                antialias: true
            });
            renderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(renderer.domElement);

            // Configurar controles de órbita
            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;

            // Añadir iluminación
            const ambientLight = new THREE.AmbientLight(0x404040);
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.position.set(1, 1, 1);
            scene.add(directionalLight);

            // Añadir ejes de referencia
            const axesHelper = new THREE.AxesHelper(20);
            scene.add(axesHelper);

            // Crear plano base
            const planeGeometry = new THREE.PlaneGeometry(100, 100);
            const planeMaterial = new THREE.MeshBasicMaterial({
                color: 0x333333,
                side: THREE.DoubleSide,
                wireframe: true
            });
            const plane = new THREE.Mesh(planeGeometry, planeMaterial);
            plane.rotation.x = Math.PI / 2;
            scene.add(plane);

            // Cargar datos de sucursales para visualización
            $.ajax({
                url: API_URLS.sucursales,
                method: 'GET',
                success: function(response) {
                    createSucursalesVisualization(response.data);
                }
            });

            // Iniciar animación
            animate();

            // Ajustar al redimensionar ventana
            window.addEventListener('resize', onWindowResize);
        }

        // Crear visualización de sucursales en 3D
        function createSucursalesVisualization(sucursales) {
            // Limpiar objetos existentes
            sucursalObjects.forEach(obj => {
                if (obj instanceof THREE.Mesh) {
                    scene.remove(obj);
                } else if (obj instanceof Element) {
                    obj.remove();
                }
            });
            sucursalObjects = [];

            // Crear esferas para cada sucursal
            sucursales.forEach(sucursal => {
                // Usar coordenadas reales si están disponibles, o generar aleatorias
                const x = sucursal.longitud ? sucursal.longitud * 2 : (Math.random() - 0.5) * 80;
                const z = sucursal.latitud ? sucursal.latitud * 2 : (Math.random() - 0.5) * 80;

                // Tamaño basado en ventas (simulado)
                const size = 1 + (Math.random() * 3);

                // Color basado en estado
                const color = sucursal.activa ? 0x4caf50 : 0xf44336;

                // Crear esfera
                const geometry = new THREE.SphereGeometry(size, 32, 32);
                const material = new THREE.MeshPhongMaterial({
                    color: color,
                    emissive: sucursal.activa ? 0x072534 : 0x3d0b0b,
                    shininess: 100
                });

                const sphere = new THREE.Mesh(geometry, material);
                sphere.position.set(x, size, z);

                // Almacenar datos de la sucursal en el objeto
                sphere.userData = sucursal;

                // Añadir a la escena y al array
                scene.add(sphere);
                sucursalObjects.push(sphere);

                // Añadir etiqueta (texto)
                addLabel(sucursal.nombre, x, size + 2, z);
            });

            // Crear transacciones entre sucursales
            createTransactionsVisualization();
        }

        // Añadir etiquetas a las sucursales
        function addLabel(text, x, y, z) {
            // Crear elemento HTML para la etiqueta
            const label = document.createElement('div');
            label.className = 'sucursal-label';
            label.textContent = text;
            label.style.position = 'absolute';
            label.style.color = 'white';
            label.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            label.style.padding = '2px 8px';
            label.style.borderRadius = '4px';
            label.style.fontSize = '12px';
            label.style.pointerEvents = 'none';

            document.getElementById('viewContainer').appendChild(label);

            // Actualizar posición en cada frame
            label.userData = {
                x,
                y,
                z
            };
            label.updatePosition = function() {
                const vector = new THREE.Vector3(x, y, z);
                vector.project(camera);

                const container = document.getElementById('viewContainer');
                const left = (vector.x * 0.5 + 0.5) * container.clientWidth;
                const top = (-vector.y * 0.5 + 0.5) * container.clientHeight;

                this.style.left = `${left}px`;
                this.style.top = `${top}px`;
                this.style.display = vector.z < 1 ? 'block' : 'none';
            };

            // Almacenar para actualizar en el bucle de animación
            sucursalObjects.push(label);
        }

        // Crear visualización de transacciones
        function createTransactionsVisualization() {
            // Limpiar líneas existentes
            transactionLines.forEach(line => scene.remove(line));
            transactionLines = [];

            // Obtener transacciones para visualizar
            $.ajax({
                url: API_URLS.transacciones,
                method: 'GET',
                success: function(response) {
                    const transacciones = response.data;

                    // Crear algunas transacciones de ejemplo entre sucursales
                    transacciones.slice(0, 5).forEach(transaccion => {
                        // Buscar sucursales de origen y destino
                        const origen = sucursalObjects.find(obj =>
                            obj.userData && obj.userData.id === transaccion.origen_sucursal_id);
                        const destino = sucursalObjects.find(obj =>
                            obj.userData && obj.userData.id === transaccion.destino_sucursal_id);

                        if (origen && destino) {
                            const originX = origen.position.x;
                            const originZ = origen.position.z;
                            const targetX = destino.position.x;
                            const targetZ = destino.position.z;

                            // Crear línea
                            const points = [];
                            points.push(new THREE.Vector3(originX, 1, originZ));

                            // Punto de control para curva
                            const controlY = 10 + Math.random() * 10;
                            points.push(new THREE.Vector3(
                                (originX + targetX) / 2,
                                controlY,
                                (originZ + targetZ) / 2
                            ));

                            points.push(new THREE.Vector3(targetX, 1, targetZ));

                            const curve = new THREE.CatmullRomCurve3(points);
                            const geometry = new THREE.TubeGeometry(curve, 20, 0.2, 8, false);
                            const material = new THREE.MeshBasicMaterial({
                                color: 0x2196f3,
                                transparent: true,
                                opacity: 0.7
                            });

                            const line = new THREE.Mesh(geometry, material);
                            scene.add(line);
                            transactionLines.push(line);

                            // Añadir animación de partículas
                            addParticlesAlongCurve(curve);
                        }
                    });
                }
            });
        }

        // Añadir partículas animadas a las transacciones
        function addParticlesAlongCurve(curve) {
            const particleCount = 20;
            const particles = new THREE.Group();

            for (let i = 0; i < particleCount; i++) {
                const geometry = new THREE.SphereGeometry(0.2, 16, 16);
                const material = new THREE.MeshBasicMaterial({
                    color: 0xff9800,
                    transparent: true,
                    opacity: 0.8
                });

                const particle = new THREE.Mesh(geometry, material);

                // Posición inicial a lo largo de la curva
                const t = i / particleCount;
                const position = curve.getPoint(t);
                particle.position.copy(position);

                // Almacenar información de animación
                particle.userData = {
                    t,
                    speed: 0.005
                };

                particles.add(particle);
            }

            scene.add(particles);
            transactionLines.push(particles);

            // Animar partículas
            particles.userData = {
                update: () => {
                    particles.children.forEach(particle => {
                        particle.userData.t += particle.userData.speed;
                        if (particle.userData.t > 1) particle.userData.t = 0;

                        const position = curve.getPoint(particle.userData.t);
                        particle.position.copy(position);
                    });
                }
            };
        }

        // Bucle de animación
        function animate() {
            requestAnimationFrame(animate);

            // Actualizar controles
            controls.update();

            // Actualizar etiquetas
            document.querySelectorAll('.sucursal-label').forEach(label => {
                if (label.updatePosition) {
                    label.updatePosition();
                }
            });

            // Actualizar animaciones de transacciones
            transactionLines.forEach(obj => {
                if (obj.userData && obj.userData.update) {
                    obj.userData.update();
                }
            });

            // Renderizar escena
            renderer.render(scene, camera);
        }

        // Ajustar vista al redimensionar ventana
        function onWindowResize() {
            const container = document.getElementById('viewContainer');
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        }

        // ===============================
        // Configurar Event Listeners
        // ===============================
        function setupEventListeners() {
            // --- Botones de vista (mapa/cubo) ---
            $('#viewMapBtn').click(function() {
                $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
                $('#viewCubeBtn').removeClass('active').removeClass('btn-primary').addClass(
                    'btn-outline-secondary');
                $('#mapStatsPanel').show();
                $('#cubeControls').hide();
            });

            $('#viewCubeBtn').click(function() {
                $(this).addClass('active').removeClass('btn-outline-secondary').addClass('btn-primary');
                $('#viewMapBtn').removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');
                $('#mapStatsPanel').hide();
                $('#cubeControls').show();
            });

            // --- Controles de zoom ---
            $('#zoomInBtn').click(() => {
                if (typeof camera !== "undefined") {
                    camera.position.y -= 5;
                    camera.position.z -= 5;
                }
            });

            $('#zoomOutBtn').click(() => {
                if (typeof camera !== "undefined") {
                    camera.position.y += 5;
                    camera.position.z += 5;
                }
            });

            $('#resetViewBtn').click(() => {
                if (typeof camera !== "undefined" && typeof controls !== "undefined") {
                    camera.position.set(0, 30, 50);
                    controls.reset();
                }
            });

            // --- Pantalla completa ---
            $('#fullscreenBtn').click(() => {
                const container = document.getElementById('viewContainer');
                if (container.requestFullscreen) {
                    container.requestFullscreen();
                } else if (container.webkitRequestFullscreen) {
                    container.webkitRequestFullscreen();
                } else if (container.msRequestFullscreen) {
                    container.msRequestFullscreen();
                }
            });

            // --- Cerrar sidenav de detalles ---
            $('#closeNav').click(() => {
                $('#detailSidenav').removeClass('open');
            });

            // --- Click en sucursales (Three.js) ---
            if (typeof renderer !== "undefined" && renderer?.domElement) {
                renderer.domElement.addEventListener('click', (event) => {
                    if (typeof camera === "undefined") return;

                    const mouse = new THREE.Vector2();
                    const rect = renderer.domElement.getBoundingClientRect();

                    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

                    const raycaster = new THREE.Raycaster();
                    raycaster.setFromCamera(mouse, camera);

                    const intersects = raycaster.intersectObjects(
                        sucursalObjects.filter(obj => obj instanceof THREE.Mesh)
                    );

                    if (intersects.length > 0) {
                        const sucursal = intersects[0].object.userData;
                        showSucursalDetails(sucursal);
                    }
                });
            } else {
                console.warn("⚠️ Renderer no está inicializado, no se activan clics en sucursales.");
            }

            // --- Filtros en tabla de inventario ---
            $('#filtroSucursal, #filtroAlerta, #buscarProducto').change(function() {
                filterInventarioTable();
            });

            // --- Formularios ---
            $('#formCrearSucursal').submit(function(e) {
                e.preventDefault();
                crearSucursal();
            });

            $('#formCrearProducto').submit(function(e) {
                e.preventDefault();
                crearProducto();
            });

            $('#formTransferirInventario').submit(function(e) {
                e.preventDefault();
                transferirInventario();
            });

            $('#formCrearTransaccion').submit(function(e) {
                e.preventDefault();
                crearTransaccion();
            });

            // --- Botón agregar producto en transacción ---
            $('#agregarProducto').click(function() {
                agregarProductoTransaccion();
            });

            // --- Botones de OLAP ---
            $('#ejecutarConsultaBtn').click(function() {
                ejecutarConsultaOLAP();
            });

            $('#guardarConsultaBtn').click(function() {
                $('#guardarConsultaModal').modal('show');
            });

            $('#cargarConsultaBtn').click(function() {
                $('#cargarConsultaModal').modal('show');
            });

            // --- Refrescar dashboard ---
            $('#refreshDashboard').click(function() {
                loadDashboardStats();
                loadTransactions();
            });

            // --- Aplicar cubo OLAP ---
            $('#aplicarCubo').click(function() {
                aplicarCuboOLAP();
            });
        }


        // Filtrar tabla de inventario
        function filterInventarioTable() {
            const sucursalId = $('#filtroSucursal').val();
            const soloAlertas = $('#filtroAlerta').is(':checked');
            const busqueda = $('#buscarProducto').val().toLowerCase();

            $('#tablaInventario tbody tr').each(function() {
                const row = $(this);
                const rowSucursal = row.find('td:first').text();
                const rowProducto = row.find('td:nth-child(2)').text().toLowerCase();
                const isAlerta = row.hasClass('table-warning');

                const showRow =
                    (sucursalId === '' || rowSucursal.includes($('#filtroSucursal option:selected')
                        .text())) &&
                    (!soloAlertas || isAlerta) &&
                    (busqueda === '' || rowProducto.includes(busqueda));

                row.toggle(showRow);
            });
        }

        // Crear nueva sucursal
        function crearSucursal() {
            const formData = $('#formCrearSucursal').serialize();

            $.ajax({
                url: API_URLS.sucursales,
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#crearSucursalModal').modal('hide');
                    mostrarNotificacion('Sucursal creada correctamente', 'success');
                    loadSucursales();
                    loadSelectOptions();
                },
                error: function(xhr) {
                    mostrarNotificacion('Error al crear sucursal: ' + xhr.responseJSON.message, 'danger');
                }
            });
        }

        // Crear nuevo producto
        function crearProducto() {
            const formData = $('#formCrearProducto').serialize();

            $.ajax({
                url: API_URLS.productos,
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#crearProductoModal').modal('hide');
                    mostrarNotificacion('Producto creado correctamente', 'success');
                    loadProductos();
                    loadSelectOptions();
                },
                error: function(xhr) {
                    mostrarNotificacion('Error al crear producto: ' + xhr.responseJSON.message, 'danger');
                }
            });
        }

        // Transferir inventario
        function transferirInventario() {
            const formData = $('#formTransferirInventario').serialize();

            $.ajax({
                url: `${API_URLS.inventario}/transferir`,
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#transferirInventarioModal').modal('hide');
                    mostrarNotificacion('Inventario transferido correctamente', 'success');
                    loadInventario();
                },
                error: function(xhr) {
                    mostrarNotificacion('Error al transferir inventario: ' + xhr.responseJSON.message,
                        'danger');
                }
            });
        }

        // Crear transacción
        function crearTransaccion() {
            const formData = $('#formCrearTransaccion').serialize();

            $.ajax({
                url: API_URLS.transacciones,
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#crearTransaccionModal').modal('hide');
                    mostrarNotificacion('Transacción creada correctamente', 'success');
                    loadTransacciones();
                },
                error: function(xhr) {
                    mostrarNotificacion('Error al crear transacción: ' + xhr.responseJSON.message, 'danger');
                }
            });
        }

        // Agregar producto a transacción
        function agregarProductoTransaccion() {
            const index = $('#productosTransaccion .producto-item').length;
            const nuevoProducto = `
                <div class="producto-item row mb-2">
                    <div class="col-md-6">
                        <select class="form-select" name="productos[${index}][producto_id]" required>
                            <option value="">Seleccionar producto</option>
                            <!-- Las opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="productos[${index}][cantidad]" min="1" placeholder="Cantidad" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm quitar-producto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

            $('#productosTransaccion').append(nuevoProducto);

            // Poblar el nuevo selector de productos
            const $nuevoSelect = $('#productosTransaccion .producto-item:last select');
            $nuevoSelect.empty().append('<option value="">Seleccionar producto</option>');

            // Obtener productos y poblar el selector
            $.ajax({
                url: API_URLS.productos,
                method: 'GET',
                success: function(response) {
                    response.data.forEach(producto => {
                        $nuevoSelect.append(
                            `<option value="${producto.id}">${producto.nombre} (${producto.codigo})</option>`
                        );
                    });
                }
            });

            // Event listener para quitar producto
            $('.quitar-producto').off('click').click(function() {
                if ($('#productosTransaccion .producto-item').length > 1) {
                    $(this).closest('.producto-item').remove();
                } else {
                    mostrarNotificacion('Debe haber al menos un producto', 'warning');
                }
            });
        }

        // Ejecutar consulta OLAP
        function ejecutarConsultaOLAP() {
            const dimensiones = $('#dimensionesSelect').val() || [];
            const medidas = $('#medidasSelect').val() || [];
            const filtros = obtenerFiltros();
            const orden = $('#ordenSelect').val();

            // Mostrar carga
            $('#resultadosTable tbody').html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Ejecutando consulta OLAP...</p>
                    </td>
                </tr>
            `);

            $.ajax({
                url: `${API_URLS.olap}/cube`,
                method: 'POST',
                data: {
                    dimensions: dimensiones,
                    measures: medidas,
                    filters: filtros
                },
                success: function(response) {
                    mostrarResultadosOLAP(response);
                    actualizarMetricasOLAP(response);
                    crearGraficoOLAP(response);

                    mostrarNotificacion('Consulta ejecutada correctamente', 'success');
                },
                error: function(xhr) {
                    mostrarNotificacion('Error al ejecutar consulta: ' + xhr.responseJSON.message, 'danger');
                    $('#resultadosTable tbody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                <p>Error al ejecutar la consulta</p>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        // Obtener filtros del formulario
        function obtenerFiltros() {
            const filtros = [];
            $('#filtrosContainer .filter-group').each(function() {
                const campo = $(this).find('[name="filtroCampo"]').val();
                const operador = $(this).find('[name="filtroOperador"]').val();
                const valor = $(this).find('[name="filtroValor"]').val();

                if (campo && operador && valor) {
                    filtros.push({
                        field: campo,
                        operator: operador,
                        value: valor
                    });
                }
            });
            return filtros;
        }

        // Mostrar resultados de consulta OLAP
        function mostrarResultadosOLAP(datos) {
            const tbody = $('#resultadosTable tbody');
            tbody.empty();

            if (datos.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-2x text-warning mb-2"></i>
                            <p>No se encontraron resultados para los criterios de búsqueda</p>
                        </td>
                    </tr>
                `);
                return;
            }

            datos.forEach(item => {
                tbody.append(`
                    <tr>
                        <td>${item.sucursal || 'N/A'}</td>
                        <td>${item.producto || 'N/A'}</td>
                        <td>${item.periodo || 'N/A'}</td>
                        <td class="text-end">$${item.ventas ? item.ventas.toLocaleString() : '0'}</td>
                        <td class="text-end">${item.cantidad || '0'}</td>
                        <td class="text-end">$${item.ganancia ? item.ganancia.toLocaleString() : '0'}</td>
                        <td class="text-end">${item.margen ? item.margen + '%' : '0%'}</td>
                    </tr>
                `);
            });

            $('#resultadosTitle').text(`Resultados: ${datos.length} registros encontrados`);
        }

        // Actualizar métricas OLAP
        function actualizarMetricasOLAP(datos) {
            const ventasTotales = datos.reduce((sum, item) => sum + (item.ventas || 0), 0);
            const gananciaTotal = datos.reduce((sum, item) => sum + (item.ganancia || 0), 0);
            const cantidadTotal = datos.reduce((sum, item) => sum + (item.cantidad || 0), 0);
            const margenPromedio = ventasTotales > 0 ? ((gananciaTotal / ventasTotales) * 100).toFixed(1) : 0;

            $('#metricVentas').text(`$${ventasTotales.toLocaleString()}`);
            $('#metricGanancia').text(`$${gananciaTotal.toLocaleString()}`);
            $('#metricMargen').text(`${margenPromedio}%`);
            $('#metricUnidades').text(cantidadTotal.toLocaleString());
        }

        // Crear gráfico OLAP
        function crearGraficoOLAP(datos) {
            // Agrupar datos para el gráfico (ejemplo: ventas por sucursal)
            const ventasPorSucursal = {};
            datos.forEach(item => {
                const sucursal = item.sucursal || 'Sin especificar';
                if (!ventasPorSucursal[sucursal]) {
                    ventasPorSucursal[sucursal] = 0;
                }
                ventasPorSucursal[sucursal] += item.ventas || 0;
            });

            const ctx = document.getElementById('olapChart').getContext('2d');

            // Destruir gráfico anterior si existe
            if (olapChartInstance) {
                olapChartInstance.destroy();
            }

            // Crear nuevo gráfico
            olapChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(ventasPorSucursal),
                    datasets: [{
                        label: 'Ventas por Sucursal',
                        data: Object.values(ventasPorSucursal),
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(255, 99, 132, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Ventas por Sucursal'
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Ventas ($)'
                            }
                        }
                    }
                }
            });
        }

        // Aplicar cubo OLAP en 3D
        function aplicarCuboOLAP() {
            const dimensionX = $('#dimensionX').val();
            const dimensionY = $('#dimensionY').val();
            const dimensionZ = $('#dimensionZ').val();

            // Aquí implementarías la visualización 3D del cubo OLAP
            // Esta es una implementación simplificada
            mostrarNotificacion(`Visualizando cubo OLAP: X=${dimensionX}, Y=${dimensionY}, Z=${dimensionZ}`, 'info');
        }

        // Mostrar detalles de sucursal
        function showSucursalDetails(sucursal) {
            $('#detailTitle').text(sucursal.nombre);

            // Simular carga de datos detallados
            $('#detailContent').html(`
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            `);

            // Cargar datos detallados de la sucursal
            $.ajax({
                url: `${API_URLS.sucursales}/${sucursal.id}`,
                method: 'GET',
                success: function(detalles) {
                    renderSucursalDetails(detalles);
                }
            });
        }

        // Renderizar detalles de sucursal
        function renderSucursalDetails(sucursal) {
            $('#detailContent').html(`
                <div class="detail-card">
                    <div class="detail-card-header">
                        Información General
                    </div>
                    <div class="detail-card-body">
                        <p><strong>Ciudad:</strong> ${sucursal.ciudad}</p>
                        <p><strong>Dirección:</strong> ${sucursal.direccion}</p>
                        <p><strong>Teléfono:</strong> ${sucursal.telefono || 'N/A'}</p>
                        <p><strong>Email:</strong> ${sucursal.email || 'N/A'}</p>
                        <p><strong>Estado:</strong> <span class="badge ${sucursal.activa ? 'bg-success' : 'bg-danger'}">${sucursal.activa ? 'Activa' : 'Inactiva'}</span></p>
                    </div>
                </div>
                
                <div class="detail-card">
                    <div class="detail-card-header">
                        Métricas
                    </div>
                    <div class="detail-card-body">
                        <p><strong>Total de productos:</strong> ${sucursal.productos_count || 0}</p>
                        <p><strong>Total de ventas:</strong> $${sucursal.metrics?.total_ventas || 0}</p>
                        <p><strong>Ganancia total:</strong> $${sucursal.metrics?.total_ganancia || 0}</p>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <button class="btn btn-primary" onclick="verReporteCompleto(${sucursal.id})">Ver Reporte Completo</button>
                </div>
            `);

            // Abrir el sidenav
            $('#detailSidenav').addClass('open');
        }

        // Mostrar notificación
        function mostrarNotificacion(mensaje, tipo = 'info') {
            // Crear notificación
            const notificacion = $(`
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${mensaje}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `);

            // Agregar al contenedor
            $('.toast-container').append(notificacion);

            // Mostrar notificación
            const toast = new bootstrap.Toast(notificacion);
            toast.show();

            // Eliminar después de ocultar
            notificacion.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }

        // Funciones de utilidad para acciones
        function editarSucursal(id) {
            mostrarNotificacion(`Editando sucursal ${id}`, 'info');
            // Implementar lógica de edición
        }

        function eliminarSucursal(id) {
            if (confirm('¿Está seguro de eliminar esta sucursal?')) {
                $.ajax({
                    url: `${API_URLS.sucursales}/${id}`,
                    method: 'DELETE',
                    success: function() {
                        mostrarNotificacion('Sucursal eliminada correctamente', 'success');
                        loadSucursales();
                    },
                    error: function(xhr) {
                        mostrarNotificacion('Error al eliminar sucursal: ' + xhr.responseJSON.message,
                            'danger');
                    }
                });
            }
        }

        function toggleSucursalEstado(id, activa) {
            $.ajax({
                url: `${API_URLS.sucursales}/${id}`,
                method: 'PUT',
                data: {
                    activa: activa
                },
                success: function() {
                    mostrarNotificacion(`Sucursal ${activa ? 'activada' : 'desactivada'} correctamente`,
                        'success');
                    loadSucursales();
                },
                error: function(xhr) {
                    mostrarNotificacion('Error al cambiar estado: ' + xhr.responseJSON.message, 'danger');
                }
            });
        }

        function editarProducto(id) {
            mostrarNotificacion(`Editando producto ${id}`, 'info');
            // Implementar lógica de edición
        }

        function eliminarProducto(id) {
            if (confirm('¿Está seguro de eliminar este producto?')) {
                $.ajax({
                    url: `${API_URLS.productos}/${id}`,
                    method: 'DELETE',
                    success: function() {
                        mostrarNotificacion('Producto eliminado correctamente', 'success');
                        loadProductos();
                    },
                    error: function(xhr) {
                        mostrarNotificacion('Error al eliminar producto: ' + xhr.responseJSON.message,
                            'danger');
                    }
                });
            }
        }

        function verMetricasProducto(id) {
            mostrarNotificacion(`Viendo métricas del producto ${id}`, 'info');
            // Implementar lógica de métricas
        }

        function editarInventario(id) {
            mostrarNotificacion(`Editando inventario ${id}`, 'info');
            // Implementar lógica de edición
        }

        function eliminarInventario(id) {
            if (confirm('¿Está seguro de eliminar este registro de inventario?')) {
                $.ajax({
                    url: `${API_URLS.inventario}/${id}`,
                    method: 'DELETE',
                    success: function() {
                        mostrarNotificacion('Registro de inventario eliminado correctamente', 'success');
                        loadInventario();
                    },
                    error: function(xhr) {
                        mostrarNotificacion('Error al eliminar inventario: ' + xhr.responseJSON.message,
                            'danger');
                    }
                });
            }
        }

        function verDetallesTransaccion(id) {
            mostrarNotificacion(`Viendo detalles de transacción ${id}`, 'info');
            // Implementar lógica de detalles
        }

        function cambiarEstadoTransaccion(id) {
            mostrarNotificacion(`Cambiando estado de transacción ${id}`, 'info');
            // Implementar lógica de cambio de estado
        }

        function verReporteCompleto(id) {
            mostrarNotificacion(`Generando reporte completo de sucursal ${id}`, 'info');
            // Implementar lógica de reporte
        }

        // Actualizar gráficos del dashboard
        function updateCharts(metrics) {
            // Destruir gráficos existentes si ya estaban creados
            if (salesChartInstance) {
                salesChartInstance.destroy();
            }
            if (productsChartInstance) {
                productsChartInstance.destroy();
            }

            // Garantizar que siempre tengamos arrays
            const sucursalesVentas = Array.isArray(metrics?.sucursalesVentas) ? metrics.sucursalesVentas : [];
            const productosPopulares = Array.isArray(metrics?.productosPopulares) ? metrics.productosPopulares : [];

            // Preparar datos seguros para el gráfico de ventas por sucursal
            const labelsSucursales = sucursalesVentas.map(s => s.sucursal ?? "Sin nombre");
            const dataVentas = sucursalesVentas.map(s => Number(s.total_ventas ?? 0));

            // Gráfico de ventas por sucursal
            const salesCtx = document.getElementById('salesChart')?.getContext('2d');
            if (salesCtx) {
                salesChartInstance = new Chart(salesCtx, {
                    type: 'bar',
                    data: {
                        labels: labelsSucursales,
                        datasets: [{
                            label: 'Ventas',
                            data: dataVentas,
                            backgroundColor: 'rgba(102, 126, 234, 0.7)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Preparar datos seguros para el gráfico de productos
            const labelsProductos = productosPopulares.map(p => p.producto ?? "Producto");
            const dataProductos = productosPopulares.map(p => Number(p.total_vendido ?? 0));

            // Gráfico de productos
            const productsCtx = document.getElementById('productsChart')?.getContext('2d');
            if (productsCtx) {
                productsChartInstance = new Chart(productsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labelsProductos,
                        datasets: [{
                            data: dataProductos,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)'
                            ]
                        }]
                    }
                });
            }
        }

        // Funciones para editar sucursales, productos e inventario
        function editarSucursal(id) {
            $.ajax({
                url: `${API_URLS.sucursales}/${id}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const sucursal = response.data;
                        $('#editar_sucursal_id').val(sucursal.id);
                        $('#editar_nombre').val(sucursal.nombre);
                        $('#editar_direccion').val(sucursal.direccion);
                        $('#editar_ciudad').val(sucursal.ciudad);
                        $('#editar_pais').val(sucursal.pais);
                        $('#editar_codigo_postal').val(sucursal.codigo_postal);
                        $('#editar_telefono').val(sucursal.telefono);
                        $('#editar_email').val(sucursal.email);
                        $('#editar_latitud').val(sucursal.latitud);
                        $('#editar_longitud').val(sucursal.longitud);
                        $('#editar_activa').prop('checked', sucursal.activa);
                        $('#editar_docker_habilitado').prop('checked', sucursal.docker_habilitado);

                        if (sucursal.docker_habilitado) {
                            $('#dockerConfigContainer').show();
                            $('#editar_docker_image').val(sucursal.docker_image);
                            $('#editar_docker_ports').val(sucursal.docker_ports);
                        } else {
                            $('#dockerConfigContainer').hide();
                        }

                        $('#editarSucursalModal').modal('show');
                    } else {
                        mostrarNotificacion('Error al cargar datos de la sucursal', 'danger');
                    }
                },
                error: function(xhr) {
                    mostrarNotificacion('Error al cargar datos de la sucursal', 'danger');
                }
            });
        }

        function editarProducto(id) {
            $.ajax({
                url: `${API_URLS.productos}/${id}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const producto = response.data;
                        $('#editar_producto_id').val(producto.id);
                        $('#editar_codigo').val(producto.codigo);
                        $('#editar_nombre').val(producto.nombre);
                        $('#editar_descripcion').val(producto.descripcion);
                        $('#editar_precio').val(producto.precio);
                        $('#editar_costo').val(producto.costo);
                        $('#editar_stock').val(producto.stock);
                        $('#editar_categoria').val(producto.categoria);
                        $('#editar_marca').val(producto.marca);
                        $('#editar_proveedor').val(producto.proveedor);
                        $('#editar_peso').val(producto.peso);
                        $('#editar_dimensiones').val(producto.dimensiones);
                        $('#editar_activo').prop('checked', producto.activo);

                        $('#editarProductoModal').modal('show');
                    } else {
                        mostrarNotificacion('Error al cargar datos del producto', 'danger');
                    }
                },
                error: function(xhr) {
                    mostrarNotificacion('Error al cargar datos del producto', 'danger');
                }
            });
        }

        function editarInventario(id) {
            $.ajax({
                url: `${API_URLS.inventario}/${id}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const inventario = response.data;
                        $('#editar_inventario_id').val(inventario.id);
                        $('#editar_cantidad').val(inventario.cantidad);
                        $('#editar_minimo_stock').val(inventario.minimo_stock);
                        $('#editar_ubicacion').val(inventario.ubicacion);
                        $('#editar_lote').val(inventario.lote);
                        $('#editar_fecha_entrada').val(inventario.fecha_entrada);
                        $('#editar_fecha_caducidad').val(inventario.fecha_caducidad);
                        $('#editar_bloqueado').prop('checked', inventario.bloqueado);

                        // Cargar selectores de sucursal y producto
                        $('#editar_sucursal_id').empty().append(
                            `<option value="${inventario.sucursal_id}" selected>${inventario.sucursal_nombre}</option>`
                        );
                        $('#editar_producto_id').empty().append(
                            `<option value="${inventario.producto_id}" selected>${inventario.producto_nombre}</option>`
                        );

                        $('#editarInventarioModal').modal('show');
                    } else {
                        mostrarNotificacion('Error al cargar datos del inventario', 'danger');
                    }
                },
                error: function(xhr) {
                    mostrarNotificacion('Error al cargar datos del inventario', 'danger');
                }
            });
        }

        // Configurar event listeners para los formularios de edición
        function setupEditFormListeners() {
            // Toggle configuración Docker
            $('#editar_docker_habilitado').change(function() {
                if ($(this).is(':checked')) {
                    $('#dockerConfigContainer').slideDown();
                } else {
                    $('#dockerConfigContainer').slideUp();
                }
            });

            // Formulario de edición de sucursal
            $('#formEditarSucursal').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const sucursalId = $('#editar_sucursal_id').val();

                $.ajax({
                    url: `${API_URLS.sucursales}/${sucursalId}`,
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#editarSucursalModal').modal('hide');
                            mostrarNotificacion('Sucursal actualizada correctamente', 'success');
                            loadSucursales();
                            loadSelectOptions();
                        } else {
                            mostrarNotificacion('Error al actualizar sucursal: ' + response.message,
                                'danger');
                        }
                    },
                    error: function(xhr) {
                        mostrarNotificacion('Error al actualizar sucursal', 'danger');
                    }
                });
            });

            // Formulario de edición de producto
            $('#formEditarProducto').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const productoId = $('#editar_producto_id').val();

                $.ajax({
                    url: `${API_URLS.productos}/${productoId}`,
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#editarProductoModal').modal('hide');
                            mostrarNotificacion('Producto actualizado correctamente', 'success');
                            loadProductos();
                            loadSelectOptions();
                        } else {
                            mostrarNotificacion('Error al actualizar producto: ' + response.message,
                                'danger');
                        }
                    },
                    error: function(xhr) {
                        mostrarNotificacion('Error al actualizar producto', 'danger');
                    }
                });
            });

            // Formulario de edición de inventario
            $('#formEditarInventario').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const inventarioId = $('#editar_inventario_id').val();

                $.ajax({
                    url: `${API_URLS.inventario}/${inventarioId}`,
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#editarInventarioModal').modal('hide');
                            mostrarNotificacion('Inventario actualizado correctamente', 'success');
                            loadInventario();
                        } else {
                            mostrarNotificacion('Error al actualizar inventario: ' + response.message,
                                'danger');
                        }
                    },
                    error: function(xhr) {
                        mostrarNotificacion('Error al actualizar inventario', 'danger');
                    }
                });
            });
        }
        // Implementación completa del motor MDX
        function initMDXBuilder() {
            // Dimensiones predefinidas
            const dimensiones = [{
                    id: 'tiempo',
                    nombre: 'Tiempo',
                    miembros: ['Año', 'Trimestre', 'Mes', 'Semana', 'Día']
                },
                {
                    id: 'ubicacion',
                    nombre: 'Ubicación',
                    miembros: ['País', 'Región', 'Ciudad', 'Sucursal']
                },
                {
                    id: 'producto',
                    nombre: 'Producto',
                    miembros: ['Categoría', 'Subcategoría', 'Marca', 'Producto']
                },
                {
                    id: 'cliente',
                    nombre: 'Cliente',
                    miembros: ['Grupo', 'Tipo', 'Cliente']
                }
            ];

            // Medidas predefinidas
            const medidas = [{
                    id: 'ventas',
                    nombre: 'Ventas',
                    tipo: 'monetario'
                },
                {
                    id: 'cantidad',
                    nombre: 'Cantidad Vendida',
                    tipo: 'entero'
                },
                {
                    id: 'ganancia',
                    nombre: 'Ganancia',
                    tipo: 'monetario'
                },
                {
                    id: 'costo',
                    nombre: 'Costo',
                    tipo: 'monetario'
                },
                {
                    id: 'margen',
                    nombre: 'Margen (%)',
                    tipo: 'porcentaje'
                }
            ];

            // Cargar dimensiones en el panel
            const dimensionesContainer = document.getElementById('dimensionesContainer');
            dimensiones.forEach(dim => {
                const div = document.createElement('div');
                div.className = 'draggable-item p-2 mb-1 border rounded';
                div.textContent = dim.nombre;
                div.draggable = true;
                div.dataset.tipo = 'dimension';
                div.dataset.id = dim.id;
                dimensionesContainer.appendChild(div);
            });

            // Cargar medidas en el panel
            const medidasContainer = document.getElementById('medidasContainer');
            medidas.forEach(med => {
                const div = document.createElement('div');
                div.className = 'draggable-item p-2 mb-1 border rounded';
                div.textContent = med.nombre;
                div.draggable = true;
                div.dataset.tipo = 'medida';
                div.dataset.id = med.id;
                medidasContainer.appendChild(div);
            });

            // Configurar eventos de drag and drop
            configurarDragAndDrop();
        }

        function configurarDragAndDrop() {
            const zonasDrop = document.querySelectorAll('.drop-zone');

            // Configurar eventos para elementos arrastrables
            document.querySelectorAll('.draggable-item').forEach(item => {
                item.addEventListener('dragstart', e => {
                    e.dataTransfer.setData('text/plain', JSON.stringify({
                        tipo: e.target.dataset.tipo,
                        id: e.target.dataset.id,
                        texto: e.target.textContent
                    }));
                });
            });

            // Configurar zonas de drop
            zonasDrop.forEach(zona => {
                zona.addEventListener('dragover', e => {
                    e.preventDefault();
                    zona.classList.add('drag-over');
                });

                zona.addEventListener('dragleave', () => {
                    zona.classList.remove('drag-over');
                });

                zona.addEventListener('drop', e => {
                    e.preventDefault();
                    zona.classList.remove('drag-over');

                    const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                    agregarElementoConsulta(data, zona);
                });
            });
        }

        function agregarElementoConsulta(data, zona) {
            const elemento = document.createElement('div');
            elemento.className =
                'consulta-item p-2 mb-1 bg-light border rounded d-flex justify-content-between align-items-center';
            elemento.innerHTML = `
        ${data.texto}
        <button class="btn btn-sm btn-danger">
            <i class="fas fa-times"></i>
        </button>
    `;

            // Evento para eliminar elemento
            elemento.querySelector('button').addEventListener('click', () => {
                elemento.remove();
                actualizarQueryMDX();
            });

            zona.appendChild(elemento);
            actualizarQueryMDX();
        }

        function actualizarQueryMDX() {
            // Obtener elementos de las zonas de consulta
            const dimensionesSeleccionadas = Array.from(document.querySelectorAll(
                '#dimensionesSeleccionadas .consulta-item')).map(item =>
                item.textContent.replace('×', '').trim()
            );

            const medidasSeleccionadas = Array.from(document.querySelectorAll('#medidasSeleccionadas .consulta-item')).map(
                item =>
                item.textContent.replace('×', '').trim()
            );

            // Construir consulta MDX
            let queryMDX = '';

            if (medidasSeleccionadas.length > 0) {
                queryMDX += `SELECT\n  { ${medidasSeleccionadas.map(m => `[Measures].[${m}]`).join(', ')} } ON COLUMNS`;
            }

            if (dimensionesSeleccionadas.length > 0) {
                if (queryMDX) queryMDX += ',\n';
                else queryMDX += 'SELECT\n';

                queryMDX += `  { ${dimensionesSeleccionadas.map(d => `[${d}].[${d}].Members`).join(' * ')} } ON ROWS`;
            }

            if (dimensionesSeleccionadas.length > 0 || medidasSeleccionadas.length > 0) {
                queryMDX += `\nFROM [Cubo Ventas]`;
            }

            document.getElementById('queryMDX').value = queryMDX;
        }

        function ejecutarConsultaMDX() {
            const query = document.getElementById('queryMDX').value;

            if (!query.trim()) {
                mostrarNotificacion('Por favor, construye una consulta MDX primero', 'warning');
                return;
            }

            mostrarNotificacion('Ejecutando consulta MDX...', 'info');

            // Simular ejecución (en una implementación real, harías una llamada AJAX)
            setTimeout(() => {
                // Aquí procesarías los resultados reales
                const resultados = generarResultadosDemo();
                mostrarResultadosMDX(resultados);
                mostrarNotificacion('Consulta ejecutada exitosamente', 'success');
            }, 2000);
        }

        function generarResultadosDemo() {
            // Generar datos de demostración
            return {
                columnas: ['Tiempo', 'Ubicación', 'Ventas', 'Cantidad', 'Ganancia'],
                filas: [
                    ['2023-Q1', 'Norte', 125000, 250, 37500],
                    ['2023-Q1', 'Sur', 98000, 180, 29400],
                    ['2023-Q1', 'Este', 156000, 320, 46800],
                    ['2023-Q1', 'Oeste', 112000, 210, 33600],
                    ['2023-Q2', 'Norte', 145000, 280, 43500],
                    ['2023-Q2', 'Sur', 110000, 200, 33000],
                    ['2023-Q2', 'Este', 168000, 340, 50400],
                    ['2023-Q2', 'Oeste', 125000, 240, 37500]
                ],
                totales: [1040000, 2020, 312000]
            };
        }

        function mostrarResultadosMDX(resultados) {
            const container = document.getElementById('resultadosMDX');
            if (!container) return;

            let html = `
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        ${resultados.columnas.map(col => `<th>${col}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
    `;

            resultados.filas.forEach(fila => {
                html += `<tr>${fila.map((celda, i) => 
            `<td>${i >= 2 ? '$' + celda.toLocaleString() : celda}</td>`
        ).join('')}</tr>`;
            });

            html += `
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <td colspan="2"><strong>Totales</strong></td>
                        <td><strong>$${resultados.totales[0].toLocaleString()}</strong></td>
                        <td><strong>${resultados.totales[1].toLocaleString()}</strong></td>
                        <td><strong>$${resultados.totales[2].toLocaleString()}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;

            container.innerHTML = html;
        }

        // Mejoras en la visualización 3D
        function mejorarVisualizacion3D() {
            // Texturas y materiales mejorados
            const textureLoader = new THREE.TextureLoader();

            // Mejorar materiales de las sucursales
            sucursalObjects.forEach(sucursal => {
                if (sucursal.isMesh) {
                    // Material más realista con reflectividad
                    sucursal.material = new THREE.MeshPhongMaterial({
                        color: sucursal.userData.activa ? 0x4caf50 : 0xf44336,
                        shininess: 100,
                        specular: 0x222222,
                        emissive: sucursal.userData.activa ? 0x072534 : 0x3d0b0b,
                        emissiveIntensity: 0.2
                    });
                }
            });

            // Añadir efectos de post-procesamiento (simulado)
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1;
            renderer.outputEncoding = THREE.sRGBEncoding;
        }

        // Implementación de procesos ETL
        function loadProcesosETL() {
            // Simular carga de procesos ETL
            setTimeout(() => {
                const procesos = [{
                        id: 1,
                        nombre: 'Carga Diaria de Ventas',
                        tipo: 'Incremental',
                        estado: 'completado',
                        ultimaEjecucion: new Date(),
                        duracion: '00:45:12',
                        registros: 1245,
                        acciones: '<button class="btn btn-sm btn-info"><i class="fas fa-play"></i></button>'
                    },
                    {
                        id: 2,
                        nombre: 'Actualización de Productos',
                        tipo: 'Completo',
                        estado: 'ejecutando',
                        ultimaEjecucion: new Date(),
                        duracion: '00:12:34',
                        registros: 567,
                        acciones: '<button class="btn btn-sm btn-warning"><i class="fas fa-pause"></i></button>'
                    },
                    {
                        id: 3,
                        nombre: 'Sincronización de Sucursales',
                        tipo: 'Incremental',
                        estado: 'error',
                        ultimaEjecucion: new Date(Date.now() - 86400000),
                        duracion: '00:03:45',
                        registros: 0,
                        acciones: '<button class="btn btn-sm btn-info"><i class="fas fa-redo"></i></button>'
                    }
                ];

                const tbody = document.querySelector('#tablaProcesosETL tbody');
                tbody.innerHTML = '';

                procesos.forEach(proceso => {
                    const tr = document.createElement('tr');
                    if (proceso.estado === 'ejecutando') tr.classList.add('running', 'running-pulse');
                    if (proceso.estado === 'error') tr.classList.add('table-danger');

                    tr.innerHTML = `
                <td>${proceso.nombre}</td>
                <td>${proceso.tipo}</td>
                <td><span class="badge bg-${getBadgeColorETL(proceso.estado)}">${proceso.estado}</span></td>
                <td>${formatFecha(proceso.ultimaEjecucion)}</td>
                <td>${proceso.duracion}</td>
                <td>${proceso.registros}</td>
                <td>${proceso.acciones}</td>
            `;

                    tbody.appendChild(tr);
                });
            }, 1500);
        }

        function getBadgeColorETL(estado) {
            switch (estado) {
                case 'completado':
                    return 'success';
                case 'ejecutando':
                    return 'warning';
                case 'error':
                    return 'danger';
                case 'pendiente':
                    return 'secondary';
                default:
                    return 'secondary';
            }
        }

        function formatFecha(fecha) {
            return new Date(fecha).toLocaleDateString() + ' ' +
                new Date(fecha).toLocaleTimeString();
        }

        // Mejoras en el manejo de errores
        function setupErrorHandling() {
            // Interceptar errores de AJAX
            $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
                let mensaje = 'Error en la solicitud';

                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    mensaje = jqXHR.responseJSON.message;
                } else if (thrownError) {
                    mensaje = thrownError;
                }

                mostrarNotificacion(mensaje, 'danger');
            });

            // Manejar errores globales de JavaScript
            window.addEventListener('error', function(e) {
                console.error('Error global:', e.error);
                mostrarNotificacion('Error en la aplicación. Por favor recarga la página.', 'danger');
            });

            // Manejar promesas no capturadas
            window.addEventListener('unhandledrejection', function(e) {
                console.error('Promesa rechazada no manejada:', e.reason);
                mostrarNotificacion('Error en la aplicación. Por favor recarga la página.', 'danger');
                e.preventDefault();
            });
        }
    </script>

    <script>
        // Configuración de event listeners para la vista de configuración
        function setupConfigViewListeners() {
            // Probar conexión a la base de datos
            $('#testConnection').click(function() {
                const server = $('#olapServer').val();
                const database = $('#olapDatabase').val();
                const user = $('#olapUser').val();

                $.ajax({
                    url: `${API_BASE}/config/test-connection`,
                    method: 'POST',
                    data: {
                        server: server,
                        database: database,
                        user: user
                    },
                    beforeSend: function() {
                        $('#testConnection').html(
                            '<i class="fas fa-spinner fa-spin me-1"></i>Probando...');
                    },
                    success: function(response) {
                        if (response.success) {
                            mostrarNotificacion('Conexión exitosa a la base de datos', 'success');
                        } else {
                            mostrarNotificacion('Error de conexión: ' + response.message, 'danger');
                        }
                    },
                    error: function(xhr) {
                        mostrarNotificacion('Error al probar la conexión', 'danger');
                    },
                    complete: function() {
                        $('#testConnection').html('<i class="fas fa-plug me-1"></i>Probar Conexión');
                    }
                });
            });

            // Guardar configuración
            $('#guardarConfiguracion').click(function() {
                const configData = {
                    database: {
                        server: $('#olapServer').val(),
                        database: $('#olapDatabase').val(),
                        user: $('#olapUser').val()
                    },
                    sucursales: {
                        autoRefresh: $('#autoRefreshSucursales').is(':checked'),
                        refreshInterval: $('#refreshInterval').val(),
                        alertStock: $('#alertStockBajo').is(':checked')
                    },
                    interface: {
                        theme: $('#temaColor').val(),
                        animations: $('#animacionesHabilitadas').is(':checked'),
                        density: $('#densidadInterfaz').val()
                    },
                    etl: {
                        auto: $('#etlAutomatico').is(':checked'),
                        time: $('#etlTime').val(),
                        frequency: $('#etlFrecuencia').val(),
                        email: $('#etlEmail').val()
                    },
                    backup: {
                        auto: $('#backupAutomatico').is(':checked'),
                        path: $('#backupPath').val(),
                        retention: $('#backupRetention').val()
                    },
                    logs: {
                        level: $('#logLevel').val(),
                        toFile: $('#logToFile').is(':checked'),
                        maxSize: $('#logMaxSize').val()
                    }
                };

                $.ajax({
                    url: `${API_BASE}/config/save`,
                    method: 'POST',
                    data: configData,
                    beforeSend: function() {
                        $('#guardarConfiguracion').html(
                            '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
                    },
                    success: function(response) {
                        if (response.success) {
                            mostrarNotificacion('Configuración guardada correctamente', 'success');
                            // Aplicar cambios de tema si es necesario
                            if (response.themeChanged) {
                                aplicarTema(configData.interface.theme);
                            }
                        } else {
                            mostrarNotificacion('Error al guardar configuración: ' + response.message,
                                'danger');
                        }
                    },
                    error: function(xhr) {
                        mostrarNotificacion('Error al guardar la configuración', 'danger');
                    },
                    complete: function() {
                        $('#guardarConfiguracion').html(
                            '<i class="fas fa-save me-1"></i>Guardar Cambios');
                    }
                });
            });

            // Realizar backup manual
            $('#backupNow').click(function() {
                if (confirm('¿Está seguro de realizar un backup ahora? Este proceso puede tomar varios minutos.')) {
                    $.ajax({
                        url: `${API_BASE}/config/backup-now`,
                        method: 'POST',
                        beforeSend: function() {
                            $('#backupNow').html(
                                '<i class="fas fa-spinner fa-spin me-1"></i>Realizando Backup...');
                        },
                        success: function(response) {
                            if (response.success) {
                                mostrarNotificacion('Backup completado correctamente', 'success');
                            } else {
                                mostrarNotificacion('Error durante el backup: ' + response.message,
                                    'danger');
                            }
                        },
                        error: function(xhr) {
                            mostrarNotificacion('Error al realizar el backup', 'danger');
                        },
                        complete: function() {
                            $('#backupNow').html(
                                '<i class="fas fa-database me-1"></i>Realizar Backup Ahora');
                        }
                    });
                }
            });

            // Ver registros del sistema
            $('#viewLogs').click(function() {
                $('#logsModal').modal('show');
                cargarRegistrosSistema();
            });

            // Nuevo usuario
            $('#nuevoUsuarioBtn').click(function() {
                $('#nuevoUsuarioModal').modal('show');
            });
        }

        // Función para aplicar tema de colores
        function aplicarTema(tema) {
            // Remover temas existentes
            $('link[data-theme]').remove();

            if (tema !== 'default') {
                // Agregar el CSS del tema seleccionado
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = `/css/themes/${tema}.css`;
                link.setAttribute('data-theme', tema);
                document.head.appendChild(link);
            }

            // Guardar preferencia de tema
            localStorage.setItem('theme', tema);
        }

        // Cargar registros del sistema
        function cargarRegistrosSistema() {
            $.ajax({
                url: `${API_BASE}/config/logs`,
                method: 'GET',
                success: function(response) {
                    $('#logsContent').html(response.logs);
                },
                error: function(xhr) {
                    $('#logsContent').html(
                        '<p class="text-danger">Error al cargar los registros del sistema.</p>');
                }
            });
        }

        // Inicializar la vista de configuración cuando se active
        $(document).on('viewChanged', function(e, view) {
            if (view === 'configuracion') {
                setupConfigViewListeners();
                cargarConfiguracionActual();
            }
        });

        // Cargar configuración actual del sistema
        function cargarConfiguracionActual() {
            $.ajax({
                url: `${API_BASE}/config/current`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Llenar los formularios con la configuración actual
                        const config = response.config;

                        // Configuración de base de datos
                        $('#olapServer').val(config.database.server || 'localhost');
                        $('#olapDatabase').val(config.database.database || 'DataWarehouse');
                        $('#olapUser').val(config.database.user || 'olap_user');

                        // Configuración de sucursales
                        $('#autoRefreshSucursales').prop('checked', config.sucursales.autoRefresh);
                        $('#refreshInterval').val(config.sucursales.refreshInterval || 5);
                        $('#alertStockBajo').prop('checked', config.sucursales.alertStock);

                        // Configuración de interfaz
                        $('#temaColor').val(config.interface.theme || 'default');
                        $('#animacionesHabilitadas').prop('checked', config.interface.animations);
                        $('#densidadInterfaz').val(config.interface.density || 'comfortable');

                        // Configuración ETL
                        $('#etlAutomatico').prop('checked', config.etl.auto);
                        $('#etlTime').val(config.etl.time || '02:00');
                        $('#etlFrecuencia').val(config.etl.frequency || 'daily');
                        $('#etlEmail').val(config.etl.email || '');

                        // Configuración de backup
                        $('#backupAutomatico').prop('checked', config.backup.auto);
                        $('#backupPath').val(config.backup.path || '/backups');
                        $('#backupRetention').val(config.backup.retention || 30);

                        // Configuración de logs
                        $('#logLevel').val(config.logs.level || 'info');
                        $('#logToFile').prop('checked', config.logs.toFile);
                        $('#logMaxSize').val(config.logs.maxSize || 10);
                    }
                }
            });
        }
    </script>

    <script>
        // Mejora de la visualización 3D del cubo OLAP
        function initOLAP3D() {
            // Configurar escena 3D para el cubo OLAP
            olapScene = new THREE.Scene();
            olapScene.background = new THREE.Color(0x0c0c0c);

            // Configurar cámara
            const container = document.getElementById('olap3DContainer');
            olapCamera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            olapCamera.position.set(50, 50, 50);

            // Configurar renderizador
            olapRenderer = new THREE.WebGLRenderer({
                antialias: true,
                alpha: true
            });
            olapRenderer.setSize(container.clientWidth, container.clientHeight);
            olapRenderer.setClearColor(0x000000, 0);
            container.appendChild(olapRenderer.domElement);

            // Configurar controles
            olapControls = new THREE.OrbitControls(olapCamera, olapRenderer.domElement);
            olapControls.enableDamping = true;
            olapControls.dampingFactor = 0.05;

            // Iluminación mejorada
            const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
            olapScene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(1, 1, 1);
            olapScene.add(directionalLight);

            const hemisphereLight = new THREE.HemisphereLight(0xffffbb, 0x080820, 0.5);
            olapScene.add(hemisphereLight);

            // Añadir ejes de referencia con etiquetas
            const axesHelper = new THREE.AxesHelper(25);
            olapScene.add(axesHelper);

            // Añadir grid para mejor orientación
            const gridHelper = new THREE.GridHelper(50, 10, 0x444444, 0x222222);
            olapScene.add(gridHelper);

            // Iniciar animación
            animateOLAPCube();

            // Cargar datos iniciales del cubo OLAP
            loadOLAPData();
        }

        // Cargar datos OLAP para visualización de forma segura
        function loadOLAPData() {
            $.ajax({
                url: `${API_URLS.olap}/cube-data`,
                method: 'GET',
                dataType: 'json',
                timeout: 10000, // 10 segundos para evitar bloqueos
                success: function(response) {
                    try {
                        // Verificar que la respuesta sea JSON válido y contenga datos
                        if (response && response.success && response.data) {
                            // Crear cubo OLAP si la función existe
                            if (typeof createOLAPCubeVisualization === "function") {
                                createOLAPCubeVisualization(response.data);
                            } else {
                                console.warn("⚠️ createOLAPCubeVisualization no definida.");
                            }

                            // Actualizar leyenda de dimensiones si existe la función y dimensiones
                            if (typeof updateDimensionLegend === "function" && response.data.dimensions) {
                                updateDimensionLegend(response.data.dimensions);
                            } else {
                                console.warn("⚠️ updateDimensionLegend no definida o sin dimensiones.");
                            }
                        } else {
                            console.error('❌ Error al cargar datos OLAP:', response?.message ||
                                "Respuesta inválida");
                            loadDemoOLAPData(); // fallback seguro
                        }
                    } catch (err) {
                        console.error("❌ Excepción al procesar datos OLAP:", err);
                        loadDemoOLAPData(); // fallback seguro
                    }
                },
                error: function(xhr, status, error) {
                    // Detectar si se recibió HTML en lugar de JSON (ej. sesión expirada o error del servidor)
                    let isHtml = xhr.responseText && xhr.responseText.startsWith('<');
                    if (isHtml) {
                        console.error(
                            '❌ Error: La API OLAP devolvió HTML en lugar de JSON. Posible sesión expirada o error 500.'
                        );
                    } else {
                        console.error(`❌ Error al cargar datos OLAP [${status}]:`, error);
                    }

                    // Siempre cargar datos demo como fallback
                    loadDemoOLAPData();
                }
            });
        }


        // Crear visualización 3D del cubo OLAP
        function createOLAPCubeVisualization(data) {
            // Limpiar escena existente (excepto luces y helpers)
            olapScene.children.filter(child =>
                child.type !== 'AmbientLight' &&
                child.type !== 'DirectionalLight' &&
                child.type !== 'HemisphereLight' &&
                child.type !== 'AxesHelper' &&
                child.type !== 'GridHelper'
            ).forEach(child => olapScene.remove(child));

            const dimensions = data.dimensions;
            const measures = data.measures;
            const cells = data.cells;

            // Crear estructura del cubo
            const cubeSize = 30;
            const cubeGroup = new THREE.Group();

            // Crear ejes con etiquetas de dimensiones
            dimensions.forEach((dim, index) => {
                const axisLength = cubeSize + 10;
                const axisColor = getDimensionColor(index);

                // Línea del eje
                const axisGeometry = new THREE.BufferGeometry().setFromPoints([
                    new THREE.Vector3(0, 0, 0),
                    new THREE.Vector3(
                        index === 0 ? axisLength : 0,
                        index === 1 ? axisLength : 0,
                        index === 2 ? axisLength : 0
                    )
                ]);

                const axisMaterial = new THREE.LineBasicMaterial({
                    color: axisColor,
                    linewidth: 3
                });

                const axis = new THREE.Line(axisGeometry, axisMaterial);
                cubeGroup.add(axis);

                // Etiqueta del eje
                const label = createAxisLabel(dim.name, axisColor);
                label.position.set(
                    index === 0 ? axisLength + 3 : 0,
                    index === 1 ? axisLength + 3 : 0,
                    index === 2 ? axisLength + 3 : 0
                );
                cubeGroup.add(label);

                // Marcadores en el eje
                dim.members.forEach((member, memberIndex) => {
                    const pos = (memberIndex + 1) * (cubeSize / dim.members.length);
                    const markerPos = new THREE.Vector3(
                        index === 0 ? pos : 0,
                        index === 1 ? pos : 0,
                        index === 2 ? pos : 0
                    );

                    // Marcador
                    const markerGeometry = new THREE.SphereGeometry(0.3, 16, 16);
                    const markerMaterial = new THREE.MeshBasicMaterial({
                        color: axisColor
                    });
                    const marker = new THREE.Mesh(markerGeometry, markerMaterial);
                    marker.position.copy(markerPos);
                    cubeGroup.add(marker);

                    // Etiqueta del miembro
                    const memberLabel = createMemberLabel(member.name);
                    memberLabel.position.set(
                        index === 0 ? pos : -2,
                        index === 1 ? pos : -2,
                        index === 2 ? pos : -2
                    );
                    cubeGroup.add(memberLabel);
                });
            });

            // Crear celdas del cubo con datos
            cells.forEach(cell => {
                const value = cell.value;
                const x = cell.coords[0] * (cubeSize / dimensions[0].members.length);
                const y = cell.coords[1] * (cubeSize / dimensions[1].members.length);
                const z = cell.coords[2] * (cubeSize / dimensions[2].members.length);

                // Tamaño basado en el valor (normalizado)
                const normalizedValue = value / data.maxValue;
                const size = 1 + (normalizedValue * 3);

                // Color basado en el valor (escala de rojo a verde)
                const color = new THREE.Color();
                color.setHSL(0.3 * normalizedValue, 0.9, 0.5);

                // Crear cubo para la celda
                const geometry = new THREE.BoxGeometry(size, size, size);
                const material = new THREE.MeshPhongMaterial({
                    color: color,
                    transparent: true,
                    opacity: 0.8,
                    emissive: color,
                    emissiveIntensity: 0.2
                });

                const cube = new THREE.Mesh(geometry, material);
                cube.position.set(x, y, z);

                // Almacenar datos de la celda para interactividad
                cube.userData = {
                    type: 'cell',
                    value: value,
                    dimensions: cell.dimensions,
                    measure: cell.measure
                };

                // Hacer el cubo interactivo
                cube.cursor = 'pointer';

                cubeGroup.add(cube);

                // Añadir etiqueta de valor
                if (normalizedValue > 0.3) { // Solo mostrar etiquetas para valores significativos
                    const valueLabel = createValueLabel(value.toLocaleString());
                    valueLabel.position.set(x, y + size / 2 + 0.5, z);
                    cubeGroup.add(valueLabel);
                }
            });

            // Crear estructura de alambre del cubo
            const wireframeGeometry = new THREE.BoxGeometry(cubeSize, cubeSize, cubeSize);
            const wireframeMaterial = new THREE.LineBasicMaterial({
                color: 0x444444,
                linewidth: 1,
                transparent: true,
                opacity: 0.3
            });
            const wireframe = new THREE.LineSegments(
                new THREE.WireframeGeometry(wireframeGeometry),
                wireframeMaterial
            );
            cubeGroup.add(wireframe);

            olapScene.add(cubeGroup);

            // Configurar evento de clic para interactividad
            setupOLAPInteraction();
        }

        // Crear etiqueta para eje
        function createAxisLabel(text, color) {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = 256;
            canvas.height = 64;

            context.fillStyle = 'rgba(0, 0, 0, 0.7)';
            context.fillRect(0, 0, canvas.width, canvas.height);

            context.font = "bold 24px Arial";
            context.fillStyle = `rgb(${color.r * 255}, ${color.g * 255}, ${color.b * 255})`;
            context.textAlign = 'center';
            context.textBaseline = 'middle';
            context.fillText(text, canvas.width / 2, canvas.height / 2);

            const texture = new THREE.CanvasTexture(canvas);
            const material = new THREE.SpriteMaterial({
                map: texture
            });
            const sprite = new THREE.Sprite(material);
            sprite.scale.set(10, 2.5, 1);

            return sprite;
        }

        // Crear etiqueta para miembro de dimensión
        function createMemberLabel(text) {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = 128;
            canvas.height = 32;

            context.fillStyle = 'rgba(0, 0, 0, 0.7)';
            context.fillRect(0, 0, canvas.width, canvas.height);

            context.font = "12px Arial";
            context.fillStyle = '#ffffff';
            context.textAlign = 'center';
            context.textBaseline = 'middle';
            context.fillText(text, canvas.width / 2, canvas.height / 2);

            const texture = new THREE.CanvasTexture(canvas);
            const material = new THREE.SpriteMaterial({
                map: texture
            });
            const sprite = new THREE.Sprite(material);
            sprite.scale.set(5, 1.25, 1);

            return sprite;
        }

        // Crear etiqueta para valor
        function createValueLabel(text) {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = 128;
            canvas.height = 32;

            context.fillStyle = 'rgba(0, 0, 0, 0.8)';
            context.fillRect(0, 0, canvas.width, canvas.height);

            context.font = "bold 14px Arial";
            context.fillStyle = '#ffffff';
            context.textAlign = 'center';
            context.textBaseline = 'middle';
            context.fillText(text, canvas.width / 2, canvas.height / 2);

            const texture = new THREE.CanvasTexture(canvas);
            const material = new THREE.SpriteMaterial({
                map: texture
            });
            const sprite = new THREE.Sprite(material);
            sprite.scale.set(4, 1, 1);

            return sprite;
        }

        // Configurar interacción con el cubo OLAP
        function setupOLAPInteraction() {
            const raycaster = new THREE.Raycaster();
            const mouse = new THREE.Vector2();

            function onMouseClick(event) {
                // Calcular posición normalizada del mouse
                const rect = olapRenderer.domElement.getBoundingClientRect();
                mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

                // Lanzar rayo
                raycaster.setFromCamera(mouse, olapCamera);
                const intersects = raycaster.intersectObjects(olapScene.children, true);

                // Verificar si se hizo clic en una celda
                for (let i = 0; i < intersects.length; i++) {
                    if (intersects[i].object.userData.type === 'cell') {
                        showCellDetails(intersects[i].object.userData);
                        break;
                    }
                }
            }

            olapRenderer.domElement.addEventListener('click', onMouseClick);
        }

        // Mostrar detalles de la celda seleccionada
        function showCellDetails(cellData) {
            $('#detailTitle').text('Detalles de Celda OLAP');

            let detailsHTML = `
        <div class="detail-card">
            <div class="detail-card-header">
                Valor de Medida
            </div>
            <div class="detail-card-body">
                <h3 class="text-primary">${cellData.value.toLocaleString()}</h3>
                <p class="mb-0">${cellData.measure}</p>
            </div>
        </div>
        
        <div class="detail-card">
            <div class="detail-card-header">
                Dimensiones
            </div>
            <div class="detail-card-body">
    `;

            cellData.dimensions.forEach(dim => {
                detailsHTML += `<p><strong>${dim.name}:</strong> ${dim.value}</p>`;
            });

            detailsHTML += `
            </div>
        </div>
        
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="drillDown('${JSON.stringify(cellData).replace(/'/g, "\\'")}')">
                <i class="fas fa-search-plus me-1"></i>Drill Down
            </button>
            <button class="btn btn-outline-primary ms-2" onclick="addToReport('${JSON.stringify(cellData).replace(/'/g, "\\'")}')">
                <i class="fas fa-chart-bar me-1"></i>Agregar a Reporte
            </button>
        </div>
    `;

            $('#detailContent').html(detailsHTML);
            $('#detailSidenav').addClass('open');
        }

        // Actualizar leyenda de dimensiones
        function updateDimensionLegend(dimensions) {
            let legendHTML = '';

            dimensions.forEach((dim, index) => {
                const color = getDimensionColor(index);
                const colorStyle = `rgb(${color.r * 255}, ${color.g * 255}, ${color.b * 255})`;

                legendHTML += `
            <div class="legend-item">
                <div class="legend-color" style="background-color: ${colorStyle};"></div>
                <span>${dim.name}</span>
            </div>
        `;
            });

            $('#dimensionLegend').html(legendHTML);
        }

        // Obtener color para dimensión
        function getDimensionColor(index) {
            const colors = [
                new THREE.Color(0xff6b6b), // Rojo
                new THREE.Color(0x4cd964), // Verde
                new THREE.Color(0x5ac8fa), // Azul
                new THREE.Color(0xffcc00), // Amarillo
                new THREE.Color(0xaf52de), // Púrpura
                new THREE.Color(0xff9500) // Naranja
            ];

            return colors[index % colors.length];
        }

        // Animación del cubo OLAP
        function animateOLAPCube() {
            requestAnimationFrame(animateOLAPCube);

            // Rotación suave automática
            if (olapScene.children.length > 0) {
                const cubeGroup = olapScene.children.find(child => child.type === 'Group');
                if (cubeGroup && autoRotateEnabled) {
                    cubeGroup.rotation.y += 0.002;
                }
            }

            olapControls.update();
            olapRenderer.render(olapScene, olapCamera);
        }

        // Cargar datos de demostración para desarrollo
        function loadDemoOLAPData() {
            const demoData = {
                dimensions: [{
                        name: "Tiempo",
                        members: [{
                                name: "Q1 2023",
                                value: "q1_2023"
                            },
                            {
                                name: "Q2 2023",
                                value: "q2_2023"
                            },
                            {
                                name: "Q3 2023",
                                value: "q3_2023"
                            },
                            {
                                name: "Q4 2023",
                                value: "q4_2023"
                            }
                        ]
                    },
                    {
                        name: "Producto",
                        members: [{
                                name: "Electrónicos",
                                value: "electronics"
                            },
                            {
                                name: "Ropa",
                                value: "clothing"
                            },
                            {
                                name: "Hogar",
                                value: "home"
                            },
                            {
                                name: "Deportes",
                                value: "sports"
                            }
                        ]
                    },
                    {
                        name: "Ubicación",
                        members: [{
                                name: "Norte",
                                value: "north"
                            },
                            {
                                name: "Sur",
                                value: "south"
                            },
                            {
                                name: "Este",
                                value: "east"
                            },
                            {
                                name: "Oeste",
                                value: "west"
                            }
                        ]
                    }
                ],
                measures: ["Ventas", "Cantidad", "Ganancia"],
                cells: [],
                maxValue: 100000
            };

            // Generar celdas de demostración
            for (let t = 0; t < 4; t++) {
                for (let p = 0; p < 4; p++) {
                    for (let l = 0; l < 4; l++) {
                        const value = Math.random() * 100000;
                        demoData.cells.push({
                            value: value,
                            measure: "Ventas",
                            dimensions: [{
                                    name: "Tiempo",
                                    value: demoData.dimensions[0].members[t].name
                                },
                                {
                                    name: "Producto",
                                    value: demoData.dimensions[1].members[p].name
                                },
                                {
                                    name: "Ubicación",
                                    value: demoData.dimensions[2].members[l].name
                                }
                            ],
                            coords: [t, p, l]
                        });
                    }
                }
            }

            createOLAPCubeVisualization(demoData);
            updateDimensionLegend(demoData.dimensions);
        }

        // Funciones de utilidad para operaciones OLAP
        function drillDown(cellData) {
            const data = JSON.parse(cellData);
            mostrarNotificacion(`Drill down en: ${data.dimensions.map(d => d.value).join(', ')}`, 'info');
            // Implementar lógica de drill down
        }

        function addToReport(cellData) {
            const data = JSON.parse(cellData);
            mostrarNotificacion(`Agregado a reporte: ${data.value}`, 'success');
            // Implementar lógica para agregar al reporte
        }

        // Variable para controlar rotación automática
        let autoRotateEnabled = true;

        // Configurar event listeners para controles 3D
        function setup3DControls() {
            $('#rotateXCube').click(() => {
                const cubeGroup = olapScene.children.find(child => child.type === 'Group');
                if (cubeGroup) {
                    cubeGroup.rotation.x += Math.PI / 8;
                }
            });

            $('#rotateYCube').click(() => {
                const cubeGroup = olapScene.children.find(child => child.type === 'Group');
                if (cubeGroup) {
                    cubeGroup.rotation.y += Math.PI / 8;
                }
            });

            $('#rotateZCube').click(() => {
                const cubeGroup = olapScene.children.find(child => child.type === 'Group');
                if (cubeGroup) {
                    cubeGroup.rotation.z += Math.PI / 8;
                }
            });

            $('#resetCubeView').click(() => {
                olapCamera.position.set(50, 50, 50);
                olapControls.reset();

                const cubeGroup = olapScene.children.find(child => child.type === 'Group');
                if (cubeGroup) {
                    cubeGroup.rotation.set(0, 0, 0);
                }
            });

            // Toggle rotación automática
            $('#toggleAutoRotate').click(function() {
                autoRotateEnabled = !autoRotateEnabled;
                $(this).html(
                    `<i class="fas fa-${autoRotateEnabled ? 'pause' : 'play'} me-1"></i>${autoRotateEnabled ? 'Pausar' : 'Reanudar'} Rotación`
                );
            });
        }
    </script>

    <script>
        // Variables globales para el globo
        let globe, globeRenderer, globeScene, globeCamera, globeControls;
        let globeAutoRotate = true;
        let branchPoints = [];
        let transactionArcs = [];

        // Inicializar el globo terráqueo
        function initGlobe() {
            // Configurar escena
            globeScene = new THREE.Scene();
            globeScene.background = new THREE.Color(0x000010);

            // Configurar cámara
            const container = document.getElementById('viewContainer');
            globeCamera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            globeCamera.position.z = 300;

            // Configurar renderizador
            globeRenderer = new THREE.WebGLRenderer({
                antialias: true,
                alpha: true
            });
            globeRenderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(globeRenderer.domElement);

            // Configurar controles
            globeControls = new THREE.OrbitControls(globeCamera, globeRenderer.domElement);
            globeControls.enableDamping = true;
            globeControls.dampingFactor = 0.05;
            globeControls.autoRotate = globeAutoRotate;
            globeControls.autoRotateSpeed = 0.5;

            // Iluminación
            const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
            globeScene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(1, 1, 1);
            globeScene.add(directionalLight);

            // Crear el globo terráqueo
            createEarthGlobe();

            // Iniciar animación
            animateGlobe();

            // Cargar datos de sucursales y transacciones
            loadBranchesData();
            loadTransactionsData();

            // Ajustar al redimensionar ventana
            window.addEventListener('resize', onGlobeResize);
        }

        // Crear el globo terráqueo
        function createEarthGlobe() {
            // Crear esfera para la Tierra
            const earthGeometry = new THREE.SphereGeometry(100, 64, 64);

            // Cargar textura de la Tierra
            const textureLoader = new THREE.TextureLoader();
            const earthTexture = textureLoader.load('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg');
            const bumpMap = textureLoader.load('https://unpkg.com/three-globe/example/img/earth-topology.png');

            const earthMaterial = new THREE.MeshPhongMaterial({
                map: earthTexture,
                bumpMap: bumpMap,
                bumpScale: 0.05,
                specular: new THREE.Color(0x333333),
                shininess: 5
            });

            const earth = new THREE.Mesh(earthGeometry, earthMaterial);
            globeScene.add(earth);

            // Crear atmósfera
            const atmosphereGeometry = new THREE.SphereGeometry(101, 64, 64);
            const atmosphereMaterial = new THREE.MeshPhongMaterial({
                color: 0x0077ff,
                transparent: true,
                opacity: 0.1
            });

            const atmosphere = new THREE.Mesh(atmosphereGeometry, atmosphereMaterial);
            globeScene.add(atmosphere);

            // Añadir estrellas de fondo
            addStarfield();
        }

        // Añadir campo de estrellas al fondo
        function addStarfield() {
            const starGeometry = new THREE.BufferGeometry();
            const starMaterial = new THREE.PointsMaterial({
                color: 0xffffff,
                size: 0.7,
                transparent: true
            });

            const starVertices = [];
            for (let i = 0; i < 10000; i++) {
                const x = (Math.random() - 0.5) * 2000;
                const y = (Math.random() - 0.5) * 2000;
                const z = (Math.random() - 0.5) * 2000;

                // Asegurarse de que las estrellas estén fuera de la Tierra
                if (Math.sqrt(x * x + y * y + z * z) > 110) {
                    starVertices.push(x, y, z);
                }
            }

            starGeometry.setAttribute('position', new THREE.Float32BufferAttribute(starVertices, 3));
            const stars = new THREE.Points(starGeometry, starMaterial);
            globeScene.add(stars);
        }

        // Cargar datos de sucursales
        function loadBranchesData() {
            $.ajax({
                url: API_URLS.sucursales,
                method: 'GET',
                success: function(response) {
                    createBranchPoints(response.data);
                    updateBranchStats(response.data);
                }
            });
        }

        // Crear puntos para las sucursales en el mapa
        function createBranchPoints(branches) {
            // Limpiar puntos existentes
            branchPoints.forEach(point => globeScene.remove(point));
            branchPoints = [];

            branches.forEach(branch => {
                // Convertir lat/long a coordenadas 3D
                const lat = branch.latitud || (Math.random() * 180 - 90);
                const lng = branch.longitud || (Math.random() * 360 - 180);
                const coordinates = latLongToVector3(lat, lng, 101);

                // Crear esfera para la sucursal
                const size = branch.activa ? 2 : 1.5;
                const geometry = new THREE.SphereGeometry(size, 16, 16);
                const material = new THREE.MeshPhongMaterial({
                    color: branch.activa ? 0x4caf50 : 0xf44336,
                    emissive: branch.activa ? 0x1b5e20 : 0xb71c1c,
                    emissiveIntensity: 0.2
                });

                const point = new THREE.Mesh(geometry, material);
                point.position.copy(coordinates);

                // Almacenar datos de la sucursal
                point.userData = branch;

                // Añadir efecto de pulso para sucursales activas
                if (branch.activa) {
                    point.userData.pulse = true;
                }

                globeScene.add(point);
                branchPoints.push(point);

                // Añadir etiqueta
                addBranchLabel(branch.nombre, coordinates);
            });
        }

        // Convertir latitud/longitud a coordenadas 3D
        function latLongToVector3(lat, lng, radius) {
            const phi = (90 - lat) * Math.PI / 180;
            const theta = (lng + 180) * Math.PI / 180;

            return new THREE.Vector3(
                -radius * Math.sin(phi) * Math.cos(theta),
                radius * Math.cos(phi),
                radius * Math.sin(phi) * Math.sin(theta)
            );
        }

        // Añadir etiqueta a sucursal
        function addBranchLabel(name, position) {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = 256;
            canvas.height = 64;

            context.fillStyle = 'rgba(0, 0, 0, 0.7)';
            context.fillRect(0, 0, canvas.width, canvas.height);

            context.font = "14px Arial";
            context.fillStyle = '#ffffff';
            context.textAlign = 'center';
            context.textBaseline = 'middle';
            context.fillText(name, canvas.width / 2, canvas.height / 2);

            const texture = new THREE.CanvasTexture(canvas);
            const material = new THREE.SpriteMaterial({
                map: texture
            });
            const sprite = new THREE.Sprite(material);
            sprite.position.copy(position);
            sprite.position.y += 8;
            sprite.scale.set(20, 5, 1);

            globeScene.add(sprite);
        }

        // Cargar datos de transacciones
        function loadTransactionsData() {
            $.ajax({
                url: API_URLS.transacciones,
                method: 'GET',
                success: function(response) {
                    createTransactionArcs(response.data);
                    updateTransactionStats(response.data);
                }
            });
        }

        // Crear arcos para transacciones entre sucursales
        function createTransactionArcs(transactions) {
            // Limpiar arcos existentes
            transactionArcs.forEach(arc => globeScene.remove(arc));
            transactionArcs = [];

            // Obtener transacciones recientes (últimas 24 horas)
            const recentTransactions = transactions.filter(t => {
                const transactionDate = new Date(t.fecha_transaccion || t.created_at);
                const hoursDiff = (new Date() - transactionDate) / (1000 * 60 * 60);
                return hoursDiff <= 24;
            });

            recentTransactions.slice(0, 20).forEach(transaction => {
                // Buscar sucursales de origen y destino
                const originBranch = branchPoints.find(b =>
                    b.userData.id === transaction.origen_sucursal_id);
                const destBranch = branchPoints.find(b =>
                    b.userData.id === transaction.destino_sucursal_id);

                if (originBranch && destBranch) {
                    // Crear arco entre sucursales
                    createArcBetweenPoints(originBranch.position, destBranch.position, transaction);
                }
            });
        }

        // Crear arco entre dos puntos
        function createArcBetweenPoints(start, end, transactionData) {
            // Calcular punto medio para la curva
            const midPoint = new THREE.Vector3()
                .addVectors(start, end)
                .multiplyScalar(0.5);

            // Elevar el punto medio para crear un arco
            const arcHeight = 20;
            const direction = new THREE.Vector3()
                .subVectors(end, start)
                .normalize();
            const perpendicular = new THREE.Vector3(-direction.z, 0, direction.x)
                .normalize();

            midPoint.add(perpendicular.multiplyScalar(arcHeight));

            // Crear curva suave
            const curve = new THREE.QuadraticBezierCurve3(
                start,
                midPoint,
                end
            );

            // Crear geometría del arco
            const points = curve.getPoints(50);
            const geometry = new THREE.BufferGeometry().setFromPoints(points);

            const material = new THREE.LineBasicMaterial({
                color: 0x2196f3,
                transparent: true,
                opacity: 0.7,
                linewidth: 2
            });

            const arc = new THREE.Line(geometry, material);
            arc.userData = transactionData;

            globeScene.add(arc);
            transactionArcs.push(arc);

            // Añadir avión animado
            addAnimatedPlane(curve, transactionData);
        }

        // Añadir avión animado a lo largo de la ruta
        function addAnimatedPlane(curve, transactionData) {
            // Crear geometría simple de avión
            const geometry = new THREE.ConeGeometry(0.5, 2, 8);
            geometry.rotateX(Math.PI / 2);

            const material = new THREE.MeshPhongMaterial({
                color: 0xff9800,
                emissive: 0xff9800,
                emissiveIntensity: 0.3
            });

            const plane = new THREE.Mesh(geometry, material);

            // Posicionar al inicio de la curva
            const startPoint = curve.getPoint(0);
            plane.position.copy(startPoint);

            // Orientar hacia la dirección del movimiento
            const tangent = curve.getTangent(0);
            plane.lookAt(tangent.add(startPoint));

            // Almacenar datos de animación
            plane.userData = {
                curve: curve,
                progress: 0,
                speed: 0.002,
                transaction: transactionData
            };

            globeScene.add(plane);

            // Añadir a la lista de objetos animados
            transactionArcs.push(plane);
        }

        // Actualizar estadísticas de sucursales
        function updateBranchStats(branches) {
            const activeBranches = branches.filter(b => b.activa).length;
            $('#activeBranches').text(activeBranches);
            $('#totalSucursales').text(branches.length);
        }

        // Actualizar estadísticas de transacciones
        function updateTransactionStats(transactions) {
            const today = new Date().toDateString();
            const todayTransactions = transactions.filter(t =>
                new Date(t.fecha_transaccion || t.created_at).toDateString() === today
            ).length;

            $('#todayTransactions').text(todayTransactions);
            $('#transaccionesActivas').text(transactions.filter(t => t.estado === 'en_transito').length);
        }

        // Animación del globo
        function animateGlobe() {
            requestAnimationFrame(animateGlobe);

            // Actualizar controles
            globeControls.update();

            // Animar puntos de sucursales (efecto de pulso)
            branchPoints.forEach(point => {
                if (point.userData.pulse) {
                    point.scale.x = 1 + 0.1 * Math.sin(Date.now() * 0.002);
                    point.scale.y = 1 + 0.1 * Math.sin(Date.now() * 0.002);
                    point.scale.z = 1 + 0.1 * Math.sin(Date.now() * 0.002);
                }
            });

            // Animar aviones en las rutas de transacción
            transactionArcs.forEach(obj => {
                if (obj.userData && obj.userData.curve) {
                    const plane = obj;
                    plane.userData.progress += plane.userData.speed;

                    if (plane.userData.progress > 1) {
                        plane.userData.progress = 0;
                    }

                    const point = plane.userData.curve.getPoint(plane.userData.progress);
                    plane.position.copy(point);

                    // Actualizar orientación
                    const tangent = plane.userData.curve.getTangent(plane.userData.progress);
                    plane.lookAt(tangent.add(point));
                }
            });

            // Renderizar escena
            globeRenderer.render(globeScene, globeCamera);
        }

        // Ajustar el globo al redimensionar ventana
        function onGlobeResize() {
            const container = document.getElementById('viewContainer');
            globeCamera.aspect = container.clientWidth / container.clientHeight;
            globeCamera.updateProjectionMatrix();
            globeRenderer.setSize(container.clientWidth, container.clientHeight);
        }

        // Configurar event listeners para controles del globo
        function setupGlobeControls() {
            $('#zoomInGlobe').click(() => {
                globeCamera.position.multiplyScalar(0.9);
            });

            $('#zoomOutGlobe').click(() => {
                globeCamera.position.multiplyScalar(1.1);
            });

            $('#resetGlobeView').click(() => {
                globeCamera.position.set(0, 0, 300);
                globeControls.reset();
            });

            $('#toggleRotateGlobe').click(function() {
                globeAutoRotate = !globeAutoRotate;
                globeControls.autoRotate = globeAutoRotate;
                $(this).html(
                    `<i class="fas fa-${globeAutoRotate ? 'pause' : 'play'} me-1"></i>${globeAutoRotate ? 'Pausar' : 'Reanudar'} Rotación`
                );
            });

            $('#toggleFlights').click(function() {
                const show = transactionArcs.length > 0 && transactionArcs[0].visible;
                transactionArcs.forEach(arc => {
                    arc.visible = !show;
                });
                $(this).html(
                    `<i class="fas fa-plane me-1"></i>${show ? 'Mostrar' : 'Ocultar'} Transacciones`
                );
            });

            // Interacción al hacer clic en una sucursal
            globeRenderer.domElement.addEventListener('click', (event) => {
                const mouse = new THREE.Vector2();
                const rect = globeRenderer.domElement.getBoundingClientRect();

                mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

                const raycaster = new THREE.Raycaster();
                raycaster.setFromCamera(mouse, globeCamera);

                const intersects = raycaster.intersectObjects(branchPoints);

                if (intersects.length > 0) {
                    const branch = intersects[0].object.userData;
                    showBranchDetails(branch);
                }
            });
        }

        // Mostrar detalles de sucursal
        function showBranchDetails(branch) {
            $('#detailTitle').text(branch.nombre);

            // Cargar información adicional de la sucursal
            $.ajax({
                url: `${API_URLS.sucursales}/${branch.id}/stats`,
                method: 'GET',
                success: function(stats) {
                    renderBranchDetails(branch, stats);
                },
                error: function() {
                    // Usar datos básicos si la API falla
                    renderBranchDetails(branch, {
                        ventas_hoy: Math.floor(Math.random() * 10000),
                        transacciones_hoy: Math.floor(Math.random() * 50),
                        productos_populares: ['Producto A', 'Producto B', 'Producto C']
                    });
                }
            });
        }

        // Renderizar detalles de la sucursal
        function renderBranchDetails(branch, stats) {
            const detailsHTML = `
        <div class="detail-card">
            <div class="detail-card-header">
                Información de la Sucursal
            </div>
            <div class="detail-card-body">
                <p><strong>Ubicación:</strong> ${branch.ciudad}, ${branch.pais}</p>
                <p><strong>Dirección:</strong> ${branch.direccion}</p>
                <p><strong>Teléfono:</strong> ${branch.telefono || 'N/A'}</p>
                <p><strong>Email:</strong> ${branch.email || 'N/A'}</p>
                <p><strong>Estado:</strong> <span class="badge ${branch.activa ? 'bg-success' : 'bg-danger'}">${branch.activa ? 'Activa' : 'Inactiva'}</span></p>
            </div>
        </div>
        
        <div class="detail-card">
            <div class="detail-card-header">
                Métricas de Hoy
            </div>
            <div class="detail-card-body">
                <p><strong>Ventas:</strong> $${stats.ventas_hoy?.toLocaleString() || '0'}</p>
                <p><strong>Transacciones:</strong> ${stats.transacciones_hoy || '0'}</p>
                <p><strong>Productos populares:</strong> ${stats.productos_populares?.join(', ') || 'N/A'}</p>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="viewBranchReports(${branch.id})">
                <i class="fas fa-chart-bar me-1"></i>Ver Reportes
            </button>
            <button class="btn btn-outline-primary ms-2" onclick="focusOnBranch(${branch.id})">
                <i class="fas fa-search-location me-1"></i>Enfocar en Mapa
            </button>
        </div>
    `;

            $('#detailContent').html(detailsHTML);
            $('#detailSidenav').addClass('open');
        }

        // Enfocar en una sucursal específica en el mapa
        function focusOnBranch(branchId) {
            const branchPoint = branchPoints.find(b => b.userData.id === branchId);
            if (branchPoint) {
                // Animar la cámara hacia la sucursal
                const targetPosition = branchPoint.position.clone();
                const cameraPosition = targetPosition.clone().multiplyScalar(1.5);

                // Animación suave
                animateCamera(globeCamera.position, cameraPosition, globeControls.target, targetPosition);
            }
        }

        // Animación suave de cámara
        function animateCamera(fromPos, toPos, fromTarget, toTarget) {
            const duration = 1000; // ms
            const startTime = Date.now();

            function update() {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Función de easing
                const ease = function(t) {
                    return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
                };

                const easedProgress = ease(progress);

                // Interpolar posición
                globeCamera.position.lerpVectors(fromPos, toPos, easedProgress);

                // Interpolar objetivo
                const currentTarget = new THREE.Vector3().lerpVectors(fromTarget, toTarget, easedProgress);
                globeControls.target.copy(currentTarget);
                globeControls.update();

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }

            update();
        }

        // Actualizar datos en tiempo real
        function setupRealTimeUpdates() {
            // Actualizar cada 30 segundos
            setInterval(() => {
                loadBranchesData();
                loadTransactionsData();
                $('#lastUpdate').text(new Date().toLocaleTimeString());
            }, 30000);

            // WebSocket para actualizaciones en tiempo real
            setupWebSocketConnection();
        }

        // Configurar conexión WebSocket para actualizaciones en tiempo real
        function setupWebSocketConnection() {
            try {
                const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
                const wsUrl = `${protocol}//${window.location.host}/ws/dashboard`;
                const socket = new WebSocket(wsUrl);

                socket.onmessage = function(event) {
                    const data = JSON.parse(event.data);

                    if (data.type === 'transaction_update') {
                        updateTransactionInRealTime(data.transaction);
                    } else if (data.type === 'branch_update') {
                        updateBranchInRealTime(data.branch);
                    }
                };

                socket.onclose = function() {
                    // Reconectar después de 5 segundos
                    setTimeout(setupWebSocketConnection, 5000);
                };
            } catch (error) {
                console.warn('WebSocket no disponible, usando polling');
            }
        }

        // Actualizar transacción en tiempo real
        function updateTransactionInRealTime(transaction) {
            // Buscar si ya existe una transacción similar
            const existingArc = transactionArcs.find(arc =>
                arc.userData && arc.userData.id === transaction.id
            );

            if (!existingArc && transaction.estado === 'en_transito') {
                // Crear nueva visualización de transacción
                const originBranch = branchPoints.find(b =>
                    b.userData.id === transaction.origen_sucursal_id);
                const destBranch = branchPoints.find(b =>
                    b.userData.id === transaction.destino_sucursal_id);

                if (originBranch && destBranch) {
                    createArcBetweenPoints(originBranch.position, destBranch.position, transaction);

                    // Mostrar notificación
                    mostrarNotificacion(`Nueva transacción: ${transaction.codigo}`, 'info');
                }
            }
        }

        // Actualizar sucursal en tiempo real
        function updateBranchInRealTime(branch) {
            const existingPoint = branchPoints.find(b => b.userData.id === branch.id);

            if (existingPoint) {
                // Actualizar propiedades visuales
                existingPoint.material.color.setHex(branch.activa ? 0x4caf50 : 0xf44336);
                existingPoint.material.emissive.setHex(branch.activa ? 0x1b5e20 : 0xb71c1c);
                existingPoint.userData.pulse = branch.activa;

                if (!branch.activa) {
                    existingPoint.scale.set(1.5, 1.5, 1.5);
                }
            }
        }

        // Inicializar el globo cuando se carga la página
        $(document).ready(function() {
            // Inicializar el globo cuando se muestra la vista de mapa
            $('#viewMapBtn').click(function() {
                if (!globe) {
                    initGlobe();
                    setupGlobeControls();
                    setupRealTimeUpdates();
                }
            });

            // Cambiar entre vistas
            $('#viewMapBtn, #viewCubeBtn, #viewMixedBtn').click(function() {
                $(this).addClass('active').removeClass(
                    'btn-outline-primary btn-outline-secondary btn-outline-info').addClass(
                    'btn-primary');

                // Desactivar otros botones
                $('#viewMapBtn, #viewCubeBtn, #viewMixedBtn').not(this).each(function() {
                    $(this).removeClass('active btn-primary');

                    if ($(this).is('#viewMapBtn')) {
                        $(this).addClass('btn-outline-primary');
                    } else if ($(this).is('#viewCubeBtn')) {
                        $(this).addClass('btn-outline-secondary');
                    } else {
                        $(this).addClass('btn-outline-info');
                    }
                });

                // Mostrar/ocultar controles según la vista
                const isMapView = $(this).is('#viewMapBtn');
                const isMixedView = $(this).is('#viewMixedBtn');

                $('.globe-controls, .globe-info-panel, .globe-legend').toggle(isMapView || isMixedView);
                $('.cube-controls').toggle(!isMapView);

                // Configurar vista mixta
                if (isMixedView && globe && scene) {
                    setupMixedView();
                }
            });
        });

        // Configurar vista mixta (Globo + Cubo OLAP)
        function setupMixedView() {
            // Posicionar el globo y el cubo lado a lado
            globeCamera.position.set(200, 100, 200);
            globeControls.target.set(0, 0, 0);

            // Posicionar la cámara del cubo OLAP
            camera.position.set(-200, 100, -200);
            controls.target.set(0, 0, 0);

            // Ajustar renderizado para mostrar ambas escenas
            function renderMixedView() {
                // Limpiar el canvas
                renderer.clear();

                // Establecer viewport para el globo (mitad izquierda)
                const width = window.innerWidth / 2;
                const height = window.innerHeight;

                renderer.setViewport(0, 0, width, height);
                renderer.render(globeScene, globeCamera);

                // Establecer viewport para el cubo (mitad derecha)
                renderer.setViewport(width, 0, width, height);
                renderer.render(scene, camera);
            }

            // Reemplazar la función de animación
            const originalAnimate = animate;
            animate = function() {
                requestAnimationFrame(animate);
                renderMixedView();
                globeControls.update();
                controls.update();
            };

            animate();
        }
    </script>

</body>

</html>
