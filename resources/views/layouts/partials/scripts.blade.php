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
<!-- Flatpickr (Modern Datepicker) -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    new WOW().init();

    // Global Toast Function (Premium SweetAlert2 Toast)
    function showToast(type, message) {
        // Map 'danger' to 'error' for SweetAlert2 compatibility
        const iconType = type === 'danger' ? 'error' : type;
        
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        Toast.fire({
            icon: iconType, // 'success', 'error', 'warning', 'info', 'question'
            html: message
        });
    }

    // --- Global DataTables Button Refinements ---
    $(document).ready(function() {
        if (typeof $.fn.dataTable !== 'undefined' && typeof $.fn.dataTable.Buttons !== 'undefined') {
            // Dynamic Title & Filename helpers
            const getExportTitle = () => {
                let title = $('.card-header h5').first().clone()    // Clone to avoid modifying UI
                    .find('i').remove().end()           // Remove icon
                    .text().trim();
                return title || 'PRS Export';
            };
            const getExportDate = () => new Date().toISOString().slice(0, 10);

            const commonExportOptions = { 
                columns: ':not(:last-child)', // Excludes "Action" column globally
                search: 'applied',
                order: 'applied',
                format: {
                    header: function (data, columnIdx) {
                        // Globally rename first column header to "No." if it's ID-like
                        if (columnIdx === 0) {
                            let text = data.replace(/<[^>]*>/g, '').trim().toLowerCase();
                            if (['id', 'sl no', 'sl.no', '#', 's.no', 'sr no', 'sr. no'].includes(text)) {
                                return 'No.';
                            }
                        }
                        return data.replace(/<[^>]*>/g, '');
                    },
                    body: function (data, row, column, node) {
                        if (typeof data === 'string' && (data.indexOf('<') !== -1 || data.indexOf('&') !== -1)) {
                            // 1. Pre-process: Replace <br> with newlines
                            let html = data.replace(/<br\s*\/?>/gi, '\n');
                            
                            // 2. Use a temporary DOM element to extract CLEAN text
                            // This handles tags inside attributes (like popovers) which regex misses
                            let tempDiv = document.createElement('div');
                            tempDiv.innerHTML = html;
                            
                            // 3. Specifically handle the "Read More" toggle logic if present
                            let preview = tempDiv.querySelector('.preview-content');
                            let toggleBtn = tempDiv.querySelector('.toggle-more-btn');
                            if (preview) preview.remove();
                            if (toggleBtn) toggleBtn.remove();
                            
                            // 4. Extract text
                            let stripped = tempDiv.textContent || tempDiv.innerText || "";
                            
                            // 5. Final cleanup: symbols, special strings, and whitespace
                            stripped = stripped
                                .replace(/[₹\â‚¹]/g, '')
                                .replace(/,/g, '')
                                .replace(/\s*Read More\s*$/gi, '')
                                .replace(/\s*Show Less\s*$/gi, '');
                                
                            return stripped.trim();
                        }
                        return data;
                    }
                }
            };

            // Override ALL default export options for all button types (including shorthands)
            const buttonTypes = ['copy', 'csv', 'excel', 'pdf', 'print', 'copyHtml5', 'csvHtml5', 'excelHtml5', 'pdfHtml5'];
            buttonTypes.forEach(type => {
                if ($.fn.dataTable.ext.buttons[type]) {
                    let config = { 
                        exportOptions: commonExportOptions,
                        title: function() { return getExportTitle(); },
                        filename: function() { return getExportTitle().replace(/\s+/g, '_') + '_' + getExportDate(); }
                    };
                    
                    // Force Landscape for PDF types
                    if (type === 'pdfHtml5' || type === 'pdf') {
                        config.orientation = 'landscape';
                        config.pageSize = 'A4';
                        config.customize = function(doc) {
                            doc.defaultStyle.fontSize = 6.5; // Further reduced from 7
                            doc.styles.tableHeader.fontSize = 7.5; // Further reduced from 8
                            doc.styles.tableHeader.alignment = 'left';
                            
                            // Dynamically set widths: First column ("No.") gets a fixed small width
                            if (doc.content[1] && doc.content[1].table) {
                                let body = doc.content[1].table.body;
                                if (body.length > 0) {
                                    let colCount = body[0].length;
                                    let widths = Array(colCount).fill('*');
                                    widths[0] = 25; // Set first column ("No.") to a tiny fixed width
                                    doc.content[1].table.widths = widths;
                                }
                            }
                            
                            doc.pageMargins = [20, 30, 40, 30]; // L, T, R, B
                            
                            // Center and style the title
                            if (doc.content[0]) {
                                doc.content[0].fontSize = 14;
                                doc.content[0].alignment = 'center';
                                doc.content[0].margin = [0, 0, 0, 12];
                            }
                        };
                    }
                    
                    $.extend(true, $.fn.dataTable.ext.buttons[type], config);
                }
            });

            // Specific Refinement for the 'Print' Button types
            ['print'].forEach(type => {
                if ($.fn.dataTable.ext.buttons[type]) {
                    $.extend(true, $.fn.dataTable.ext.buttons[type], {
                        exportOptions: commonExportOptions,
                        title: function() { return getExportTitle(); },
                        orientation: 'landscape',
                        customize: function (win) {
                            // Enforce Landscape and basic margins via CSS injection
                            var style = $('<style>@page { size: landscape; margin: 1cm !important; } body { padding-right: 1.5cm !important; }</style>');
                            $(win.document.head).append(style);

                            $(win.document.body)
                                .css('font-size', '8pt') // Reduced from 10pt
                                .css('color', '#1f2937')
                                .css('background', '#fff');

                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit')
                                .css('width', '100%')
                                .css('margin-top', '20px');

                            // Minimize "No." column in Print
                            $(win.document.body).find('table th:first-child, table td:first-child')
                                .css('width', '30px')
                                .css('min-width', '30px')
                                .css('text-align', 'center');

                            $(win.document.body).find('h1')
                                .css('font-size', '16pt')
                                .css('text-align', 'center')
                                .css('margin-bottom', '20px')
                                .css('color', '#00497a');
                            
                            // Ensure images are reasonably sized (e.g. logos)
                            $(win.document.body).find('img').css('max-width', '100px');
                        }
                    });
                }
            });
        }
    });
</script>