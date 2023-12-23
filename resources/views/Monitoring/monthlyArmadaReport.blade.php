@extends('Layout.app')
@section('local-css')

@endsection
@section('content')

<div class="content-header">
</div>
<div class="content-body">
    <div class="d-flex flex-column align-items-center">
        <h4>Monthly Armada Report</h4><br>
        <h5>{{ $selected_date->translatedFormat('F Y') }}</h5>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Vendor</th>
                <th>Saldo Awal {{ $selected_date->translatedFormat('F Y')}}</th>
                @foreach ($ticket_types as $ticket_type)
                    <th>{{ $ticket_type }}</th>
                @endforeach
                <th>Saldo Akhir {{ $selected_date->translatedFormat('F Y')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vendor_name_list as $vendor_name)
                <tr>
                    <td>{{ $vendor_name }}</td>
                    <td>{{ $pos_group_by_ticket_type_start->where('vendor_name',$vendor_name)->first()->total_count ?? 0 }}</td>
                    @foreach ($ticket_types as $ticket_type)
                        @php
                            $groupby_vendor = $pos_group_by_ticket_type_between->where('vendor_name',$vendor_name)->first();
                            $value = 0;
                            foreach ($groupby_vendor->po_groupBy_ticket_type ?? [] as $type_name => $ticket_type_count){
                                // dd($ticket_type);
                                if($type_name == $ticket_type){
                                    $value = $ticket_type_count;
                                }
                            }
                        @endphp
                        <td>{{ $value }}</td>
                    @endforeach
                    <td>{{ $pos_group_by_ticket_type_end->where('vendor_name',$vendor_name)->first()->total_count ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="table">
        <thead>
            <tr>
                <th>Nama Vendor</th>
                <th>Armada Niaga</th>
                <th>Armada Non Niaga</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pos_groupby_niaga as $item)
                <tr>
                    <td>{{ $item->vendor_name }}</td>
                    <td>{{ $item->niaga }}</td>
                    <td>{{ $item->non_niaga }}</td>
                    <td>{{ $item->niaga + $item->non_niaga }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@section('local-js')
@endsection
