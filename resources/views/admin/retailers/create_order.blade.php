@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Place Order</h2>

    <form action="{{ route('retailers.orders.store') }}" method="POST">
        @csrf

        <div id="items">
            <div class="order-item row mb-2">
                <div class="col-md-5">
                    <input name="items[0][product_name]" class="form-control" placeholder="Product name" required>
                </div>
                <div class="col-md-2">
                    <input name="items[0][quantity]" type="number" class="form-control" value="1" min="1" required>
                </div>
                <div class="col-md-3">
                    <input name="items[0][unit_price]" type="number" class="form-control" step="0.01" value="0" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-item">X</button>
                </div>
            </div>
        </div>

        <button type="button" id="addItem" class="btn btn-secondary mb-3">Add Item</button>
        <div class="mb-3">
            <textarea name="notes" class="form-control" placeholder="Notes (optional)"></textarea>
        </div>

        <button class="btn btn-success">Place Order</button>
    </form>
</div>

<script>
let idx = 1;
document.getElementById('addItem').addEventListener('click', function(){
    const tpl = document.querySelector('.order-item').cloneNode(true);
    tpl.querySelectorAll('input').forEach(function(inp){
        const name = inp.getAttribute('name').replace(/\d+/, idx);
        inp.setAttribute('name', name);
        inp.value = (inp.type === 'number') ? (inp.name.indexOf('quantity')>-1 ? 1 : 0) : '';
    });
    document.getElementById('items').appendChild(tpl);
    idx++;
});

document.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('remove-item')){
        const node = e.target.closest('.order-item');
        if(document.querySelectorAll('.order-item').length > 1) node.remove();
    }
});
</script>
@endsection
