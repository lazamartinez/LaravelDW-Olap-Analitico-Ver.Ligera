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
                        <a href="#" class="nav-link" onclick="event.preventDefault(); this.closest('form').submit();">
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
                            <i class="fas fa-info-circle"></i> <span id="sucursalesInactivas">0</span> en mantenimiento
                        </div>
                    </div>
                </div>

                <!-- 3D Visualization -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Visualización 3D de Sucursales y Transacciones</h5>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary active" id="viewMapBtn">
                                        <i class="fas fa-globe-americas me-1"></i> Mapa
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" id="viewCubeBtn">
                                        <i class="fas fa-cube me-1"></i> Cubo OLAP
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="view-container" id="viewContainer">
                                    <div class="view-switcher">
                                        <div class="btn-group-vertical">
                                            <button class="btn btn-light btn-sm" id="zoomInBtn">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn btn-light btn-sm" id="zoomOutBtn">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <button class="btn btn-light btn-sm" id="resetViewBtn">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="stats-panel" id="mapStatsPanel">
                                        <h6>Estadísticas del Mapa</h6>
                                        <p class="mb-1">Sucursales: <span class="badge bg-info" id="totalSucursales">0</span></p>
                                        <p class="mb-1">Transacciones activas: <span
                                                class="badge bg-success" id="transaccionesActivas">0</span></p>
                                        <p class="mb-0">Productos en movimiento: <span
                                                class="badge bg-warning" id="productosMovimiento">0</span></p>
                                    </div>

                                    <div class="cube-controls" id="cubeControls" style="display: none;">
                                        <h6>Controles del Cubo OLAP</h6>
                                        <div class="mb-2">
                                            <label class="form-label">Dimensión X</label>
                                            <select class="form-select form-select-sm" id="dimensionX">
                                                <option value="tiempo">Tiempo</option>
                                                <option value="sucursal">Ubicación</option>
                                                <option value="producto">Producto</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Dimensión Y</label>
                                            <select class="form-select form-select-sm" id="dimensionY">
                                                <option value="ventas">Ventas</option>
                                                <option value="ganancia">Ganancia</option>
                                                <option value="cantidad">Cantidad</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Dimensión Z</label>
                                            <select class="form-select form-select-sm" id="dimensionZ">
                                                <option value="categoria">Categoría</option>
                                                <option value="sucursal">Sucursal</option>
                                                <option value="region">Región</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-primary btn-sm w-100" id="aplicarCubo">Aplicar</button>

                                        <div class="mt-3">
                                            <h6>Leyenda</h6>
                                            <div class="legend-item">
                                                <div class="legend-color" style="background-color: #ff6b6b;"></div>
                                                <span>Ventas Bajas</span>
                                            </div>
                                            <div class="legend-item">
                                                <div class="legend-color" style="background-color: #ffe066;"></div>
                                                <span>Ventas Medias</span>
                                            </div>
                                            <div class="legend-item">
                                                <div class="legend-color" style="background-color: #51cf66;"></div>
                                                <span>Ventas Altas</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Transacciones en Tiempo Real</h5>
                                <span class="badge bg-success" id="liveBadge">
                                    <i class="fas fa-circle me-1"></i> En vivo
                                </span>
                            </div>
                            <div class="card-body overflow-auto" style="max-height: 460px;">
                                <div id="transactionsContainer">
                                    <!-- Las transacciones se cargarán dinámicamente -->
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
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
                                            <input type="text" class="form-control form-control-sm" name="filtroValor" placeholder="Valor">
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
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearTransaccionModal">
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

            <div class="view-section" id="reportes-view">
                <div class="main-header">
                    <h1 class="header-title">Reportes y Análisis</h1>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Módulo de reportes en desarrollo.
                </div>
            </div>

            <div class="view-section" id="configuracion-view">
                <div class="main-header">
                    <h1 class="header-title">Configuración del Sistema</h1>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Módulo de configuración en desarrollo.
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
                                <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                            </div>
                            <div class="col-md-6">
                                <label for="pais" class="form-label">País</label>
                                <input type="text" class="form-control" id="pais" name="pais" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="codigo_postal" class="form-label">Código Postal</label>
                                <input type="text" class="form-control" id="codigo_postal" name="codigo_postal">
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
                            <input type="number" step="any" class="form-control" id="latitud" name="latitud">
                        </div>
                        <div class="mb-3">
                            <label for="longitud" class="form-label">Longitud</label>
                            <input type="number" step="any" class="form-control" id="longitud" name="longitud">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="activa" name="activa" checked>
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
                                <input type="number" class="form-control" id="stock" name="stock" required>
                            </div>
                            <div class="col-md-6">
                                <label for="categoria" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" required>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="origen_sucursal_id" class="form-label">Sucursal Origen</label>
                                <select class="form-select" id="origen_sucursal_id" name="origen_sucursal_id" required>
                                    <option value="">Seleccionar origen</option>
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="destino_sucursal_id" class="form-label">Sucursal Destino</label>
                                <select class="form-select" id="destino_sucursal_id" name="destino_sucursal_id" required>
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
                                        <input type="number" class="form-control" name="productos[0][cantidad]" min="1" placeholder="Cantidad" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm quitar-producto">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="agregarProducto">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
        const API_BASE = '/api';
        const API_URLS = {
            sucursales: `${API_BASE}/sucursales`,
            productos: `${API_BASE}/productos`,
            inventario: `${API_BASE}/inventario`,
            transacciones: `${API_BASE}/transacciones`,
            ventas: `${API_BASE}/ventas`,
            olap: `${API_BASE}/olap`,
            etl: `${API_BASE}/etl`
        };

        // Configuración de AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Inicializar la aplicación
        $(document).ready(function() {
            // Inicializar navegación
            initNavigation();

            // Cargar datos iniciales
            loadInitialData();

            // Configurar event listeners
            setupEventListeners();

            // Inicializar la visualización 3D
            init3DView();
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
            switch(view) {
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
                url: `${API_URLS.sucursales}`,
                method: 'GET',
                success: function(sucursales) {
                    updateSucursalesStats(sucursales);
                }
            });

            // Cargar métricas desde el controlador del dashboard
            $.ajax({
                url: '/dashboard-metrics',
                method: 'GET',
                success: function(metrics) {
                    updateDashboardMetrics(metrics);
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
            $('#ventasHoy').text(`$${metrics.ventasHoy.toLocaleString()}`);
            $('#ventasMes').text(`$${metrics.ventasMes.toLocaleString()}`);
            $('#gananciaTotal').text(`$${metrics.gananciaTotal.toLocaleString()}`);
            $('#productosVendidos').text(metrics.productosVendidos.toLocaleString());
            
            // Actualizar cambios porcentuales (simulados)
            $('#ventasChange').text('12.5%');
            $('#ventasMesChange').text('8.3%');
            $('#gananciaChange').text('10.2%');
            
            // Actualizar gráficos
            updateCharts(metrics);
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
            switch(estado) {
                case 'completada': return 'success';
                case 'pendiente': return 'warning';
                case 'en_transito': return 'info';
                case 'cancelada': return 'danger';
                default: return 'secondary';
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
                        $select.append(`<option value="${producto.id}">${producto.nombre} (${producto.codigo})</option>`);
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
                    renderProductosTable(response.data);
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
                const consultas = [
                    {
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

        // Configurar event listeners
        function setupEventListeners() {
            // Manejadores de eventos para los botones de vista
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

            // Manejadores para los controles de zoom
            $('#zoomInBtn').click(() => {
                camera.position.y -= 5;
                camera.position.z -= 5;
            });

            $('#zoomOutBtn').click(() => {
                camera.position.y += 5;
                camera.position.z += 5;
            });

            $('#resetViewBtn').click(() => {
                camera.position.set(0, 30, 50);
                controls.reset();
            });

            // Manejador para pantalla completa
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

            // Manejador para el sidenav de detalles
            $('#closeNav').click(() => {
                $('#detailSidenav').removeClass('open');
            });

            // Event listener para clics en las sucursales
            renderer.domElement.addEventListener('click', (event) => {
                const mouse = new THREE.Vector2();
                const rect = renderer.domElement.getBoundingClientRect();

                mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

                const raycaster = new THREE.Raycaster();
                raycaster.setFromCamera(mouse, camera);

                const intersects = raycaster.intersectObjects(sucursalObjects.filter(obj => obj instanceof THREE
                    .Mesh));

                if (intersects.length > 0) {
                    const sucursal = intersects[0].object.userData;
                    showSucursalDetails(sucursal);
                }
            });

            // Filtros para la tabla de inventario
            $('#filtroSucursal, #filtroAlerta, #buscarProducto').change(function() {
                filterInventarioTable();
            });

            // Formulario de crear sucursal
            $('#formCrearSucursal').submit(function(e) {
                e.preventDefault();
                crearSucursal();
            });

            // Formulario de crear producto
            $('#formCrearProducto').submit(function(e) {
                e.preventDefault();
                crearProducto();
            });

            // Formulario de transferir inventario
            $('#formTransferirInventario').submit(function(e) {
                e.preventDefault();
                transferirInventario();
            });

            // Formulario de crear transacción
            $('#formCrearTransaccion').submit(function(e) {
                e.preventDefault();
                crearTransaccion();
            });

            // Botón para agregar producto en transacción
            $('#agregarProducto').click(function() {
                agregarProductoTransaccion();
            });

            // Botón para ejecutar consulta OLAP
            $('#ejecutarConsultaBtn').click(function() {
                ejecutarConsultaOLAP();
            });

            // Botón para guardar consulta
            $('#guardarConsultaBtn').click(function() {
                $('#guardarConsultaModal').modal('show');
            });

            // Botón para cargar consulta
            $('#cargarConsultaBtn').click(function() {
                $('#cargarConsultaModal').modal('show');
            });

            // Botón para refrescar dashboard
            $('#refreshDashboard').click(function() {
                loadDashboardStats();
                loadTransactions();
            });

            // Aplicar cubo OLAP
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
                    mostrarNotificacion('Error al transferir inventario: ' + xhr.responseJSON.message, 'danger');
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
                        $nuevoSelect.append(`<option value="${producto.id}">${producto.nombre} (${producto.codigo})</option>`);
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
                        mostrarNotificacion('Error al eliminar sucursal: ' + xhr.responseJSON.message, 'danger');
                    }
                });
            }
        }

        function toggleSucursalEstado(id, activa) {
            $.ajax({
                url: `${API_URLS.sucursales}/${id}`,
                method: 'PUT',
                data: { activa: activa },
                success: function() {
                    mostrarNotificacion(`Sucursal ${activa ? 'activada' : 'desactivada'} correctamente`, 'success');
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
                        mostrarNotificacion('Error al eliminar producto: ' + xhr.responseJSON.message, 'danger');
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
                        mostrarNotificacion('Error al eliminar inventario: ' + xhr.responseJSON.message, 'danger');
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
            // Destruir gráficos existentes
            if (salesChartInstance) {
                salesChartInstance.destroy();
            }
            if (productsChartInstance) {
                productsChartInstance.destroy();
            }

            // Gráfico de ventas por sucursal
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            salesChartInstance = new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: metrics.sucursalesVentas.map(s => s.sucursal),
                    datasets: [{
                        label: 'Ventas',
                        data: metrics.sucursalesVentas.map(s => s.total_ventas),
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

            // Gráfico de productos
            const productsCtx = document.getElementById('productsChart').getContext('2d');
            productsChartInstance = new Chart(productsCtx, {
                type: 'doughnut',
                data: {
                    labels: metrics.productosPopulares.map(p => p.producto),
                    datasets: [{
                        data: metrics.productosPopulares.map(p => p.total_vendido),
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
    </script>

</body>

</html>