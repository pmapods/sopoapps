<style>
    .formfasilitas .table-bordered td{
        border : 1px solid #000 !important;
    }
</style>
@if ($armadaticket->status != -1)
    <h5>
        Formulir Fasilitas
        @if (($armadaticket->facility_form->status ?? -1) == 1) <span class="text-success">(Selesai)</span> @endif
    </h5> 
    @if (isset($armadaticket->facility_form))
        @php
            $facility_form = $armadaticket->facility_form;
        @endphp
        <div class="row border border-dark bg-light p-2">
            <div class="col-9">
                <table class="table table-bordered table-sm text-center h-100">
                    <tbody>
                        <tr>
                            <td rowspan="2" class="align-middle">
                                <img src="/assets/logo.png" width="80px">
                            </td>
                            <td class="align-middle h5 table-secondary">FORMULIR</td>
                            <td class="align-middle table-secondary">Hal</td>
                        </tr>
                        <tr>
                            <td class="align-middle h5">FASILITAS KARYAWAN & PERLENGKAPAN KERJA KARYAWAN BARU</td>
                            <td class="align-middle">1/1</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-3">
                <table class="table table-bordered table-sm text-center h-100 small">
                    <tr><td class="table-secondary">
                        <label class="required_field">
                            Tanggal
                        </label>
                    </td></tr>
                    <tr><td>
                        <input type="date" class="form-control form-control-sm" name="date" value="{{ $facility_form->created_at->format('Y-m-d') }}" readonly>
                    </td></tr>
                    <tr><td class="table-secondary">
                        <label class="required_field">
                            Nomor
                        </label>
                    </td></tr>
                    <tr><td>
                        <input type="text" 
                        class="form-control form-control-sm"
                        placeholder="akan diisi oleh sistem"
                        value="{{ $facility_form->code }}"
                        readonly>
                    </td></tr>
                </table>
            </div>
            <div class="col-12">
                <table class="table table-bordered table-sm align-middle">
                    <tr>
                        <td width="20%" class="table-secondary">
                            <label class="required_field small">Nama</label>
                        </td>
                        <td colspan="3">
                            <input type="text" class="form-control form-control-sm" placeholder="Masukkan Nama" value="{{ $facility_form->nama}}" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td width="20%" class="table-secondary">
                            <label class="required_field small">Divisi/Dept/Bag</label>
                        </td>
                        <td>
                            <input type="text" 
                            class="form-control form-control-sm"
                            value="{{ $facility_form->divisi }}"
                            placeholder="Masukkan Divisi" readonly>
                        </td>
                        <td width="20%" class="table-secondary">
                            <label class="required_field small">Telephone</label>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" 
                            value="{{ $facility_form->phone }}"
                            name="phone" 
                            placeholder="Masukkan Telefon" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td width="20%" class="table-secondary">
                            <label class="required_field small">Jabatan</label>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                            value="{{ $facility_form->jabatan }}"
                        placeholder="Masukkan Jabatan" readonly>
                        </td>
                        <td width="20%" class="table-secondary">
                            <label class="required_field small">Tanggal Masuk Kerja</label>
                        </td>
                        <td>
                            <input type="date" 
                            class="form-control form-control-sm"
                            value="{{ $facility_form->tanggal_mulai_kerja }}"
                            readonly>
                        </td>
                    </tr>
                    <tr>
                        <td width="20%" class="table-secondary">
                            <label class="required_field small">HO/Cabang/Depo</label>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" placeholder="Masukkan Nama SalesPoint" readonly value="{{ $facility_form->salespoint->name }}">
                        </td>
                        <td width="20%" class="table-secondary">
                            <label class="required_field small">Golongan</label>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" 
                            placeholder="Masukkan Golongan"
                            value="{{ $facility_form->golongan }}" 
                            readonly>
                        </td>
                    </tr>
                    <tr>
                        <td width="20%" class="table-secondary">
                            <label class="small required_field">Status Karyawan</label>
                        </td>
                        <td colspan="3">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" 
                                    @if($facility_form->status_karyawan == "percobaan") checked @endif disabled
                                    value="percobaan" readonly>Percobaan
                                </label>
                            </div>
                            
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" 
                                    @if($facility_form->status_karyawan == "tetap") checked @endif disabled
                                    value="tetap" readonly>Tetap
                                </label>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td width="20%" class="table-secondary">
                            <label class="small">Fasilitas & Perlengkapan Kerja</label>
                        </td>
                        <td colspan="3">
                            @php
                                $list = json_decode($facility_form->facilitylist);
                            @endphp
                            <div class="row">
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="1" @if (in_array(1,$list)) checked @endif disabled > Ruangan, lokasi</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="2" @if (in_array(2,$list)) checked @endif disabled> Pesawat telepon</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="3" @if (in_array(3,$list)) checked @endif disabled> Meja & Kursi</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="4" @if (in_array(4,$list)) checked @endif disabled> Line & Telepon</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="5" @if (in_array(5,$list)) checked @endif disabled> PC / LOP</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="6" @if (in_array(6,$list)) checked @endif disabled> Kartu Nama</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="7" @if (in_array(7,$list)) checked @endif disabled> Mobil Dinas</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="8" @if (in_array(8,$list)) checked @endif disabled> ATK & perlengkapan kerja</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="9" @if (in_array(9,$list)) checked @endif disabled> Rumah Dinas</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="10" @if (in_array(10,$list)) checked @endif disabled> Lemari Arsip / Filling Kabinet / Whiteboard</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="11" @if (in_array(11,$list)) checked @endif disabled> Akses Internet</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="12" @if (in_array(12,$list)) checked @endif disabled> ID Card</div>
                                <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]"
                                value="13" @if (in_array(13,$list)) checked @endif disabled> Akses email Pinus Merah Abadi</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:100px;" width="20%" class="table-secondary">
                            <label class="small">Catatan</label>
                        </td>
                        <td colspan="3">
                            <textarea class="form-control" 
                            rows="3"
                            style="resize:none" 
                            placeholder="Tambahkan catatan (optional)" 
                            readonly
                            name="notes">{{ $facility_form->notes }}</textarea>
                        </td>
                    </tr>
                </table>
                <div class="col-12">
                    <small>* Jenis Fasilitas yang disiapkan adalah standar yang berdasarkan Surat keputusan Direksi mengenai Standar Kompetensi dan Benefit</small>
                </div>
                <div class="offset-6 col-6">
                    <table class="table table-sm table-bordered text-center authorization_table">
                        <tbody>
                            <tr>
                                @foreach ($facility_form->facility_form_authorizations as $authorization)
                                    <td class="align-middle small table-secondary">{{ $authorization->as }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach ($facility_form->facility_form_authorizations as $authorization)
                                    <td width="50%" class="align-bottom small" style="height: 80px">
                                        @if (($facility_form->current_authorization()->id ?? -1) == $authorization->id)
                                            <span class="text-warning">Pending approval</span><br>
                                        @endif
                                        @if ($authorization->status == 1)
                                            <span class="text-success">Approved {{ $authorization->updated_at->format('Y-m-d (H:i)') }}</span><br>
                                        @endif
                                        {{ $authorization->employee_name }}<br>{{ $authorization->employee_position }}
                                    </td>
                                @endforeach
                            </tr>   
                        </tbody>
                    </table>
                </div>
                <div class="col-12 text-center">
                    @if (($facility_form->current_authorization()->employee_id ?? '-1') == Auth::user()->id)
                        <button type="button" class="btn btn-success" onclick="facilityapprove({{ $facility_form->id }})">Approve</button>
                        <button type="button" class="btn btn-danger" onclick="facilityreject({{ $facility_form->id }})">Reject</button>
                    @endif
                </div>
            </div>
            @if ($facility_form->status == 1)
            <div class="col-12 d-flex justify-content-center">
                <a class="btn btn-primary btn-sm" href="/printfacilityform/{{ $armadaticket->code }}" role="button">Cetak</a>
            </div>
            @endif
        </div>
    @else
        @isset($armadaticket->last_rejected_facility_form)
            Di Reject Oleh : <span class="text-danger">{{ $armadaticket->last_rejected_facility_form->terminated_by_employee->name ?? "-" }}</span><br>
            Alasan Reject : <span class="text-danger">{{ $armadaticket->last_rejected_facility_form->termination_reason ?? "-" }}</span>
        @endisset
        <form id="formfasilitas" method="post" action="/addfacilityform">
            @csrf
            <input type="hidden" name="armada_ticket_id" value="{{ $armadaticket->id }}">
            <div class="row border border-dark bg-light p-2">
                <div class="col-9">
                    <table class="table table-bordered table-sm text-center h-100">
                        <tbody>
                            <tr>
                                <td rowspan="2" class="align-middle">
                                    <img src="/assets/logo.png" width="80px">
                                </td>
                                <td class="align-middle h5 table-secondary">FORMULIR</td>
                                <td class="align-middle table-secondary">Hal</td>
                            </tr>
                            <tr>
                                <td class="align-middle h5">FASILITAS KARYAWAN & PERLENGKAPAN KERJA KARYAWAN BARU</td>
                                <td class="align-middle">1/1</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-3">
                    <table class="table table-bordered table-sm text-center h-100 small">
                        <tr><td class="table-secondary">
                            <label class="required_field">
                                Tanggal
                            </label>
                        </td></tr>
                        <tr><td>
                            <input type="date" class="form-control form-control-sm" name="date" value="{{now()->format('Y-m-d') }}" readonly>
                        </td></tr>
                        <tr><td class="table-secondary">
                            <label class="required_field">
                                Nomor
                            </label>
                        </td></tr>
                        <tr><td>
                            <input type="text" 
                            class="form-control form-control-sm"
                            placeholder="akan diisi oleh sistem"
                            name="code" 
                            readonly>
                        </td></tr>
                    </table>
                </div>
                <div class="col-12">
                    <table class="table table-bordered table-sm align-middle">
                        <tr>
                            <td width="20%" class="table-secondary">
                                <label class="required_field small">Nama</label>
                            </td>
                            <td colspan="3">
                                <input type="text" class="form-control form-control-sm" placeholder="Masukkan Nama" name="nama" required>
                            </td>
                        </tr>
                        <tr>
                            <td width="20%" class="table-secondary">
                                <label class="required_field small">Divisi/Dept/Bag</label>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="divisi" placeholder="Masukkan Divisi" required>
                            </td>
                            <td width="20%" class="table-secondary">
                                <label class="required_field small">Telephone</label>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="phone" placeholder="Masukkan Telefon" required>
                            </td>
                        </tr>
                        <tr>
                            <td width="20%" class="table-secondary">
                                <label class="required_field small">Jabatan</label>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="jabatan" placeholder="Masukkan Jabatan" required>
                            </td>
                            <td width="20%" class="table-secondary">
                                <label class="required_field small">Tanggal Masuk Kerja</label>
                            </td>
                            <td>
                                <input type="date" 
                                name="tanggal_mulai_kerja"
                                class="form-control form-control-sm">
                            </td>
                        </tr>
                        <tr>
                            <td width="20%" class="table-secondary">
                                <label class="required_field small">HO/Cabang/Depo</label>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Masukkan Nama SalesPoint" readonly value="{{ $armadaticket->salespoint->name }}">
                                <input type="hidden" 
                                name="salespoint_id"
                                value="{{ $armadaticket->salespoint->id }}">
                            </td>
                            <td width="20%" class="table-secondary">
                                <label class="required_field small">Golongan</label>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" 
                                placeholder="Masukkan Golongan"
                                name="golongan"
                                required>
                            </td>
                        </tr>
                        <tr>
                            <td width="20%" class="table-secondary">
                                <label class="small required_field">Status Karyawan</label>
                            </td>
                            <td colspan="3">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="status_karyawan" value="percobaan" required>Percobaan
                                    </label>
                                </div>
                                
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="status_karyawan" value="tetap" required>Tetap
                                    </label>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td width="20%" class="table-secondary">
                                <label class="small">Fasilitas & Perlengkapan Kerja</label>
                            </td>
                            <td colspan="3">
                                <div class="row">
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="1"> Ruangan, lokasi</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="2"> Pesawat telepon</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="3"> Meja & Kursi</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="4"> Line & Telepon</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="5"> PC / LOP</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="6"> Kartu Nama</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="7" checked> Mobil Dinas</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="8"> ATK & perlengkapan kerja</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="9"> Rumah Dinas</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="10"> Lemari Arsip / Filling Kabinet / Whiteboard</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="11"> Akses Internet</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="12"> ID Card</div>
                                    <div class="col-6"> <input type="checkbox" name="fasilitasdanperlengkapan[]" value="13"> Akses email Pinus Merah Abadi</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="20%" class="table-secondary">
                                <label class="small">Catatan</label>
                            </td>
                            <td colspan="3">
                                <textarea class="form-control" rows="3" style="resize:none" placeholder="Tambahkan catatan (optional)" name="notes"></textarea>
                            </td>
                        </tr>
                    </table>
                    <div class="col-12">
                        <small>* Jenis Fasilitas yang disiapkan adalah standar yang berdasarkan Surat keputusan Direksi mengenai Standar Kompetensi dan Benefit</small>
                    </div>
                    <div class="form-group">
                    <label class="required_field">Pilih Matriks Approval</label>
                    <select class="form-control authorization" name="authorization_id" required>
                        <option value="">-- Pilih Matriks Approval --</option>
                        @foreach ($formfasilitas_authorizations as $authorization)
                            @php
                            $list= $authorization->authorization_detail;
                            $string = "";
                            foreach ($list as $key=>$author){
                                $author->employee_position->name;
                                $string = $string.$author->employee->name;
                                if(count($list)-1 != $key){
                                    $string = $string.' -> ';
                                }
                            }
                            @endphp
                            <option value="{{ $authorization->id }}"
                                data-list = "{{ $list }}">
                                {{$string}}</option>
                        @endforeach
                    </select>
                    </div>
                    <div class="offset-6 col-6">
                        <table class="table table-sm table-bordered text-center authorization_table">
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <br><span>FRM-HCD-114 REV 00</span>
                @if ($armadaticket->status != -1)
                <div class="col-12">
                    <center>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </center>
                </div>
                @endif
            </div>
        </form>
    @endif
@endif
@section('fasilitas-js')
{{-- form fasilitas --}}
<script>
    let formfasilitas = $('#formfasilitas');
    $(document).ready(function () {
        formfasilitas.find('.vendor').change(function(){
            formfasilitas.find('.localvendor').val('');
            if($(this).val() == 'lokal'){
                formfasilitas.find('.localvendor').prop('disabled',false);
            }else{
                formfasilitas.find('.localvendor').prop('disabled',true);
            }
        });

        formfasilitas.find('.authorization').change(function(){
            let list = $(this).find('option:selected').data('list');
            if(list == null){
                formfasilitas.find('.authorization_table').hide();
                return;
            }
            formfasilitas.find('.authorization_table').show();
            let table_string = '<tr>';
            let temp = '';
            let col_count = 1;
            // authorization header
            list.forEach((item,index)=>{
                if(index > 0){
                    if(temp == item.sign_as){
                        col_count++;
                    }else{
                        table_string += '<td class="align-middle small table-secondary" colspan="'+col_count+'">'+temp+'</td>';
                        temp = item.sign_as;
                        col_count =1;
                    }
                }else{  
                    temp = item.sign_as;
                }
                if(index == list.length-1){
                    table_string += '<td class="align-middle small table-secondary" colspan="'+col_count+'">'+temp+'</td>';
                }
            });
            table_string += '</tr><tr>';
            // authorization body
            list.forEach((item,index)=>{
                table_string += '<td width="50%" class="align-bottom small" style="height: 120px"><b>'+item.employee.name+'</b><br>'+item.employee_position.name+'</td>';
            });
            table_string += '</tr>';

            formfasilitas.find('.authorization_table tbody').empty();
            formfasilitas.find('.authorization_table tbody').append(table_string);
        });
    });
    function facilityapprove(facility_form_id){
        $('#submitform').prop('action', '/approvefacilityform');
        $('#submitform').prop('method', 'POST');
        $('#submitform').find('div').append('<input type="hidden" name="facility_form_id" value="'+facility_form_id+'">');
        $('#submitform').submit();
    }
    function facilityreject(facility_form_id){
        var reason = prompt("Harap memasukan alasan reject formulir");
        if (reason != null) {
            if(reason.trim() == ''){
                alert("Alasan Harus diisi");
                return;
            }
            $('#submitform').prop('action', '/rejectfacilityform');
            $('#submitform').prop('method', 'POST');
            $('#submitform').find('div').append('<input type="hidden" name="facility_form_id" value="'+facility_form_id+'">');
            $('#submitform').find('div').append('<input type="hidden" name="reason" value="'+reason+'">');
            $('#submitform').submit();
        }
    }
</script>
@endsection