Dear Bapak/Ibu {{ $to }}<br>
Akun anda dengan informasi akun : <br>
<table>
    <tr>
        <td>Nama</td>
        <td>: {{ $data['employee']->name }}</td>
    </tr>
    <tr>
        <td>NIK</td>
        <td>: {{ $data['employee']->nik }}</td>
    </tr>
    <tr>
        <td>Email</td>
        <td>: {{ $data['employee']->email }}</td>
    </tr>
</table><br>
Telah melakukan reset password oleh {{ $from }}. <br>
Harap segera melakukan Login dengan NIK dan password default yaitu "pma123", dan melakukan pergantian password<br>

<br>
Jika anda tidak melakukan request pergantian password harap dapat menghubungi admin.


<br>
@if (config('app.env') != 'production')
DEVELOPMENT PODS<br>
=========================================<br>
original emails : {{ implode(', ',$original_emails) }}<br>
original ccs : {{ implode(', ',$original_ccs) }}<br>
=========================================<br>
@endif