@extends('Layout.app')
@section('local-css')

@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Karyawan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item active">Karyawan</li>
                </ol>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-4">
            
            <button type="button" class="btn btn-info ml-2" data-toggle="modal" data-target="#resetPasswordModal">
                Reset Password
            </button>

            <button type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#addEmployeeModal">
                Tambah Karyawan
            </button>
        </div>
    </div>
</div>
<div class="content-body px-4">
    <div class="table-responsive">
        <table id="employeeDT" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr role="row">
                    <th>#</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Job Title</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employee_pst as $key => $employee)
                {{-- @dd($employee) --}}
                    <tr data-employee="{{$employee}}">
                        <td>{{$key+1}}</td>
                        <td>{{$employee->code}}</td>
                        <td>{{$employee->name}}</td>
                        @if ($employee->username == NULL)
                            <td>-</td>
                        @else
                            <td>{{$employee->username}}</td>
                        @endif
                        <td>{{$employee->emp_position}}</td>
                        <td>{{$employee->email}}</td>
                        <td>{{$employee->statusName()}}</td>
                    </tr> 
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/addEmployee" method="post" id="addemployeeform">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Karyawan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field">Nama Karyawan</label>
                              <input type="text" class="form-control" name="name" placeholder="Masukkan nama karyawan" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field">Job Title</label>
                              <select class="form-control select2" id="job_title_id" name="job_title_id" required>
                                    <option value="">-- Pilih Job Title --</option>
                                    @foreach ($employee_positions as $employee_positions)
                                        <option value="{{ $employee_positions->id }}">{{ $employee_positions->name }}</option>
                                    @endforeach
                              </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                            <label class="optional_field">Nomor Telfon</label>
                            <input type="text" class="form-control" name="phone" placeholder="ex 08xxxxxxxxxx">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field required_field_username">username</label>
                              <input type="text" class="form-control username" name="username" placeholder="Masukkan username (ex: userhobandung1)" required>
                              <small class="text-info">Username bersifat unik</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field">Email Karyawan</label>
                              <input type="email" class="form-control" name="email" placeholder="Masukkan email karyawan" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field required_field_password">Kata Sandi</label>
                              <input type="password" class="form-control password" oninput="validatepassword()" value="pma123" name="password" placeholder="Masukkan kata sandi" id="password" required>
                              <small class="text-danger">* karyawan akan melakukan pergantian password saat pertama kali melakukan login. Kata sandi ini merupakan kata sandi untuk pertama kali / sementara</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field required_field_password">Konfirmasi Kata Sandi</label>
                              <input type="password" class="form-control password" oninput="validatepassword()" value="pma123" id="confirmpassword" name="conf_password" placeholder="Konfirmasi Kata sandi" required>
                              <small class="text-danger d-none" id="confpassworderror">konfirmasi kata sandi tidak sesuai</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Tambah Karyawan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password Karyawan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <form action="/resetemployeepassword" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field">Pilih Karyawan yang di dilakukan reset password</label>
                              <select class="form-control select2" name="employee_id" required>
                                  <option value="">-- Pilih Karyawan --</option>
                                  @foreach ($employee_pst as $employee)
                                      <option value="{{ $employee->id }}">{{ $employee->name }} || {{ $employee->code }} || {{ $employee->emp_position }}</option>
                                  @endforeach
                              </select>
                              <small class="text-danger">* Password karyawan secara otomatis akan menjadi password default "pma123"</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/updateEmployee" method="post" id="updateemployeeform">
            @csrf
            @method('patch')
            <input type="hidden" name="employee_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Karyawan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field">Code</label>
                              <input type="text" class="form-control" name="code" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field">Nama Karyawan</label>
                              <input type="text" class="form-control" name="name" placeholder="Masukkan nama karyawan">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                            <label class="optional_field">Nomor Telfon</label>
                            <input type="text" class="form-control" name="phone" placeholder="ex 08xxxxxxxxxx">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field">Job Title</label>
                              <input type="text" class="form-control" name="job_title_id_bfr" readonly>
                            </div>
                        </div>
                        <div class="col-12 username_edit">
                            <div class="form-group">
                              <label class="required_field required_field_username_edit">username</label>
                              <input type="text" class="form-control" name="username" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field">Email Karyawan</label>
                              <input type="email" class="form-control" name="email" placeholder="Masukkan nama karyawan">
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    {{-- <button type="submit" class="btn btn-danger delete-button" onclick="deleteemployee()">Hapus</button> --}}
                    <button type="submit" class="btn btn-danger nonactive-button" onclick="nonactive()">Non Aktifkan</button>
                    <button type="submit" class="btn btn-success active-button" onclick="active()">Aktifkan</button>
                    <button type="submit" class="btn btn-info">Update Karyawan</button>
                </div>
            </div>
        </form>
        <form action="/nonactiveemployee" method="post" id="nonactiveform">
            @csrf
            @method('patch')
            <input type="hidden" name="updated_at">
            <input type="hidden" name="employee_id">
        </form>
        <form action="/activeemployee" method="post" id="activeform">
            @csrf
            @method('patch')
            <input type="hidden" name="updated_at">
            <input type="hidden" name="employee_id">
        </form>
        <form action="/deleteemployee" method="post" id="deleteform">
            @csrf
            @method('delete')
            <input type="hidden" name="updated_at">
            <input type="hidden" name="employee_id">
        </form>
    </div>
</div>

{{-- new Employee Assign --}}
<div class="modal fade" id="switchEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pindah Karyawan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="form-group">
                            <label for="">Pilih Email Karyawan Lama</label>
                            <select class="form-control select2" name="old_employee" required>
                                <option value="">-- Pilih karyawan --</option>
                                @for ($i = 0; $i < 10; $i++)
                                    <option value="{{$i}}">Karyawan{{$i}}@gmail.com - Jabatan {{$i}}</option>
                                @endfor
                            </select>
                            <small class="text-danger">*PERHATIAN -- email karyawan lama yang dipilih tidak akan mendapatkan akses ke PMA Purchasing. Semua tugas akan dipindah ke akun baru.</small>
                          </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                          <label for="">Nama Karyawan</label>
                          <input type="text" class="form-control" name="name" aria-describedby="helpId" placeholder="Masukkan nama karyawan" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                          <label for="">Email Karyawan</label>
                          <input type="email" class="form-control" name="email" aria-describedby="helpId" placeholder="Masukkan email karyawan" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                          <label for="">Kata Sandi</label>
                          <input type="password" class="form-control" value="12345678" name="password" aria-describedby="helpId" placeholder="Masukkan nama karyawan" required>
                          <small class="helpId text-danger">* karyawan akan melakukan pergantian password saat pertama kali melakukan login. Kata sandi ini merupakan kata sandi untuk pertama kali / sementara</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                          <label for="">Konfirmasi Kata Sandi</label>
                          <input type="password" class="form-control" value="12345678" name="password" aria-describedby="helpId" placeholder="Masukkan nama karyawan" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Pindah Karyawan</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        var table = $('#employeeDT').DataTable(datatable_settings);
        $('#employeeDT tbody').on('click', 'tr', function () {
            let modal = $("#editEmployeeModal");
            let data = $(this).data('employee');
            console.log(data);
            modal.find('input[name="employee_id"]').val(data['id']);
            modal.find('input[name="updated_at"]').val(data['updated_at']);
            modal.find('input[name="name"]').val(data['name']);
            modal.find('input[name="job_title_id_bfr"]').val(data['emp_position']);
            modal.find('select[name="position"]').val(data['position_id']);
            modal.find('select[name="position"]').trigger('change');
            modal.find('input[name="phone"]').val(data['phone']);
            modal.find('input[name="code"]').val(data['code']);
            if (data['position_id'] == 106) {
                modal.find('.username_edit').hide();
                modal.find('.required_field_username_edit').removeClass('required_field').addClass('optional_field');
            }
            modal.find('input[name="username"]').val(data['username']);
            modal.find('input[name="email"]').val(data['email']);
            if(data['status'] == 0){
                modal.find('.active-button').hide();
                modal.find('.nonactive-button').show();
            }else{
                modal.find('.active-button').show();
                modal.find('.nonactive-button').hide();
            }
            modal.modal('show');
        });

        $('#target_employee, #source_employee').change(function(){
            if($('#source_employee').val() != "" && $('#target_employee').val() != ""){
                if($('#target_employee').val() == $('#source_employee').val()){
                    alert('Pilihan karyawan asal dan target karyawan tidak boleh sama');
                    $(this).val("");
                    $(this).trigger('change');
                }
            }
        });

        $('#job_title_id').on('change', function() {
            job_title_id = parseInt($(this).val());
            job_title_lock = [106];
            if (job_title_lock.includes(job_title_id)) {
                $('.username').prop('required',false);
                $('.username').prop('readonly',true);

                $('.password').prop('required',false);
                $('.password').prop('readonly',true);

                $('.password').val('');

                $('.required_field_username').removeClass('required_field').addClass('optional_field');
                $('.required_field_password').removeClass('required_field').addClass('optional_field');
            }
            else {
                $('.username').prop('required',true);
                $('.username').prop('readonly',false);

                $('.password').prop('required',true);
                $('.password').prop('readonly',false);

                
                $('.password').val('pma123');

                $('.required_field_username').removeClass('optional_field').addClass('required_field');
                $('.required_field_password').removeClass('optional_field').addClass('required_field');
            }
        });

        $('#employee_id').change(function () { 
            let requestdata = {
                employee_id: $(this).val()
            }; 
            
            $.ajax({
                type: "get",
                url: '/getEmployeePosition',
                data: requestdata,
                success: function(response) {
                    let data = response.data;
                    data.forEach(item => {
                        $('#job_title_id_bfr').val(item.emp_position)
                    });
                    $('.job_title_id_bfr').val("");
                    $('.job_title_id_bfr').trigger('change');
                },
                error: function(response) {
                    alert('load data failed. Please refresh browser or contact admin');
                    console.log(response);
                },
                complete: function() {
                    $('.job_title_id_bfr').trigger('change');
                }
            });
        });
    })
    function validatepassword(){
        let password = $('#password').val();
        let confirmpassword = $('#confirmpassword').val();
        let message = $('#confpassworderror')
        if(password != confirmpassword){
            message.removeClass('d-none');
        }else{
            message.addClass('d-none');
        }
    }
    
    function nonactive(){
        if (confirm('Karyawan yang di non aktifkan tidak dapat login. Lanjutkan?')) {
            $('#nonactiveform').submit();
        }
    }
    function active(){
        if (confirm('Karyawan akan diaktifkan kembali. Lanjutkan?')) {
            $('#activeform').submit();
        }
    }
    function deleteemployee(){
        if (confirm('Karyawan yang dihapus tidak dapat dikembalikan. Lanjutkan?')) {
            $('#deleteform').submit();
        }
    }
</script>
@endsection
