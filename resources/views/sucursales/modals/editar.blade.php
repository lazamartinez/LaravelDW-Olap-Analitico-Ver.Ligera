<div class="modal fade" id="editarSucursalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="formEditarSucursal">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Sucursal</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre_editar" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre_editar" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="direccion_editar" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion_editar" name="direccion" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ciudad_editar" class="form-label">Ciudad</label>
                            <input type="text" class="form-control" id="ciudad_editar" name="ciudad" required>
                        </div>
                        <div class="col-md-6">
                            <label for="pais_editar" class="form-label">País</label>
                            <input type="text" class="form-control" id="pais_editar" name="pais" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
