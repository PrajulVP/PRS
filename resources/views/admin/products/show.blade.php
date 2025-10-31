
@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Product Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Product Code</th>
                                <td>{{ $product->product_code }}</td>
                            </tr>
                            <tr>
                                <th>Product Name</th>
                                <td>{{ $product->product_name }}</td>
                            </tr>
                            <tr>
                                <th>Generic Name</th>
                                <td>{{ $product->generic_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Pack Quantity</th>
                                <td>{{ $product->pack_quantity }}</td>
                            </tr>
                            <tr>
                                <th>Expiry Date</th>
                                <td>{{ \Carbon\Carbon::parse($product->expiry)->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <th>Strip Size</th>
                                <td>{{ $product->strip_size ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Box Size</th>
                                <td>{{ $product->box_size ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Carton Size</th>
                                <td>{{ $product->carton_size ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>HSN Code</th>
                                <td>{{ $product->hsn_code ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Batch No.</th>
                                <td>{{ $product->batch_no }}</td>
                            </tr>
                            <tr>
                                <th>MRP</th>
                                <td>{{ number_format($product->mrp, 2) }}</td>
                            </tr>
                            <tr>
                                <th>PTR</th>
                                <td>{{ number_format($product->ptr, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Taxable Value</th>
                                <td>{{ number_format($product->taxable_value, 2) }}</td>
                            </tr>
                            <tr>
                                <th>GST</th>
                                <td>{{ number_format($product->gst, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Offer</th>
                                <td>{{ number_format($product->offer, 2) ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Discount</th>
                                <td>{{ number_format($product->discount, 2) ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Net Amount</th>
                                <td>{{ number_format($product->net_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Back to Products</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
