@extends('Layout.app')
@section('local-css')
    <style>
        #pills-tab .nav-link {
            background-color: #a01e2b48;
            color: black !important;
        }

        #pills-tab .nav-link.active {
            background-color: #A01E2A;
            color: white !important;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Customer</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Customer</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCustomerModal"
                    id="addbutton">
                    Tambah Customer
                </button>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-barangjasa" role="tabpanel"
                aria-labelledby="pills-barangjasa-tab">
                <div class="table-responsive">
                    <table id="customerDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Alias</th>
                                <th>Type</th>
                                <th>Alamat</th>
                                <th>Kota</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1 @endphp
                            @foreach ($customers as $key => $customers)
                                <tr data-customers="{{ $customers }}">
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $customers->code }}</td>
                                    <td>{{ $customers->name }}</td>
                                    <td>{{ $customers->alias }}</td>
                                    <td>{{ $customers->type_name() }}</td>
                                    <td>{{ $customers->address }}</td>
                                    <td>{{ $customers->regency->name }}</td>
                                    <td>{{ $customers->status_name() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCustomerModal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Customer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Kode</label>
                                    <input type="text" class="form-control code" name="code"
                                        placeholder="Masukkan kode customer" required>
                                    <small class="form-text text-danger">Kode customer bersifat unik / tidak boleh sama
                                        dengan
                                        kode customer lainnya</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Nama</label>
                                    <input type="text" class="form-control name" name="name"
                                        placeholder="Masukkan nama customer" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Alias</label>
                                    <input type="text" class="form-control alias" name="alias"
                                        placeholder="Masukkan alias" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Tipe Customer</label>
                                    <select class="form-control select2" name="cust_type">
                                        <option value="">-- Pilih Tipe --</option>
                                        @foreach ($customersType as $customersType)
                                            <option value="{{ $customersType->code }}">{{ $customersType->code }} || {{ $customersType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Kota Lokasi Customer</label>
                                    <select class="form-control select2" name="city_id">
                                        <option value="">-- Pilih Kota --</option>
                                        @foreach ($provinces as $province)
                                            <optgroup label="{{ $province->name }}">
                                                @foreach ($province->regencies as $regency)
                                                    <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Alamat</label>
                                    <textarea class="form-control address" name="address" placeholder="Masukkan alamat" required></textarea>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Tanggal Opening Store</label>
                                    <input type="date" class="form-control requirement_date" name="requirement_date" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Space</label>
                                    <input type="text" class="form-control space" name="space"
                                        placeholder="Masukkan Space" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h5>Store Staff</h5>
                                <table class="table table-bordered table_level" id="table_level">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Jabatan</th>
                                            <th>No. Telfon</th>
                                            <th>Email</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="empty_row text-center">
                                            <td colspan="5">Tidak ada</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="optional_field">Nama Store Staff</label>
                                    <input type="text" class="form-control staff_name" name="staff_name" id="staff_name"
                                        placeholder="Masukkan nama staff">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="optional_field">Jabatan</label>
                                    <select class="form-control select2 position_select2 position_text">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}">{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="optional_field">No. Telp Staff</label>
                                    <input type="text" class="form-control phone_staff" name="phone_staff" id="phone_staff"
                                        placeholder="Masukkan no. telp staff">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="optional_field">Email</label>
                                    <input type="email" class="form-control email_addr" name="email_addr" id="email_addr"
                                        placeholder="Masukkan alamat email">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp</label>
                                    <button type="button"
                                        class="btn btn-info form-control if_edit_disable add_new_level">Tambah</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary if_edit_disable" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary if_edit_disable" onclick="addCustomer()">Tambah
                            Customer</button>
                    </div>
                </div>
        </div>
        <form action="/addcustomer" method="post" id="#addform">
            @csrf
            <div class="inputfield">
            </div>
        </form>
    </div>
    
    <div class="modal fade" id="detailCustomerModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Kode</label>
                                <input type="text" class="form-control code" name="code"
                                    placeholder="Masukkan kode customer" disabled>
                                <small class="form-text text-danger">Kode customer bersifat unik / tidak boleh sama
                                    dengan
                                    kode customer lainnya</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Nama</label>
                                <input type="text" class="form-control name" name="name"
                                    placeholder="Masukkan nama customer" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Alias</label>
                                <input type="text" class="form-control alias" name="alias"
                                    placeholder="Masukkan alias" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Tipe Customer</label>
                                <select class="form-control select2" name="cust_type">
                                    <option value="">-- Pilih Tipe --</option>
                                    @foreach ($customersType2 as $customersType)
                                        <option value="{{ $customersType->code }}">{{ $customersType->code }} || {{ $customersType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Kota Lokasi Customer</label>
                                <select class="form-control select2" name="city_id">
                                    <option value="">-- Pilih Kota --</option>
                                    @foreach ($provinces as $province)
                                        <optgroup label="{{ $province->name }}">
                                            @foreach ($province->regencies as $regency)
                                                <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Alamat</label>
                                <textarea class="form-control address" name="address" placeholder="Masukkan alamat" required></textarea>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Tanggal Opening Store</label>
                                <input type="date" class="form-control requirement_date" name="requirement_date" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Space</label>
                                <input type="text" class="form-control space" name="space"
                                    placeholder="Masukkan Space" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <h5>Store Staff</h5>
                            <table class="table table-bordered table_level" id="table_level">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>No. Telfon</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="empty_row text-center">
                                        <td colspan="5">Tidak ada</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="optional_field">Nama Store Staff</label>
                                <input type="text" class="form-control staff_name" name="staff_name" id="staff_name"
                                    placeholder="Masukkan nama staff">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="optional_field">Jabatan</label>
                                <select class="form-control select2 position_select2 position_text">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="optional_field">No. Telp Staff</label>
                                <input type="text" class="form-control phone_staff" name="phone_staff" id="phone_staff"
                                    placeholder="Masukkan no. telp staff">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="optional_field">Email</label>
                                <input type="email" class="form-control email_addr" name="email_addr" id="email_addr"
                                    placeholder="Masukkan alamat email">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp</label>
                                <button type="button"
                                    class="btn btn-info form-control if_edit_disable add_new_level">Tambah</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary if_edit_disable" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger if_edit_disable"
                        onclick="deleteCustomer()">Hapus</button>
                    <button type="button" class="btn btn-primary if_edit_disable" onclick="updateCustomer()">Update
                        Customer</button>
                </div>
            </div>
        </div>
        <form action="/updatecustomer" method="post" id="updateform">
            @csrf
            @method('patch')
            <input type="hidden" name="customer_id">
            <div class="inputfield">
            </div>
        </form>

        <form action="/deletecustomer" method="post" id="deleteform">
            @csrf
            @method('delete')
            <input type="hidden" name="customer_id">
            <div class="inputfield">
            </div>
        </form>

    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            var table = $('#customerDT').DataTable(datatable_settings);
            $('#customerDT tbody').on('click', 'tr', function() {
                let modal = $('#detailCustomerModal');
                let data = $(this).data('customers');
                let table_level = modal.find('.table_level');
                let storeStaff = JSON.parse(data.store_staff);
                table_level.find('tbody').empty();
                
                storeStaff.forEach((item, index) => {
                    let append_text = '<tr data-id="' + item.id + 
                        '" data-name="' + item.name +
                        '" data-position="' + item.position + 
                        '" data-positionname="' + item.positionName + 
                        '" data-phone="' + item.phone + 
                        '" data-email="' + item.email + 
                        '"><td>' + item.name +
                        '</td><td>' + item.positionName + 
                        '</td><td>' + item.phone +
                        '</td><td>' + item.email +
                        '</td>';
                    append_text += '<td>';
                    append_text +=
                        '<i class="fa fa-trash text-danger remove_list" onclick="removeList(this)" aria-hidden="true"></i>';
                    append_text += '</td></tr>';

                    
                    table_level.find('tbody').append(append_text);
                });

                let code = modal.find('.code');
                let name = modal.find('.name');
                let alias = modal.find('.alias');
                let cust_type = modal.find('select[name="cust_type"]');
                let city_id = modal.find('select[name="city_id"]');
                let address = modal.find('.address');
                let requirement_date = modal.find('.requirement_date');
                let space = modal.find('.space');

                code.val(data['code']);
                name.val(data['name']);
                alias.val(data['alias']);
                cust_type.val(data['type']);
                cust_type.trigger('change');
                city_id.val(data['regency_id']);
                city_id.trigger('change');
                address.val(data['address']);
                requirement_date.val(data['opening_date']);
                space.val(data['space']);

                tableRefreshed(table_level);
                modal.modal('show');
            });

            $("#phone_staff").keyup((e) => {
                let tlp = $('#phone_staff').val();
                let tlp_format = format_tlp(tlp);

                if(tlp_format.length > 13){
                    tlp_format = tlp_format.substr(0, 13);
                }
                $('#phone_staff').val(tlp_format);
            });

            $('.add_new_level').on('click', function() {
                let closestmodal = $(this).closest('.modal');
                let staff_name = closestmodal.find('.staff_name');
                let position_select = closestmodal.find('.position_select2');
                let phone_staff = closestmodal.find('.phone_staff');
                let email_addr = closestmodal.find('.email_addr');
                let table_level = closestmodal.find('.table_level');
                
                let name = staff_name.val();
                let position_id = position_select.val();
                let position = position_select.find('option:selected').text().trim();
                let telp = phone_staff.val();
                let email = email_addr.val();

                // console.log(name, staff_name.val(), email_addr.val(), email);
                if (!isEmail(email)) {
                    
                    alert('Format email salah');
                    return;
                }
                
                let rowCount = $('#table_level tbody tr').not('.empty_row').length;
                let id = rowCount+1
                
                table_level.find('tbody').append('<tr data-id="' + id + 
                    '" data-name="' + name +
                    '" data-position="' + position_id + 
                    '" data-positionname="' + position + 
                    '" data-phone="' + telp + 
                    '" data-email="' + email + 
                    '"><td>' + name + 
                    '</td><td>' + position +
                    '</td><td>' + telp +
                    '</td><td>' + email +
                    '</td><td><i class="fa fa-trash text-danger remove_list" onclick="removeList(this)" aria-hidden="true"></i></td></tr>'
                );

                $('#staff_name').val('');
                position_select.val('');
                position_select.trigger('change');
                $('#phone_staff').val('');
                $('#email_addr').val('');
                tableRefreshed($(this));
            });
        });

        function deleteVendor(el) {
            if (confirm('Vendor akan dihapus dan tidak dapat dikembalikan. Lanjutkan?')) {
                $('#deleteform').submit();
            }
        }

        function isEmail(email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }

        function format_tlp(angka){
            var str = angka.replace(/[^,\d]/g, "").replaceAll(',','').toString();

            return str;
        }

        // table on refresh
        function tableRefreshed(current_element) {
            let closestmodal = $(current_element).closest('.modal');
            let table_level = closestmodal.find('.table_level');
            let cust_type = closestmodal.find('.cust_type');
            let city_id = closestmodal.find('.city_id');
            // check table level if table has data / tr or not
            let row_count = 0;
            table_level.find('tbody tr').not('.empty_row').each(function() {
                row_count++;
            });
            if (row_count > 0) {
                cust_type.prop('disabled', true);
                city_id.prop('disabled', true);
                table_level.find('.empty_row').remove();
                table_level.find('.level').each(function(index, el) {
                    $(el).text(index + 1);
                });
            } else {
                cust_type.prop('disabled', false);
                city_id.prop('disabled', false);
                table_level.append('<tr class="empty_row text-center"><td colspan="5">Tidak Ada</td></tr>');
            }
        }

        function removeList(el) {
            let closestmodal = $(el).closest('.modal');
            let table = closestmodal.find('table');
            let staff_name = closestmodal.find('.staff_name');
            let tr = $(el).closest('tr');
            let employee_id = tr.data('id');

            staff_name.val(employee_id);
            staff_name.val("");
            staff_name.trigger('change');
            tr.remove();
            tableRefreshed(table);
        }

        function addCustomer() {
            let modal = $('#addCustomerModal');
            let kode = modal.find('.code').val();
            let name = modal.find('.name').val();
            let alias = modal.find('.alias').val();
            let cust_type = modal.find('select[name="cust_type"]').val();
            let city = modal.find('select[name="city_id"]').val();
            let address = modal.find('.address').val();
            let requirement_date = modal.find('.requirement_date').val();
            let space = modal.find('.space').val();
                      
            let table_level = modal.find('.table_level');
            let stafflist = [];
            let list_count = 0;
            if (cust_type == "") {
                alert('Harap memilih customer type');
                return;
            }
            if (city == "") {
                alert('Harap memilih jenis kota');
                return;
            }
            table_level.find('tbody tr').not('.empty_row').each(function(index, el) {
                list_count++

                let id = $(el).data('id');
                let name = $(el).data('name');
                let position = $(el).data('position');
                let positionName = $(el).data('positionname');
                let phone = $(el).data('phone');
                let email = $(el).data('email');
                    
                stafflist.push({
                    "id": id,
                    "name": name,
                    "position": position,
                    "positionName": positionName,
                    "phone": phone,
                    "email": email
                })
            });
            
            // if (list_count < 1) {
            //     alert('Minimal 1 staff dipilih');
            //     return;
            // }
            
            // form filling
            let form = modal.find('form');
            let inputfield = form.find('.inputfield');
            
            inputfield.empty();
            inputfield.append('<input type="hidden" name="kode" value="' + kode + '">');
            inputfield.append('<input type="hidden" name="nama" value="' + name + '">');
            inputfield.append('<input type="hidden" name="alias" value="' + alias + '">');
            inputfield.append('<input type="hidden" name="cust_type" value="' + cust_type + '">');
            inputfield.append('<input type="hidden" name="regency_id" value="' + city + '">');
            inputfield.append('<input type="hidden" name="address" value="' + address + '">');
            inputfield.append('<input type="hidden" name="requirement_date" value="' + requirement_date + '">');
            inputfield.append('<input type="hidden" name="space" value="' + space + '">');
            inputfield.append("<input type='hidden' name='stafflist' value='" + JSON.stringify(stafflist) + "'>");
            form.submit();       
        }

        function updateCustomer() {
            let modal = $('#detailCustomerModal');
            let kode = modal.find('.code').val();
            let name = modal.find('.name').val();
            let alias = modal.find('.alias').val();
            let cust_type = modal.find('select[name="cust_type"]').val();
            let city = modal.find('select[name="city_id"]').val();
            let address = modal.find('.address').val();
            let requirement_date = modal.find('.requirement_date').val();
            let space = modal.find('.space').val();

            let table_level = modal.find('.table_level');
            let stafflist = [];
            let list_count = 0;
            if (cust_type == "") {
                alert('Harap memilih customer type');
                return;
            }
            if (city == "") {
                alert('Harap memilih jenis kota');
                return;
            }
            
            table_level.find('tbody tr').not('.empty_row').each(function(index, el) {
                list_count++

                let id = $(el).data('id');
                let name = $(el).data('name');
                let position = $(el).data('position');
                let positionName = $(el).data('positionname');
                let phone = $(el).data('phone');
                let email = $(el).data('email');
                    
                stafflist.push({
                    "id": id,
                    "name": name,
                    "position": position,
                    "positionName": positionName,
                    "phone": phone,
                    "email": email
                })
            });

            // if (list_count < 1) {
            //     alert('Minimal 1 otorisasi dipilih');
            //     return;
            // }

            // form filling
            let form = $('#updateform');
            let inputfield = form.find('.inputfield');
            
            inputfield.empty();
            inputfield.append('<input type="hidden" name="kode" value="' + kode + '">');
            inputfield.append('<input type="hidden" name="nama" value="' + name + '">');
            inputfield.append('<input type="hidden" name="alias" value="' + alias + '">');
            inputfield.append('<input type="hidden" name="cust_type" value="' + cust_type + '">');
            inputfield.append('<input type="hidden" name="regency_id" value="' + city + '">');
            inputfield.append('<input type="hidden" name="address" value="' + address + '">');
            inputfield.append('<input type="hidden" name="requirement_date" value="' + requirement_date + '">');
            inputfield.append('<input type="hidden" name="space" value="' + space + '">');
            inputfield.append("<input type='hidden' name='stafflist' value='" + JSON.stringify(stafflist) + "'>");
            form.submit();
        }

        function deleteCustomer() {
            if (confirm('Data Customer akan dihapus dan tidak dapat dikembalikan. Lanjutkan?')) {
                let modal = $('#detailCustomerModal');
                let kode = modal.find('.code').val();
                let form = $('#deleteform');
                let inputfield = form.find('.inputfield');
                
                inputfield.empty();
                inputfield.append('<input type="hidden" name="kode" value="' + kode + '">');
                form.submit();
            } else {

            }
        }

    </script>
@endsection
