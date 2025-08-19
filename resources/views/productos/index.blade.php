@extends('layouts.app')

@section('title', 'Gestión de Productos')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Productos</h5>
        <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#crearProductoModal">
            <i class="fas fa-plus me-2"></i>Nuevo Producto
        </button>
    </div>
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
                    @foreach($productos as $producto)
                    <tr>
                        <td>{{ $producto->codigo }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>${{ number_format($producto->precio, 2) }}</td>
                        <td>
                            <span class="badge {{ $producto->stock < 10 ? 'bg-danger' : 'bg-success' }}">
                                {{ $producto->stock }}
                            </span>
                        </td>
                        <td>{{ $producto->categoria }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" data-mdb-toggle="modal" 
                                data-mdb-target="#editarProductoModal" 
                                data-producto="{{ json_encode($producto) }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" data-mdb-toggle="modal" 
                                data-mdb-target="#eliminarProductoModal" 
                                data-id="{{ $producto->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                            <a href="{{ route('productos.metrics', $producto->id) }}" 
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-chart-line"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $productos->links() }}
        </div>
    </div>
</div>

<!-- Modales -->
@include('productos.modals.crear')
@include('productos.modals.editar')
@include('productos.modals.eliminar')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Llenar modal de edición
    $('#editarProductoModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const producto = JSON.parse(button.data('producto'));
        
        const modal = $(this);
        modal.find('form').attr('action', `/productos/${producto.id}`);
        modal.find('#codigo').val(producto.codigo);
        modal.find('#nombre').val(producto.nombre);
        modal.find('#descripcion').val(producto.descripcion);
        modal.find('#precio').val(producto.precio);
        modal.find('#costo').val(producto.costo);
        modal.find('#stock').val(producto.stock);
        modal.find('#categoria').val(producto.categoria);
        modal.find('#marca').val(producto.marca);
    });
});
</script>
@endsection