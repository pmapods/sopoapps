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
                <h1 class="m-0 text-dark">Setup PO</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operasional</li>
                    <li class="breadcrumb-item">Purchase Requisition</li>
                    <li class="breadcrumb-item active">Setup PO ({{$ticket_code}})</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body px-3">
    <div class="row">
    @if (isset($ticket) && isset($ticket->ticket_item))
        <div class="col-6">
            <h5>Daftar Item</h5>
            <table class="table table-sm table-bordered small">
                <thead>
                    <tr>
                        <th>Nama Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ticket->ticket_item as $ticket_item)
                        <tr>
                            <td scope="row">{{ $ticket_item->name }}</td>
                            <td>{{ $ticket_item->count }}</td>
                            <td class="rupiah_text">{{ $ticket_item->price }}</td>
                            <td class="rupiah_text">{{ $ticket_item->count * $ticket_item->price }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if ($issuepos->count()>0)
        <div class="col-6">
            <h5>Issue PO</h5>
            <table class="table table-bordered table-sm small">
                <thead>
                    <tr>
                        <th>Nomor PO</th>
                        <th>Notes</th>
                        <th>Attachment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($issuepos as $issuepo)
                        <tr>
                            <td>{{ $issuepo->po_number }}</td>
                            <td>{{ $issuepo->notes }}</td>
                            <td><a href="#" onclick="window.open('/storage/{{ $issuepo->ba_file }}')">Tampilkan File BA</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    </div>
    <p>Harap Melakukan Pembuatan PR dan PO pada SAP dengan memasukan kode <b>{{ $ticket_code }}</b> pada kolom yang tersedia saat pembuatan PR SAP</p>

    @if ($type == 'barangjasa') 
        @if (count($ticket->po) == 0 && $ticket->revise_po == 1)
            Di Revisi Oleh : <span
                class="text-danger">{{ $ticket->revise_by_employee->name 
                . ' (' . date('l, d F Y (H:i)', strtotime($ticket->revise_date)) . ')' ?? '-' }}</span><br>
            Alasan Reject : <span
                class="text-danger">{{ $ticket->reason_revise ?? '-' }}</span>
        @endif
    @elseif ($type == 'armada')
        @if (count($armadaticket->po) == 0 && $armadaticket->revise_po == 1)
            Di Revisi Oleh : <span
                class="text-danger">{{ $armadaticket->revise_by_employee->name 
                . ' (' . date('l, d F Y (H:i)', strtotime($armadaticket->revise_date)) . ')' ?? '-' }}</span><br>
            Alasan Reject : <span
                class="text-danger">{{ $armadaticket->reason_revise ?? '-' }}</span>
        @endif
    @elseif ($type == 'security')
        @if (count($securityticket->po) == 0 && $securityticket->revise_po == 1)
            Di Revisi Oleh : <span
                class="text-danger">{{ $securityticket->revise_by_employee->name 
                . ' (' . date('l, d F Y (H:i)', strtotime($securityticket->revise_date)) . ')' ?? '-' }}</span><br>
            Alasan Reject : <span
                class="text-danger">{{ $securityticket->reason_revise ?? '-' }}</span>
        @endif
    @endif

    <hr>

    <div class="d-flex align-items-center">
        <button type="button" class="btn btn-primary mr-3" id="refresh_button">Quick Refresh</button>
    </div>
    <div class="d-flex flex-column align-items-center">
        <h5>Data PR</h5>
        <table class="table table-bordered" id="pr_table">
            <thead>
                <tr>
                    <th>Pods Code</th>
                    <th>PR Number</th>
                    <th>PO Number</th>
                    <th>Short Text</th>
                    <th>Qty Requested</th>
                    <th>Uom</th>
                    <th>Create Date</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
         <span class="text-secondary" id="pr_message"></span>
        <div class="spinner-border text-danger" id="prsap_loading" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="d-flex flex-column align-items-center mt-3">
        <h5>Data PO</h5>
        <table class="table table-bordered" id="po_table">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Create On</th>
                    <th>Material Short Text</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Plant</th>
                    <th>Vendor</th>
                    <th>Payment Days</th>
                    <th>Deliv Date</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <span class="text-secondary" id="po_message"></span>
        <div class="spinner-border text-danger" id="posap_loading" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <form action="/po/setuppo" method="post" id="create_po_form">
        @csrf
        <input type="hidden" name="ticket_code" value="{{ $ticket_code }}">
        @isset ($ticket)
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
        @endisset
        @isset ($armadaticket)
            <input type="hidden" name="armada_ticket_id" value="{{ $armadaticket->id }}">
        @endisset
        @isset ($securityticket)
            <input type="hidden" name="security_ticket_id" value="{{ $securityticket->id }}">
        @endisset
        <div id="po_numbers_field"></div>
        <div class="d-flex justify-content-center mt-3">
            <button type="submit" class="btn btn-primary" id="create_po_button" disabled>Create PO</button>
        </div>
    </form>
    <form action="/po/quickrefresh" method="post" id="quickrefreshform">
        @csrf
        <div>

        </div>
    </form>
</div>
@endsection
@section('local-js')
<script>
    @isset ($ticket)
        let ticket = @json($ticket);
    @endisset
    @isset ($armadaticket)
        let ticket = @json($armadaticket);
    @endisset
    @isset ($securityticket)
        let ticket = @json($securityticket);
    @endisset
    
    $(document).ready(function(){
        $('#quickrefreshform div').empty();
        loadDataPR(ticket.code)
        $('#refresh_button').click(function(){
            $('#quickrefreshform').submit();
        });
    });
    function loadDataPR(code){
        let po_numbers = [];
        $('#prsap_loading').show();
        let requestdata = {
            ticket_code: code,
        };

        $.ajax({
            type: "GET",
            url: "/getPrSapbyTicketCode",
            data: requestdata,
            success: function (response) {
                let message = response.message;
                $('#pr_message').text(message);
                let data = response.data;
                let column_count = $('#pr_table thead th').length;
                $('#pr_table tbody').empty();
                if(data.length == 0){
                    $('#pr_table tbody').append('<tr><td colspan="'+column_count+'">Data Tidak Ditemukan</td></tr>')
                }else{
                    data.forEach(item => {
                        if(item.po_number){
                            po_numbers.push(item.po_number);
                        }
                        let option_text = '<tr>';
                        option_text += '<td>'+item.pods_code+'</td>';
                        option_text += '<td>'+item.pr_number+'</td>';
                        option_text += '<td>'+(item.po_number ?? '-')+'</td>';
                        option_text += '<td>'+item.short_text+'</td>';
                        option_text += '<td>'+item.order_qty+'</td>';
                        option_text += '<td>'+item.uom+'</td>';
                        option_text += '<td>'+item.create_date+'</td>';
                        option_text += '</tr>';
                        $('#pr_table tbody').append(option_text);
                        $('#quickrefreshform div').append('<input type="hidden" name="pr[]" value="'+item.pr_number+'">');
                    });
                }
            },
            error: function (response){
                let column_count = $('#pr_table thead th').length;
                $('#pr_table tbody').empty();
                $('#pr_table tbody').append('<tr><td colspan="'+column_count+'">Data Tidak Ditemukan</td></tr>')
                $('#pr_message').text("");
                alert("PR data retrieve error : "+response.statusText);
            },
            complete: function (){
                $('#prsap_loading').hide();
                loadDataPO(po_numbers);
            }
        });
    }
    
    function loadDataPO(po_numbers){
        $('#posap_loading').show();
        if(po_numbers.length == 0){
            let column_count = $('#po_table thead th').length;
            $('#po_table tbody').empty();
            $('#po_table tbody').append('<tr><td colspan="'+column_count+'">Data Tidak Ditemukan</td></tr>')
            $('#po_message').text("PO belum di proses");
            $('#posap_loading').hide();
            $('#create_po_button').prop('disabled',true);
            return;
        }
        $('#po_numbers_field').empty();
        po_numbers.forEach(po_number =>{
            $('#po_numbers_field').append('<input type="hidden" name="po_numbers[]" value="'+po_number+'"/>');
        })
        let requestdata = {
            po_numbers: po_numbers,
        };

        $.ajax({
            type: "GET",
            url: "/getPoSap",
            data: requestdata,
            success: function (response) {
                let message = response.message;
                $('#po_message').text(message);
                let data = response.data;
                let column_count = $('#po_table thead th').length;
                $('#po_table tbody').empty();
                if(data.length == 0){
                    $('#po_table tbody').append('<tr><td colspan="'+column_count+'">Data Tidak Ditemukan</td></tr>');
                    $('#create_po_button').prop('disabled',true);
                }else{
                    data.forEach(item => {
                        let option_text = '<tr>';
                        option_text += '<td>'+item.po_number ?? "data not found"+'</td>';
                        option_text += '<td>'+item.create_on ?? "data not found"+'</td>';
                        option_text += '<td style="white-space : pre-wrap">'+(item.material_short_text ?? "data not found")+"\n"+'<small class="text-secondary">'+(item.item_text_po ?? "")+'</small></td>';
                        option_text += '<td>'+item.scheduled_qty_requested ?? "data not found"+'</td>';
                        option_text += '<td>'+item.order_unit ?? "data not found"+'</td>';
                        option_text += '<td>'+setRupiah(item.net_order_price / item.price_unit * 100) ?? "data not found"+'</td>';
                        let plant_text = (item.plant_name_1 ?? "")+"\n"+(item.plant_name_2 ?? "")+"\n"+(item.plant_addrs ?? "")+'\n'+(item.plant_city ?? "");
                        option_text += '<td style="white-space : pre-wrap; font-size: 0.8em">'+plant_text+'</td>';
                        let vendor_text = (item.vendor_name ?? "")+"\n"+(item.vendor_addr ?? "")+"\n"+(item.vendor_city ?? "")
                        option_text += '<td style="white-space : pre-wrap; font-size: 0.8em">'+vendor_text ?? "data not found"+'</td>';
                        option_text += '<td>'+item.payment_days ?? "data not found"+'</td>';
                        option_text += '<td>'+item.delv_date ?? "data not found"+'</td>';
                        option_text += '</tr>';
                        $('#po_table tbody').append(option_text);
                        $('#quickrefreshform div').append('<input type="hidden" name="po[]" value="'+item.po_number+'">');
                    });

                    // set ready to create PO
                    $('#create_po_button').prop('disabled',false);
                }
            },
            error: function (response){
                let column_count = $('#po_table thead th').length;
                $('#po_table tbody').empty();
                $('#po_table tbody').append('<tr><td colspan="'+column_count+'">Data Tidak Ditemukan</td></tr>')
                alert("PO data retrieve error : "+response.statusText);
                $('#po_message').text("");
                $('#create_po_button').prop('disabled',true);
            },
            complete: function (){
                $('#posap_loading').hide();
            }
        });
    }
</script>
@endsection
