<?php

namespace App\Http\Controllers\Dashboard;

use Auth;
use App\Http\Controllers\Controller;
use App\Models\VendorEvaluationAuthorization;

class DashboardVendorEvaluationController extends Controller
{
    public function dashboardVendorEvaluationView()
    {
        return view('Dashboard.vendorevaluation');
    }

    public function getVendorEvaluation()
    {
        $data = [];
        $vendorevaluationauthorization = VendorEvaluationAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->orderBy('created_at')
            ->get();

        foreach ($vendorevaluationauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $vendor_evaluation = $author->vendorEvaluation;

                $newAuth->salespoint = $vendor_evaluation->salespoint->name;
                $newAuth->code = $vendor_evaluation->code;
                $newAuth->created_at = $vendor_evaluation->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $vendor_evaluation->created_by_employee->name;
                $newAuth->transaction_type = 'Vendor Evaluation';
                $newAuth->status = $vendor_evaluation->status();
                $newAuth->link = "/vendor-evaluation/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        $data = array_values(collect($data)->toArray());

        foreach ($data as $key => &$xxx) {
            $xxx->nomor = $key + 1;
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    public function getVendorEvaluationCount()
    {
        $vendorevaluation = VendorEvaluationAuthorization::where('status', 0)
            ->where('employee_id', Auth::user()->id)
            ->get();

        $vendor = 0;
        foreach ($vendorevaluation as $vendorevaluations) {
            if ($vendorevaluations->level == 1) {
                $vendorevaluation_before = VendorEvaluationAuthorization::where('vendor_evaluation_id', $vendorevaluations->vendor_evaluation_id)
                    ->where('level', 1)
                    ->count();
                if ($vendorevaluation_before) {
                    $vendor++;
                }
            } else {
                $vendorevaluation_before = VendorEvaluationAuthorization::where('vendor_evaluation_id', $vendorevaluations->vendor_evaluation_id)
                    ->where('level', $vendorevaluations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($vendorevaluation_before) {
                    $vendor++;
                }
            }
        }

        return $vendor;
    }
}
