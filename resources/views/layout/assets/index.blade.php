@extends ('admin.admin_master')
@section('title', 'Saacid - Fixed Assets ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Asset List</h4>
<h6>Manage your Assets</h6>
</div>
<div class="page-btn">
<a href="{{route('asset.add')}}" class="btn btn-added"><img src="assets/img/icons/plus.svg" alt="img">Add Asset</a>
</div>
</div>

@if (session('status'))
    <div class="toast-container">
        <div class="toast-message success">
            <div class="toast-icon">
                <i data-lucide="circle-check" class="icon-checkmark"></i> <!-- Success checkmark icon -->
            </div>
            <div class="toast-content">
                <strong>Success!</strong>
                <p>{{ session('status') }}</p>
            </div>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="toast-container">
        <div class="toast-message error">
            <div class="toast-icon">
                <i data-lucide="circle-alert" class="icon-error"></i> <!-- Error exclamation mark icon -->
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
<input type="text" id="name" placeholder="Filter By Name">
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<input type="text" id="type" placeholder="Filter By Type">
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<input type="date" class="form-control" id="startDate" >
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<input type="date" class="form-control" id="endDate" >
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
<table class="table" id="assetTable">
<thead>
<tr>
<th>#</th>
<th>Asset Name</th>
<th>Type</th>
<th>Amount</th>
<th>Description</th>
<th>Action</th>
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
        if (!navigator.onLine && window.StoreManagementFinanceModule) {
            window.StoreManagementFinanceModule.mountListPage('assets');
            return;
        }

    // Initialize the DataTable
    let table = $('#assetTable').DataTable({
        processing: true,
        serverSide: true,
        lengthMenu: [10, 25, 50],
        ajax: {
            url: "{{ route('asset') }}",
            type: 'GET',
            data: function (d) {
                d.name = $('#name').val();
                d.type = $('#type').val();
                d.startDate = $('#startDate').val();
                d.endDate = $('#endDate').val();
            },
            dataSrc: function (json) {
                if (window.StoreManagementFinanceModule) {
                    window.StoreManagementFinanceModule.cacheList('assets', json.data || []);
                }
                console.log("AJAX response received:", json);
                return json.data;
            },
            error: function (xhr, error, thrown) {
                console.error("AJAX Error:", xhr.responseText);
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'type', name: 'type' },
            { data: 'amount', name: 'amount' },
            { data: 'description', name: 'description' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let deleteUrl = `{{ url('asset/asset') }}/${data}`;
                    return `
                       <form id="deleteForm-${data}" action="${deleteUrl}" method="POST" style="display:inline;">
                           @csrf
                           @method('DELETE')
                           <button style="float:right !important;" type="button" class="btn btn-rounded btn-sm bg-outline-light me-2" onclick="confirmDelete(${data})">
                               <i data-lucide="trash-2"></i>
                           </button>
                       </form>`;
                }
            }
        ]
    });

    // Filter search button click
    $('#searchBtn').click(function() {
        table.draw();
    });
});

function confirmDelete(catId) {
        // Trigger SweetAlert
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // If confirmed, submit the form
                document.getElementById('deleteForm-' + catId).submit();
            }
        });
    }
</script>
@endsection
