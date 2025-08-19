@extends('layouts.app')

@section('title', 'Gestión de Inventario')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Inventario de Productos</h5>
        <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#transferirInventarioModal">
            <i class="fas fa-exchange-alt me-2"></i>Transferir
        </button>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <select class="form-select" id="filtroSucursal">
                    <option value="">Todas las sucursales</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
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
                    @foreach($inventarios as $inventario)
                    <tr class="{{ $inventario->cantidad <= $inventario->minimo_stock ? 'table-warning' : '' }}">
                        <td>{{ $inventario->sucursal->nombre }}</td>
                        <td>{{ $inventario->producto->nombre }}</td>
                        <td>{{ $inventario->producto->codigo }}</td>
                        <td>
                            <span class="badge {{ $inventario->cantidad <= $inventario->minimo_stock ? 'bg-danger' : 'bg-success' }}">
                                {{ $inventario->cantidad }}
                            </span>
                        </td>
                        <td>{{ $inventario->minimo_stock }}</td>
                        <td>{{ $inventario->ubicacion ?? 'N/A' }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" data-mdb-toggle="modal" 
                                data-mdb-target="#editarInventarioModal" 
                                data-inventario="{{ json_encode($inventario) }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" data-mdb-toggle="modal" 
                                data-mdb-target="#eliminarInventarioModal" 
                                data-id="{{ $inventario->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $inventarios->links() }}
        </div>
    </div>
</div>

<!-- Modales -->
@include('inventarios.modals.transferir')
@include('inventarios.modals.editar')
@include('inventarios.modals.eliminar')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Llenar modal de edición
    $('#editarInventarioModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const inventario = JSON.parse(button.data('inventario'));
        
        const modal = $(this);
        modal.find('form').attr('action', `/inventarios/${inventario.id}`);
        modal.find('#cantidad').val(inventario.cantidad);
        modal.find('#minimo_stock').val(inventario.minimo_stock);
        modal.find('#ubicacion').val(inventario.ubicacion);
    });

    // Filtros
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
});
</script>
@endsection