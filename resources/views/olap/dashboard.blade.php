@extends('layouts.app')

@section('title', 'Dashboard OLAP')
@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Ventas Hoy</h5>
                <p class="card-text h2">${{ number_format($ventasHoy, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Ventas Mes</h5>
                <p class="card-text h2">${{ number_format($ventasMes, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Ganancia Total</h5>
                <p class="card-text h2">${{ number_format($gananciaTotal, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Sucursales Activas</h5>
                <p class="card-text h2">{{ $sucursalesActivas }}/{{ $totalSucursales }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Mapa de Sucursales</h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary active" id="btnMapaTodos">Todas</button>
                    <button class="btn btn-sm btn-outline-success" id="btnMapaActivas">Activas</button>
                    <button class="btn btn-sm btn-outline-danger" id="btnMapaInactivas">Inactivas</button>
                </div>
            </div>
            <div class="card-body p-0" style="height: 500px;">
                <div id="mapa-sucursales" style="height: 100%; width: 100%;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5>Transacciones en Tiempo Real</h5>
            </div>
            <div class="card-body overflow-auto" style="max-height: 460px;">
                <div id="transacciones-live">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Ventas por Sucursal</h5>
            </div>
            <div class="card-body">
                <canvas id="ventasSucursalChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Top Productos</h5>
            </div>
            <div class="card-body">
                <canvas id="topProductosChart"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Leaflet MarkerCluster -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<script>
$(document).ready(function() {
    // Inicializar el mapa
    const map = L.map('mapa-sucursales').setView([-34.6037, -58.3816], 4); // Coordenadas de Argentina
    
    // Añadir capa de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Grupo de clusters
    const markers = L.markerClusterGroup();
    
    // Iconos personalizados
    const iconActivo = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    const iconInactivo = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    // Cargar sucursales
    function cargarSucursales(filtro = 'todas') {
        markers.clearLayers();
        
        $.get('/api/sucursales', function(sucursales) {
            sucursales.forEach(sucursal => {
                if (filtro === 'todas' || 
                   (filtro === 'activas' && sucursal.activa) || 
                   (filtro === 'inactivas' && !sucursal.activa)) {
                    
                    const marker = L.marker(
                        [sucursal.latitud || -34.6037, sucursal.longitud || -58.3816], 
                        {
                            icon: sucursal.activa ? iconActivo : iconInactivo
                        }
                    ).bindPopup(`
                        <b>${sucursal.nombre}</b><br>
                        <small>${sucursal.direccion}, ${sucursal.ciudad}</small><br>
                        Estado: ${sucursal.activa ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-danger">Inactiva</span>'}<br>
                        Ventas hoy: $${sucursal.ventas_hoy || 0}<br>
                        <button class="btn btn-sm btn-info mt-2 w-100" onclick="verDetalleSucursal(${sucursal.id})">Ver Detalle</button>
                    `);
                    
                    markers.addLayer(marker);
                }
            });
            
            map.addLayer(markers);
            
            // Ajustar zoom para mostrar todos los marcadores
            if (markers.getLayers().length > 0) {
                map.fitBounds(markers.getBounds());
            }
        });
    }

    // Filtros del mapa
    $('#btnMapaTodos').click(function() {
        $(this).addClass('active').siblings().removeClass('active');
        cargarSucursales('todas');
    });

    $('#btnMapaActivas').click(function() {
        $(this).addClass('active').siblings().removeClass('active');
        cargarSucursales('activas');
    });

    $('#btnMapaInactivas').click(function() {
        $(this).addClass('active').siblings().removeClass('active');
        cargarSucursales('inactivas');
    });

    // Cargar transacciones en tiempo real
    function cargarTransacciones() {
        $.get('/api/transacciones-recientes', function(transacciones) {
            let html = '';
            
            if (transacciones.length === 0) {
                html = '<div class="alert alert-info">No hay transacciones recientes</div>';
            } else {
                transacciones.forEach(trans => {
                    html += `
                    <div class="card mb-2 border-start border-${trans.tipo === 'venta' ? 'success' : 'info'} border-3">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between">
                                <strong>Sucursal: ${trans.sucursal}</strong>
                                <small class="text-muted">${trans.fecha}</small>
                            </div>
                            <div>${trans.descripcion}</div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="badge bg-${trans.tipo === 'venta' ? 'success' : 'info'}">${trans.tipo}</span>
                                <strong>$${trans.monto}</strong>
                            </div>
                        </div>
                    </div>
                    `;
                });
            }
            
            $('#transacciones-live').html(html);
            
            // Efecto de animación para nuevas transacciones
            $('#transacciones-live .card').first().hide().slideDown();
        });
    }

    // Cargar datos iniciales
    cargarSucursales();
    cargarTransacciones();

    // Actualizar datos cada 30 segundos
    setInterval(cargarTransacciones, 30000);
    setInterval(cargarSucursales, 60000);

    // Gráficos (se mantienen igual que antes)
    const sucursalesData = @json($sucursalesVentas);
    const ventasSucursalCtx = document.getElementById('ventasSucursalChart').getContext('2d');
    new Chart(ventasSucursalCtx, {
        type: 'bar',
        data: {
            labels: sucursalesData.map(item => item.sucursal),
            datasets: [{
                label: 'Ventas',
                data: sucursalesData.map(item => item.total_ventas),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
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

    const topProductosData = @json($productosPopulares);
    const topProductosCtx = document.getElementById('topProductosChart').getContext('2d');
    new Chart(topProductosCtx, {
        type: 'doughnut',
        data: {
            labels: topProductosData.map(item => item.producto),
            datasets: [{
                data: topProductosData.map(item => item.total_vendido),
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
});

// Función global para ver detalles de sucursal
function verDetalleSucursal(id) {
    window.location.href = `/sucursales/${id}`;
}
</script>
@endsection
