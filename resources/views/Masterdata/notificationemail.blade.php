@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 d-flex flex-column">
                    <h1 class="m-0 text-dark">Notification Email</h1>
                    <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                        <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Notification Email</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#multiReplaceModal">Multi
                Replace</button>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addnotificationmodal">Tambah
                Notifikasi</button>
        </div>
        <div class="table-responsive">
            <table id="notificationDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th width="5%">#</th>
                        <th width="20%">Salespoint</th>
                        <th width="15%">Tipe</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @php $count = 0 @endphp
                    @foreach ($emailreminders as $reminder)
                        <tr data-details="{{ $reminder->detail }}" data-reminder="{{ $reminder }}">
                            <td>{{ $count += 1 }}</td>
                            <td>{{ $reminder->salespoint_name }}</td>
                            <td>{{ $reminder->type() }}</td>
                            <td>
                                @foreach ($reminder->detail as $key => $detail)
                                    @php
                                        $emails = json_decode($detail->emails);
                                        $emails = implode(', ', $emails);
                                    @endphp
                                    <div><b>{{ $detail->days }} Hari :</b><span>{{ $emails }}</span></div>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="updatenotificationmodal" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Notifikasi Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="reminder_id">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="required_field">Pilih Salespoint</label>
                                <select class="form-control salespoint_select" name="salespoint_id" disabled>
                                    <option value="">-- Pilih Salespoint --</option>
                                    <option value="all">All</option>
                                    @foreach ($salespoints as $salespoint)
                                        <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="required_field">Pilih Tipe</label>
                                <select class="form-control type_select" name="type" disabled>
                                    <option data-mindays="30" value="">-- Pilih Tipe --</option>
                                    <option data-mindays="30" value="po_armada_niaga">PO Armada Niaga (min days : 30)
                                    </option>
                                    <option data-mindays="30" value="po_armada_non_niaga">PO Armada Non Niaga (min days :
                                        30)</option>
                                    <option data-mindays="60" value="po_security">PO Security (min days : 60)</option>
                                    <option data-mindays="30" value="po_cit">PO CIT (min days : 30)</option>
                                    <option data-mindays="30" value="po_pest_control">PO Pest Control (min days : 30)
                                    </option>
                                    <option data-mindays="90" value="po_merchandiser">PO Merchandiser (min days : 90)
                                    </option>
                                    <option data-mindays="0" value="asset_number">Nomor Asset (min days : 0)</option>
                                    <option data-mindays="0" value="vendor_evaluation">Evaluasi Vendor (min days : 5)
                                    </option>
                                    <option data-mindays="0" value="cop">COP (min days : 5)
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    Tipe belum dipilih
                                </div>
                            </div>
                        </div>
                    </div>
                    <h5>List Notifikasi</h5>
                    <div class="border p-1 list_notif">
                    </div>
                    <div class="row mt-2">
                        <div class="col-2">
                            <div class="form-group">
                                <label>Jumlah Hari</label>
                                <input type="number" class="form-control add_control autonumber days_input" min="0"
                                    disabled>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="form-group">
                                <label>Emails</label>
                                <textarea class="form-control add_control emails_input" rows="1" disabled></textarea>
                            </div>
                        </div>
                        <div class="col-2">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block add_control add_button"
                                disabled>Tambah</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger" onclick="submitDeleteNotification(this)">Hapus</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddNotification('update',this)">Simpan
                        Notifikasi</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addnotificationmodal" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Notifikasi Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="required_field">Pilih Salespoint</label>
                                <select class="form-control select2 salespoint_select" name="salespoint_id" required>
                                    <option value="">-- Pilih Salespoint --</option>
                                    <option value="all">All</option>
                                    @foreach ($salespoints as $salespoint)
                                        <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Salespoint belum dipilih
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="required_field">Pilih Tipe</label>
                                <select class="form-control type_select" name="type" required>
                                    <option data-mindays="30" value="">-- Pilih Tipe --</option>
                                    <option data-mindays="30" value="po_armada_niaga">PO Armada Niaga (min days : 30)</option>
                                    <option data-mindays="30" value="po_armada_non_niaga">PO Armada Non Niaga (min days : 30)</option>
                                    <option data-mindays="60" value="po_security">PO Security (min days : 60)</option>
                                    <option data-mindays="30" value="po_cit">PO CIT (min days : 30)</option>
                                    <option data-mindays="30" value="po_pest_control">PO Pest Control (min days : 30)
                                    </option>
                                    <option data-mindays="90" value="po_merchandiser">PO Merchandiser (min days : 90)
                                    </option>
                                    <option data-mindays="0" value="asset_number">Nomor Asset (min days : 0)</option>
                                    <option data-mindays="0" value="vendor_evaluation">Evaluasi Vendor (min days : 5)
                                    </option>
                                    <option data-mindays="0" value="cop">COP (min days : 5)
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    Tipe belum dipilih
                                </div>
                            </div>
                        </div>
                    </div>
                    <h5>List Notifikasi</h5>
                    <div class="border p-1 list_notif">
                        <center class="empty_list">No Data</center>
                    </div>
                    <div class="row mt-2">
                        <div class="col-2">
                            <div class="form-group">
                                <label>Jumlah Hari</label>
                                <input type="number" class="form-control add_control autonumber days_input"
                                    min="0" disabled>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="form-group">
                                <label>Emails</label>
                                <textarea class="form-control add_control emails_input" rows="1" disabled></textarea>
                            </div>
                        </div>
                        <div class="col-2">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block add_control add_button"
                                disabled>Tambah</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddNotification('add',this)">Simpan
                        Notifikasi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="multiReplaceModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Multi Replace</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/notificationemail/multireplace" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Pilih Salespoint</label>
                                    <select class="form-control select2" name="salespoint_id" required>
                                        <option value="">-- Pilih Salespoint --</option>
                                        @foreach ($regions as $region)
                                            <optgroup label="{{ $region->first()->region_name() }}">
                                                @foreach ($region as $salespoint)
                                                    <option value="{{ $salespoint->id }}">{{ $salespoint->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Email</label>
                                    <select class="form-control select2" name="email" required>
                                        <option value="">-- Pilih Email --</option>
                                        @foreach ($registered_emails as $email)
                                            <option value="{{ $email }}">{{ $email }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tipe</th>
                                            <th>Hari</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Diubah menjadi Email</label>
                                    <input type="email" class="form-control" name="to_email" required>
                                </div>
                            </div>
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
    <form method="post" id="submitform" enctype="multipart/form-data">
        @csrf
        <div></div>
    </form>
@endsection
@section('local-js')
    <script>
        let old_min_days = "";
        let old_type = "";
        $(document).ready(function() {
            $('.autonumber').change(function() {
                autonumber($(this));
            });
            var table = $('#notificationDT').DataTable(datatable_settings);
            $('#notificationDT tbody').on('click', 'tr', function() {
                let reminder_data = $(this).data('reminder');
                let details_data = $(this).data('details');
                $('#updatenotificationmodal input[name="reminder_id"]').val(reminder_data.id);
                $('#updatenotificationmodal select[name="salespoint_id"]').val(reminder_data.salespoint_id);
                $('#updatenotificationmodal select[name="salespoint_id"]').trigger('change');
                $('#updatenotificationmodal select[name="type"]').val(reminder_data.type);
                $('#updatenotificationmodal select[name="type"]').trigger('change');

                // details
                let list_text = "";
                details_data.forEach(function(detail, index) {
                    let append_text = '<div class="row list_item">';
                    append_text += '<div class="col-3"><span class="days_count">' + detail.days +
                        '</span> Hari</div>';
                    append_text += '<div class="col-8"><ul>';
                    let emails = JSON.parse(detail.emails);
                    emails.forEach(function(email) {
                        append_text += '<li>' + email + '</li>';
                    });
                    append_text += '</ul></div>';
                    if (index == details_data.length - 1) {
                        append_text +=
                            '<div class="col-1 remove_field"><i class="fa fa-times text-danger remove_list" aria-hidden="true" onclick="removeList(this)"></i></div>'
                    } else {
                        append_text += '<div class="col-1 remove_field"></div>'
                    }
                    append_text += '</div>';
                    list_text += append_text;
                });
                $('#updatenotificationmodal .list_notif').empty();
                $('#updatenotificationmodal .list_notif').append(list_text);
                $('#updatenotificationmodal').modal('show');
            });
            $('.type_select').change(function() {
                let modal = $(this).closest('.modal')
                let type = $(this).val();
                let mindays = $(this).find('option:selected').data('mindays');
                if (modal.prop('id') == "addnotificationmodal") {
                    // confirm kalo ganti yang min daysnya
                    if ((old_min_days != "" && old_min_days != mindays && modal.find('.list_item').length >
                            0) || type == "") {
                        if (!confirm(
                                'Minimal hari tipe yang akan dipilih berbeda dari sebelumnya item saat ini akan dihapus dan tidak tersimpan. Lanjutkan?'
                            )) {
                            // ganti ke pilihan tipe sebelumnya;
                            $(this).val(old_type);
                            $(this).trigger('change');
                            return;
                        } else {
                            // reset seluruh item dan lanjutkan proses
                            list_reset(modal);
                        }
                    }
                    old_min_days = mindays;
                    old_type = type;
                }

                if (type != "") {
                    modal.find('.add_control').prop('disabled', false);
                    modal.find('.days_input').prop('min', mindays);
                    modal.find('.days_input').val(mindays);
                } else {
                    modal.find('.add_control').prop('disabled', true);
                }
                modal.find('.add_control').val('');
                modal.find('.add_control').trigger('change');
            });
            $('.add_button').click(function() {
                let modal = $(this).closest('.modal');
                let days = modal.find('.days_input').val();
                let emails = modal.find('.emails_input').val();
                if (days == "") {
                    alert('Hari harus diisi');
                    return;
                }
                if (emails == "") {
                    alert('Email harus diisi');
                    return;
                }
                let flag = true;
                modal.find('.days_count').each(function() {
                    let selected_value = parseInt($(this).text());
                    if (selected_value == days) {
                        alert('Setting untuk ' + days + ' Hari telah di set sebelumnya');
                        flag = false;
                    }
                });
                if (!flag) {
                    return;
                }
                let array_email = convertTexttoArray(emails);
                let isvalidated = true;
                array_email.forEach(function(email, index) {
                    // validasi format email
                    let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
                    if (!email.match(mailformat)) {
                        alert('Format email ' + email + ' tidak sesuai');
                        isvalidated = false;
                        return false;
                    }
                });
                if (!isvalidated) {
                    return;
                }
                let append_text = '<div class="row list_item">';
                append_text += '<div class="col-3"><span class="days_count">' + days + '</span> Hari</div>';
                append_text += '<div class="col-8"><ul>';
                array_email.forEach(function(email, index) {
                    append_text += '<li>' + email + '</li>';
                });
                append_text += '</ul></div>';
                append_text +=
                    '<div class="col-1 remove_field"><i class="fa fa-times text-danger remove_list" aria-hidden="true" onclick="removeList(this)"></i></div>'
                append_text += '</div>';
                modal.find('.list_notif').append(append_text);
                modal.find('.add_control').val("");
                modal.find('.add_control').trigger("change");
                modal.find('.remove_list:not(:last)').remove();
                modal.find('.empty_list').remove();
            });
            $('#multiReplaceModal select[name="salespoint_id"], #multiReplaceModal select[name="email"]').change(
                function() {
                    const salespoint_id = $('#multiReplaceModal select[name="salespoint_id"]').val();
                    const selected_email = $('#multiReplaceModal select[name="email"]').val();
                    $('#multiReplaceModal table tbody').empty();
                    if (salespoint_id != "" && selected_email != "") {
                        $.ajax({
                            type: "GET",
                            url: "/notificationemail/getdetails?salespoint_id=" + salespoint_id +
                                "&email=" + selected_email,
                            success: function(response) {
                                if (!response.error) {
                                    let data = response.data;
                                    data.forEach(item => {
                                        let string_text = "<tr>";
                                        string_text += "<td>" + item.type_name + "</td>";
                                        string_text += "<td>" + item.days + "</td>";
                                        string_text += "<td>";
                                        item.emails.forEach((email, idx, arr) => {
                                            if (selected_email == email) {
                                                string_text += "<b>" + email +
                                                    "</b>";
                                            } else {
                                                string_text += email;
                                            }
                                            if (idx !== arr.length - 1) {
                                                string_text += ", ";
                                            }
                                        });
                                        string_text += "</td>";
                                        string_text += "</tr>";
                                        $('#multiReplaceModal table tbody').append(
                                            string_text);
                                    });
                                }
                            },
                            error: function(err) {
                                alert("Error get data : " + err.message);
                            }
                        });
                    }
                });
        });

        function list_reset(modal) {
            modal.find('.list_notif').empty();
            modal.find('.list_notif').append('<center class="empty_list">No Data</center>');
        }

        function convertTexttoArray(text) {
            let strArray = text.split(",");
            strArray.forEach(function(str, index, array) {
                array[index] = str.trim();
            });
            return strArray;
        }

        function removeList(el) {
            let modal = $(el).closest('.modal');
            $(el).closest('.list_item').remove();
            modal.find('.remove_field:last').append(
                '<i class="fa fa-times text-danger remove_list" aria-hidden="true" onclick="removeList(this)"></i>');
            if (modal.find('.list_item').length == 0) {
                modal.find('.list_notif').append('<center class="empty_list">No Data</center>');
            }
        }

        function submitAddNotification(submit_type, el) {
            let modal = $(el).closest('.modal');
            // fetch data
            if (modal.find('.salespoint_select').val() == '') {
                modal.find('.salespoint_select').addClass('is-invalid');
                return;
            } else {
                modal.find('.salespoint_select').removeClass('is-invalid');
            }
            if (modal.find('.type_select').val() == '') {
                modal.find('.type_select').addClass('is-invalid');
                return;
            } else {
                modal.find('.type_select').removeClass('is-invalid');
            }
            if (modal.find('.list_item').length == 0) {
                alert('Minimal satu pilihan notifikasi');
                return;
            }
            let salespoint_id = modal.find('.salespoint_select').val();
            let type = modal.find('.type_select').val();
            let reminder_id = modal.find('input[name="reminder_id"]').val();
            let input_text = '<input type="hidden" name="salespoint_id" value="' + salespoint_id + '"/>';
            input_text += '<input type="hidden" name="type" value="' + type + '"/>';
            modal.find('.list_item').each(function(index, item) {
                let days_count = parseInt($(item).find('.days_count').text());
                // get all emails
                let emails = "";
                $(item).find('li').each(function(index, email) {
                    emails += $(email).text().trim() + ',';
                });
                input_text += '<input type="hidden" name="item[' + index + '][emails]" value="' + emails + '"/>';
                input_text += '<input type="hidden" name="item[' + index + '][daycount]" value="' + days_count +
                    '"/>';
            });
            $('#submitform div').empty();
            $('#submitform div').append(input_text);
            if (submit_type == "add") {
                $('#submitform').prop('action', '/notificationemail/create');
            } else {
                $('#submitform div').append('<input type="hidden" name="reminder_id" value="' + reminder_id + '">');
                $('#submitform').prop('action', '/notificationemail/update');
            }
            $('#submitform').submit();
        }

        function submitDeleteNotification(el) {
            let reminder_id = $(el).closest('.modal').find('input[name="reminder_id"]').val();
            $('#submitform div').empty();
            $('#submitform div').append('<input type="hidden" name="reminder_id" value="' + reminder_id + '">');
            $('#submitform').prop('action', '/notificationemail/delete');
            $('#submitform').submit();
        }
    </script>
@endsection
