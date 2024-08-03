@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Bidding @if (request()->get('status') == -1)
                            (History)
                        @endif
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item active">Bidding @if (request()->get('status') == -1)
                                (History)
                            @endif
                        </li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4">
                @if (request()->get('status') == -1)
                    <a href="/bidding" class="btn btn-primary ml-2">Bidding Aktif</a>
                @else
                    <a href="/bidding?status=-1" class="btn btn-info ml-2">History</a>
                @endif
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="table-responsive">
            <table id="biddingDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>
                            #
                        </th>
                        <th>
                            Kode Form Pengadaan
                        </th>
                        <th>
                            Nama Pengaju
                        </th>
                        <th>
                            Area
                        </th>
                        <th>
                            Tanggal Permintaan
                        </th>
                        <th>
                            Tanggal Pengadaan
                        </th>
                        <th width="20%">List Item</th>
                        <th width="20%">Status Bidding</th>
                    </tr>
                </thead>
                <tbody>
                    @php $count = 0; @endphp
                    @foreach ($tickets as $key => $ticket)
                        @php
                            $colorclass = '';
                            if ($ticket->status >= 3) {
                                $colorclass = 'table-success';
                            }
                            $count++;
                        @endphp
                        <tr class="{{ $colorclass }}">
                            <td>{{ $count }}</td>
                            <td class="text-nowrap">{{ $ticket->code }}</td>
                            <td>{{ $ticket->created_by_employee->name ?? '-' }}</td>
                            <td>{{ $ticket->salespoint->name }}</td>
                            <td>{{ $ticket->updated_at->translatedFormat('d F Y (H:i)') }}</td>
                            <td>{{ \Carbon\Carbon::parse($ticket->requirement_date)->translatedFormat('d F Y') }}</td>
                            <td class="small" style="white-space: pre-line;">
                                {{ implode(",\n", array_values($ticket->ticket_item->pluck('name')->toArray())) }}</td>
                            <td class="small">
                                @php
                                    $current_waiting_authorizations = [];
                                    foreach ($ticket->ticket_item as $ticket_item) {
                                        if ($ticket_item->bidding) {
                                            if ($ticket_item->bidding->current_authorization() != null) {
                                                array_push($current_waiting_authorizations, $ticket_item->bidding->current_authorization()->employee_name);
                                            }
                                        }
                                    }
                                @endphp

                                @php
                                    $custom_settings = json_decode($ticket->custom_settings);
                                @endphp

                                @if (count($current_waiting_authorizations) > 0)
                                    Menunggu approval dari {{ implode(',', array_unique($current_waiting_authorizations)) }}
                                @elseif ($ticket->status >= 3 && $ticket->custom_settings && $custom_settings->item_type == 'disposal')
                                    Proses Bidding Disposal Inventaris Selesai
                                @elseif ($ticket->status >= 3)
                                    Approval Selesai
                                @else
                                    @php
                                        $elseone = false;
                                    @endphp

                                    @php
                                        $employee_terminateddd = '';
                                    @endphp

                                    @foreach ($ticket->ticket_item as $titem)
                                        @foreach ($titem->ticket_item_file_requirement->where('status', -1) as $key => $item)
                                            @php
                                                $employee_terminateddd = $item->rejected_by_employee()->name;
                                            @endphp
                                        @endforeach
                                    @endforeach

                                    @foreach ($ticket->ticket_item as $titem)
                                        @foreach ($titem->ticket_item_attachment->where('status', -1) as $key => $item)
                                            @php
                                                $employee_terminateddd = $item->rejected_by_employee()->name;
                                            @endphp
                                        @endforeach
                                    @endforeach

                                    @foreach ($ticket->ticket_item as $titem)
                                        @if ($titem->ticket_item_attachment->where('status', -1)->isNotEmpty())
                                            Menunggu proses reupload file dari area / di reject oleh :
                                            {{ $employee_terminateddd }}
                                        @elseif ($titem->ticket_item_file_requirement->where('status', -1)->isNotEmpty())
                                            Menunggu proses reupload file dari area / di reject oleh :
                                            {{ $employee_terminateddd }}
                                        @elseif ($titem->ticket_item_file_requirement->where('revised_by')->isNotEmpty())
                                            @if ($elseone === false)
                                                @php
                                                    $elseone = true;
                                                @endphp
                                                Menunggu proses bidding
                                            @endif
                                        @elseif ($titem->ticket_item_attachment->where('revised_by')->isNotEmpty())
                                            @if ($elseone === false)
                                                @php
                                                    $elseone = true;
                                                @endphp
                                                Menunggu proses bidding
                                            @endif
                                        @else
                                            @if ($elseone === false)
                                                @php
                                                    $elseone = true;
                                                @endphp
                                                Menunggu proses pembuatan form bidding
                                            @endif
                                        @endif
                                    @endforeach
                                @endif

                                @foreach ($ticket->ticket_item as $titem)
                                    @foreach ($titem->ticket_item_attachment->where('status', -1) as $key => $item)
                                        <br><span class='text-danger'>{{ $item->name }} :
                                            @if (strlen($item->reject_notes) > 20)
                                                {{ $item = substr($item->reject_notes, 0, 35) . '...' }}
                                            @else
                                                {{ $item = $item->reject_notes }}
                                            @endif
                                        </span>
                                    @endforeach
                                    @foreach ($titem->ticket_item_file_requirement->where('status', -1) as $key => $item)
                                        <br><span class='text-danger'>{{ $item->file_completement->name }} :
                                            @if (strlen($item->reject_notes) > 20)
                                                {{ $item = substr($item->reject_notes, 0, 35) . '...' }}
                                            @else
                                                {{ $item = $item->reject_notes }}
                                            @endif
                                        </span>
                                    @endforeach
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            var table = $('#biddingDT').DataTable(datatable_settings);
            $('#biddingDT tbody').on('click', 'tr', function() {
                let code = $(this).find('td').eq(1).text().trim();
                window.location.href = "/bidding/" + code;
            });
        })
    </script>
@endsection
