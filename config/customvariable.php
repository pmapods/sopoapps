<?php
return [
    'masterdata_accesses' => [
        'Jabatan',
        'Karyawan',
        'SalesPoint',
        'Akses Karyawan',
        'Matriks Approval',
        'Vendor',
        'Budget Pricing',
        'Kelengkapan Berkas',
        'Master Armada',
        'Additional Email',
        'Notification Email',
        'Custom Ticketing',
        'Ticketing Block',
        'Email CC',
        'Upload PO Manual'
    ],

    'budget_accesses' => [
        'Inventory',
        'Armada',
        'Assumption',
        'HO',
        'Monitoring Feature'
    ],

    'operational_accesses' => [
        'Pengadaan',
        'Bidding',
        'Purchase Requisition',
        'Purchase Order (Setup)',
        'Purchase Order (Process)',
        'Form Validation',
        'Vendor Evaluation',
        'Peremajaan Armada'
    ],

    'monitoring_accesses' => [
        'Monitor Pengadaan',
        'Monitor Security',
        'Monitor Armada',
        'Monitor CIT',
        'Monitor PEST',
        'Monitor Merchandiser'
    ],

    'reporting_accesses' => [
        'Armada Accident (view, add, update)',
        'Armada Accident (update status open & close)',
        'Upload Report',
        'Download Report'
    ],

    'feature_accesses' => [
        'Multi Approve'
    ],

    'division' => [
        "Finance",
        "Accounting",
        "Tax",
        "Claim",
        "Purchasing",
        "Internal Audit",
        "Indirect",
        "Sales HO",
        "Sales GT",
        "Sales MT",
        "Sales Support",
        "Key Account",
        "Merchandiser",
        "Customer Relationship",
        "Logistik",
        "Demand Planner",
        "Compensation and Benefit",
        "People and Organization Development",
        "Industrial Relationship",
        "General Affair",
        "Legal and Compliance",
        "Recruitment",
        "IT",
        "Internal Audit",
        "FAD"
    ],
    'fail_email_text' => "Notif email sedang gangguan silahkan hubungi pic untuk approval selanjutnya",
    'bearer_token' => env('BEARER_TOKEN', null),
    'asset_bearer_token' => env('ASSET_BEARER_TOKEN', null)
];
