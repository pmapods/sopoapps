@extends('Layout.app')
@section('local-css')
<style>
    .box {
        box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.25);
        border: 1px solid;
        border-color: gainsboro;
        border-radius: 0.5em;
    }

    .tdbreak {
        /* word-break : break-all; */
    }

    a {
        color: #0069D9 !important;
        cursor: pointer !important;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Approval Vendor Register</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operasional</li>
                    <li class="breadcrumb-item">Approval Vendor Register</li>
                    <li class="breadcrumb-item active">{{ $vendor->name }}</li>
                </ol>
            </div>
        </div>
        <div class="d-flex justify-content-end">
        </div>
    </div>
</div>
<div class="content-body">
    <div class="row">
        <div class="col-md-4">
            <table class="table table-borderless table-sm">
                <tbody>
                    <tr>
                        <td><b>Tanggal Pengajuan</b></td>
                        <td>{{ $vendor->created_at->translatedFormat('d F Y (H:i)') }}</td>
                    </tr>
                    <tr>
                        <td><b>Nama Perusahaan</b></td>
                        <td>{{ $vendor->name }}</td>
                    </tr>
                    <tr>
                        <td><b>Nama Pimpinan Perusahaan</b></td>
                        <td>{{ $vendorCompany->ceo_name }}</td>
                    </tr>
                    <tr>
                        <td><b>Status Perusahaan</b></td>
                        <td>{{ $vendorCompany->company_status ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table table-borderless table-sm">
                <tbody>
                    <tr>
                        <td><b>Alamat Perusahaan</b></td>
                        <td>{{ $vendorCompany->address }}</td>
                    </tr>
                    <tr>
                        <td><b>Kota</b></td>
                        <td>{{ $vendor->nama_city }}</td>
                    </tr>
                    <tr>
                        <td><b>No. Telp</b></td>
                        <td>{{ $vendorCompany->company_phone }}</td>
                    </tr>
                    <tr>
                        <td><b>Website</b></td>
                        <td>{{ $vendorCompany->company_website }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-4 text-right">

        </div>
        <div class="col-md-12 box p-3 mt-3">
            <h5 class="font-weight-bold ">Legalitas</h5>
            <div class="table-responsive">
                <table class="table table-bordered table_item">
                    <thead>
                        <tr>
                            <th nowrap>Nama Document</th>
                            <th>Attachment</th>
                        </tr>
                        <tr>
                            <td nowrap>Profil Perusahaan</td>
                            <td class="tdbreak">
                                <a onclick='window.open("/storage/{{ $vendorCompany->company_profile }}")'>{{ $vendorCompany->company_profile }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap>Akta Pendirian</td>
                            <td class="tdbreak">
                                <a onclick='window.open("/storage/{{ $vendorCompany->company_doi }}")'>{{ $vendorCompany->company_doi }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap>Domisili / Izin Lokasi</td>
                            <td class="tdbreak">
                                <a onclick='window.open("/storage/{{ $vendorCompany->location_permission }}")'>{{ $vendorCompany->location_permission }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap>SIUP / Izin Usaha</td>
                            <td class="tdbreak">
                                <a onclick='window.open("/storage/{{ $vendorCompany->siup }}")'>{{ $vendorCompany->siup }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap>TDP (Tanda Daftar Perusahaan) / NIB</td>
                            <td class="tdbreak">
                                <a onclick='window.open("/storage/{{ $vendorCompany->tdp_nib }}")'>{{ $vendorCompany->tdp_nib }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap>NPWP</td>
                            <td class="tdbreak">
                                <a onclick='window.open("/storage/{{ $vendorCompany->company_npwp }}")'>{{ $vendorCompany->company_npwp }}</a>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-12 box p-3 mt-3">
        <h5 class="font-weight-bold">PIC Perusahaan</h5>
        <table class="table table-bordered table_vendor">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>No. HP</th>
                    <th>Email</th>
                </tr>
                <tr>
                    <td>{{$vendorCompany->pic_name}}</td>
                    <td>{{$vendorCompany->pic_position}}</td>
                    <td>{{$vendorCompany->pic_phone}}</td>
                    <td>{{$vendorCompany->pic_email}}</td>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <br>
    <form action="/vendor-approve-register-approved" method="post">
    @csrf
    <center>
        <input type="hidden" name="id" value="{{$vendor->id}}">
        <button type="submit" class="btn btn-success">Approve</button>
    </center>
    </form>
    <center>
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">Reject</button>
    </center>


</div>
<form action="/uploadticketfilerevision" method="post" enctype="multipart/form-data" id="uploadrevisionform">
    @method('patch')
    @csrf
    <div class="input_field"></div>
</form>
<form action="/approveticket" method="post" id="approveform">
    @method('patch')
    @csrf
</form>
<form action="/rejectticket" method="post" id="rejectform">
    @method('patch')
    @csrf
</form>

</div>

<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/vendor-approve-register-reject" method="post" enctype="multipart/form-data">
        @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Vendor Register</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex flex-column">
                    <div class="form-group mt-3">
                        <input type="hidden" name="id" value="{{$vendor->id}}">
                        <textarea class="form-control" name="reason" rows="5" style="resize: none" placeholder="Masukan Alasan (wajib)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection
@section('local-js')
<script>
    $('#over_platform').change(function() {
        if ($(this).is(':checked')) {
            $("#submit_no_over_platform").hide();
            $("#submit_over_platform").show();
        } else {
            $("#submit_no_over_platform").show();
            $("#submit_over_platform").hide();

        }
    })

    function confirmationOverPlafon() {
        if ($('#file_perjanjian_legal').get(0).files.length === 0) {
            alert("File Perjanjian belum di Pilih");
        } else if ($('#file_tor_legal').get(0).files.length === 0) {
            alert("File TOR belum di Pilih");
        } else if ($('#file_sph_legal').get(0).files.length === 0) {
            alert("File SPH belum di Pilih");
        } else if ($('#over_platform').is(':checked')) {
            $('#overPlafon').modal('show');
        }
    }

    function changeButton() {
        $("#submit_no_over_platform").show();
        $("#submit_over_platform").hide();
    }

    function rejectOverPlafon() {
        $('#rejectOverPlafonModal').modal('show');
    }

    function rejectAgreementCOP() {
        $('#rejectAgreementCOPModal').modal('show');
    }

    function rejectTorCOP() {
        $('#rejectTorCOPModal').modal('show');
    }

    function rejectSphCOP() {
        $('#rejectSphCOPModal').modal('show');
    }

    function rejectUserAgreementCOP() {
        $('#rejectUserAgreementCOPModal').modal('show');
    }

    $('.cheker').change(function() {
        let data = [];
        $('.cheker').each(function() {
            data.push($(this).is(':checked'));
        })
        console.log(data);
        if (data.includes(true)) {
            $("#revision_lpb_invoice").prop("disabled", false);
        } else {
            $("#revision_lpb_invoice").prop("disabled", true);

        }
    })

    $('#lpb1').change(function() {
        if ($(this).is(':checked')) {
            $("#js_lpb").show();
        } else {
            $("#js_lpb").hide();
        }
    })

    $('#invoice1').change(function() {
        if ($(this).is(':checked')) {
            $("#js_invoice").show();
        } else {
            $("#js_invoice").hide();
        }
    })

    function revisionDocument(id) {
        console.log(id);
        $('#revisionDocument').modal('show');
        $('#upload_confirmation_file').val(id);
    }

    function approve() {
        $('#approveform').submit();
    }

    function reject() {
        var reason = prompt("Harap memasukan alasan penolakan");
        if (reason != null) {
            if (reason.trim() == '') {
                alert("Alasan Harus diisi");
                return;
            }
            $('#rejectform').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#rejectform').submit();
        }
    }

    function selectfile(el) {
        $(el).closest('td').find('.inputFile').click();
    }

    function uploadfile(id, type, el) {
        let linkfile = $(el).closest('td').find('.revision_file');
        if (linkfile.length == 0) {
            alert('Silahkan pilih file revisi untuk di upload terlebih dahulu');
        } else {
            let inputfield = $('#uploadrevisionform').find('.input_field');
            let file = linkfile.prop('href');
            let filename = linkfile.text().trim();
            inputfield.empty();
            inputfield.append('<input type="hidden" name="id" value="' + id + '">');
            inputfield.append('<input type="hidden" name="type" value="' + type + '">');
            inputfield.append('<input type="hidden" name="file" value="' + file + '">');
            inputfield.append('<input type="hidden" name="filename" value="' + filename + '">');
            $('#uploadrevisionform').submit();
        }
    }

    function issuePO() {
        $('#issuePOmodal').modal('show');
    }
    $(document).ready(function() {
        $(this).on('change', '.inputFile', function(event) {
            var reader = new FileReader();
            let value = $(this).val();
            let display_field = $(this).closest('td').find('.display_field');
            if (validatefilesize(event)) {
                reader.onload = function(e) {
                    display_field.empty();
                    let name = value.split('\\').pop().toLowerCase();
                    display_field.append('<a class="revision_file" href="' + e.target
                        .result +
                        '" download="' + name + '">' + name + '</a>');
                }
                reader.readAsDataURL(event.target.files[0]);
            } else {
                $(this).val('');
            }
        });
        $('.validatefilesize').change(function(event) {
            if (!validatefilesize(event)) {
                $(this).val('');
            }
        });
        $('#over_platform').prop('checked', true);
        $("#submit_no_over_platform").hide();
    });
</script>
@endsection