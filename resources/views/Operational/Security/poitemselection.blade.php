@extends('Layout.app')
@section('local-css')
<style>
    .table td,
    .table th {
        vertical-align: middle !important;
    }
</style>
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Setting PO ({{ $securityticket->type() }} Security)</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operasional</li>
                    <li class="breadcrumb-item">Purchase Requisition</li>
                    <li class="breadcrumb-item active">Setting PO ({{$securityticket->code}})</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body">
    @php
        $item_name = "Sewa Jasa Security";
    @endphp
    <div class="row mb-3">
        <div class="col-9 row">
            @php
                $sewa_notes = '';
                switch ($securityticket->type()) {
                    case 'Pengadaan':
                        $edit_vendor = true;
                        $show_old_vendor = false;
                        $sewa_notes .= 'PO '.$securityticket->type().' '.$securityticket->salespoint->name."\r\n";
                        break;

                    case 'Pengadaan Lembur':
                        $edit_vendor = true;
                        $show_old_vendor = false;
                        $sewa_notes .= 'PO '.$securityticket->type().' '.$securityticket->salespoint->name."\r\n";
                        break;

                    case 'Perpanjangan':
                        $edit_vendor = false;
                        $show_old_vendor = true;
                        $sewa_notes .= 'PO '.$securityticket->type().' '.($po->no_po_sap ?? $pomanual->po_number)."\r\n";
                        break;

                    case 'Replace':
                        $edit_vendor = true;
                        $show_old_vendor = true;
                        $sewa_notes .= 'PO '.$securityticket->type().' '.($po->no_po_sap ?? $pomanual->po_number)."\r\n";
                        break;

                    case 'End Kontrak':
                        break;
                }
            @endphp
            @if ($show_old_vendor)
                <div class="col">
                    <div class="form-group">
                        <label>Vendor Lama</label>
                        <input type="text" class="form-control" 
                        value="{{ $po->security_ticket->vendor_name ?? $pomanual->vendor_name }}"
                        name="old_vendor" readonly>
                    </div>
                </div>
            @endif
            @if ($edit_vendor)
                <div class="col">
                    <div class="form-group">
                        <label class="required_field">Vendor Baru / Pilihan</label>
                        <input type="text" class="form-control" 
                        name="new_vendor" required>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-3 d-flex flex-column align-items-end justify-content-center">
            @if (in_array($securityticket->type(),['Perpanjangan','Replace']))
                <a href="modalInfo" class="font-weight-bold text-primary" data-toggle="modal" data-target="#modalInfo">
                    Tampilkan Form Evaluasi
                </a>
            @endif
            @if ($po != null)
            <a class="font-weight-bold text-info"
                onclick="window.open('/storage/{{ $po->external_signed_filepath }}')">
                Tampilkan PO Sebelumnya ({{ $po->no_po_sap }})</a>
            @endif
        </div>
    </div>
    <h5>NON PPN</h5>
    <div class="row">
        <table class="table table-bordered" id="table_list">
            <thead>
                <tr class="thead-dark">
                    <th width="1%"></th>
                    <th>Nama Barang</th>
                    <th width="8%">Qty</th>
                    <th width="30%">Harga Satuan (Rp)</th>
                    <th width="30%">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr class="data_row_nonppn">
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input row_check" checked="checked" disabled="disabled">
                        </div>
                    </td>
                    <td>
                        <input type="hidden" name="item_name" value="{{ $item_name }}">
                        {{ $item_name }}
                        <div class="form-group mt-1">
                            <textarea class="form-control form-control-sm" rows="2" style="resize: none"
                                placeholder="notes" name="sewa_notes">{{ $sewa_notes }}</textarea>
                        </div>
                    </td>
                    <td>
                        <input class="form-control autonumber count" onchange="sumRow(this)" type="number"
                            name="sewa_count" value="1" min="1">
                    </td>
                    <td>
                        <input type="text" class="form-control rupiah value" onchange="sumRow(this)">
                    </td>
                    <td class="rupiah_text total">0</td>
                </tr>
                <tr class="data_row_nonppn">
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input row_check">
                        </div>
                    </td>
                    <td>
                        <input type="hidden" name="item_name" value="Prorate Awal Jasa Security">
                            Prorate Awal Jasa Security
                        <div class="form-group mt-1">
                            <textarea class="form-control form-control-sm" rows="2" style="resize: none"
                                placeholder="notes" name="sewa_notes" disabled>{{ $sewa_notes }}</textarea>
                        </div>
                    </td>
                    <td>
                        <input class="form-control autonumber count" onchange="sumRow(this)" type="number"
                            name="sewa_count" value="1" min="1" max="1" disabled>
                    </td>
                    <td>
                        <input type="text" class="form-control rupiah value" disabled onchange="sumRow(this)">
                    </td>
                    <td class="rupiah_text total">0</td>
                </tr>
                <tr class="data_row_nonppn">
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input row_check">
                        </div>
                    </td>
                    <td>
                        <input type="hidden" name="item_name"
                            value="Prorate Akhir Jasa Security">
                        Prorate Akhir Jasa Security
                        <div class="form-group mt-1">
                            <textarea class="form-control form-control-sm" rows="2" style="resize: none"
                                placeholder="notes" name="sewa_notes" disabled>{{ $sewa_notes }}</textarea>
                        </div>
                    </td>
                    <td>
                        <input class="form-control autonumber count" onchange="sumRow(this)" type="number"
                            name="sewa_count" value="1" min="1" max="1" disabled>
                    </td>
                    <td>
                        <input type="text" class="form-control rupiah value" disabled onchange="sumRow(this)">
                    </td>
                    <td class="rupiah_text total">0</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <h5>PPN</h5>
    <div class="row">
        <table class="table table-bordered">
            <thead>
                <tr class="thead-dark">
                    <th width="1%"></th>
                    <th>Nama Barang</th>
                    <th width="8%">Qty</th>
                    <th width="30%">Harga Satuan (Rp)</th>
                    <th width="30%">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr class="data_row_ppn">
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input row_check">
                        </div>
                    </td>
                    <td>
                        <input type="hidden" name="item_name" value="Fee {{ $item_name }}">
                        Fee {{ $item_name }}
                        <div class="form-group mt-1">
                            <textarea class="form-control form-control-sm" rows="2" style="resize: none"
                                placeholder="notes" name="sewa_notes" disabled>{{ $sewa_notes }}</textarea>
                        </div>
                    </td>
                    <td>
                        <input class="form-control autonumber count" onchange="sumRow(this)" type="number"
                            name="sewa_count" value="1" min="1" disabled>
                    </td>
                    <td>
                        <input type="text" class="form-control rupiah value" onchange="sumRow(this)" disabled>
                    </td>
                    <td class="rupiah_text total">0</td>
                </tr>
                <tr class="data_row_ppn">
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input row_check">
                        </div>
                    </td>
                    <td>
                        <input type="hidden" name="item_name"
                            value="Fee Prorate Awal Jasa Security">
                        Fee Prorate Awal Jasa Security
                        <div class="form-group mt-1">
                            <textarea class="form-control form-control-sm" rows="2" style="resize: none"
                                placeholder="notes" name="sewa_notes" disabled>{{ $sewa_notes }}</textarea>
                        </div>
                    </td>
                    <td>
                        <input class="form-control autonumber count" onchange="sumRow(this)" type="number"
                            name="sewa_count" value="1" min="1" max="1" disabled>
                    </td>
                    <td>
                        <input type="text" class="form-control rupiah value" disabled onchange="sumRow(this)">
                    </td>
                    <td class="rupiah_text total">0</td>
                </tr>
                <tr class="data_row_ppn">
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input row_check">
                        </div>
                    </td>
                    <td>
                        <input type="hidden" name="item_name"
                            value="Fee Prorate Akhir Jasa Security">
                        Fee Prorate Akhir Jasa Security
                        <div class="form-group mt-1">
                            <textarea class="form-control form-control-sm" rows="2" style="resize: none"
                                placeholder="notes" name="sewa_notes" disabled>{{ $sewa_notes }}</textarea>
                        </div>
                    </td>
                    <td>
                        <input class="form-control autonumber count" onchange="sumRow(this)" type="number"
                            name="sewa_count" value="1" min="1" max="1"disabled>
                    </td>
                    <td>
                        <input type="text" class="form-control rupiah value" disabled onchange="sumRow(this)">
                    </td>
                    <td class="rupiah_text total">0</td>
                </tr>
            </tbody>
        </table>
    </div>
    <center>
        <button type="button" class="btn btn-primary" onclick="doSubmit()">Submit</button>
    </center>
</div>

<div class="modal fade" id="modalInfo" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-body">
                @switch($securityticket->type())
                    @case('Pengadaan')
                        @break
                    @case('Perpanjangan')
                        @foreach ($securityticket->evaluasi_form as $evaluasiform)
                            @include('Operational.Security.formevaluasi')
                        @endforeach
                        @break
                    @case('Replace')
                        @foreach ($securityticket->evaluasi_form as $evaluasiform)
                            @include('Operational.Security.formevaluasi')
                        @endforeach
                        @break
                    @case('End Kontrak')
                        @foreach ($securityticket->evaluasi_form as $evaluasiform)
                            @include('Operational.Security.formevaluasi')
                        @endforeach
                        @break
                    @default
                        @break
                @endswitch
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
<form action="/setupPO" method="post" id="submitform">
    @csrf
    <input type="hidden" name="security_ticket_id" value="{{$securityticket->id}}">
    <div></div>
</form>

@section('local-js')
<script>
    $(document).ready(function () {
        $('.autonumber').change(function () {
            autonumber($(this));
        });
        $('.row_check').change(function () {
            let notes_field = $(this).closest('tr').find('textarea');
            let price_field = $(this).closest('tr').find('.value');
            let count_field = $(this).closest('tr').find('.count');
            if($(this).prop('checked')){
                notes_field.prop('disabled', false);
                price_field.prop('disabled', false);
                count_field.prop('disabled', false);
            }else{
                notes_field.prop('disabled', true);
                price_field.prop('disabled', true);
                count_field.prop('disabled', true);
            }
        });
    });

    function doSubmit() {
        let data = [];
        let err_messages = [];
        let input_text_append = "";
        let flag = true;
        let count = 0;

        $('.data_row_nonppn').each(function() {
            if($(this).find('input[type="checkbox"]').prop('checked')) {
                let name = $(this).find('input[name="item_name"]').val();
                let notes = $(this).find('textarea[name="sewa_notes"]').val();
                let count = $(this).find('input[name="sewa_count"]').val();
                let value = AutoNumeric.unformat($(this).find('.value').val(), autonum_setting);
                if(value<100){
                    err_messages.push('Minimal Rp 100,- untuk biaya '+name);
                    flag = false;
                }else{
                    input_text_append += '<input type="hidden" name="item_nonppn['+count+'][name]" value="'+name+'">';
                    input_text_append += '<input type="hidden" name="item_nonppn['+count+'][notes]" value="'+notes+'">';
                    input_text_append += '<input type="hidden" name="item_nonppn['+count+'][count]" value="'+count+'">';
                    input_text_append += '<input type="hidden" name="item_nonppn['+count+'][value]" value="'+value+'">';
                    count++;
                }
            }
        });

        $('.data_row_ppn').each(function() {
            if($(this).find('input[type="checkbox"]').prop('checked')) {
                let name = $(this).find('input[name="item_name"]').val();
                let notes = $(this).find('textarea[name="sewa_notes"]').val();
                let count = $(this).find('input[name="sewa_count"]').val();
                let value = AutoNumeric.unformat($(this).find('.value').val(),autonum_setting);
                if(value<100){
                    err_messages.push('Minimal Rp 100,- untuk biaya '+name);
                    flag = false;
                }else{
                    input_text_append += '<input type="hidden" name="item_ppn['+count+'][name]" value="'+name+'">';
                    input_text_append += '<input type="hidden" name="item_ppn['+count+'][notes]" value="'+notes+'">';
                    input_text_append += '<input type="hidden" name="item_ppn['+count+'][count]" value="'+count+'">';
                    input_text_append += '<input type="hidden" name="item_ppn['+count+'][value]" value="'+value+'">';
                    count++;
                }
            }
        });

        let new_vendor = $('input[name="new_vendor"]').val();
        if(new_vendor !== undefined){
            if(new_vendor == ''){
                err_messages.push('Vendor baru harus diisi');
                flag = false;
            }else{
                input_text_append += '<input type="hidden" name="new_vendor" value="'+new_vendor+'">';
            }
        }

        let old_vendor = $('input[name="old_vendor"]').val();
        if(old_vendor !== undefined){
            if(old_vendor == ''){
                err_messages.push('Vendor baru harus diisi');
                flag = false;
            }else{
                input_text_append += '<input type="hidden" name="old_vendor" value="'+old_vendor+'">';
            }
        }

        if(!flag){
            alert(err_messages.join('\n'));
            return;
        }

        $('#submitform div').empty();
        $('#submitform div').append(input_text_append);

        if (confirm('Pastikan semua nilai telah terinput dengan benar ! Lanjutkan ?')) {
            $('#submitform').submit();
        }
    }


    function sumRow(el) {
        let tr = $(el).closest('tr');
        let value = tr.find('.value').val();
        let count = tr.find('.count').val();
        value = AutoNumeric.unformat(value, autonum_setting);
        let total = count * value;

        tr.find('.total').text(setRupiah(total));
    }
</script>
@endsection