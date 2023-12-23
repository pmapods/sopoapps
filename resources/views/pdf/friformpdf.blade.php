<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <link rel="stylesheet" href="/fontawesome/css/all.min.css">
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <center>
        <h5>FORM<br>REQUEST INFRASTRUKTUR</h5>
    </center>
    <table class="table table-bordered table-sm small">
        <tbody>
            <tr>
                <th width="30%">Date Request</th>
                <td>{{ $fri_form->date_request }}</td>
            </tr>
            <tr>
                <th width="30%">Date Use</th>
                <td>{{ $fri_form->date_use }}</td>
            </tr>
            <tr>
                <th width="30%">Work Location</th>
                <td>{{ $fri_form->work_location }}</td>
            </tr>
            <tr>
                <th width="30%">Area</th>
                <td>{{ $fri_form->salespoint_name }}</td>
            </tr>
            <tr>
                <th width="30%">User Name / Position</th>
                <td>{{ $fri_form->username_position }}</td>
            </tr>
            <tr>
                <th width="30%">Div / Dept</th>
                <td>{{ $fri_form->division_department }}</td>
            </tr>
            <tr>
                <th width="30%">Contact Number</th>
                <td>{{ $fri_form->contact_number }}</td>
            </tr>
            <tr>
                <th width="30%">Email Address</th>
                <td>{{ $fri_form->email_address }}</td>
            </tr>
        </tbody>
    </table>
    <div class="text-center"><b>HARDWARE DETAILS</b></div>
    <table class="table table-bordered table-sm small">
        <thead>
            <tr class="text-center">
                <th width="8%">NO</th>
                <th width="25%">UNIT</th>
                <th width="8%">QTY</th>
                <th>REMARK</th>
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
    {{-- @if (count($hardware_details)>13)
        <div class="page-break"></div>
    @endif --}}
    <div class="text-center"><b>APPLICATION DETAILS</b></div>
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
    <table class="table table-sm table-borderless small">
        <tbody>
            @foreach ($application_detail_items as $key=>$item)
                @if ($key != 0 && ($key % 2) == 0 && !$loop->last) </tr> @endif
                @if ($key % 2 == 0 && !$loop->last) <tr> @endif
                
                <td style="padding : 0px !important">
                    @if (in_array($item, $selected_details))
                        <input type="checkbox" checked="checked" style="margin-top : 5px !important; margin-right : 5px !important;">{{ $item }}
                    @elseif($item == 'free_text' && count($custom_details) > 0)
                        <input type="checkbox" checked="checked" style="margin-top : 5px !important; margin-right : 5px !important;">{{ $custom_details[0] }}
                        @php array_shift($custom_details); @endphp
                    @else
                        <input type="checkbox" style="margin-top : 5px !important; margin-right : 5px !important;">{{ ($item != 'free_text') ? $item : '' }}
                    @endif
                </td>
                @if ($loop->last)
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @php
        $sebagais = ['Dibuat Oleh', 'Diperiksa Oleh', 'Diketahui Oleh', 'Diinput Oleh'];
        $positions = ['User ( Min. SPV )', 'Atasan Langsung (Min.manager)', 'IT OSM', 'IT IS Staff / IT IS SPV'];
        $level = [1, 2, 4, 3];
    @endphp
    <table class="table table-bordered small text-center">
        <tr>
            @foreach ($sebagais as $sebagai)
                <td>{{ $sebagai }}</td>
            @endforeach
        </tr>
        <tr>
            @foreach ($positions as $key => $position)
            @php
                $author = $fri_form->authorizations->where('level', $level[$key])->first();
            @endphp
            <td>
                {{ $position }}<br>
                @if ($author->status == 1)
                    <span class="text-success">Approved</span><br>
                    <span class="text-success">{{ $author->updated_at->translatedFormat('d F Y (H:i)') }}</span><br>
                @endif
                <br>
                <span>{{ $author->employee_name }}</span>
            </td>
            @endforeach
        </tr>

    </table>
    <div>FRM-ITD-008 REV 02</div>
</body>

</html>
