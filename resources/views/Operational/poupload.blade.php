<!doctype html>
<html lang="en">

<head>
    <title>Upload Dokumen PO</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="/assets/logo.png" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<style>
    html,
    body,
    form {
        height: 100%
    }

    .info-table {
        max-width: 40vw;

    }

</style>

<body>
    @if (Session::has('success'))
        <div class="m-1 alert alert-success alert-dismissible fade show" role="alert">
            {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (Session::has('error'))
        <div class="m-1 alert alert-danger alert-dismissible fade show" role="alert">
            {{ Session::get('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <form action="/uploadsigneddocument" method="post" enctype="multipart/form-data">
        @csrf
        <div class="container h-100 d-flex flex-column justify-content-center align-items-center">
            <input type="hidden" name="po_upload_request_id" value="{{ $poupload->id }}">
            <img src="/assets/logo.png" alt="" srcset="" width="20%">
            <div class="display-4">UPLOAD DOKUMEN</div>
            <div class="row">
                <div class="offset-md-3 col-md-3">Nomor PO</div>
                <div class="col-md-6">: {{ $poupload->po->no_po_sap }}</div>
                <div class="offset-md-3 col-md-3">Tanggal terbit PO</div>
                <div class="col-md-6">: {{ $poupload->po->created_at->translatedFormat('d F Y') }}</div>
                <div class="offset-md-3 col-md-3">Nama Supplier</div>
                <div class="col-md-6">: {{ $poupload->vendor_name }}</div>
                <div class="offset-md-3 col-md-3">Nama Supplier PIC</div>
                <div class="col-md-6">: {{ $poupload->vendor_pic }}</div>
                @php
                    $filename = explode('/', $poupload->po->internal_signed_filepath);
                    $filename = $filename[count($filename) - 1];
                @endphp
                <div class="offset-md-3 col-md-3">Dokumen untuk di Tanda tangan</div>
                <div class="col-md-6">: <a href="/storage/{{ $poupload->po->internal_signed_filepath }}"
                        download="{{ $filename }}">{{ $filename }}</a></div>
                @if ($poupload->status == 1)
                    @php
                        $filename = explode('/', $poupload->filepath);
                        $filename = $filename[count($filename) - 1];
                    @endphp
                    <div class="offset-md-3 col-md-3">Dokumen Tanda tangan yang telah di upload</div>
                    <div class="col-md-6">: <a href="/storage/{{ $poupload->filepath }}"
                            download="{{ $filename }}">{{ $filename }}</a></div>
                @endif
            </div>
            @if ($poupload->status == 0)
                <div class="text-center small text-danger mt-3">
                    * Dokumen yang dapat dipilih hanya pdf
                </div>
                <div class="text-center mt-1 d-flex flex-column">
                    <div class="form-group">
                        <label class="required_field">Pilih Dokumen PO yang sudah di tandatangan</label>
                        <input type="file" name="file" class="form-control-file form-control-lg"
                            accept="application/pdf" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg">Upload Dokumen</button>
                    <button type="button" onclick="reject()" class="btn btn-danger btn-lg mt-2">Laporan Kesalahan</button>
                </div>
            @endif
            @if ($poupload->status == 1)
                <div class="text-warning text-center mt-3">* Menunggu proses konfirmasi oleh Tim PMA.</div>
                <h3 class="text-warning">Pending</h3>
            @endif

            @if ($poupload->status == 2)
                <h3 class="text-success mt-3">Confirmed</h3>
            @endif

            @if ($poupload->status == -1)
                <div class="text-danger text-center mt-3">notes : {{ $poupload->notes }}</div>
                <h3 class="text-danger mt-3">Rejected</h3>
            @endif
        </div>
    </form>

    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Laporkan Kesalahan pada PO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/rejectsigneddocument" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="po_upload_request_id" value="{{ $poupload->id }}">
                    <input type="hidden" name="po_id" value="{{ $poupload->po_id }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="reject_reason">Jelaskan kesalahan dalam PO <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="reject_reason" id="reject_reason" style="resize:none" rows="4"
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
    <script>
      function reject(){
        $('#rejectModal').modal('show');
        $('#rejectModal textarea').val('');
      }
    </script>
</body>

</html>
