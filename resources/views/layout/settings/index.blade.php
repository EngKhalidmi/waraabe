@extends ('admin.admin_master')
@section('title', 'Saacid - Header & Merchant Settings')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Header & Merchant Settings</h4>
                    <h6>Manage Report & Invoice Header Phone Numbers & Merchants (ZAAD, EDAHAB)</h6>
                </div>
            </div>

            @if (session('status'))
                <div class="toast-container mb-3">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title text-white mb-0"><i class="fas fa-cog me-2"></i>Header & Contact Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-12">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $settings->company_name) }}" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Station / Address Info</label>
                                    <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $settings->company_address) }}">
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-12">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Phone Number 1 (Primary)</label>
                                    <input type="text" name="phone1" class="form-control" placeholder="e.g. +252 63 7044460" value="{{ old('phone1', $settings->phone1) }}" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Phone Number 2 (Secondary)</label>
                                    <input type="text" name="phone2" class="form-control" placeholder="e.g. +252 63 4445566" value="{{ old('phone2', $settings->phone2) }}">
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-12">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">ZAAD Merchant Number</label>
                                    <input type="text" name="zaad" class="form-control" placeholder="e.g. 51234" value="{{ old('zaad', $settings->zaad) }}">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">EDAHAB Merchant Number</label>
                                    <input type="text" name="edahab" class="form-control" placeholder="e.g. 61234" value="{{ old('edahab', $settings->edahab) }}">
                                </div>
                            </div>

                            <div class="col-md-12 mt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i> Save Header Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
