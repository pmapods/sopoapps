@extends('Layout.app')
@section('local-css')
<style>
    .box {
        box-shadow: 0px 1px 2px rgba(0, 0, 0,0.25);
        border : 1px solid;
        border-color: gainsboro;
        border-radius: 0.5em;
    }
    .brand_list{
        font-size: 15px !important;
        padding-top: 0.4em !important;
        padding-bottom: 0.4em !important;
        margin-right: 0.4em !important;
        margin-bottom: 0.4em !important;
    }
    .brand_list .brand_remove{
        cursor: pointer;
        margin-left: 10px;
    }
    .type_list{
        font-size: 15px !important;
        padding-top: 0.4em !important;
        padding-bottom: 0.4em !important;
        margin-right: 0.4em !important;
        margin-bottom: 0.4em !important;
    }
    .type_list .type_remove{
        cursor: pointer;
        margin-left: 10px;
    }
</style>
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">HO Budget</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item active">HO Budget</li>
                </ol>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-1">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBudgetModal">
                Tambah
            </button>
            <select class="ml-2" id="budget_type_select">
                <option value="budgetpricing">Budget Pricing</option>
                <option value="hobudget">HO Budget</option>
                <option value="maintenancebudget">Maintenance Budget</option>
            </select>
        </div>
    </div>
</div>
<div class="content-body px-4">
    <div class="table-responsive">
        <table id="budgetDT" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr>
                    <th>#</th>
                    <th width="8%">Kode</th>
                    <th>Kategori</th>
                    <th>Nama</th>
                    <th>Jenis IT</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $count = 1;
                @endphp
                @foreach ($budgets as $key => $budget)
                    <tr data-budget="{{$budget}}" @if ($budget->deleted_at) class="table-danger" @endif>
                        <td>{{$count++}}</td>
                        <td>{{$budget->code}}</td>
                        <td>{{$budget->category_name}}</td>
                        <td>{{$budget->name}}</td>
                        <td class="text-nowrap">
                            {{ ($budget->isIT) ? "IT" : "Non IT" }}
                            @if ($budget->isIT)
                                <br>
                                <span class="small font-weight-bold">{{ $budget->IT_alias }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addBudgetModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="/hobudget/add" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Budget</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Kategori</label>
                                <select class="form-control pricing_category" name="category" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($budget_categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Nama</label>
                                <input type="text" class="form-control" name="name" placeholder="Masukkan nama budget" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Jenis IT</label>
                                <select class="form-control" name="isIT" required>
                                    <option value="">-- Pilih Jenis IT --</option>
                                    <option value="1">IT</option>
                                    <option value="0">Non IT</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-group">
                                  <label class="required_field">IT Alias</label>
                                  <input type="text"
                                    class="form-control" name="IT_alias" placeholder="Alias IT" disabled required>
                                  <small class="form-text text-danger">*wajib untuk pilihan item IT</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Tambah Budget</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="detailBudgetModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="/hobudget/update" method="post" enctype="multipart/form">
            @csrf
            <input type="hidden" name="id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Budget</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Kategori</label>
                                <select class="form-control" name="category" disabled>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($budget_categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Nama</label>
                                <input type="text" class="form-control" name="name" placeholder="Masukkan nama budget" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Jenis IT</label>
                                <select class="form-control" name="isIT" required>
                                    <option value="">-- Pilih Jenis IT --</option>
                                    <option value="1">IT</option>
                                    <option value="0">Non IT</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-group">
                                  <label class="required_field">IT Alias</label>
                                  <input type="text"
                                    class="form-control" name="IT_alias" placeholder="Alias IT" disabled required>
                                  <small class="form-text text-danger">*wajib untuk pilihan item IT</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger" onclick="deleteBudget(this)" id="deletebudgetbutton">Hapus Budget</button>
                    <button type="submit" class="btn btn-primary" id="updatebudgetbutton">Perbarui Budget</button>
                </div>
            </div>
        </form>
        <form action="/hobudget/delete" method="post" id="deleteform">
            @csrf
            <input type="hidden" name="id">
        </form>
    </div>
</div>

@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        var table = $('#budgetDT').DataTable(datatable_settings);
        $('#budgetDT tbody').on('click', 'tr', function () {
            let modal = $('#detailBudgetModal');
            let data =  $(this).data('budget');
            let id = modal.find('input[name="id"]');
            let category = modal.find('select[name="category"]');
            let name = modal.find('input[name="name"]');
            let isIT = modal.find('select[name="isIT"]');
            let IT_alias = modal.find('input[name="IT_alias"]');
            id.val(data['id']);
            name.val(data['name']);
            category.val(data['ho_budget_category_id']);
            if(data.deleted_at != null){
                id.prop('disabled',true);
                name.prop('disabled',true);
                $("#deletebudgetbutton").prop('disabled',true);
                $("#updatebudgetbutton").prop('disabled',true);
            }else{
                id.prop('disabled',false);
                name.prop('disabled',false);
                $("#deletebudgetbutton").prop('disabled',false);
                $("#updatebudgetbutton").prop('disabled',false);
            }
            isIT.val(data['isIT']);
            isIT.trigger('change');
            if(data['IT_alias']){
                IT_alias.val(data['IT_alias']);
            }else{
                IT_alias.val("");
            }
            modal.modal('show');
        });

        let path = window.location.pathname.split('/');
        $('#budget_type_select').val(path[1]);
        $('#budget_type_select').trigger('change');
        $('#budget_type_select').change(function(){
            window.location.href = "/"+$(this).val();
        });
        
        $('select[name="isIT"]').change(function() {
            let closestmodal = $(this).closest('.modal');
            closestmodal.find('input[name="IT_alias"]').val('');
            if($(this).val() == true){
                closestmodal.find('input[name="IT_alias"]').prop('disabled', false);
            }else{
                closestmodal.find('input[name="IT_alias"]').prop('disabled', true);
            }
        });
    });
    function deleteBudget(){
        if (confirm('Budget yang dihapus tidak dapat dikembalikan lagi. Lanjutkan?')) {
            $('#deleteform').submit();
        }
    }
    
</script>
@endsection
