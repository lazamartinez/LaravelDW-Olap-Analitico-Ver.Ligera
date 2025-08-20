<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard OLAP 3D</title>
    
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
        
        .nav-link:hover, .nav-link.active {
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
        
        .stat-card:nth-child(2)::before { background: var(--success-gradient); }
        .stat-card:nth-child(3)::before { background: var(--warning-gradient); }
        .stat-card:nth-child(4)::before { background: var(--danger-gradient); }
        
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
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
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
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes toastOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
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
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard View -->
            <div class="view-section active" id="dashboard-view">
                <div class="main-header">
                    <h1 class="header-title">Dashboard OLAP 3D</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary">
                            <i class="fas fa-sync-alt me-2"></i>Actualizar
                        </button>
                        <button class="btn btn-outline-primary" id="fullscreenBtn">
                            <i class="fas fa-expand me-2"></i>Pantalla Completa
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                        <div class="stat-icon text-primary">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-value">$18,542</div>
                        <div class="stat-label">Ventas Hoy</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> 12.5% desde ayer
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                        <div class="stat-icon text-success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value">$243,876</div>
                        <div class="stat-label">Ventas Mes</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> 8.3% desde el mes pasado
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                        <div class="stat-icon text-warning">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-value">$67,385</div>
                        <div class="stat-label">Ganancia Total</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> 10.2% desde ayer
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.4s;">
                        <div class="stat-icon text-info">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="stat-value">8/10</div>
                        <div class="stat-label">Sucursales Activas</div>
                        <div class="stat-change negative">
                            <i class="fas fa-info-circle"></i> 2 en mantenimiento
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
                                        <p class="mb-1">Sucursales: <span class="badge bg-info">10</span></p>
                                        <p class="mb-1">Transacciones activas: <span class="badge bg-success">14</span></p>
                                        <p class="mb-0">Productos en movimiento: <span class="badge bg-warning">327</span></p>
                                    </div>
                                    
                                    <div class="cube-controls" id="cubeControls" style="display: none;">
                                        <h6>Controles del Cubo OLAP</h6>
                                        <div class="mb-2">
                                            <label class="form-label">Dimensión X</label>
                                            <select class="form-select form-select-sm">
                                                <option>Tiempo</option>
                                                <option>Ubicación</option>
                                                <option>Producto</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Dimensión Y</label>
                                            <select class="form-select form-select-sm">
                                                <option>Ventas</option>
                                                <option>Ganancia</option>
                                                <option>Cantidad</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Dimensión Z</label>
                                            <select class="form-select form-select-sm">
                                                <option>Categoría</option>
                                                <option>Sucursal</option>
                                                <option>Región</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-primary btn-sm w-100">Aplicar</button>
                                        
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
                            <table class="table table-striped table-hover">
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
                                        <td>1</td>
                                        <td>Sucursal Centro</td>
                                        <td>Buenos Aires</td>
                                        <td><span class="badge bg-success">Activo</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning">
                                                <i class="fas fa-stop"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Sucursal Norte</td>
                                        <td>Rosario</td>
                                        <td><span class="badge bg-success">Activo</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning">
                                                <i class="fas fa-stop"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Sucursal Sur</td>
                                        <td>La Plata</td>
                                        <td><span class="badge bg-secondary">Inactivo</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-success">
                                                <i class="fas fa-play"></i>
                                            </button>
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
                            <table class="table table-striped table-hover">
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
                                        <td>P001</td>
                                        <td>Laptop Gaming</td>
                                        <td>$1,250.00</td>
                                        <td><span class="badge bg-success">45</span></td>
                                        <td>Tecnología</td>
                                        <td>
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning">
                                                <i class="fas fa-chart-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>P002</td>
                                        <td>Smartphone</td>
                                        <td>$850.00</td>
                                        <td><span class="badge bg-warning">8</span></td>
                                        <td>Tecnología</td>
                                        <td>
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning">
                                                <i class="fas fa-chart-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>P003</td>
                                        <td>Tablet</td>
                                        <td>$450.00</td>
                                        <td><span class="badge bg-danger">3</span></td>
                                        <td>Tecnología</td>
                                        <td>
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning">
                                                <i class="fas fa-chart-line"></i>
                                            </button>
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
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transferirInventarioModal">
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
                                    <option value="1">Sucursal Centro</option>
                                    <option value="2">Sucursal Norte</option>
                                    <option value="3">Sucursal Sur</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="filtroAlerta">
                                    <label class="form-check-label" for="filtroAlerta">Mostrar solo alertas de stock</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar producto...">
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
                                        <td>Sucursal Centro</td>
                                        <td>Laptop Gaming</td>
                                        <td>P001</td>
                                        <td><span class="badge bg-success">15</span></td>
                                        <td>5</td>
                                        <td>Estante A1</td>
                                        <td>
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td>Sucursal Norte</td>
                                        <td>Smartphone</td>
                                        <td>P002</td>
                                        <td><span class="badge bg-warning">4</span></td>
                                        <td>5</td>
                                        <td>Estante B2</td>
                                        <td>
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td>Sucursal Sur</td>
                                        <td>Tablet</td>
                                        <td>P003</td>
                                        <td><span class="badge bg-danger">2</span></td>
                                        <td>5</td>
                                        <td>Estante C3</td>
                                        <td>
                                            <button class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Otras vistas (Transacciones, Reportes, Configuración) -->
            <div class="view-section" id="transacciones-view">
                <div class="main-header">
                    <h1 class="header-title">Gestión de Transacciones</h1>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Módulo de transacciones en desarrollo.
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
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Nueva Sucursal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Nuevo Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <input type="number" step="0.01" class="form-control" id="precio" name="precio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="costo" class="form-label">Costo</label>
                                <input type="number" step="0.01" class="form-control" id="costo" name="costo" required>
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
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Transferir Inventario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="producto_id" class="form-label">Producto</label>
                            <select class="form-select" id="producto_id" name="producto_id" required>
                                <option value="">Seleccionar producto</option>
                                <option value="1">Laptop Gaming (P001)</option>
                                <option value="2">Smartphone (P002)</option>
                                <option value="3">Tablet (P003)</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sucursal_origen_id" class="form-label">Sucursal Origen</label>
                                <select class="form-select" id="sucursal_origen_id" name="sucursal_origen_id" required>
                                    <option value="">Seleccionar origen</option>
                                    <option value="1">Sucursal Centro</option>
                                    <option value="2">Sucursal Norte</option>
                                    <option value="3">Sucursal Sur</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="sucursal_destino_id" class="form-label">Sucursal Destino</label>
                                <select class="form-select" id="sucursal_destino_id" name="sucursal_destino_id" required>
                                    <option value="">Seleccionar destino</option>
                                    <option value="1">Sucursal Centro</option>
                                    <option value="2">Sucursal Norte</option>
                                    <option value="3">Sucursal Sur</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
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

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Three.js -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/controls/OrbitControls.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Variables globales para Three.js
        let scene, camera, renderer, controls;
        let sucursalObjects = [];
        let transactionLines = [];
        
        // Datos de ejemplo
        const sucursales = [
            { id: 1, nombre: "Sucursal Centro", ciudad: "Buenos Aires", ventas: 12500, activa: true, lat: -34.6037, lng: -58.3816, productos: 45 },
            { id: 2, nombre: "Sucursal Norte", ciudad: "Rosario", ventas: 9800, activa: true, lat: -32.9468, lng: -60.6393, productos: 32 },
            { id: 3, nombre: "Sucursal Sur", ciudad: "La Plata", ventas: 7600, activa: true, lat: -34.9215, lng: -57.9545, productos: 28 },
            { id: 4, nombre: "Sucursal Este", ciudad: "Mar del Plata", ventas: 11500, activa: true, lat: -38.0055, lng: -57.5426, productos: 41 },
            { id: 5, nombre: "Sucursal Oeste", ciudad: "Mendoza", ventas: 6800, activa: true, lat: -32.8895, lng: -68.8458, productos: 27 },
            { id: 6, nombre: "Sucursal Noroeste", ciudad: "Salta", ventas: 5400, activa: true, lat: -24.7829, lng: -65.4232, productos: 22 },
            { id: 7, nombre: "Sucursal Noreste", ciudad: "Posadas", ventas: 6200, activa: true, lat: -27.3621, lng: -55.9009, productos: 25 },
            { id: 8, nombre: "Sucursal Patagonia", ciudad: "Bariloche", ventas: 8900, activa: true, lat: -41.1335, lng: -71.3103, productos: 35 },
            { id: 9, nombre: "Sucursal Cuyo", ciudad: "San Juan", ventas: 4800, activa: false, lat: -31.5375, lng: -68.5364, productos: 19 },
            { id: 10, nombre: "Sucursal Litoral", ciudad: "Paraná", ventas: 5700, activa: true, lat: -31.7333, lng: -60.5333, productos: 23 }
        ];

        const transacciones = [
            { id: 1, tipo: "venta", sucursal: "Sucursal Centro", descripcion: "Venta de 15 productos electrónicos", monto: 4250, fecha: "Hace 2 min", estado: "completed" },
            { id: 2, tipo: "transferencia", sucursal: "Sucursal Norte", descripcion: "Transferencia de 32 unidades a Sucursal Sur", monto: 0, fecha: "Hace 5 min", estado: "pending" },
            { id: 3, tipo: "venta", sucursal: "Sucursal Este", descripcion: "Venta de 8 productos deportivos", monto: 1850, fecha: "Hace 8 min", estado: "completed" },
            { id: 4, tipo: "devolución", sucursal: "Sucursal Oeste", descripcion: "Devolución de 3 productos", monto: -750, fecha: "Hace 12 min", estado: "completed" },
            { id: 5, tipo: "venta", sucursal: "Sucursal Patagonia", descripcion: "Venta de 12 productos de hogar", monto: 3200, fecha: "Hace 15 min", estado: "completed" },
            { id: 6, tipo: "transferencia", sucursal: "Sucursal Centro", descripcion: "Transferencia de 25 unidades a Sucursal Norte", monto: 0, fecha: "Hace 18 min", estado: "pending" },
            { id: 7, tipo: "venta", sucursal: "Sucursal Litoral", descripcion: "Venta de 5 productos tecnológicos", monto: 2100, fecha: "Hace 22 min", estado: "completed" }
        ];

        // Inicializar la aplicación
        $(document).ready(function() {
            // Inicializar navegación
            initNavigation();
            
            // Inicializar la visualización 3D
            init3DView();
            
            // Cargar transacciones
            loadTransactions();
            
            // Inicializar gráficos
            initCharts();
            
            // Simular transacciones en tiempo real
            simulateLiveTransactions();
            
            // Configurar event listeners
            setupEventListeners();
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
            renderer = new THREE.WebGLRenderer({ antialias: true });
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
            
            // Crear representación de sucursales
            createSucursales();
            
            // Crear transacciones entre sucursales
            createTransactions();
            
            // Iniciar animación
            animate();
            
            // Ajustar al redimensionar ventana
            window.addEventListener('resize', onWindowResize);
        }
        
        // Crear representación de sucursales en 3D
        function createSucursales() {
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
                // Calcular posición en el mapa (escala lat/lng a coordenadas 3D)
                const x = (sucursal.lng + 60) * 1.5;
                const z = (sucursal.lat + 30) * 1.5;
                
                // Tamaño basado en ventas
                const size = 1 + (sucursal.ventas / 5000);
                
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
            label.userData = { x, y, z };
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
        
        // Crear transacciones entre sucursales
        function createTransactions() {
            // Limpiar líneas existentes
            transactionLines.forEach(line => scene.remove(line));
            transactionLines = [];
            
            // Crear algunas transacciones de ejemplo entre sucursales
            for (let i = 0; i < 5; i++) {
                const originIndex = Math.floor(Math.random() * sucursales.length);
                let targetIndex;
                do {
                    targetIndex = Math.floor(Math.random() * sucursales.length);
                } while (targetIndex === originIndex);
                
                const origin = sucursales[originIndex];
                const target = sucursales[targetIndex];
                
                // Calcular posiciones
                const originX = (origin.lng + 60) * 1.5;
                const originZ = (origin.lat + 30) * 1.5;
                const targetX = (target.lng + 60) * 1.5;
                const targetZ = (target.lat + 30) * 1.5;
                
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
                particle.userData = { t, speed: 0.005 };
                
                particles.add(particle);
            }
            
            scene.add(particles);
            transactionLines.push(particles);
            
            // Animar partículas
            particles.userData = { update: () => {
                particles.children.forEach(particle => {
                    particle.userData.t += particle.userData.speed;
                    if (particle.userData.t > 1) particle.userData.t = 0;
                    
                    const position = curve.getPoint(particle.userData.t);
                    particle.position.copy(position);
                });
            }};
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
        
        // Cargar transacciones en el panel
        function loadTransactions() {
            const container = document.getElementById('transactionsContainer');
            container.innerHTML = '';
            
            transacciones.forEach(trans => {
                const transactionEl = document.createElement('div');
                transactionEl.className = `transaction-item ${trans.estado}`;
                
                transactionEl.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <strong>${trans.sucursal}</strong>
                        <small class="text-muted">${trans.fecha}</small>
                    </div>
                    <div>${trans.descripcion}</div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="badge bg-${trans.tipo === 'venta' ? 'success' : trans.tipo === 'transferencia' ? 'info' : 'warning'}">
                            ${trans.tipo}
                        </span>
                        ${trans.monto > 0 ? `<strong>$${trans.monto}</strong>` : ''}
                    </div>
                `;
                
                container.appendChild(transactionEl);
            });
        }
        
        // Inicializar gráficos
        function initCharts() {
            // Gráfico de ventas por sucursal
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: sucursales.map(s => s.nombre),
                    datasets: [{
                        label: 'Ventas',
                        data: sucursales.map(s => s.ventas),
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
            new Chart(productsCtx, {
                type: 'doughnut',
                data: {
                    labels: sucursales.map(s => s.nombre),
                    datasets: [{
                        data: sucursales.map(s => s.productos),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(199, 199, 199, 0.7)',
                            'rgba(83, 102, 255, 0.7)',
                            'rgba(40, 159, 64, 0.7)',
                            'rgba(210, 99, 132, 0.7)'
                        ]
                    }]
                }
            });
        }
        
        // Simular transacciones en tiempo real
        function simulateLiveTransactions() {
            setInterval(() => {
                // Ocasionalmente añadir una nueva transacción
                if (Math.random() > 0.7) {
                    const tipos = ["venta", "transferencia", "devolución"];
                    const sucursalesNames = sucursales.map(s => s.nombre);
                    
                    const nuevaTrans = {
                        id: transacciones.length + 1,
                        tipo: tipos[Math.floor(Math.random() * tipos.length)],
                        sucursal: sucursalesNames[Math.floor(Math.random() * sucursalesNames.length)],
                        descripcion: `Transacción automática #${Math.floor(Math.random() * 1000)}`,
                        monto: Math.random() > 0.3 ? Math.floor(Math.random() * 5000) : 0,
                        fecha: "Ahora mismo",
                        estado: "completed"
                    };
                    
                    transacciones.unshift(nuevaTrans);
                    if (transacciones.length > 10) transacciones.pop();
                    
                    loadTransactions();
                    
                    // Efecto de destello en el badge de en vivo
                    const liveBadge = document.getElementById('liveBadge');
                    liveBadge.classList.add('bg-warning');
                    setTimeout(() => liveBadge.classList.remove('bg-warning'), 500);
                }
            }, 5000);
        }
        
        // Configurar event listeners
        function setupEventListeners() {
            // Manejadores de eventos para los botones de vista
            $('#viewMapBtn').click(function() {
                $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
                $('#viewCubeBtn').removeClass('active').removeClass('btn-primary').addClass('btn-outline-secondary');
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
                
                const intersects = raycaster.intersectObjects(sucursalObjects.filter(obj => obj instanceof THREE.Mesh));
                
                if (intersects.length > 0) {
                    const sucursal = intersects[0].object.userData;
                    showSucursalDetails(sucursal);
                }
            });
            
            // Filtros para la tabla de inventario
            $('#filtroSucursal, #filtroAlerta, #buscarProducto').change(function() {
                const sucursalId = $('#filtroSucursal').val();
                const soloAlertas = $('#filtroAlerta').is(':checked');
                const busqueda = $('#buscarProducto').val().toLowerCase();
                
                $('#tablaInventario tbody tr').each(function() {
                    const row = $(this);
                    const rowSucursal = row.find('td:first').text();
                    const rowProducto = row.find('td:nth-child(2)').text().toLowerCase();
                    const isAlerta = row.hasClass('table-warning');
                    
                    const showRow = 
                        (sucursalId === '' || rowSucursal.includes($('#filtroSucursal option:selected').text())) &&
                        (!soloAlertas || isAlerta) &&
                        (busqueda === '' || rowProducto.includes(busqueda));
                    
                    row.toggle(showRow);
                });
            });
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
            
            // Simular retraso de carga
            setTimeout(() => {
                $('#detailContent').html(`
                    <div class="detail-card">
                        <div class="detail-card-header">
                            Información General
                        </div>
                        <div class="detail-card-body">
                            <p><strong>Ciudad:</strong> ${sucursal.ciudad}</p>
                            <p><strong>Estado:</strong> <span class="badge ${sucursal.activa ? 'bg-success' : 'bg-danger'}">${sucursal.activa ? 'Activa' : 'Inactiva'}</span></p>
                            <p><strong>Ventas Totales:</strong> $${sucursal.ventas.toLocaleString()}</p>
                            <p><strong>Productos en Stock:</strong> ${sucursal.productos}</p>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-card-header">
                            Rendimiento
                        </div>
                        <div class="detail-card-body">
                            <p><strong>Meta de Ventas:</strong> $15,000</p>
                            <div class="progress">
                                <div class="progress-bar ${sucursal.ventas > 15000 ? 'bg-success' : 'bg-warning'}" 
                                     role="progressbar" 
                                     style="width: ${Math.min(100, (sucursal.ventas / 15000) * 100)}%" 
                                     aria-valuenow="${(sucursal.ventas / 15000) * 100}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small>${Math.min(100, ((sucursal.ventas / 15000) * 100).toFixed(1))}% completado</small>
                            
                            <p class="mt-3"><strong>Eficiencia Operativa:</strong> 87%</p>
                            <div class="progress">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 87%" aria-valuenow="87" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-card-header">
                            Transacciones Recientes
                        </div>
                        <div class="detail-card-body">
                            ${transacciones.filter(t => t.sucursal === sucursal.nombre)
                                .slice(0, 3)
                                .map(trans => `
                                    <div class="transaction-item ${trans.estado} mb-2">
                                        <div class="d-flex justify-content-between">
                                            <strong>${trans.tipo}</strong>
                                            <small class="text-muted">${trans.fecha}</small>
                                        </div>
                                        <div>${trans.descripcion}</div>
                                        ${trans.monto > 0 ? `<div class="text-end"><strong>$${trans.monto}</strong></div>` : ''}
                                    </div>
                                `).join('')}
                            ${transacciones.filter(t => t.sucursal === sucursal.nombre).length === 0 ? 
                                '<p class="text-center text-muted">No hay transacciones recientes</p>' : ''}
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <button class="btn btn-primary">Ver Reporte Completo</button>
                    </div>
                `);
            }, 800);
            
            // Abrir el sidenav
            $('#detailSidenav').addClass('open');
        }
    </script>
</body>
</html>