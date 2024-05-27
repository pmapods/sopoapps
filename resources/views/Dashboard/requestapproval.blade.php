@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"> Dashboard Request Approval<span class="spinner-border text-sm text-danger ml-3"
                            id="loading_request_approval" role="status" style="display:none">
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">Dashboard Request Approval</li>
                    </ol>
                </div>
                <br>
                <br>
                <div class="col-sm-6">
                    <h5 class="m-0 text-info">
                        <a href="/dashboard"> Back To Dashboard </a>
                    </h5>
                </div>
            </div>
        </div>
    </div>

    @if (((Auth::user()->menu_access->feature ?? 0) & 1) != 0)
        <div class="d-flex justify-content-end mt-1">
            <button type="button" class="btn btn-info btn-sm" onclick="multiApprove()">Multi Approve</button>
        </div>
    @endif
    <br>

    <div class="col-md-12 mt-1">
        <table id="wait_authorization_table" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Sales Point</th>
                    <th>Kode Pengadaan</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Nama Pengaju</th>
                    <th>Jenis Transaksi</th>
                    <th>Status Akhir</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="multiApproveModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Multi Approve</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/multiapprove" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                <div class="form-group">
                                    <label>Jenis Approval</label>
                                    <select class="form-control" name="approval_type" id="approval_type" required>
                                        <option value="">-- Pilih Jenis Approval --</option>
                                        <option value="perpanjangan_form">Form Perpanjangan Armada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 col-12 d-none" id="armada_approval_table_field">
                                <table class="table table-striped table-sm small" id="armada_approval_table">
                                    <thead>
                                        <tr>
                                            <th>Salespoint</th>
                                            <th>Jenis Armada</th>
                                            <th>Tipe Armada</th>
                                            <th>Nopol</th>
                                            <th>Vendor</th>
                                            <th>Tipe Ticket</th>
                                            <th>Periode Perpanjangan</th>
                                            <th>Alasan</th>
                                            <th>Periode StopSewa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <span class="spinner-border text-sm text-danger" id="loading_armada_approval" role="status"
                                    style="display:none"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Approve All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        var csrf = "{{ csrf_token() }}";
        $(document).ready(function() {
            $("#loading_request_approval").show();
            $.ajax({
                type: "get",
                url: "/getCurrentAuthorization",
                success: function(response) {
                    let data = response.data;
                    let count = 0;
                    // localStorage.setItem('dtRenewal', JSON.stringify(data));
                    data.forEach(function(item) {
                        let append_text = '';
                        let key = 'codeRenewal';
                        if (item.needApproval) {
                            append_text += '<tr class="table-warning">';
                        } else {
                            append_text += "<tr>";
                        }
                        append_text += '<td>' + item.nomor + '</td>';
                        append_text += '<td>' + item.salespoint + '</td>';
                        append_text += '<td>' + item.code + '</td>';
                        append_text += '<td>' + item.created_at + '</td>';
                        append_text += '<td>' + item.created_by + '</td>';
                        append_text += '<td>' + item.transaction_type + '</td>';
                        append_text += '<td>' + item.status + '</td>';
                        append_text += '<td data-link = "' + item.link + '">';
                        append_text +=
                            '<a href="#" class="text-primary font-weight-bold" onclick="window.open(\'' +
                            item.link + '\'); localStorage.setItem(\'' + key + '\',' + '\'' + item.code + '\');">Buka</a>';
                        if (item.needApproval && item.canQuickApprove) {
                            append_text +=
                                '<a href="#" class="text-success ml-2 font-weight-bold" onclick="approveApproval(this)">Approve</a>';
                            append_text +=
                                '<a href="#" class="text-danger ml-2 font-weight-bold" onclick="rejectApproval(this)">Reject</a>';
                        }
                        append_text += '</td>';
                        append_text += '</tr>';
                        $('#wait_authorization_table tbody').append(append_text);
                        count++;
                    });
                    if (count == 0) {
                        let append_text = '<tr>';
                        append_text += '<td colspan="7">No Data</td>';
                        append_text += '</tr>';
                        $('#wait_authorization_table tbody').append(append_text);
                    }
                },
                error: function(response) {

                },
                complete: function(response) {
                    $("#loading_request_approval").hide();
                }
            });
        });

        function approveApproval(el) {
            // $('#loading_modal').modal('show');
            let requestdata = {
                approval_type: 'approve',
                code: $(el).closest('tr').find('td:eq(1)').text().trim(),
                transaction_type: $(el).closest('tr').find('td:eq(4)').text().trim(),
                link: $(el).closest('tr').find('td:eq(6)').data("link"),
                _token: csrf
            };
            $.ajax({
                type: "POST",
                url: "/quickapproval",
                data: requestdata,
                success: function(response) {
                    alert(response.message);
                    if (!response.error) {
                        let tr = $(el).closest('tr');
                        tr.fadeOut("slow", function() {
                            tr.remove();
                        })
                    }
                },
                error: function(response) {
                    alert('load data failed. Please refresh browser or contact admin. Error message: ' +
                        response.message);
                },
                complete: function() {
                    // $('#loading_modal').modal('hide');
                }
            });
        }

        function rejectApproval(el) {
            let reason = prompt("Harap memasukan alasan reject");
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return;
                }

                $('#loading_modal').modal('show');
                let requestdata = {
                    approval_type: 'reject',
                    code: $(el).closest('tr').find('td:eq(1)').text().trim(),
                    transaction_type: $(el).closest('tr').find('td:eq(4)').text().trim(),
                    link: $(el).closest('tr').find('td:eq(6)').data("link"),
                    reason: reason,
                    _token: csrf
                };
                $.ajax({
                    type: "POST",
                    url: "/quickapproval",
                    data: requestdata,
                    success: function(response) {
                        alert(response.message);
                        if (!response.error) {
                            let tr = $(el).closest('tr');
                            tr.fadeOut("slow", function() {
                                tr.remove();
                            })
                        }
                    },
                    error: function(response) {
                        alert('load data failed. Please refresh browser or contact admin. Error message: ' +
                            response.message);
                    },
                    complete: function() {
                        $('#loading_modal').modal('hide');
                    }
                });
            }
        }

        function multiApprove() {
            $('#multiApproveModal').modal('show');
        }
        $('#approval_type').change(function() {
            let approval_type = $(this).val();
            if (approval_type == "perpanjangan_form") {
                $("#armada_approval_table_field").removeClass('d-none');
                $("#armada_approval_table tbody").empty();
                $("#loading_armada_approval").show();
                $.ajax({
                    type: "get",
                    url: "/getCurrentAuthorization/perpanjangan_form",
                    success: function(response) {
                        let data = response.data;
                        let count = 0;
                        data.forEach(function(item) {
                            let append_text = '<tr>';
                            append_text += '<td>' + item.salespoint + '</td>';
                            append_text += '<td>' + ((item.isNiaga == true) ? 'Niaga' :
                                'Non Niaga') + '</td>';
                            append_text += '<td>' + item.armada_type_name + '</td>';
                            append_text += '<td>' + item.nopol + '</td>';
                            append_text += '<td>' + item.vendor_name + '</td>';
                            append_text += '<td>' + item.form_type + '</td>';
                            append_text += '<td>' + (item.perpanjangan_length ?? '-') + '</td>';
                            append_text += '<td>' + (item.stopsewa_reason ?? '-') + '</td>';
                            append_text += '<td>' + (item.stopsewa_date ?? '-') + '</td>';
                            append_text += '</tr>';
                            append_text +=
                                '<input type="hidden" name="perpanjangan_form_ids[]" value="' +
                                item.perpanjangan_form_id + '">';
                            $('#armada_approval_table tbody').append(append_text);
                            count++;
                        });
                        if (count == 0) {
                            let append_text = '<tr>';
                            append_text += '<td colspan="8">No Data</td>';
                            append_text += '</tr>';
                            $('#armada_approval_table tbody').append(append_text);
                        }
                    },
                    error: function(response) {

                    },
                    complete: function(response) {
                        $("#loading_armada_approval").hide();
                    }
                });
            } else {
                $("#armada_approval_table_field").addClass('d-none');
                $("#armada_approval_table tbody").empty();
            }
        });
    </script>
@endsection
