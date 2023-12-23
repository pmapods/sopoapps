<div class="d-flex flex-column">
    <b>NAMA VENDOR :
        {{ $po->sender_name ?? ($pomanual->vendor_name ?? 'ERROR_PO_' . $securityticket->po_reference_number . '_NOT_FOUND') }}</b>
    <b>PERIODE PENILAIAN : {{ now()->translatedFormat('F Y') }}</b>
    <b>CABANG/DEPO :
        {{ $po->security_ticket->salespoint->name ?? ($pomanual->salespoint_name ?? 'ERROR_PO_' . $securityticket->po_reference_number . '_NOT_FOUND') }}</b>
    <b class="mt-3">A. ASPEK PENILAIAN PERSONIL SECURITY</b>
    <div>
        <table class="table table-bordered text-sm">
            <thead>
                <tr>
                    <th class="bg-success text-light" rowspan="2" width="3%">NO</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">KATEGORI</th>
                    <th class="bg-success text-light" rowspan="2" width="">ITEM PENILAIAN</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">RATA-RATA</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">BOBOT</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">TOTAL NILAI</th>
                    <th class="bg-success text-light" colspan="5" width="35%">DETAIL PENILAIAN PERSONIL</th>
                </tr>
                <tr class="bg-success">
                    <td style="padding:1px !important;"><input type="text"
                            class="nama_personil form-control form-control-sm" data-col="0" placeholder="Nama"
                            onchange="setColumn(this,0)"></td>
                    <td style="padding:1px !important;"><input type="text"
                            class="nama_personil form-control form-control-sm" data-col="1" placeholder="Nama"
                            onchange="setColumn(this,1)"></td>
                    <td style="padding:1px !important;"><input type="text"
                            class="nama_personil form-control form-control-sm" data-col="2" placeholder="Nama"
                            onchange="setColumn(this,2)"></td>
                    <td style="padding:1px !important;"><input type="text"
                            class="nama_personil form-control form-control-sm" data-col="3" placeholder="Nama"
                            onchange="setColumn(this,3)"></td>
                    <td style="padding:1px !important;"><input type="text"
                            class="nama_personil form-control form-control-sm" data-col="4" placeholder="Nama"
                            onchange="setColumn(this,4)"></td>
                </tr>
            </thead>
            @php
                $row_count = 0;
            @endphp
            <tbody>
                <tr>
                    <td rowspan="5">A.1</td>
                    <td rowspan="5">SIKAP</td>
                    <td>a) Kerapian & Penampilan Diri (Seragam & Atribut)</td>
                    <td rowspan="5" id="rate_a1">-</td>
                    <td rowspan="5">30%</td>
                    <td rowspan="5" id="total_a1">-</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td style="padding:3px !important;"><input type="number"
                                class="nilai_personil form-control form-control-sm autonumber"
                                data-row="{{ $row_count }}" data-col="{{ $i }}" placeholder="-"
                                min="1" max="4" disabled></td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>
                @php
                    $a1_array_text = ['b) Pelayanan terhadap Karyawan & Tamu (Salam & Kesopanan)', 'c) Ketepatan waktu hadir di lokasi (Kantor)', 'd) Tegas, Dapat diandalkan, Sigap', 'e) Kedisiplinan atas peraturan yang dijaga nya'];
                @endphp
                @foreach ($a1_array_text as $text)
                    <tr style="line-height: 30px">
                        <td>{{ $text }}</td>
                        @for ($i = 0; $i < 5; $i++)
                            <td style="padding:3px !important;"><input type="number"
                                    class="nilai_personil form-control form-control-sm autonumber" placeholder="-"
                                    data-row="{{ $row_count }}" data-col="{{ $i }}" min="1"
                                    max="4" disabled></td>
                        @endfor
                        @php $row_count++; @endphp
                    </tr>
                @endforeach

                <tr style="line-height: 30px">
                    <td rowspan="10">A.2</td>
                    <td rowspan="10">HASIL KERJA</td>
                    <td>a) Kepatuhan atas instruksi user</td>
                    <td rowspan="10" id="rate_a2">-</td>
                    <td rowspan="10">20%</td>
                    <td rowspan="10" id="total_a2">-</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td style="padding:3px !important;"><input type="number"
                                class="nilai_personil form-control form-control-sm autonumber" placeholder="-"
                                data-row="{{ $row_count }}" data-col="{{ $i }}" min="1"
                                max="4" disabled></td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>

                @php
                    $a2_array_text = ['b) Menjaga gudang steril dari orang yang tidak berkepentingan', 'c) Pencatatan Kilometer Kendaraan dengan benar', 'd) Pencatatan stock pada saat proses Loading & Unloading dengan benar', 'e) Penataan / pengaturan letak kendaraan bermotor (Mobil/Motor)', 'f) Mencatat Setiap Tamu yang Hadir dan melaporkan kepada orang dituju', 'g) Kunjungan (Patroli) Penanggung Jawab atau Koordinator Lapangan', 'h) Mampu bertugas dengan menerapkan standar-standar pengamanan yang baik', 'i) Komunikasi dengan Pihak Luar(RT/RW, Kelurahan, Aparat TNI & POLRI di sekitar)', 'j) Penjangaan terhadap Gerbang Utama / Kantor \'Tidak Pernah Kosong\' '];
                @endphp
                @foreach ($a2_array_text as $text)
                    <tr style="line-height: 30px">
                        <td>{{ $text }}</td>
                        @for ($i = 0; $i < 5; $i++)
                            <td style="padding:3px !important;"><input type="number"
                                    class="nilai_personil form-control form-control-sm autonumber" placeholder="-"
                                    data-row="{{ $row_count }}" data-col="{{ $i }}" min="1"
                                    max="4" disabled></td>
                        @endfor
                        @php $row_count++; @endphp
                    </tr>
                @endforeach

                <tr style="line-height: 30px">
                    <td rowspan="2">A.3</td>
                    <td rowspan="2">RESPON ATAS KEJADIAN</td>
                    <td>1. Update terhadap situasi dan kondisi pengamanan di Lokasi</td>
                    <td rowspan="2" id="rate_a3">-</td>
                    <td rowspan="2">10%</td>
                    <td rowspan="2" id="total_a3">-</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td style="padding:3px !important;"><input type="number"
                                class="nilai_personil form-control form-control-sm autonumber" placeholder="-"
                                data-row="{{ $row_count }}" data-col="{{ $i }}" min="1"
                                max="4" disabled></td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>

                <tr style="line-height: 30px">
                    <td>2. Respons atas komplain dari User / Tamu</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td style="padding:3px !important;"><input type="number"
                                class="nilai_personil form-control form-control-sm autonumber" placeholder="-"
                                data-row="{{ $row_count }}" data-col="{{ $i }}" min="1"
                                max="4" disabled></td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>
                <tr>
                    <td class="bg-success text-light" colspan="3">SUBTOTAL</td>
                    <td class="bg-success text-light text-right" colspan="3" id="subtotal_value">0%</td>
                    <td class="bg-success" colspan="5"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <b class="mt-3">B. ASPEK PENILAIAN KELEMBAGAAN</b>
    <div class="row">
        <div class="col-12">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th class="bg-success text-light" width="5%">NO</th>
                        <th class="bg-success text-light">KATEGORI</th>
                        <th class="bg-success text-light">ITEM PENILAIAN</th>
                        <th class="bg-success text-light">NILAI</th>
                        <th class="bg-success text-light" width="8%">BOBOT</th>
                        <th class="bg-success text-light" width="8%">TOTAL NILAI</th>
                        <th class="bg-success text-light">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lists = [
                            [
                                'no' => 'B.1',
                                'category' => 'TRAINING',
                                'item' => 'Pemenuhan pelatihan personil security',
                                'bobot' => '15',
                            ],
                            [
                                'no' => 'B.2',
                                'category' => 'SUPERVISI',
                                'item' => 'Pemenuhan inspeksi / supervisi terhadap personil security',
                                'bobot' => '10',
                            ],
                            [
                                'no' => 'B.3',
                                'category' => 'ATRIBUT',
                                'item' => 'Pemenuhan atribut atau penggantian atribut jika ada kerusakan',
                                'bobot' => '15',
                            ],
                        ];
                    @endphp
                    @foreach ($lists as $key => $list)
                        <tr>
                            <td>{{ $list['no'] }}</td>
                            <td>{{ $list['category'] }}</td>
                            <td>{{ $list['item'] }}</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <select class="form-control form-control-sm nilai_lembaga" name=""
                                        id="">
                                        <option value="25">25</option>
                                        <option value="75">75</option>
                                        <option value="100">100</option>
                                    </select>
                                    {{-- <input type="number" class="autonumber form-control form-control-sm nilai_lembaga"
                                        min="0" max="100" value="0"> --}}
                                    <div class="input-group-append">
                                        <div class="input-group-text">%</div>
                                    </div>
                                </div>
                            </td>
                            <td class="bobot_nilai_lembaga" data-value="{{ $list['bobot'] }}">{{ $list['bobot'] }}%
                            </td>
                            <td class="total_nilai_lembaga">0%</td>
                            <td>
                                <textarea class="form-control form-control-sm keterangan" rows="3"></textarea>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="text-light bg-success" colspan="3">SUB TOTAL</td>
                        <td class="text-light bg-success" colspan="3" id="subtotalb_value">0%</td>
                        <td class="text-light bg-success">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-8 mx-2 border border-dark d-flex justify-content-center align-items-center"
            style="height: 4em">
            <h5>TOTAL NILAI</h5>
            <h5>(Catatan : Nilai Minimum 70%)</h5>
        </div>
        <div class="col-1 border border-dark d-flex justify-content-center align-items-center" style="height: 4em">
            <h5 id="grandtotal_value">0%</h5>
        </div>
        <div class="col-2 offset-1 ml-2 border border-dark d-flex justify-content-center align-items-center"
            style="height: 4em">
            <h5 id="recommendation_value">TIDAK DIREKOMENDASIKAN</h5>
        </div>
        <div class="col-12 border p-3 mt-2 border-dark mx-2 d-flex justify-content-start align-items-center">
            <span>KESIMPULAN <br> (Pilih salah satu)</span>
            <div class="row ml-5">
                <div class="col-12">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="kesimpulan" value="0" checked>
                            VENDOR DAN PERSONIL TETAP
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="kesimpulan" value="1">
                            GANTI VENDOR
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="kesimpulan" value="2">
                            GANTI PERSONIL SECURITY DENGAN VENDOR SAMA
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="form-group">
                <label class="required_field">Pilih Matriks Approval</label>
                <select class="form-control" id="evaluasi_authorization">
                    <option value="">-- Pilih Matriks Approval --</option>
                    @foreach ($evaluasiform_authorizations as $authorization)
                        @php
                            $list = $authorization->authorization_detail;
                            $string = '';
                            foreach ($list as $key => $author) {
                                $author->employee_position->name;
                                $string = $string . $author->employee->name;
                                if (count($list) - 1 != $key) {
                                    $string = $string . ' -> ';
                                }
                            }
                        @endphp
                        <option value="{{ $authorization->id }}" data-list="{{ $list }}">
                            {{ $string }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <table class="table table-bordered" id="evaluasi_authorization_table">
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="col-12 text-center">
            <button type="button" onclick="doSubmit()" class="btn btn-primary">Submit Form dan Mulai
                Approval</button>
        </div>
    </div>
</div>
<form action="/addevaluasiform" method="post" id="formevaluasi">
    @csrf
    <input type="hidden" name="security_ticket_id" value="{{ $securityticket->id }}">
    <div></div>
</form>
<form action="" method="post" id=""></form>

@section('newevaluasiform-js')
    {{-- form evaluasi --}}
    <script>
        $(document).ready(function() {
            $('.autonumber').change(function() {
                autonumber($(this));
            });
            $('.nilai_personil').change(function() {
                calculate_rate(this);
            });
            $('.nilai_lembaga').change(function() {
                let bobot = $(this).closest('tr').find('.bobot_nilai_lembaga').data('value');
                let total_td = $(this).closest('tr').find('.total_nilai_lembaga');
                let nilai = $(this).val();
                total_td.text(parseInt(nilai / 100 * bobot) + '%');
                calculateSubtotalB();
            });

            $('#evaluasi_authorization').change(function() {
                let list = $(this).find('option:selected').data('list');
                if (list == null) {
                    $('#evaluasi_authorization_table').hide();
                    return;
                }
                $('#evaluasi_authorization_table').show();
                let table_string = '<tr>';
                let temp = '';
                let col_count = 1;
                // authorization header
                list.forEach((item, index) => {
                    if (index > 0) {
                        if (temp == item.sign_as) {
                            col_count++;
                        } else {
                            table_string += '<td class="small" colspan="' + col_count + '">' +
                                temp + '</td>';
                            temp = item.sign_as;
                            col_count = 1;
                        }
                    } else {
                        temp = item.sign_as;
                    }
                    if (index == list.length - 1) {
                        table_string += '<td class="small" colspan="' + col_count + '">' + temp +
                            '</td>';
                    }
                });
                table_string += '</tr><tr>';
                list.forEach((item, index) => {
                    table_string +=
                        '<td width="20%" class="align-bottom small" style="height: 80px"><b>' + item
                        .employee.name + '</b><br>' + item.employee_position.name + '</td>';
                });
                table_string += '</tr>';

                $('#evaluasi_authorization_table tbody').empty();
                $('#evaluasi_authorization_table tbody').append(table_string);
            });
        });

        function setColumn(el, column_index) {
            let value = $(el).val();
            if (value == '') {
                $('.nilai_personil').each(function(index, el) {
                    if ($(el).data('col') == column_index) {
                        $(el).prop('disabled', true);
                        $(el).val('');
                        $(el).trigger('change');
                    }
                });
            } else {
                $('.nilai_personil').each(function(index, el) {
                    if ($(el).data('col') == column_index) {
                        $(el).prop('disabled', false);
                        $(el).trigger('change');
                    }
                });
            }
        }

        function calculate_rate(el) {
            let row = $(el).data('row');
            let active_columns = [];
            $('.nama_personil').each(function(index, el) {
                if ($(el).val() != '') {
                    active_columns.push(parseInt($(el).data('col')));
                }
            });
            let values = [];
            if (row <= 4) {
                // A1
                $('.nilai_personil').each(function(index) {
                    let col = parseInt($(this).data('col'));
                    let row = parseInt($(this).data('row'));
                    let value = $(this).val();
                    if ($.inArray(col, active_columns) != -1 && row <= 4) {
                        values.push(parseFloat(value));
                    }
                });
                let average = calculateAverage(values).toFixed(2);
                let total_percentage = parseInt(average / 4 * 30);
                if (!isNaN(average)) {
                    $('#rate_a1').text(average);
                    $('#total_a1').text(total_percentage + "%");
                } else {
                    $('#rate_a1').text('-');
                    $('#total_a1').text('-');
                }
            } else if (row <= 14) {
                // A2
                $('.nilai_personil').each(function(index) {
                    let col = parseInt($(this).data('col'));
                    let row = parseInt($(this).data('row'));
                    let value = $(this).val();
                    if ($.inArray(col, active_columns) != -1 && row <= 14 && row > 4) {
                        values.push(parseFloat(value));
                    }
                });
                let average = calculateAverage(values).toFixed(2);
                let total_percentage = parseInt(average / 4 * 20);
                if (!isNaN(average)) {
                    $('#rate_a2').text(average);
                    $('#total_a2').text(total_percentage + "%");
                } else {
                    $('#rate_a2').text('-');
                    $('#total_a2').text('-');
                }
            } else {
                // A3
                $('.nilai_personil').each(function(index) {
                    let col = parseInt($(this).data('col'));
                    let row = parseInt($(this).data('row'));
                    let value = $(this).val();
                    if ($.inArray(col, active_columns) != -1 && row > 14) {
                        values.push(parseFloat(value));
                    }
                });
                let average = calculateAverage(values).toFixed(2);
                let total_percentage = parseInt(average / 4 * 10);
                if (!isNaN(average)) {
                    $('#rate_a3').text(average);
                    $('#total_a3').text(total_percentage + "%");
                } else {
                    $('#rate_a3').text('-');
                    $('#total_a3').text('-');
                }
            }
            calculateSubTotal();
        };

        function calculateAverage(array) {
            var total = 0;
            var count = 0;

            array.forEach(function(item, index) {
                total += item;
                count++;
            });

            return total / count;
        }

        function calculateSubTotal() {
            let a1_percentage = parseInt($('#total_a1').text().replace(/[^\d.-]/g, ''));
            let a2_percentage = parseInt($('#total_a2').text().replace(/[^\d.-]/g, ''));
            let a3_percentage = parseInt($('#total_a3').text().replace(/[^\d.-]/g, ''));
            $('#subtotal_value').text((a1_percentage + a2_percentage + a3_percentage) + "%");
            calculateSummary();
        }

        function calculateSubtotalB() {
            let total = 0;
            $('.total_nilai_lembaga').each(function() {
                let value = parseInt($(this).text().replace(/[^\d.-]/g, ''));
                total += value;
            });
            $('#subtotalb_value').text(total + '%');
            calculateSummary();
        }

        function calculateSummary() {
            let value_a = parseInt($('#subtotal_value').text().replace(/[^\d.-]/g, ''));
            let value_b = parseInt($('#subtotalb_value').text().replace(/[^\d.-]/g, ''));
            let grandtotal = value_a + value_b;
            $('#grandtotal_value').text(grandtotal + '%');
            if (grandtotal >= 70) {
                $('#recommendation_value').text('DIREKOMENDASIKAN');
            } else {
                $('#recommendation_value').text('TIDAK DIREKOMENDASIKAN');
            }
        }

        function doSubmit() {
            // cek minimal satu orang
            $count_name = 0;
            $('.nama_personil').each(function() {
                if ($(this).val() != '') {
                    $count_name++;
                }
            });
            if ($count_name == 0) {
                alert('Minimal mengisi satu nama');
                return;
            }
            // cek apakah otorisasi sudah dipilih
            if ($('#evaluasi_authorization').val() == "") {
                alert('Matriks Approval belum dipilih');
                return;
            }

            submitform();
        }

        function submitform() {
            $('#formevaluasi div').empty();
            let input_append_text = "";
            let selected_names = $('.nama_personil').filter(function() {
                if ($(this).val() != '') {
                    return true;
                } else {
                    return false;
                }
            });
            selected_names.each(function(index) {
                let col = $(this).data('col');
                let name = $(this).val();
                let selected_values = $('.nilai_personil').filter(function() {
                    if ($(this).data('col') == col) {
                        return true;
                    } else {
                        return false;
                    }
                });

                let values = [];
                selected_values.each(function() {
                    values.push($(this).val());
                });
                input_append_text += "<input type='hidden' name='personil[" + index + "][column_index]' value='" +
                    col + "'>";
                input_append_text += "<input type='hidden' name='personil[" + index + "][name]' value='" + name +
                    "'>";
                input_append_text += "<input type='hidden' name='personil[" + index + "][values]' value='" +
                    values + "'>";
            });

            $('.nilai_lembaga').each(function(index) {
                input_append_text += "<input type='hidden' name='lembaga[" + index + "][nilai]' value='" + $(this)
                    .val() + "'>"
            });
            $('.keterangan').each(function(index) {
                input_append_text += "<input type='hidden' name='lembaga[" + index + "][keterangan]' value='" + $(
                    this).val() + "'>"
            });

            let kesimpulan = $("input[name='kesimpulan']:checked").val();
            input_append_text += "<input type='hidden' name='kesimpulan' value='" + kesimpulan + "'>";

            let authorization_id = $('#evaluasi_authorization').val();
            input_append_text += "<input type='hidden' name='authorization_id' value='" + authorization_id + "'>";

            $('#formevaluasi div').append(input_append_text);
            $('#formevaluasi').submit();
        }

        function evaluasiapprove(evaluasi_form_id) {
            $('#submitform').prop('action', '/approveevaluasiform');
            $('#submitform').prop('method', 'POST');
            $('#submitform').find('div').append('<input type="hidden" name="evaluasi_form_id" value="' + evaluasi_form_id +
                '">');
            $('#submitform').submit();
        }

        function evaluasireject(evaluasi_form_id) {
            var reason = prompt("Harap memasukan alasan reject formulir");
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return;
                }
                $('#submitform').prop('action', '/rejectevaluasiform');
                $('#submitform').prop('method', 'POST');
                $('#submitform').find('div').append('<input type="hidden" name="evaluasi_form_id" value="' +
                    evaluasi_form_id + '">');
                $('#submitform').find('div').append('<input type="hidden" name="reason" value="' + reason + '">');
                $('#submitform').submit();
            }
        }
    </script>
@endsection
