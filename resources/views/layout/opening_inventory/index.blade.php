@extends ('admin.admin_master')
@section('title', 'Saacid - Opening Inventory ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Opening Inventory List</h4>
<h6>Manage your Opening Inventory</h6>
</div>
<div class="page-btn">
<a href="{{route('opening_inventory.add')}}" class="btn btn-added"><img src="assets/img/icons/plus.svg" alt="img">Add Opening Inventory</a>
</div>
</div>

@if (session('status'))
    <div class="toast-container">
        <div class="toast-message success">
            <div class="toast-icon">
                <i class="icon-checkmark fas fa-check-circle"></i> <!-- Success checkmark icon -->
            </div>
            <div class="toast-content">
                <strong>Success!</strong>
                <p>{{ session('status') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('success'))
    <div class="toast-container">
        <div class="toast-message success">
            <div class="toast-icon">
                <i class="icon-checkmark fas fa-check-circle"></i> <!-- Success checkmark icon -->
            </div>
            <div class="toast-content">
                <strong>Success!</strong>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="toast-container">
        <div class="toast-message error">
            <div class="toast-icon">
                <i class="icon-error fa fa-exclamation-circle"></i> <!-- Error exclamation mark icon -->
            </div>
            <div class="toast-content">
                <strong>Error!</strong>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="toast-container">
        <div class="toast-message error">
            <div class="toast-icon">
                <i class="icon-error fa fa-exclamation-circle"></i> <!-- Error exclamation mark icon -->
            </div>
            <div class="toast-content">
                <strong>Error!</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif


<!-- Search Info -->
<div class="card">
<div class="card-body">
<div class="table-top">
<div class="search-set">
<div class="search-path">
<a class="btn btn-filter" id="filter_search">
<img src="assets/img/icons/filter.svg" alt="img">
<span><img src="assets/img/icons/closes.svg" alt="img"></span>
</a>
</div>
<div class="search-input">
<a class="btn btn-searchset"><img src="assets/img/icons/search-white.svg" alt="img"></a>
</div>
</div>
</div>

<div class="card" id="filter_inputs">
<div class="card-body pb-0">
<div class="row">
<div class="col-lg-3 col-sm-6 col-13">
<div class="form-group">
<input type="text" id="name" placeholder="Filter By Product Name">
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<input type="date" class="form-control" id="startDate" placeholder="Start Date">
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<input type="date" class="form-control" id="endDate" placeholder="End Date">
</div>
</div>
<div class="col-lg-1 col-sm-6 col-12  ms-auto">
<div class="form-group">
<button type="buttom" class="btn btn-filters ms-auto" id="searchBtn"><img src="assets/img/icons/search-whites.svg" alt="img"></button>
</div>
</div>
</div>
</div>
</div>

<div class="table-responsive">
<table class="table" id="openingInventoryTable">
<thead class="">
<tr class="bg-primary">
<th class="text-white">#</th>
<th class="text-white">Product</th>
<th class="text-white">Opening Quantity</th>
<th class="text-white">Opening Date</th>
<th class="text-white">Created At</th>
<th class="text-white">Actions</th>
</tr>
</thead>
<tbody>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<script>
    $(document).ready(function() {

    // Initialize the DataTable
    let table = $('#openingInventoryTable').DataTable({
        processing: true,
        serverSide: true,
        lengthMenu: [10, 25, 50],
        ajax: {
            url: "{{ route('opening_inventory') }}",
            type: 'GET',
            data: function (d) {
                d.name = $('#name').val();
                d.startDate = $('#startDate').val();
                d.endDate = $('#endDate').val();
            },
            dataSrc: function (json) {
                console.log("AJAX response received:", json);
                return json.data;
            },
            error: function (xhr, error, thrown) {
                console.error("AJAX Error:", xhr.responseText);
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'product', name: 'product' },
            { data: 'opening_quantity', name: 'opening_quantity' },
            { data: 'opening_date', name: 'opening_date' },
            { data: 'created_at', name: 'created_at' },
            { 
                data: 'id', 
                name: 'actions', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <a href="{{ url('opening_inventory/opening_inventory') }}/${data}/edit" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ url('opening_inventory/opening_inventory') }}/${data}" method="POST" style="display:inline-block;" id="deleteForm-${data}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(${data})">Delete</button>
                        </form>
                    `;
                }
            }
        ]
    });

    // Filter search button click
    $('#searchBtn').click(function() {
        table.draw();
    });
});

function confirmDelete(id) {
    // Trigger SweetAlert
    Swal.fire({
        title: 'Are you sure?',
        text: "This will also decrement the product quantity!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // If confirmed, submit the form
            document.getElementById('deleteForm-' + id).submit();
        }
    });
}
</script>
@endsection