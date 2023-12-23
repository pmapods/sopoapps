@extends('Layout.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">PR Manual ({{ $ticket->code }})</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Purchase Requisition</li>
                        <li class="breadcrumb-item">PR Manual ({{ $ticket->code }})</li>
                        <li class="breadcrumb-item active">Update PR Data</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <form action="/pr/{{ $ticket->code }}/updateprdata/update" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="pr_id" value="{{ $ticket->pr->id }}">
        <input type="hidden" name="code" value="{{ $ticket->code }}">
        <input type="hidden" name="updated_at" value="{{ $ticket->pr->updated_at->translatedFormat('Y-m-d H:i:s') }}">

        <table class="table table-bordered table-sm" id="pr_table">
            <thead>
                <tr>
                    <th width="2%">No</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ticket->pr->pr_detail ?? [] as $key => $detail)
                    <input type="hidden" name="item[{{ $key }}][pr_detail_id]" value="{{ $detail->id }}">
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $detail->name }}</td>
                        <td>{{ $detail->qty }}</td>
                        <td><input class="form-control rupiahDecimal" type="text"
                                name="item[{{ $key }}][price]" value="{{ $detail->price }}"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @php
            $issued_po = [];
            foreach ($ticket->po as $po) {
                if ($po->issue) {
                    array_push($issued_po, $po->issue);
                }
            }
            $issued_po = collect($issued_po);
        @endphp
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="optional_field">Pilih BA terkait</label>
                    <select class="form-control" name="selected_issue_po_id">
                        <option value="">-- Pilih BA Terkait --</option>
                        @foreach ($issued_po as $issue)
                            <option value="{{ $issue->id }}" data-filepath="/storage/{{ $issue->ba_file }}">BA ISSUE PO
                                {{ $issue->po_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="">&nbsp;</label>
                    <button type="button" class="form-control btn btn-info" onclick="showBAFile()">Tampilkan BA</button>
                </div>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="required_field">Alasan</label>
                    <textarea class="form-control" name="reason" placeholder="Masukan Alasan perubahan" rows="3" required="required"></textarea>
                </div>
            </div>
        </div>
        <center>
            <button type="submit" class="btn btn-primary">Update PR Data</button><br>
        </center>
    </form>
@endsection
@section('local-js')
    <script>
        function showBAFile() {
            let ba_select_filepath = $('select[name="selected_issue_po_id"] option:selected').data("filepath");
            console.log(ba_select_filepath);
            if (ba_select_filepath) {
                window.open(ba_select_filepath);
            } else {
                alert("BA belum dipilih");
            }
        }
    </script>
@endsection
