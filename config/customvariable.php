<?php
return [
    'masterdata_accesses' => [
        'Karyawan',
        'Akses Karyawan',
        'Jabatan',
        'Cabang',
        'Supplier',
        'Customer',
        'Armada',
        'Notification Email',
        'Matriks Approval',
        'Produk',
        'Bahan Baku'
    ],

    'sales_accesses' => [
        'POQuotation',
        'Sales Order',
        'Delivery Order',
        'Pengiriman',
        'Pelunasan',
        'Invoice',
        'Undelivery'
    ],

    'logistik_accesses' => [
        'Stock Realtime',
        'Mutasi',
        'Stock Opname',
        'Disposal'
    ],

    'monitoring_accesses' => [
        'Monitor PO Penjualan',
        'Monitor PO Sewa',
        'Monitor PO Custom'
    ],

    'reporting_accesses' => [
        'Report Penjualan',
        'Report Sewa',
        'Report Omset'
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
