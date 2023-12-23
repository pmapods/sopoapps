@if ($isEditFRI)
    <form id="fri_form_create">
        <div class="border p-2 border-dark">
            <div class="text-center h5">FORM<br>REQUEST INFRASTRUKTUR</div>
            <table class="table table-bordered table-sm small">
                <tbody>
                    <tr>
                        <td class="required_field">Date Request</td>
                        <td><input type="text" class="form-control form-control-sm date_request"
                                value="Mengikuti Tanggal Pengajuan Tiket" readonly></td>
                    </tr>
                    <tr>
                        <td class="required_field">Date Use</td>
                        <td><input type="text" class="form-control form-control-sm date_use"
                                value="Mengikuti Tanggal Pengadaan Tiket" readonly></td>
                    </tr>
                    <tr>
                        <td class="required_field">Work Location</td>
                        <td><input type="text" class="form-control form-control-sm" name="work_location" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="required_field">Area</td>
                        <td><input type="text" class="form-control form-control-sm" name="salespoint_name" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="required_field">User Name / Position</td>
                        <td>
                            <input type="text" class="form-control form-control-sm" name="username_position"
                                readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="required_field">Div / Dept</td>
                        <td><input type="text" class="form-control form-control-sm" name="division_department"
                                required>
                        </td>
                    </tr>
                    <tr>
                        <td class="required_field">Contact Number</td>
                        <td><input type="text" class="form-control form-control-sm" name="contact_number" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="required_field">Email Address</td>
                        <td><input type="text" class="form-control form-control-sm" name="email_address" readonly>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="h5 text-center">HARDWARE DETAILS</div>
            <table class="table table-bordered table-sm small" id="hardware_details_table">
                <thead>
                    <tr class="text-center">
                        <th width="8%">NO</th>
                        <th>UNIT</th>
                        <th>QTY</th>
                        <th width="50%">REMARK</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="text-center h5">
                Application Details
            </div>
            @php
                $application_detail_items = ['Microsoft Office 2007', 'Anti Virus MS Essential / MS Defender', 'SAP', 'Mozilla Firefox / Google Chrome / IE', 'Adobe Acrobat', 'free_text', '7Zip', 'free_text', 'PDF Creator', 'free_text', 'Team Viewer QS6 / QS7', 'free_text'];
            @endphp
            <div class="row">
                @foreach ($application_detail_items as $item)
                    <div class="col-6">
                        @if ($item == 'free_text')
                            <input class="form-control form-control-sm application_details_input" type="text"
                                name="application_details[]" placeholder="(optional)">
                        @else
                            <div class="form-check">
                                <input class="form-check-input application_details_check" type="checkbox"
                                    name="application_details[]" value="{{ $item }}">
                                <label class="form-check-label">{{ $item }}</label>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @php
                $sebagais = ['Dibuat Oleh', 'Diperiksa Oleh', 'Diketahui Oleh', 'Diinput Oleh'];
                $positions = ['User ( Min. SPV )', 'Atasan Langsung (Min.manager)', 'IT OSM', 'IT IS Staff / IT IS SPV'];
            @endphp
            <div class="row">
                @foreach ($sebagais as $sebagai)
                    <div class="col-3 border border-dark small text-center">
                        {{ $sebagai }}
                    </div>
                @endforeach
                @foreach ($positions as $position)
                    <div class="col-3 border border-dark small text-center">
                        {{ $position }}
                    </div>
                @endforeach
                <div class="col-6 border border-dark small text-center text-info">
                    otomatis mengikuti matriks approval tiket
                </div>
                <div class="col-6 border border-dark small text-center">
                    @php
                        if (isset($fri_authorization)) {
                            $string_authorization = $fri_authorization->authorization_detail->pluck('employee_name')->toArray();
                            $string_authorization = implode($string_authorization, ' -> ');
                        } else {
                            $string_authorization = 'Approval FRI belum terdaftar';
                        }
                    @endphp
                    <input type="text" class="form-control form-control-sm" value="{{ $string_authorization }}"
                        disabled>
                    <input type="hidden" name="fri_authorization"
                        value="{{ isset($fri_authorization) ? $fri_authorization->id : '' }}" required>
                </div>
            </div>
            <br>FRM-ITD-008 REV 02
        </div>
    </form>
@else
    @php
        $fri_forms = $ticket->fri_forms;
    @endphp
    <div class="row">
        @foreach ($fri_forms as $fri_form)
        <div class="col-md-6 px-1 mt-2">
            <div class="box p-3">
                <div class="text-center h5">FORM<br>REQUEST INFRASTRUKTUR</div>
                <table class="table table-bordered table-sm small">
                    <tbody>
                        <tr>
                            <td>Date Request</td>
                            <td>{{ $fri_form->date_request }}</td>
                        </tr>
                        <tr>
                            <td>Date Use</td>
                            <td>{{ $fri_form->date_use }}</td>
                        </tr>
                        <tr>
                            <td>Work Location</td>
                            <td>{{ $fri_form->work_location }}</td>
                        </tr>
                        <tr>
                            <td>Area</td>
                            <td>{{ $fri_form->salespoint_name }}</td>
                        </tr>
                        <tr>
                            <td>User Name / Position</td>
                            <td>{{ $fri_form->username_position }}</td>
                        </tr>
                        <tr>
                            <td>Div / Dept</td>
                            <td>{{ $fri_form->division_department }}</td>
                        </tr>
                        <tr>
                            <td>Contact Number</td>
                            <td>{{ $fri_form->contact_number }}</td>
                        </tr>
                        <tr>
                            <td>Email Address</td>
                            <td>{{ $fri_form->email_address }}</td>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="h5 text-center">HARDWARE DETAILS</div>
                <table class="table table-bordered table-sm small">
                    <thead>
                        <tr class="text-center">
                            <th width="8%">NO</th>
                            <th>UNIT</th>
                            <th>QTY</th>
                            <th width="50%">REMARK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hardware_details = json_decode($fri_form->hardware_details);
                            $disabled_hardware_details = ($fri_form->disabled_hardware_details != null) ? json_decode($fri_form->disabled_hardware_details) : [];
                        @endphp
                        @foreach ($hardware_details as $key => $detail)
                            @php
                                $isDisabled = false;
                                if(in_array($detail->name,$disabled_hardware_details)){
                                    $isDisabled = true;
                                }
                            @endphp
                            <tr class="text-center">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $detail->name }}</td>
                                <td @if($isDisabled) style="text-decoration: line-through;" @endif>{{ $detail->qty != 0 ? $detail->qty : '' }}</td>
                                <td @if($isDisabled) style="text-decoration: line-through;" @endif>{{ $detail->remark }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-center h5">
                    Application Details
                </div>
                @php
                    $application_detail_items = ['Microsoft Office 2007', 'Anti Virus MS Essential / MS Defender', 'SAP', 'Mozilla Firefox / Google Chrome / IE', 'Adobe Acrobat', 'free_text', '7Zip', 'free_text', 'PDF Creator', 'free_text', 'Team Viewer QS6 / QS7', 'free_text'];
                    $selected_details = json_decode($fri_form->application_details);
                    $custom_details = [];
                    foreach ($selected_details as $key => $detail) {
                        // filter untuk item2 dengan nama custom
                        if (!in_array($detail, $application_detail_items)) {
                            unset($selected_details[$key]);
                            array_push($custom_details, $detail);
                        }
                    }
                @endphp
                <div class="row">
                    @foreach ($application_detail_items as $item)
                        <div class="col-6">
                            @if (in_array($item, $selected_details))
                                <i class="fal fa-check-square mr-1" aria-hidden="true"></i>{{ $item }}
                            @elseif($item == 'free_text' && count($custom_details) > 0)
                                <i class="fal fa-check-square mr-1" aria-hidden="true"></i>{{ $custom_details[0] }}
                                @php array_shift($custom_details); @endphp
                            @else
                                <i class="fal fa-square mr-1" aria-hidden="true"></i>{{ $item != 'free_text' ? $item : '' }}
                            @endif
                        </div>
                    @endforeach
                </div>
        
                @php
                    $sebagais = ['Dibuat Oleh', 'Diperiksa Oleh', 'Diketahui Oleh', 'Diinput Oleh'];
                    $positions = ['User ( Min. SPV )', 'Atasan Langsung (Min.manager)', 'IT OSM', 'IT IS Staff / IT IS SPV'];
                    $level = [1, 2, 4, 3];
                @endphp
                <div class="row">
                    @foreach ($sebagais as $sebagai)
                        <div class="col-3 border border-dark small text-center">
                            {{ $sebagai }}
                        </div>
                    @endforeach
                    @foreach ($positions as $key => $position)
                        @php
                            $author = $fri_form->authorizations->where('level', $level[$key])->first();
                        @endphp
                        <div class="col-3 border border-dark small text-center d-flex justify-content-between flex-column">
                            <span style="white-space: pre-line;">{{ $position }}</span>
                            <div>
                                @if ($author->status == 1)
                                    <span class="text-success">Approved</span><br>
                                    <span
                                        class="text-success">{{ $author->updated_at->translatedFormat('d F Y (H:i)') }}</span><br>
                                @endif
                                @if ($author->status == 0)
                                    <span class="text-warning">Menunggu Approval</span><br>
                                @endif
                            </div>
                            <span>{{ $author->employee_name }}</span>
                        </div>
                    @endforeach
        
                </div>
        
                @if ($fri_form->status == 1)
                    <div class="d-flex justify-content-center mt-2">
                        <a class="btn btn-primary btn-sm text-light" href="/printfriform/{{ $fri_form->id }}"
                            role="button">Cetak</a>
                        @if ($fri_form->ticket_vendor_id == null && Auth::user()->id == 1)
                        <a class="btn btn-primary btn-sm text-light ml-2" onclick="splitFRI({{ $fri_form->id }})" role="button">Split Form</a>
                        @endif
                    </div>
                @endif
                @if (isset($fri_form->ticket_vendor))
                <small class="text-secondary">* terkait vendor terpilih "{{ $fri_form->ticket_vendor->name }}"</small>
                @endif
                <br>FRM-ITD-008 REV 02
            </div>
        </div>
        @endforeach
    </div>
@endif
@section('fri-js')
    {{-- form fri --}}
    <script>
        let pageURL = $(location).attr("href");
        let fri_form_data = null;
        @if(isset($ticket))
            fri_form_data = @json(($ticket->fri_forms->count()>0) ? $ticket->fri_forms->first() : null);
        @endif
        if (pageURL.includes('/ticketing/') || pageURL.includes('/addnewticket')) {
            $(document).ready(function() {
                refreshFRIitems();
                $('.is_it').change(function() {
                    if ($(this).val() == true) {
                        $('#fri_form_field').removeClass('d-none');
                    } else {
                        $('#fri_form_field').addClass('d-none');
                    }
                });
                $(document).on('click', '.add_button , .remove_list', function(e) {
                    refreshFRIitems();
                });
                $('.salespoint_select2').change(function() {
                    let kantor_type = "";
                    switch ($(this).find('option:selected').data('status')) {
                        case 0: // 0 depo
                            kantor_type ="Kantor Cabang";
                            break;
                        case 1: // 1 cabang
                            kantor_type ="Kantor Cabang";
                            break;
                        case 2: // 2 cellpoint
                            kantor_type ="Kantor Cabang";
                            break;
                        case 3: // 3 subdist / indirect
                            kantor_type ="Kantor Cabang";
                            break;
                        case 4: // 4 nasional
                            kantor_type ="Kantor Cabang";
                            break;
                        case 5: // 5 HO
                            kantor_type ="Kantor Pusat";
                            break;
                        case 6: // 6 cellpoint+
                            kantor_type ="Kantor Cabang";
                            break;
                        default:
                            kantor_type ="";
                            break;
                    }
                    $('#fri_form_create input[name="work_location"]').val(kantor_type);
                    if ($(this).val()) {
                        $('#fri_form_create input[name="salespoint_name"]').val($(this).find(
                            'option:selected').text().split("--")[0].trim());
                    } else {
                        $('#fri_form_create input[name="salespoint_name"]').val("");
                    }
                });
                $('.salespoint_select2').trigger('change');
                $('.created_date').change(function() {
                    if ($(this).val()) {
                        $('#fri_form_create .date_request').val($(this).val());
                    } else {
                        $('#fri_form_create .date_request').val("");
                    }
                });
                $('.created_date').trigger('change');
                $('.requirement_date').change(function() {
                    if ($(this).val()) {
                        $('#fri_form_create .date_use').val($(this).val());
                    } else {
                        $('#fri_form_create .date_use').val('');
                    }
                });
                $('.requirement_date').trigger('change');
                $('.authorization_select2').change(function() {
                    if ($(this).val()) {
                        let selected_data = $(this).find('option:selected').data('item');
                        $('#fri_form_create input[name="email_address"]').val(selected_data.detail[0]
                            .employee_email);
                        $('#fri_form_create input[name="username_position"]').val(selected_data.detail[0]
                            .name + " | " + selected_data.detail[0].position);
                    } else {
                        $('#fri_form_create input[name="email_address"]').val("");
                        $('#fri_form_create input[name="username_position"]').val("");
                    }
                })
                setTimeout(() => {
                    if (fri_form_data) {
                        $('#fri_form_create .date_request').val(fri_form_data.date_request);
                        $('#fri_form_create .date_use').val(fri_form_data.date_use);
                        $('#fri_form_create input[name="work_location"]').val(fri_form_data.work_location);
                        $('#fri_form_create input[name="salespoint_name"]').val(fri_form_data
                            .salespoint_name);
                        $('#fri_form_create input[name="username_position"]').val(fri_form_data
                            .username_position);
                        $('#fri_form_create input[name="division_department"]').val(fri_form_data
                            .division_department);
                        $('#fri_form_create input[name="contact_number"]').val(fri_form_data
                            .contact_number);
                        $('#fri_form_create input[name="email_address"]').val(fri_form_data.email_address);

                        // application details
                        const application_detail_items = ['Microsoft Office 2007',
                            'Anti Virus MS Essential / MS Defender', 'SAP',
                            'Mozilla Firefox / Google Chrome / IE', 'Adobe Acrobat', '7Zip',
                            'PDF Creator', 'Team Viewer QS6 / QS7'
                        ];
                        const application_details = JSON.parse(fri_form_data.application_details);
                        application_details.forEach(function(application, index) {
                            if (application_detail_items.includes(application)) {
                                $('#fri_form_create .application_details_check[value="' +
                                    application + '"]').prop('checked', true);
                            } else {
                                $('#fri_form_create .application_details_input').each(function(
                                    index, element) {
                                    if ($(element).val() == "") {
                                        $(element).val(application);
                                        return false;
                                    }
                                });
                            }
                        })
                    }
                }, 3000);
            });

            function refreshFRIitems() {
                const items = ['PC Desktop', 'Server', 'Notebook', 'Monitor', 'UPS', 'Printer INK Copy Scan',
                    'Printer Dot Matriks', 'HandHelt / Handset', 'Finger Scan', 'Dvd Eksternal', 'Hdd Eksternal',
                    'Memory', 'Etc'
                ];
                $('#hardware_details_table tbody').empty();
                let count_items = 0
                // default item;
                items.forEach(function(item, index, arr) {
                    count_items++;
                    let selected_item = null;
                    $('.item_list').each(function(index, element) {
                        const it_alias = $(element).data('it_alias');
                        const name = $(element).find('td:eq(0)').text().trim();
                        const brand = $(element).find('td:eq(1)').text().trim();
                        const type = $(element).find('td:eq(2)').text().trim();
                        const qty = $(element).find('td:eq(4)').text().trim();
                        if (item == it_alias) {
                            selected_item = new Object();
                            selected_item.name = name;
                            selected_item.brand = brand;
                            selected_item.type = type;
                            selected_item.qty = qty;
                        }
                    });

                    let remark = "";
                    let string_row = '<tr>';
                    string_row += '<td class="text-center">' + count_items + '</td>';
                    string_row += '<td>' + item + '</td>';
                    if (selected_item) {
                        string_row += '<td class="text-center">' + selected_item.qty + '</td>';
                        string_row += '<td>';
                        remark += selected_item.name;
                        if (selected_item.brand.trim() != "") {
                            remark += ' | ' + selected_item.brand;
                        }
                        if (selected_item.type.trim() != "") {
                            remark += ' | ' + selected_item.type
                        }
                        string_row += remark;
                        string_row += '</td>';
                    } else {
                        string_row += '<td></td>';
                        string_row += '<td></td>';
                    }
                    string_row += '</tr>';
                    string_row += '<input type="hidden" name="hardware_details[' + count_items +
                        '][name]" value="' + item + '">';
                    string_row += '<input type="hidden" name="hardware_details[' + count_items + '][qty]" value="' +
                        ((selected_item) ? selected_item.qty : 0) + '">';
                    string_row += '<input type="hidden" name="hardware_details[' + count_items +
                        '][remark]" value="' + remark + '">';
                    $('#hardware_details_table tbody').append(string_row);
                });
                // non default item
                $('.item_list').each(function(index, element) {
                    const it_alias = $(element).data('it_alias');
                    const name = $(element).find('td:eq(0)').text().trim();
                    const brand = $(element).find('td:eq(1)').text().trim();
                    const type = $(element).find('td:eq(2)').text().trim();
                    const qty = $(element).find('td:eq(4)').text().trim();
                    if (!items.includes((it_alias ?? name))) {
                        count_items++;
                        let remark = "";
                        let string_row = '<tr>';
                        string_row += '<td class="text-center">' + count_items + '</td>';
                        string_row += '<td>' + (it_alias ?? name) + '</td>';
                        string_row += '<td class="text-center">' + qty + '</td>';
                        string_row += '<td>';
                        remark += name;
                        if (brand.trim() != "") {
                            remark += ' | ' + brand;
                        }
                        if (type.trim() != "") {
                            remark += ' | ' + type
                        }
                        string_row += remark;
                        string_row += '</td>';
                        string_row += '</tr>';
                        string_row += '<input type="hidden" name="hardware_details[' + count_items +
                            '][name]" value="' + (it_alias ?? name) + '">';
                        string_row += '<input type="hidden" name="hardware_details[' + count_items +
                            '][qty]" value="' + qty + '">';
                        string_row += '<input type="hidden" name="hardware_details[' + count_items +
                            '][remark]" value="' + remark + '">';
                        $('#hardware_details_table tbody').append(string_row);
                    }
                });
            }
        }

        function splitFRI(fri_form_id){
            $('#submitform').prop('action', '/bidding/manualsplit');
            $('#submitform').find('div').empty();
            $('#submitform').prop('method', 'POST');
            $('#submitform').find('div').append('<input type="hidden" name="fri_form_id" value="'+fri_form_id+'">');
            $('#submitform').submit();
        }
    </script>
@endsection
