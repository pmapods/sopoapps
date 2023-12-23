<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalesPoint;
use App\Models\Authorization;
use App\Models\AuthorizationDetail;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeeLocationAccess;
use DB;

class AuthorizationSeederX5B extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $authorization_data = array(

            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "05130055,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "05130055,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "05130055,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "18000218,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "18000218,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "18000218,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "16000178,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000178,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000178,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "18000289,18000140,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "18000289,18000140,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "18000289,18000140,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "05130039,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "05130039,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "05130039,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "17000121,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000121,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000121,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "17000650,18000140,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000650,18000140,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000650,18000140,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "08141323,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08141323,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08141323,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "05130037,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "05130037,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "05130037,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "16000377,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000377,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000377,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "08140328,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08140328,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08140328,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "18000052,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "18000052,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "18000052,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "16000509,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000509,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000509,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "05130051,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "05130051,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "05130051,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "16000502,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000502,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000502,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "16000073,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000073,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000073,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "17000186,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000186,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000186,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "17000752,18000140,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000752,18000140,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000752,18000140,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "16000030,08140836,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000030,08140836,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000030,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "17000825,18000140,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000825,18000140,16000348,16000445,08141504", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000825,18000140,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Regional Business Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head Of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "15000215,16000445,16000348", "urutan_employee_position" => "Accounting Manager, Head of Finance Accounting, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "15000215,16000445,16000348,16000445,08141504", "urutan_employee_position" => "Accounting Manager, Head of Finance Accounting, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "15000215,16000445,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Accounting Manager, Head of Finance Accounting, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "16000214,16000445,16000348", "urutan_employee_position" => "Tax Plan Manager, Head of Finance Accounting, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000214,16000445,16000348,16000445,08141504", "urutan_employee_position" => "Tax Plan Manager, Head of Finance Accounting, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000214,16000445,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Tax Plan Manager, Head of Finance Accounting, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "08130379,08141504,16000348", "urutan_employee_position" => "National Operation Manager, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08130379,08141504,16000348,16000445,08141504", "urutan_employee_position" => "National Operation Manager, Head of Operation, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08130379,08141504,16000348,16000445,08141504,16000348", "urutan_employee_position" => "National Operation Manager, Head of Operation, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "17000652,08140836,16000348", "urutan_employee_position" => "National Trade Support Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000652,08140836,16000348,16000445,08141504", "urutan_employee_position" => "National Trade Support Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution,Head of Finance Accounting, Head of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "17000652,08140836,16000348,16000445,08141504,16000348", "urutan_employee_position" => "National Trade Support Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution,Head of Finance Accounting, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "16000336,18000140,16000348", "urutan_employee_position" => "National Trade Support Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000336,18000140,16000348,16000445,08141504", "urutan_employee_position" => "National Trade Support Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution,Head of Finance Accounting, Head of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "16000336,18000140,16000348,16000445,08141504,16000348", "urutan_employee_position" => "National Trade Support Manager, Head of Sales & Distribution, Vice President of Domestic Sales & Distribution,Head of Finance Accounting, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "08140496,08141504,16000348", "urutan_employee_position" => "Logistic Operation Manager, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08140496,08141504,16000348,16000445,08141504", "urutan_employee_position" => "Logistic Operation Manager, Head of Operation, Vice President of Domestic Sales & Distribution,Head of Finance Accounting, Head of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08140496,08141504,16000348,16000445,08141504,16000348", "urutan_employee_position" => "Logistic Operation Manager, Head of Operation, Vice President of Domestic Sales & Distribution,Head of Finance Accounting, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 0, "urutan_nik" => "08130976,08141504,16000348", "urutan_employee_position" => "National Operation Manager, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Pengaju, Atasan Langsung, Atasan Tidak Langsung", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08130976,08141504,16000348,16000445,08141504", "urutan_employee_position" => "National Operation Manager, Head of Operation, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head of Operation", "urutan_sign_as" => "Dibuat Oleh, Diperiksa Oleh, Diperiksa Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),
            array("salespoint" => "HO TANGERANG", "form_type" => 2, "urutan_nik" => "08130976,08141504,16000348,16000445,08141504,16000348", "urutan_employee_position" => "National Operation Manager, Head of Operation, Vice President of Domestic Sales & Distribution, Head of Finance Accounting, Head of Operation, Vice President of Domestic Sales & Distribution", "urutan_sign_as" => "Dibuat Oleh,Diperiksa Oleh,Diperiksa Oleh,Disetujui Oleh, Disetujui Oleh, Disetujui Oleh", "notes" => ""),

        );

        foreach ($authorization_data as $data) {
            try {
                DB::beginTransaction();
                if (!in_array($data["salespoint"], ["all", "west", "east", "indirect"])) {
                    $salespoint = SalesPoint::where('name', $data["salespoint"])->first();
                    if (!$salespoint) {
                        throw new \Exception("Salespoint dengan nama " . $data["salespoint"] . " tidak ditemukan di sistem");
                    } else {
                        $data["salespoint"] = $salespoint->id;
                    }
                }
                $newAuthorization = new Authorization;
                $newAuthorization->salespoint_id  = $data["salespoint"];
                $newAuthorization->form_type  = $data['form_type'];
                // 0  form pengadaan barang jasa | ticketing (1)
                // 1  form bidding   | bidding (2)
                // 2  form pr| pr (4)
                // 3  form po| po (8)
                // 4  form fasilitas | ticketing (1)
                // 5  form mutasi| ticketing (1)
                // 6  form perpanjangan perhentian   | ticketing (1)
                // 7  form pengadaan armada  | ticketing (1)
                // 8  form pengadaan security| ticketing (1)
                // 9  form evaluasi  | ticketing (1)
                // 10 Upload Budget (baru)   | budget inventory,armada,assumption (7)
                // 11 Upload Budget (revisi) | budget inventory,armada,assumption (7)
                $newAuthorization->notes  = $data['notes'] ?? null;
                // $newAuthorization->save();
                // pindah kebawa cek jumpah author dulu
                $urutan_nik = explode(",", $data['urutan_nik']);
                $urutan_employee_position  = explode(",", $data['urutan_employee_position']);
                $urutan_sign_as = explode(",", $data['urutan_sign_as']);
                // map array into objects
                $approval_data = [];
                foreach ($urutan_nik as $key => $nik) {
                    if (!is_numeric($nik)) {
                        continue;
                    }
                    if (strlen(trim($nik)) != 8) {
                        $nik = substr_replace(trim($nik), "0", 0, 0);
                    }
                    array_push($approval_data, [
                        "nik" => trim($nik),
                        "employee_position" => trim($urutan_employee_position[$key]),
                        "sign_as" => trim($urutan_sign_as[$key])
                    ]);
                }
                if (count($approval_data) < 1) {
                    throw new \Exception("Salespoint " . $data['salespoint'] . " tidak memiliki otorisasi / otorisasi < 1");
                }
                $newAuthorization->save();
                $level = 1;
                foreach ($approval_data as $approval) {
                    // check if nik exists
                    $employee = Employee::where('nik', $approval["nik"])->first();
                    if (!$employee) {
                        throw new \Exception("nik " . $approval["nik"] . " tidak tersedia");
                    }
                    // check if employee position exists
                    $employee_position = EmployeePosition::where('name', $approval["employee_position"])->first();
                    if (!$employee_position) {
                        throw new \Exception("jabatan \"" . $approval["employee_position"] . "\" tidak terdaftar");
                    }
                    $detail = new AuthorizationDetail;
                    $detail->authorization_id   = $newAuthorization->id;
                    $detail->employee_id = $employee->id;
                    $detail->employee_position_id   = $employee_position->id;
                    $detail->sign_as = $approval['sign_as'];
                    $detail->level  = $level;
                    $detail->save();
                    $level++;

                    // tambah hak akses area jika blom ada
                    $salespoint_id_access_list = [];
                    if ($data["salespoint"] == "all") {
                        $salespoint_id_access_list = SalesPoint::all()->pluck("id");
                    } else if (in_array($data["salespoint"], ["west", "east"])) {
                        $salespoint_id_access_list = SalesPoint::where("region_type", $data["salespoint"])->get()->pluck("id");
                    } else if ($data["salespoint"] == "indirect") {
                        $salespoint_id_access_list = SalesPoint::where("region", 19)->get()->pluck("id");
                    } else {
                        $salespoint_id_access_list = SalesPoint::where("id", $data["salespoint"])->get()->pluck("id");
                    }
                    foreach ($salespoint_id_access_list as $salespoint_id) {
                        $check_if_employee_location_access_exists = EmployeeLocationAccess::where("employee_id", $employee->id)->where("salespoint_id", $salespoint_id)->first();
                        if (!$check_if_employee_location_access_exists) {
                            $newAccess = new EmployeeLocationAccess;
                            $newAccess->employee_id = $employee->id;
                            $newAccess->salespoint_id = $salespoint_id;
                            $newAccess->save();
                        }
                    }
                }
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                print($ex->getMessage() . "|" . $ex->getLine() . "\n");
            }
        }
        print("import 5B authorization data finished ");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
