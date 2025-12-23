<script src="{{ asset('admin/assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/icons/feather-icon/feather.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
<script src="{{ asset('admin/assets/js/scrollbar/simplebar.js') }}"></script>
<script src="{{ asset('admin/assets/js/scrollbar/custom.js') }}"></script>
<script src="{{ asset('admin/assets/js/config.js') }}"></script>
<script src="{{ asset('admin/assets/js/sidebar-menu.js') }}"></script>
<script src="{{ asset('admin/assets/js/sidebar-pin.js') }}"></script>
<script src="{{ asset('admin/assets/js/slick/slick.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/slick/slick.js') }}"></script>
<script src="{{ asset('admin/assets/js/header-slick.js') }}"></script>
<script src="{{ asset('admin/assets/js/chart/apex-chart/apex-chart.js') }}"></script>
<script src="{{ asset('admin/assets/js/chart/apex-chart/stock-prices.js') }}"></script>
<script src="{{ asset('admin/assets/js/range-slider/rSlider.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/rangeslider/rangeslider.js') }}"></script>
<script src="{{ asset('admin/assets/js/prism/prism.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/clipboard/clipboard.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/counter/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/counter/jquery.counterup.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/counter/counter-custom.js') }}"></script>
<script src="{{ asset('admin/assets/js/custom-card/custom-card.js') }}"></script>
<script src="{{ asset('admin/assets/js/calendar/fullcalender.js') }}"></script>
<script src="{{ asset('admin/assets/js/calendar/custom-calendar.js') }}"></script>
<script src="{{ asset('admin/assets/js/dashboard/dashboard_2.js') }}"></script>
<script src="{{ asset('admin/assets/js/animation/wow/wow.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/script.js') }}"></script>
<script src="{{ asset('admin/assets/js/custom.js') }}"></script>
<!-- Local DataTables commented out to use CDNs as requested
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/jszip.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/pdfmake.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/vfs_fonts.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/dataTables.autoFill.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/dataTables.select.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/buttons.bootstrap5.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/buttons.html5.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/buttons.print.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/dataTables.keyTable.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/dataTables.colReorder.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/dataTables.fixedHeader.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/dataTables.rowReorder.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/datatable/datatable-extension/custom.js') }}"></script>
-->

<!-- DataTables CDNs -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    new WOW().init();

    // Global Toast Function
    function showToast(type, message) {
        const toastContainer = document.getElementById('toastContainer');
        const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-primary');
        const toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = toastHtml;
        const toastEl = tempDiv.firstElementChild;
        toastContainer.appendChild(toastEl);

        const toast = new bootstrap.Toast(toastEl, {
            delay: 3000
        });
        toast.show();

        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
</script>