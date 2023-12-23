@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Development (Admin Only)</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Development (Admin Only)</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body px-3">
    <form action="/development/update" method="post">
    @csrf
    <div class="row">
        <div class="col-3">
            <div class="form-group">
              <label>Testing Email</label>
              <input type="email" class="form-control" 
                name="email_testing" value="{{ config('mail.testing_email') }}">
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

@endsection
@section('local-js')
<script>
$(document).ready(function () {
    // set minimal tanggal pengadaan 14 setelah tanggal pengajuan
    $('.requirement_date').val(moment().add(14,'days').format('YYYY-MM-DD'));
    $('.requirement_date').prop('min',moment().add(14,'days').format('YYYY-MM-DD'));
    $('.requirement_date').trigger('change');

    $('.salespoint_select2').change(function() {
        let salespoint_id = $(this).val();
        loadAuthorizationbySalespoint(salespoint_id);
    });

    $('.ticket_type').change(function() {
        if($(this).val() != ""){
            $('.request_type').prop('disabled',false);
        }else{
            $('.request_type').prop('disabled',true);
        }
        $('.request_type').val('');
        $('.request_type').trigger('change');
    });
    
    $('.request_type').change(function() {
        let salespoint_id = $('.salespoint_select2').val();
        let ticket_type = $('.ticket_type').val();
        let request_type = $(this).val();

        // field otorisasi hanya untuk pengadaan baru
        if(request_type == "0") {
            $('.po_field').hide();
            $('.po_select').prop('required', false);
            $('#authorization').val("");
            $('#authorization').prop('disabled', false);
            $('#authorization').prop('required', true);
            loadAuthorizationbySalespoint(salespoint_id);
        }else if(request_type != "") {
            // selain pengadaan baru minta untuk memilih po terkait
            $('.po_field').show();
            $('.po_select').prop('required', true);
            $('#authorization').prop('disabled', true);
            $('#authorization').prop('required', false);
            $('#authorization').val("");
            $('#authorization').trigger('change');
            loadPO(ticket_type,request_type);
        }else{
            $('.po_field').hide();
            $('.po_select').prop('required', false);
            $('#authorization').prop('disabled', true);
            $('#authorization').prop('required', false);
            $('#authorization').val("");
            $('#authorization').trigger('change');
        }
        // kalo pilihan replace sama baru minta input nama vendor 
        // if(request_type == "1" || request_type == "0"){
        //     $('.new_vendor_field').show();
        //     $('.new_vendor_input').prop('required',true);
        // }else{
        //     $('.new_vendor_field').hide(); 
        //     $('.new_vendor_input').prop('required',false);
        // }
    });

    $('#authorization').change(function() {
        let list = $(this).find('option:selected').data('list');
        $('#authorization_field').empty();
        if(list !== undefined){
            list.forEach(function(item,index){
                $('#authorization_field').append('<div class="d-flex text-center flex-column mr-3"><div class="font-weight-bold">'+item.sign_as+'</div><div>'+item.employee.name+'</div><div class="text-secondary">('+item.employee_position.name+')</div></div>');
                if(index != list.length -1){
                    $('#authorization_field').append('<div class="mr-3"><i class="fa fa-chevron-right" aria-hidden="true"></i></div>');
                }
            });
        }
    });
    
    $('#createticket').click(function(event){
        // let vendor_item_list = $('.vendor_item_list');
        // if($('.request_type').val() == "1" || $('.request_type').val() == "0"){
        //     if(vendor_item_list.length < 1){
        //         alert('Minimal 1 vendor');
        //         return;
        //     }
        // }
        // $('#additionaldata').empty();
        // vendor_item_list.each(function(index, el) {
        //     $('#additionaldata').append('<input type="hidden" name="vendor['+index+'][id]" value="'+$(el).data('id')+'">');
        //     $('#additionaldata').append('<input type="hidden" name="vendor['+index+'][vendor_id]" value="'+$(el).data('vendor_id')+'">');
        //     $('#additionaldata').append('<input type="hidden" name="vendor['+index+'][name]" value="'+$(el).data('name')+'">');
        //     $('#additionaldata').append('<input type="hidden" name="vendor['+index+'][sales]" value="'+$(el).data('sales')+'">');
        //     $('#additionaldata').append('<input type="hidden" name="vendor['+index+'][phone]" value="'+$(el).data('phone')+'">');
        // });
        $('#additionalform').submit();
    });
});

function loadAuthorizationbySalespoint(salespoint_id){
    $('#authorization').find('option[value!=""]').remove();
    $('#authorization').prop('disabled', true);
    if(salespoint_id == ""){
        return;
    } 
    $.ajax({
        type: "get",
        url: '/getAuthorization/'+salespoint_id,
        success: function (response) {
            let data = response.data;
            if(data.length == 0){
                alert('Matriks Approval Barang Jasa tidak tersedia untuk salespoint yang dipilih, silahkan mengajukan Matriks Approval ke admin');
                return;
            }
            data.forEach(item => {
                let namelist = item.list.map(a => a.employee_name);
                let option_text = '<option value="'+item.id+'">'+namelist.join(" -> ")+'</option>';
                $('#authorization').append(option_text);
            });
            $('#authorization').val("");
            $('#authorization').trigger('change');
            $('#authorization').prop('disabled', false);
        },
        error: function (response) {
            alert('load data failed. Please refresh browser or contact admin');
            $('#authorization').find('option[value!=""]').remove();
            $('#authorization').prop('disabled', true);
        },
        complete: function () {
            $('#authorization').val("");
            $('#authorization').trigger('change');
            $('#authorization').prop('disabled', false);
        }
    });
}

function loadPO(ticket_type,request_type){
    let requestdata = {
        ticket_type: ticket_type,
        request_type: request_type,
        type: 'additional'
    };
    $('.po_select').empty();
    $('.po_select').append('<option value="">-- Pilih PO --</option>');
    $.ajax({
        type: "get",
        url: "/getActivePO",
        data: requestdata,
        success: function (response) {
            let data = response.data;
            data.forEach(item => {
                let option_text = '<option data-vendor="'+item.vendor_name+'" value="'+item.po_number+'">'+item.po_number+' ('+item.salespoint_name+') - '+item.vendor_name+'</option>';
                $('.po_select').append(option_text);
            });
            $('.po_select').val("");
            $('.po_select').prop('disabled', false);
        },
        error: function (response) {
            $('.po_select').prop('disabled', true);
            alert('error: ',response);
        },
        complete: function () {
            $('.po_select').trigger('change');
        }
    });
}

// add vendor
function addVendor(el){
    let select_vendor = $('.select_vendor');
    let table_vendor = $('.table_vendor');
    let id = select_vendor.find('option:selected').data('id');
    let name = select_vendor.find('option:selected').data('name');
    let code = select_vendor.find('option:selected').data('code');
    let salesperson = select_vendor.find('option:selected').data('salesperson');
    if(select_vendor.val()==""){
        alert('Harap pilih vendor terlebih dulu');
        return;
    }
    if($('.vendor_item_list').length>2){
        alert('Maksimal 3 vendor');
        return;
    }
    table_vendor.find('tbody').append('<tr class="vendor_item_list" data-vendor_id="'+id+'"><td>'+code+'</td><td>'+name+'</td><td>'+salesperson+'</td><td>-</td><td>Terdaftar</td><td><i class="fa fa-trash text-danger" onclick="removeVendor(this)" aria-hidden="true"></i></td></tr>');
    select_vendor.val('');
    select_vendor.trigger('change');
    tableVendorRefreshed(select_vendor);
}

function addOTVendor(el){
    let vendor_name = $('.ot_vendor_name');
    let vendor_sales = $('.ot_vendor_sales');
    let vendor_phone = $('.ot_vendor_phone');
    let table_vendor = $('.table_vendor');
    if(vendor_name.val()==""){
        alert('Nama Vendor tidak boleh kosong');
        return;
    }
    if(vendor_sales.val()==""){
        alert('Sales Vendor tidak boleh kosong');
        return;
    }
    if(vendor_phone.val()==""){
        alert('Telfon Vendor tidak boleh kosong');
        return;
    }
    if($('.vendor_item_list').length>1){
        alert('Maksimal 2 vendor');
        return;
    }
    table_vendor.find('tbody').append('<tr class="vendor_item_list" data-name="'+vendor_name.val()+'" data-sales="'+vendor_sales.val()+'" data-phone="'+vendor_phone.val()+'"><td>-</td><td>'+vendor_name.val()+'</td><td>'+vendor_sales.val()+'</td><td>'+vendor_phone.val()+'</td><td>One Time</td><td><i class="fa fa-trash text-danger" onclick="removeVendor(this)" aria-hidden="true"></i></td></tr>'
    );
    vendor_name.val('');
    vendor_sales.val('');
    vendor_phone.val('');
    tableVendorRefreshed(vendor_name);
}

// remove vendor
function removeVendor(el) {
    let tr = $(el).closest('tr');
    tr.remove();
    tableVendorRefreshed();
}

// table on refresh
function tableVendorRefreshed(current_element) {
    let table_vendor = $('.table_vendor');

    let row_count = 0;
    table_vendor.find('tbody tr').not('.empty_row').each(function () {
        row_count++;
    });
    if (row_count > 0) {
        table_vendor.find('.empty_row').remove();
    } else {
        table_vendor.find('tbody').append('<tr class="empty_row text-center"><td colspan="6">Vendor belum dipilih</td></tr>');
    }
    if($('.vendor_item_list').length<2){
        $('.vendor_ba_field').show();
    }else{
        $('.vendor_ba_field').hide();
    }
}
</script>
@endsection
