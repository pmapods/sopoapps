@extends('Layout.app')
@section('local-css')
    <style>
        .table td, .table th{
            vertical-align: middle !important;
        }
    </style>
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Setting PO</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operasional</li>
                    <li class="breadcrumb-item">Purchase Requisition</li>
                    <li class="breadcrumb-item active">Setting PO ({{$ticket->code}})</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body">
    <form action="/setupPO" method="post" id="setupForm">
    @csrf
    <input type="hidden" name="ticket_id" value="{{$ticket->id}}">
    <div class="row">
        <table class="table table-bordered">
            <thead>
                <tr class="thead-dark">
                    <th width="20%">Nama Barang</th>
                    <th width="8%">Satuan</th>
                    <th width="5%">Qty</th>
                    <th width="15%">Harga Satuan (Rp)</th>
                    <th width="15%">Total Harga</th>
                    <th>Vendor Terpilih</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ticket->pr->pr_detail ?? [] as $key=>$item)
                    @php
                        $rowspan = 1;
                        if($item->ongkir){
                            $rowspan++;
                        }
                        if($item->ongpas){
                            $rowspan++;
                        }
                    @endphp
                    <tr class="@if($key % 2 == 0) table-secondary @endif">
                        <td>
                            {{ $item->ticket_item->name }}
                            <input type="hidden" name="item[{{$key}}][pr_detail_id]" value="{{$item->id}}">
                        </td>
                        <td>{{ $item->uom }}</td>
                        <td>{{ $item->qty }}</td>
                        <td class="rupiah_text">{{ $item->price }}</td>
                        <td rowspan="{{ $rowspan }}" class="rupiah_text">{{ ($item->qty * $item->price) + $item->ongkir + $item->ongpas}}</td>
                        <td rowspan="{{ $rowspan }}">
                            {{$item->ticket_item->bidding->selected_vendor()->ticket_vendor->name}}
                            <input type="hidden" name="item[{{$key}}][ticket_vendor_id]" value="{{$item->ticket_item->bidding->selected_vendor()->ticket_vendor->id}}">
                        </td>
                        <td rowspan="{{ $rowspan }}">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input percent_checker" type="checkbox" disabled>
                                    Apakah item PPN ?
                                </label>
                            </div>
                            <div class="col-md-6 percent_field d-none">
                                <div class="form-group">
                                    <label class="required_field">Masukkan persentase PPN</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control autonumber percent_value" min="1" name="item[{{$key}}][ppn_percentage]">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <small class="text-danger">* minimal 1%</small>
                                </div>
                            </div>
                        </td>
                    </tr>

                    @if ($item->ongkir > 0)
                    <tr class="@if($key % 2 == 0) table-secondary @endif">
                        <td>Ongkir {{ $item->ticket_item->name }}</td>
                        <td>-</td>
                        <td>1</td>
                        <td class="rupiah_text">{{ $item->ongkir }}</td>
                    </tr> 
                    @endif
                    
                    @if ($item->ongpas > 0)
                    <tr class="@if($key % 2 == 0) table-secondary @endif">
                        <td>Ongpas {{ $item->ticket_item->name }}</td>
                        <td>-</td>
                        <td>1</td>
                        <td class="rupiah_text">{{ $item->ongpas }}</td>
                    </tr> 
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <center>
        <button type="button" class="btn btn-primary" onclick="doSubmit('barangjasa')">Submit</button>
        <button type="submit" class="btn btn-primary d-none">Submit</button>
    </center>
    </form>
</div>
@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        $('.autonumber').change(function(){
            autonumber($(this));
        });
        $('.percent_checker').change(function(){
            if($(this).prop('checked')){
                $(this).closest('tr').find('.percent_field').removeClass('d-none');
                $(this).closest('tr').find('.percent_field').find('.percent_value').prop('required',true);
                $(this).closest('tr').find('.percent_field').find('.percent_value').val(1);
            }else{
                $(this).closest('tr').find('.percent_field').addClass('d-none');
                $(this).closest('tr').find('.percent_field').find('.percent_value').prop('required',false);
                $(this).closest('tr').find('.percent_field').find('.percent_value').val('');
            }
        })
        $('.percent_checker').prop('disabled',false);
        $('.pr_detail_price').change(function (e) { 
            $(this).closest('tr').find('.total_price').text($(this).val());
        });
    });
    function doSubmit(type){
        $('#setupForm').find('button[type="submit"]').trigger('click');
    }   
</script>
@endsection
