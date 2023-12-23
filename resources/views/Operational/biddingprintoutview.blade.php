
<!doctype html>
<html lang="en">
  <head>
    <title>Bidding</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        @media print { 
            #form_table thead{
                background-color: #76933C;
                border : 1px solid #000 !important;
                -webkit-print-color-adjust: exact; 
            }
        }
        #form_table thead{
            background-color: #76933C;
            border : 1px solid #000 !important;
        }
        #form_table td, #form_table th{
            vertical-align: middle !important;
        }
        textarea{
            resize:none !important;
        }
    </style>
  </head>
  <body>

    <div class="border border-dark px-4">
        <center>
            <h5>FORM SELEKSI VENDOR</h5>
        </center>
        <span class="text-danger small text-nowrap">* bidding expired date :
            {{ \Carbon\Carbon::parse($bidding->expired_date)->format('d-m-Y') }}</span>
        <div class="row">
            <div class="col-md-2 mt-3">Jenis Produk</div>
            <div class="col-md-4 mt-3">
                <input type="text" class="form-control" value="{{$ticket_item->name}}" readonly>
            </div>

            <div class="col-md-2 mt-3">Area / SalesPoint</div>
            <div class="col-md-4 mt-3">
                <input type="text" class="form-control" value="{{$ticket->salespoint->name}}" readonly>
            </div>

            <div class="col-md-2 mt-3">Tanggal Seleksi</div>
            <div class="col-md-4 mt-3">
                <input type="text" class="form-control" value="{{$bidding->created_at->translatedFormat('d F Y (H:i)')}}" readonly>
            </div>

            <div class="col-md-2 mt-3">Kelompok</div>
            <div class="col-md-4 mt-3">
                <div class="form-group">
                    <select class="form-control" id="select_kelompok" name="group" value="{{ $bidding->group }}" disabled>
                        <option value="asset" @if ($bidding->group == 'asset') selected @endif>Asset</option>
                        <option value="inventory" @if ($bidding->group == 'inventory') selected @endif>Inventaris</option>
                        <option value="others" @if ($bidding->group == 'others') selected @endif>Lain-Lain</option>
                    </select>
                    @if ($bidding->group == 'others')
                        <input type="text" class="form-control mt-2" name="others_name" id="input_kelompok_lain"
                            value="{{ $bidding->other_name }}" disabled>
                    @endif
                </div>
            </div>
        </div>
        <table class="table table-sm table-bordered small" id="form_table">
            <thead>
                <tr>
                    <th class="text-center" rowspan="5" class="text-center">No</th>
                    <th class="text-center" rowspan="5" class="text-center">Penilaian</th>
                    <th class="text-center" rowspan="5" class="text-center">Bobot</th>
                    @foreach ($bidding->bidding_detail as $detail)
                        <th colspan="3" class="text-center">
                            {{$detail->ticket_vendor->name}}
                        </th>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <th colspan="3">-</th>
                    @endif
                    <th class="text-center" rowspan="5" class="text-center">Keterangan</th>
                </tr>
                <tr>
                    @foreach ($bidding->bidding_detail as $detail)
                        <th>Alamat</th>
                        <th colspan="2">
                            {{$detail->address}}
                        </th>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <th>Alamat</th>
                        <th colspan="2">-</th>
                    @endif
                </tr>
                <tr>
                    @foreach ($bidding->bidding_detail as $detail)
                        <th>PIC</th>
                        <th colspan="2">
                            {{$detail->ticket_vendor->salesperson}}
                        </th>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <th>PIC</th>
                        <th colspan="2">-</th>
                    @endif
                </tr>
                <tr>
                    @foreach ($bidding->bidding_detail as $detail)
                        <th>Telp/HP</th>
                        <th colspan="2">
                            @if($detail->ticket_vendor->vendor_id == null)
                                {{$detail->ticket_vendor->phone}}
                            @else
                            {{$detail->ticket_vendor->vendor()->phone}}
                            @endif
                        </th>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <th>Telp/HP</th>
                        <th colspan="2">-</th>
                    @endif
                </tr>
                <tr>
                    
                    @for ($i = 0; $i < (($bidding->bidding_detail->count() == 1) ? 2 : $bidding->bidding_detail->count()); $i++)
                    <th>Proposal Awal</th>
                    <th>Proposal Akhir</th>
                    <th width="80">Nilai</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @php
                    $title_colspan = 3*(($bidding->bidding_detail->count() == 1) ? 2 : $bidding->bidding_detail->count())+4;
                @endphp
                {{-- price --}}
                <tr class="table-success"><td colspan="{{ $title_colspan }}"><b>Price</b></td></tr>
                <tr>
                    <td>1</td>
                    <td>Harga</td>
                    <td class="text-center" rowspan="4">5</td>
                    @foreach ($bidding->bidding_detail as $detail)
                    <td class="rupiah_text">
                        {{$detail->start_harga}}
                    </td>
                    <td class="rupiah_text">
                        {{$detail->end_harga}}
                    </td>
                    <td class="text-center" rowspan="4">
                        {{$detail->price_score}}
                    </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                        <td class="text-center" rowspan="4">-</td>
                    @endif
                    <td style="white-space: pre-line; !important" rowspan="4">
                        {{$bidding->price_notes}}
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>PPN</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td class="rupiah">
                            {{$detail->start_ppn}}
                        </td>
                        <td class="rupiah">
                            {{$detail->end_ppn}}
                        </td>
                    @endforeach
                    
                    @if($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>
                <tr>
                    <td>3</td>
                    <td>Ongkos Kirim</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td class="rupiah">
                            {{$detail->start_ongkir_price}}
                        </td>
                        <td class="rupiah">
                            {{$detail->end_ongkir_price}}
                        </td>
                    @endforeach
                    
                    @if($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>
                <tr>
                    <td>4</td>
                    <td>Ongkos Pasang</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td class="rupiah">
                            {{$detail->start_pasang_price}}
                        </td>
                        <td class="rupiah">
                            {{$detail->end_pasang_price}}
                        </td>
                    @endforeach
                    
                    @if($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>

                {{-- Ketersediaan  Barang --}}
                <tr class="table-success"><td colspan="{{ $title_colspan }}"><b>Ketersediaan Barang</b></td></tr>
                <tr>
                    <td>5</td>
                    <td>Spesifikasi (merk/type)</td>
                    <td class="text-center" rowspan="5">3</td>
                    @foreach ($bidding->bidding_detail as $detail)
                    <td colspan="2">
                        {{$detail->spesifikasi}}
                    </td>
                    <td class="text-center" rowspan="5">
                        {{$detail->ketersediaan_barang_score}}
                    </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                        <td rowspan="5">-</td>
                    @endif
                    <td rowspan="5" style="white-space: pre-line; !important">
                        {{$bidding->ketersediaan_barang_notes}}
                    </td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Ready</td>
                    @foreach ($bidding->bidding_detail as $detail)
                    <td colspan="2">
                        {{$detail->ready}}
                    </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>
                <tr>
                    <td>7</td>
                    <td>Indent</td>
                    @foreach ($bidding->bidding_detail as $detail)
                    <td colspan="2">
                        {{$detail->indent}}
                    </td>
                    @endforeach
                    
                    @if($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>
                <tr>
                    <td>8</td>
                    <td>Barang bergaransi</td>
                    @foreach ($bidding->bidding_detail as $detail)
                    <td colspan="2">
                        {{$detail->garansi}}
                    </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>
                <tr>
                    <td>9</td>
                    <td>Bonus</td>
                    @foreach ($bidding->bidding_detail as $detail)
                    <td colspan="2">
                        {{$detail->bonus}}
                    </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>

                {{-- Ketentuan Pembayaran --}}
                <tr class="table-success"><td colspan="{{ $title_colspan }}"><b>Ketentuan Pembayaran</b></td></tr>
                <tr>
                    <td>10</td>
                    <td>Credit / Cash</td>
                    <td class="text-center" rowspan="2">2</td>
                    @foreach ($bidding->bidding_detail as $detail)
                    <td colspan="2">
                        {{$detail->creditcash}}
                    </td>
                    <td class="text-center" rowspan="2">
                        {{$detail->ketentuan_bayar_score}}
                    </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                        <td rowspan="2">-</td>
                    @endif
                    <td style="white-space: pre-line; !important" rowspan="2">
                        {{$bidding->ketentuan_bayar_notes}}
                    </td>
                </tr>
                <tr>
                    <td>11</td>
                    <td>Menerbitkan Faktur Pajak</td>
                    @foreach ($bidding->bidding_detail as $detail)
                    <td colspan="2">
                        {{($detail->menerbitkan_faktur_pajak)?'Ya':'Tidak'}}
                    </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>
            
                {{-- Informasi lain-Lain --}}
                <tr class="table-success"><td colspan="{{ $title_colspan }}"><b>Informasi Lain-lain</b></td></tr>
                <tr>
                    <td>12</td>
                    <td>Masa berlaku penawaran</td>
                    <td class="text-center" rowspan="4">2</td>
                    @foreach ($bidding->bidding_detail as $detail)
                    <td colspan="2">
                        {{$detail->masa_berlaku_penawaran}} Hari
                    </td>
                    <td class="text-center" rowspan="4">
                        {{$detail->others_score}}
                    </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                        <td rowspan="4">-</td>
                    @endif
                    <td style="white-space: pre-line; !important" rowspan="4">
                        {{$bidding->others_notes}}
                    </td>
                </tr>
                <tr>
                    <td>13</td>
                    <td>Lama Pengerjaan</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td>
                            {{$detail->start_lama_pengerjaan}} Hari
                        </td>
                        <td>
                            {{$detail->end_lama_pengerjaan}} Hari
                        </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>
                
                <tr>
                    <td>14</td>
                    <td>
                        {{$bidding->optional1_name}}
                    </td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td>
                            {{$detail->optional1_start}}
                        </td>
                        <td>
                            {{$detail->optional1_end}}
                        </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>
                
                <tr>
                    <td>15</td>
                    <td>
                        {{$bidding->optional2_name}}
                    </td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td>
                            {{$detail->optional2_start}}
                        </td>
                        <td>
                            {{$detail->optional2_end}}
                        </td>
                    @endforeach
                    @if($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>
                <tr>
                    @php
                        $scores = [];
                        foreach ($bidding->bidding_detail as $key =>$detail){
                            $scores[$key] = 0;
                            $scores[$key] += $detail->price_score * 5;
                            $scores[$key] += $detail->ketersediaan_barang_score * 3;
                            $scores[$key] += $detail->ketentuan_bayar_score * 2;
                            $scores[$key] += $detail->others_score * 2;
                        }
                    @endphp
                    <td class="empty_column" colspan="3"></td>
                    @foreach ($scores as $score)
                        <td colspan="2" class="table-success">Total Nilai</td>
                        <td>{{$score}}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        <center>
            <h5>Rekomendasi Vendor Terpilih</h5>
            <h4 id="selected_vendor">{{$bidding->selected_vendor()->ticket_vendor->name}}</h4>
        </center>
        <b>CATATAN</b><br>
        <ol>
            <li>VENDOR YANG DINYATAKAN LULUS ADALAH JIKA NILAI > 30</li>
            <li>SELEKSI VENDOR DIIKUTI OLEH MINIMAL 2 VENDOR SEJENIS</li>
            <li>VENDOR YANG DIPILIH ADALAH 1 VENDOR YANG LULUS SELEKSI DENGAN NILAI PALING TINGGI</li>
        </ol>
        <center><h4>Otorisasi</h4></center>
        <div class="d-flex align-items-center justify-content-center">
            @foreach ($bidding->bidding_authorization as $key =>$author)
                <div class="mr-3">
                    <span>{{$author->as}}</span><br>
                    <span class="font-weight-bold">{{$author->employee->name}} -- {{$author->employee_position}}</span><br>
                    @if($author->status == 1)
                    <span class="text-success">
                        Approved -- {{$author->updated_at->translatedFormat('d F Y (H:i)')}}
                    </span>
                    @endif
                </div>
                @if($key < $bidding->bidding_authorization->count()-1)
                <i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>
                @endif
            @endforeach
        </div>
        <div class="d-flex justify-content-center align-items-center mt-3">
            @php
                if(($bidding->signed_filename == null || $bidding->signed_filepath == null) && $bidding->current_authorization()->level == 2 && Auth::user()->id == $bidding->current_authorization()->employee->id){
                    $needUploadSigned = true;
                }else{
                    $needUploadSigned = false;
                }
            @endphp
        </div>
        <br><span>FRM-PCD-001 REV 00</span>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <!-- Bootstrap 4.6 -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.0.5/js/adminlte.min.js"></script>
    {{-- Select 2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    {{-- Autonumeric --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.1.0/autoNumeric.min.js"></script>
    {{-- Datatable --}}
    <script src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
    {{-- moment --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <script src="/js/layout.js?ver={{ now()->format('Ymdhi') }}"></script>
  </body>
</html>



