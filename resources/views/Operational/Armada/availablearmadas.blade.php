{{-- validasi kalo bukan pengadaan aktif (selesai/cancelled) --}}
<h5>
    Armada Tersedia di {{ $armadaticket->salespoint->name }}
</h5> 
<div class="text-secondary">Harap melengkapi data berikut untuk melanjutkan proses pengadaan</div>
<form action="/completearmadabookedby" method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="armada_ticket_id" value="{{ $armadaticket->id }}">
    <table class="table">
        <thead class="table-secondary">
            <tr>
                <td>Nomor Plat</td>
                <td>Jenis Kendaraan</td>
                <td>Di Booking Oleh</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($available_armadas as $key => $available_armada)
            <tr>
                <td>{{ $available_armada->plate }}</td>
                <td>{{ $available_armada->armada_type->brand_name }} {{ $available_armada->armada_type->name }}</td>
                <td>
                    <input type="hidden" class="form-control"
                    name="armada[{{ $key }}][armada_id]"
                    value="{{ $available_armada->id }}">
                    <input type="text" class="form-control"
                    placeholder="Masukkan nama"
                    name="armada[{{ $key }}][booked_by]"
                    required>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="text-center">
        <button type="submit" class="btn btn-primary">Lengkapi Data</button>
    </div>
</form>
@section('availablearmadas-js')
<script>
</script>
@endsection