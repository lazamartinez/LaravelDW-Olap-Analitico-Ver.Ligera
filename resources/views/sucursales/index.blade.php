@extends('layouts.app')

@section('title', 'Gestión de Sucursales')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Sucursales</h5>
        <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#crearSucursalModal">
            <i class="fas fa-plus me-2"></i>Nueva Sucursal
        </button>
    </div>
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
                    @foreach($sucursales as $sucursal)
                    <tr>
                        <td>{{ $sucursal->id }}</td>
                        <td>{{ $sucursal->nombre }}</td>
                        <td>{{ $sucursal->ciudad }}</td>
                        <td>
                            @if($sucursal->docker_container_id)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" data-mdb-toggle="modal" 
                                data-mdb-target="#editarSucursalModal" 
                                data-sucursal="{{ json_encode($sucursal) }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" data-mdb-toggle="modal" 
                                data-mdb-target="#eliminarSucursalModal" 
                                data-id="{{ $sucursal->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                            @if($sucursal->docker_container_id)
                                <button class="btn btn-sm btn-warning btn-stop-container" 
                                    data-id="{{ $sucursal->id }}">
                                    <i class="fas fa-stop"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-success btn-start-container" 
                                    data-id="{{ $sucursal->id }}">
                                    <i class="fas fa-play"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $sucursales->links() }}
        </div>
    </div>
</div>

<!-- Modales -->
@include('sucursales.modals.crear')
@include('sucursales.modals.editar')
@include('sucursales.modals.eliminar')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Llenar modal de edición
    $('#editarSucursalModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const sucursal = JSON.parse(button.data('sucursal'));
        
        const modal = $(this);
        modal.find('form').attr('action', `/sucursales/${sucursal.id}`);
        modal.find('#nombre').val(sucursal.nombre);
        modal.find('#direccion').val(sucursal.direccion);
        modal.find('#ciudad').val(sucursal.ciudad);
        modal.find('#pais').val(sucursal.pais);
        modal.find('#codigo_postal').val(sucursal.codigo_postal);
        modal.find('#telefono').val(sucursal.telefono);
        modal.find('#email').val(sucursal.email);
        modal.find('#activa').prop('checked', sucursal.activa);
    });

    // Iniciar contenedor Docker
    $('.btn-start-container').click(function() {
        const sucursalId = $(this).data('id');
        $.post(`/sucursales/${sucursalId}/docker/start`, function(response) {
            location.reload();
        }).fail(function() {
            alert('Error al iniciar el contenedor');
        });
    });

    // Detener contenedor Docker
    $('.btn-stop-container').click(function() {
        const sucursalId = $(this).data('id');
        $.post(`/sucursales/${sucursalId}/docker/stop`, function(response) {
            location.reload();
        }).fail(function() {
            alert('Error al detener el contenedor');
        });
    });
});
</script>
@endsection