import sys

file_path = r'c:\wamp64\www\prs\resources\views\admin\orders\retailers\index.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

start_str = "            // --- Show Logic ---"
end_str = "                $('#showOrderModal').modal('show');\n            });"

start_idx = content.find(start_str)
end_idx = content.find(end_str, start_idx) + len(end_str)

if start_idx == -1 or end_idx < len(end_str):
    print("Could not find start or end string.")
else:
    replacement = '''            // --- Show Logic ---
            $(document).on('click', '.view-btn', function () {
                let row = $(this).data('row');
                $('#modalOrderCode').text('#' + row.order_code);
                
                let detailsHtml = `
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3"><i class="fa fa-shop me-2"></i>Retailer Info</h6>
                                    <h5 class="fw-bold text-dark mb-1">${row.retailer_shop || row.retailer_name}</h5>
                                    ${row.retailer_shop ? `<div class="text-muted small mb-2"><i class="fa fa-user me-2"></i>${row.retailer_name}</div>` : ''}
                                    <div class="d-flex align-items-center mb-1"><i class="fa fa-phone text-muted me-2" style="width: 16px;"></i> <span>${row.retailer_phone || 'N/A'}</span></div>
                                    <div class="d-flex align-items-start"><i class="fa fa-map-marker text-muted me-2 mt-1" style="width: 16px;"></i> <span class="text-wrap">${row.retailer_address || 'N/A'}</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3"><i class="fa fa-building me-2"></i>Distributor Info</h6>
                                    <h5 class="fw-bold text-dark mb-2">${row.distributor_name || 'N/A'}</h5>
                                    <div class="d-flex align-items-center mb-1"><i class="fa fa-phone text-muted me-2" style="width: 16px;"></i> <span>${row.distributor_phone || 'N/A'}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="py-3 px-4">Product</th>
                                            <th class="py-3 px-4 text-center">Batch/Exp</th>
                                            <th class="py-3 px-4 text-center">Qty</th>
                                            <th class="py-3 px-4 text-end">Price</th>
                                            <th class="py-3 px-4 text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                `;

                (row.items || []).forEach(function (i) {
                    let name = i.product_name || i.name || '-';
                    let qty = i.quantity || i.qty || 0;
                    let unitPrice = parseFloat(i.unit_price || 0);
                    let totalAmt = parseFloat(i.total_amount || i.total || (i.unit_price ? (i.unit_price * qty) : 0));
                    
                    let batchHtml = '-';
                    if (i.batches && i.batches.length > 0) {
                        batchHtml = i.batches.map(b => `<div class="small"><span class="badge bg-soft-primary text-primary px-1 py-0 me-1">${b.batch_no}</span><span class="text-muted small">${b.expiry_date}</span></div>`).join('');
                    }

                    detailsHtml += `
                        <tr>
                            <td class="py-3 px-4">
                                <div class="fw-bold text-dark">${name}</div>
                            </td>
                            <td class="py-3 px-4 text-center">${batchHtml}</td>
                            <td class="py-3 px-4 text-center"><span class="badge bg-soft-primary text-primary px-2 py-1">${qty} ${i.unit || ''}</span></td>
                            <td class="py-3 px-4 text-end">₹${unitPrice.toFixed(2)}</td>
                            <td class="py-3 px-4 text-end fw-bold text-primary">₹${totalAmt.toFixed(2)}</td>
                        </tr>
                    `;
                });

                detailsHtml += `
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="4" class="text-end py-3 px-4 text-uppercase fw-bold text-muted">Grand Total:</td>
                                            <td class="py-3 px-4 text-end fw-bold text-success fs-5">₹${parseFloat(row.total_amount).toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="bg-light rounded p-3 h-100">
                                <h6 class="text-muted fw-bold text-uppercase mb-2">Order Status</h6>
                                <p class="mb-0 fs-5"><span class="badge bg-secondary">${row.status}</span></p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="bg-light rounded p-3 h-100">
                                <h6 class="text-muted fw-bold text-uppercase mb-2">Payment Status</h6>
                                <p class="mb-0 fs-5"><span class="badge ${row.payment_status === 'paid' ? 'bg-success' : 'bg-warning'}">${(row.payment_status || 'Pending').toUpperCase()}</span></p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="bg-light rounded p-3 h-100">
                                <h6 class="text-muted fw-bold text-uppercase mb-2">Order Timeline</h6>
                                <div class="d-flex align-items-center"><i class="fa fa-calendar-alt text-muted me-2"></i> <strong>${row.placed_at || 'N/A'}</strong></div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#showOrderContent').html(detailsHtml);
                $('#showOrderModal').modal('show');
            });'''
            
    new_content = content[:start_idx] + replacement + content[end_idx:]
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Successfully replaced.")
