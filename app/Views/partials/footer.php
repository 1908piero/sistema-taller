</div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        // 1. Inicializar DataTables
        if ($('#datatable').length) {
            $('#datatable').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                order: [[0, 'desc']],
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', className: 'btn btn-success btn-sm', text: '<i class="fa-solid fa-file-excel"></i> Excel' },
                    { extend: 'pdfHtml5', className: 'btn btn-danger btn-sm', text: '<i class="fa-solid fa-file-pdf"></i> PDF' },
                    { extend: 'print', className: 'btn btn-secondary btn-sm', text: '<i class="fa-solid fa-print"></i> Imprimir' }
                ]
            });
        }

        // 2. Inicializar Select2 (Buscadores)
        // Aplicamos a cualquier select con clase 'select2'
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Seleccione una opción',
            language: {
                noResults: function() { return "No se encontraron resultados"; }
            }
        });

    });
    
    // Arreglo para que Select2 funcione dentro de Modals de Bootstrap 5
    document.addEventListener('shown.bs.modal', function (e) {
        $(e.target).find('.select2').each(function () {
            $(this).select2({ dropdownParent: $(e.target), theme: 'bootstrap-5', width: '100%' });
        });
    });
</script>

</body>
</html>