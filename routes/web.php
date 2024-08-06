<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApprovalManagement\AuctionbeapprovalController;
use App\Http\Controllers\ApprovalManagement\AuctionbeController as ApprovalManagementAuctionbeController;

use App\Mail\TestMail;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LoginVendorController;

// Dashboard
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DashboardVendorEvaluationController;
use App\Http\Controllers\Dashboard\DashboardRequestApprovalController;
use App\Http\Controllers\Dashboard\DashboardGaPrManualController;
use App\Http\Controllers\Dashboard\DashboardItBarangJasaReminderController;
use App\Http\Controllers\Dashboard\DashboardBaVerificationController;
use App\Http\Controllers\Dashboard\DashboardPengadaanCOPController;
use App\Http\Controllers\Dashboard\DashboardPrManualAssetController;
use App\Http\Controllers\Dashboard\DashboardPoWillExpiredController;
use App\Http\Controllers\Dashboard\DashboardRequestTypePendingController;

// Masterdata
use App\Http\Controllers\Masterdata\EmployeeController;
use App\Http\Controllers\Masterdata\SalesPointController;
use App\Http\Controllers\Masterdata\EmployeeAccessController;
use App\Http\Controllers\Masterdata\AuthorizationController;
use App\Http\Controllers\Masterdata\VendorController;
use App\Http\Controllers\Masterdata\BudgetPricingController;
use App\Http\Controllers\Masterdata\MaintenanceBudgetController;
use App\Http\Controllers\Masterdata\HOBudgetController;
use App\Http\Controllers\Masterdata\FileCompletementController;
use App\Http\Controllers\Masterdata\ArmadaController;
use App\Http\Controllers\Masterdata\MailingController;
use App\Http\Controllers\Masterdata\CustomTicketingController;
use App\Http\Controllers\Masterdata\MasterDataTicketingBlockController;
use App\Http\Controllers\Masterdata\CcEmailController;
use App\Http\Controllers\Masterdata\PoManualController;

// Budget
use App\Http\Controllers\Budget\BudgetUploadController;
use App\Http\Controllers\Budget\ArmadaBudgetUploadController;
use App\Http\Controllers\Budget\AssumptionBudgetUploadController;
use App\Http\Controllers\Budget\HOBudgetUploadController;

// Operational
use App\Http\Controllers\Operational\RenewalArmadaController;
use App\Http\Controllers\Operational\TicketingController;
use App\Http\Controllers\Operational\TicketingBlockController;
use App\Http\Controllers\Operational\ArmadaTicketingController;
use App\Http\Controllers\Operational\SecurityTicketingController;
use App\Http\Controllers\Operational\AdditionalTicketingController;
use App\Http\Controllers\Operational\BiddingController;
use App\Http\Controllers\Operational\PRController;
use App\Http\Controllers\Operational\FormValidationController;
use App\Http\Controllers\Operational\POController;
use App\Http\Controllers\Operational\VendorEvaluationController;

// Monitoring
use App\Http\Controllers\Monitoring\MonitoringController;

// Register
use App\Http\Controllers\Register\RegisterController;

// Reporting
use App\Http\Controllers\Reporting\AccidentController;
use App\Http\Controllers\Reporting\ArmadaAccidentController;
use App\Http\Controllers\Reporting\UploadReportController;
use App\Http\Controllers\Reporting\DownloadReportController;

// Auction
use App\Http\Controllers\Auction\AuctionController;

// Approval Management
use App\Http\Controllers\ApprovalManagement\VendorApprovalController;
use App\Http\Controllers\Auction\AuctionbeController;


Route::get('/', function () {
    return redirect('login');
});
ROute::get('/myphp', function () {
    phpinfo();
});
//Auth
Route::get('/login', [LoginController::class, 'loginView'])->name('login');
Route::post('/doLogin', [LoginController::class, 'doLogin']);

// PUBLIC ACCESS
// Rute untuk Vendor
Route::prefix('auction')->group(function() {
    // AuthVendor
    Route::get('/login', [LoginVendorController::class, 'loginVendorView'])->name('loginVendor');
    Route::post('/doLoginVendor', [LoginVendorController::class, 'doLoginVendor'])->name('vendor.dologin');
    Route::post('/logoutVendor', function () {
        Auth::guard('vendor')->logout();
        return redirect()->route('loginVendor'); // Mengarahkan pengguna ke halaman login vendor setelah logout
    })->name('vendor.logout');
    // GetVendor
    Route::get('/register', [RegisterController::class, 'register'])->name('vendor.register');
    Route::post('/addVendorCompany', [RegisterController::class, 'addVendorCompany'])->name('vendor.company');
    // Auction
    Route::get('/auctionTicket', [AuctionController::class, 'AuctionView'])->name('vendor.dashboard');
    Route::get('/auctionTicketDetail/{code}', [AuctionController::class, 'AuctionDetailView'])->name('vendor.auctiondetail');
    Route::post('/vendor-request-auction', [AuctionController::class, 'RequestAuctionBidding']);
});


// Bidding
Route::get('/bidding/printview/{encrypted_bidding_id}', [BiddingController::class, 'biddingPrintView']);

// Purchase Requisition
Route::get('/printPR/{ticket_code}', [PRController::class, 'printPR']);

// Purchase Order
Route::get('/signpo/{po_upload_request_id}', [POController::class, 'poUploadRequestView']);
Route::post('/uploadsigneddocument', [POController::class, 'poUploadRequest']);
Route::post('/rejectsigneddocument', [POController::class, 'poRejectSignedRequest']);

Route::middleware(['auth'])->group(function () {
    Route::middleware(['superadmin'])->group(function () {
        Route::get('/development', function () {
            return view('development');
        });

        Route::get('/development/sendemailtest', function () {
            try {
                Mail::to(request()->email)->send(new TestMail());
                return response()->json([
                    "error" => false,
                    "message" => "Success send to " . request()->email
                ]);
            } catch (\Exception $ex) {
                return response()->json([
                    "error" => true,
                    "message" => $ex->getMessage()
                ]);
            }
        });

        Route::post('/development/update', function () {
            \Config::set('mail.testing_email', request()->email_testing);
            return back()->with('success', 'berhasil update');
        });

        Route::get('/development/optimize', function () {
            Artisan::call('optimize:clear');
            Artisan::call('optimize');
            return back();
        });

        Route::get('/development/sendreminder', function () {
            try {
                Artisan::call('po:remindexpired');
                dd('reminder success');
            } catch (\Exception $ex) {
                dd($ex);
            }
        });

        Route::get('/development/sendremindervendorevaluation', function () {
            try {
                Artisan::call('vendorevaluation:reminder');
                dd('reminder success');
            } catch (\Exception $ex) {
                dd($ex);
            }
        });

        Route::get('/development/sendreminderbarangjasait', function () {
            try {
                Artisan::call('barangjasait:reminder');
                dd('reminder success');
            } catch (\Exception $ex) {
                dd($ex);
            }
        });

        Route::get('/development/pomanualattachmentreminder', function () {
            try {
                Artisan::call('pomanualattachment:reminder');
                dd('reminder success');
            } catch (\Exception $ex) {
                dd($ex);
            }
        });

        Route::get('/development/hobudgetsedder', function () {
            try {
                Artisan::call('hobudget:hobudgetsedder');
                dd('success');
            } catch (\Exception $ex) {
                dd($ex);
            }
        });
    });

    Route::get('/changepassword', function () {
        return view('Auth.changepassword');
    });
    Route::patch('/updatepassword', [LoginController::class, 'updatePassword']);
    Route::get('/logout', function () {
        Auth::logout();
        return back();
    });

    // DASHBOARD
    Route::get('/profile', [DashboardController::class, 'profileView']);
    Route::post('/changepassword', [DashboardController::class, 'changePassword']);
    Route::get('/dashboard', [DashboardController::class, 'dashboardView']);

    // Dashboard Request Approval
    Route::get('/dashboardRequestApproval', [DashboardRequestApprovalController::class, 'dashboardRequestApprovalView']);
    Route::post('/quickapproval', [DashboardRequestApprovalController::class, 'quickApproval']);
    Route::get('/getCurrentAuthorization/{approval_type}', [DashboardRequestApprovalController::class, 'getCurrentAuthorizationwithType']);
    Route::get('/getCurrentAuthorization', [DashboardRequestApprovalController::class, 'getCurrentAuthorization']);

    Route::middleware(['menu_access:feature:1'])->group(function () {
        Route::post('/multiapprove', [DashboardRequestApprovalController::class, 'multiApprove']);
    });

    // Dashboard Request PR SAP (GA)
    Route::get('/dashboardGaPrManual', [DashboardGaPrManualController::class, 'dashboardGaPrManualView']);
    Route::get('/getgaprmanual', [DashboardGaPrManualController::class, 'getGaPrManual']);

    // Dashboard Reminder Perpanjangan Barang Jasa Jenis IT
    Route::get('/dashboarditbarangjasareminder', [DashboardItBarangJasaReminderController::class, 'dashboardItBarangJasaReminderView']);
    Route::get('/itbarangjasareminder', [DashboardItBarangJasaReminderController::class, 'getItBarangJasaReminder']);

    // Dashboard Vendor Evaluation
    Route::get('/dashboardVendorEvaluation', [DashboardVendorEvaluationController::class, 'dashboardVendorEvaluationView']);
    Route::get('/getVendorEvaluation', [DashboardVendorEvaluationController::class, 'getVendorEvaluation']);

    // Dashboard Ba Verification
    Route::get('/dashboardBaVerification', [DashboardBaVerificationController::class, 'dashboardBaVerificationView']);
    Route::get('/getBAverification', [DashboardBaVerificationController::class, 'getBAverification']);

    // Dashboard Pengadaan COP
    Route::get('/dashboardPengadaanCOP', [DashboardPengadaanCOPController::class, 'dashboardPengadaanCOPView']);
    Route::get('/getPengadaanCOP', [DashboardPengadaanCOPController::class, 'getPengadaanCOP']);

    // Dashboard PR Manual Asset
    Route::get('/dashboardPrManualAsset', [DashboardPrManualAssetController::class, 'dashboardPrManualAssetView']);
    Route::get('/getprmanualasset', [DashboardPrManualAssetController::class, 'getPrManualAsset']);

    // Dashboard PO Will Expired
    Route::get('/dashboardPoWillExpired', [DashboardPoWillExpiredController::class, 'dashboardPoWillExpiredView']);
    Route::get('/getPoWillExpired', [DashboardPoWillExpiredController::class, 'getPoWillExpired']);

    // Dashboard Request Type Pending
    Route::get('/dashboardRequestTypePending', [DashboardRequestTypePendingController::class, 'dashboardRequestTypePendingView']);
    Route::get('/getRequestTypePending', [DashboardRequestTypePendingController::class, 'getRequestTypePending']);


    // MASTERDATA
    // Employee Postion
    Route::middleware(['menu_access:masterdata:1'])->group(function () {
        Route::get('/employeeposition', [EmployeeController::class, 'employeepostitionView']);
        Route::post('/addPosition', [EmployeeController::class, 'addEmployeePosition']);
        Route::patch('/updatePosition', [EmployeeController::class, 'updateEmployeePosition']);
        Route::delete('/deletePosition', [EmployeeController::class, 'deleteEmployeePosition']);
    });

    // Employee
    Route::middleware(['menu_access:masterdata:2'])->group(function () {
        Route::get('/employee', [EmployeeController::class, 'employeeView']);
        Route::post('/addEmployee', [EmployeeController::class, 'addEmployee']);
        Route::patch('/updateEmployee', [EmployeeController::class, 'updateEmployee']);
        Route::delete('/deleteemployee', [EmployeeController::class, 'deleteEmployee']);
        Route::patch('/nonactiveemployee', [EmployeeController::class, 'nonactiveEmployee']);
        Route::patch('/activeemployee', [EmployeeController::class, 'activeEmployee']);
        Route::get('/migrateemployeeconfirmation', [EmployeeController::class, 'migrateEmployeeConfirmationView']);
        Route::get('/jobtitleemployeeconfirmation', [EmployeeController::class, 'jobtitleEmployeeConfirmation']);
        Route::post('/employee/migrate', [EmployeeController::class, 'doMigrateEmployee']);
        Route::post('/resetemployeepassword', [EmployeeController::class, 'resetEmployeePassword']);
        Route::get('/orgcharts', [EmployeeController::class, 'orgChartView']);
        Route::get('/orgcharts/{nik}', [EmployeeController::class, 'orgChartDetailView']);
        Route::get('/getEmployeePosition', [EmployeeController::class, 'getEmployeePosition']);
    });

    // Sales Point
    Route::middleware(['menu_access:masterdata:4'])->group(function () {
        Route::get('/salespoint', [SalesPointController::class, 'salespointView']);
        Route::post('/addsalespoint', [SalesPointController::class, 'addSalesPoint']);
        Route::patch('/updatesalespoint', [SalesPointController::class, 'updateSalesPoint']);
        Route::delete('/deletesalespoint', [SalesPointController::class, 'deleteSalesPoint']);
    });

    // Employee Access
    Route::middleware(['menu_access:masterdata:8'])->group(function () {
        Route::get('/employeeaccess', [EmployeeAccessController::class, 'employeeaccessView']);
        Route::get('/employeeaccess/{employee_code}', [EmployeeAccessController::class, 'employeeaccessdetailView']);
        Route::patch('/updateemployeeaccessdetail', [EmployeeAccessController::class, 'updateemployeeaccessdetail']);
    });
    Route::get('/myaccess', [EmployeeAccessController::class, 'myAccessView']);

    // Authorization
    Route::middleware(['menu_access:masterdata:16'])->group(function () {
        Route::get('/authorization', [AuthorizationController::class, 'authorizationView']);
        Route::get('/authorization/data', [AuthorizationController::class, 'authorizationData']);
        Route::get('/getauthorizedemployeebysalesPoint/{salespoint_id}', [AuthorizationController::class, 'AuthorizedEmployeeBySalesPoint']);
        Route::post('/addauthorization', [AuthorizationController::class, 'addAuthorization']);
        Route::patch('/updateauthorization', [AuthorizationController::class, 'updateAuthorization']);
        Route::delete('/deleteauthorization', [AuthorizationController::class, 'deleteAuthorization']);
        Route::get('/authorization/getdetails', [AuthorizationController::class, 'getAuthorizationDetails']);
        Route::post('/authorization/multireplace', [AuthorizationController::class, 'multiReplace']);
    });

    // VENDOR
    Route::middleware(['menu_access:masterdata:32'])->group(function () {
        Route::get('/vendor', [VendorController::class, 'vendorView']);
        Route::post('/addvendor', [VendorController::class, 'addVendor']);
        Route::patch('/updatevendor', [VendorController::class, 'updateVendor']);
        Route::delete('/deletevendor', [VendorController::class, 'deleteVendor']);
    });

    // Budget Pricing
    Route::middleware(['menu_access:masterdata:64'])->group(function () {
        Route::get('/budgetpricing', [BudgetPricingController::class, 'budgetpricingView']);
        Route::get('/budgetpricing/updateall/template', [BudgetPricingController::class, 'updateAllTemplate']);
        Route::post('/budgetpricing/updateall/update', [BudgetPricingController::class, 'updateAllUpdate']);
        Route::post('/budgetpricing/updateall/readtemplate', [BudgetPricingController::class, 'updateAllReadTemplate']);
        Route::post('/addbudget', [BudgetPricingController::class, 'addBudget']);
        Route::patch('/updatebudget', [BudgetPricingController::class, 'updateBudget']);
        Route::delete('/deletebudget', [BudgetPricingController::class, 'deleteBudget']);

        // maintenance budget
        Route::get('/maintenancebudget', [MaintenanceBudgetController::class, 'view']);
        Route::post('/maintenancebudget/add', [MaintenanceBudgetController::class, 'addBudget']);
        Route::post('/maintenancebudget/update', [MaintenanceBudgetController::class, 'updateBudget']);
        Route::post('/maintenancebudget/delete', [MaintenanceBudgetController::class, 'deleteBudget']);

        // ho budget
        Route::get('/hobudget', [HOBudgetController::class, 'view']);
        Route::post('/hobudget/add', [HOBudgetController::class, 'addBudget']);
        Route::post('/hobudget/update', [HOBudgetController::class, 'updateBudget']);
        Route::post('/hobudget/delete', [HOBudgetController::class, 'deleteBudget']);
    });

    // Kelengkapan berkas
    Route::middleware(['menu_access:masterdata:128'])->group(function () {
        Route::get('/filecompletement', [FileCompletementController::class, 'fileCompletementView']);
    });

    // Armada
    Route::middleware(['menu_access:masterdata:256'])->group(function () {
        Route::get('/armada', [ArmadaController::class, 'armadaView']);
        Route::post('/addarmada', [ArmadaController::class, 'addArmada']);
        Route::post('/addarmadatype', [ArmadaController::class, 'addArmadaType']);
        Route::post('/deletearmadatype', [ArmadaController::class, 'deleteArmadaType']);
        Route::patch('/updatearmada', [ArmadaController::class, 'updateArmada']);
        Route::delete('/deletearmada', [ArmadaController::class, 'deleteArmada']);
    });

    // Additional Email
    Route::middleware(['menu_access:masterdata:512'])->group(function () {
        Route::get('/additionalemail', [MailingController::class, 'additionalEmailView']);
        Route::get('/additionalemail/purchasing', [MailingController::class, 'purchasingEmailView']);
        Route::get('/additionalemail/ga', [MailingController::class, 'GAEmailView']);
        Route::post('/additionalemail/update', [MailingController::class, 'updateAdditionalEmail']);
    });

    // Notification Email
    Route::middleware(['menu_access:masterdata:1024'])->group(function () {
        Route::get('/notificationemail', [MailingController::class, 'notificationEmailView']);
        Route::post('/notificationemail/create', [MailingController::class, 'createNotification']);
        Route::post('/notificationemail/update', [MailingController::class, 'updateNotification']);
        Route::post('/notificationemail/delete', [MailingController::class, 'deleteNotification']);
        Route::get('/notificationemail/getdetails', [MailingController::class, 'getNotificationDetails']);
        Route::post('/notificationemail/multireplace', [MailingController::class, 'multiReplace']);
    });

    // Custom Ticketing
    Route::middleware(['menu_access:masterdata:2048'])->group(function () {
        Route::get('/customticketing', [CustomTicketingController::class, 'customTicketingView']);
        Route::post('/customticketing/create', [CustomTicketingController::class, 'createCustomTicket']);
        Route::post('/customticketing/update', [CustomTicketingController::class, 'updateCustomTicket']);
        Route::post('/customticketing/delete', [CustomTicketingController::class, 'deleteCustomTicket']);
        // Route::post('/customticketing/ticket/create', [CustomTicketingController::class, 'createTicket']);
    });

    // Ticketing Block
    Route::middleware(['menu_access:masterdata:4096'])->group(function () {
        Route::get('/ticketingblocking', [MasterDataTicketingBlockController::class, 'ticketingBlockView']);
        Route::post('/ticketingblocking/create', [MasterDataTicketingBlockController::class, 'createTicketingBlock']);
        Route::post('/ticketingblocking/update', [MasterDataTicketingBlockController::class, 'updateTicketingBlock']);
        Route::post('/ticketingblocking/reset', [MasterDataTicketingBlockController::class, 'resetTicketingBlock']);
    });

    // Email CC
    Route::middleware(['menu_access:masterdata:8192'])->group(function () {
        Route::get('/emailcc', [CcEmailController::class, 'ccEmailView']);
        Route::post('/emailcc/create', [CcEmailController::class, 'ccEmailCreate']);
        Route::get('/emailcc/delete/{id}', [CcEmailController::class, 'ccEmailDelete']);
    });

    // Upload PO Manual
    Route::middleware(['menu_access:masterdata:16384'])->group(function () {
        Route::get('/pomanual', [PoManualController::class, 'pomanualView']);
        Route::post('/addpomanual', [PoManualController::class, 'addPoManual'])->name('upload.excel');
    });


    // BUDGET UPLOAD
    Route::get('/getSalespointBudget', [BudgetUploadController::class, 'getSalespointBudget']);
    Route::get('/budget/itemtracking', [BudgetUploadController::class, 'itemTracking']);
    Route::get('/getBudgetAuthorizationbySalespoint/{salespoint_id}', [BudgetUploadController::class, 'getBudgetAuthorizationbySalespoint']);
    // inventory budget upload
    Route::middleware(['menu_access:budget:1'])->group(function () {
        Route::get('/inventorybudget', [BudgetUploadController::class, 'inventoryBudgetView']);
        Route::middleware(['menu_access:budget:16'])->group(function () {
            Route::get('/inventorybudget/monitoring', [BudgetUploadController::class, 'monitoringBudget']);
            Route::get('/inventorybudget/monitoring/export', [BudgetUploadController::class, 'monitoringBudgetExport']);
        });
        Route::get('/inventorybudget/create', [BudgetUploadController::class, 'addInventoryBudgetView']);
        Route::get('/inventorybudget/create/template', [BudgetUploadController::class, 'getBudgetTemplate']);
        Route::post('/inventorybudget/create/readtemplate', [BudgetUploadController::class, 'readTemplate']);

        Route::get('/inventorybudget/{budget_upload_code}', [BudgetUploadController::class, 'inventoryBudgetDetailView']);
        Route::post('/inventorybudget/approvebudgetauthorization', [BudgetUploadController::class, 'approveBudgetAuthorization']);
        Route::post('/inventorybudget/rejectbudgetauthorization', [BudgetUploadController::class, 'rejectBudgetAuthorization']);
        Route::post('/inventorybudget/reviseBudget', [BudgetUploadController::class, 'reviseBudget']);
        Route::post('/inventorybudget/terminateBudget', [BudgetUploadController::class, 'terminateBudget']);
        Route::post('/createBudgetRequest/inventory', [BudgetUploadController::class, 'createBudgetRequest']);
        Route::get('/inventorybudget/nonActiveBudget/{budget_upload_code}', [BudgetUploadController::class, 'nonActiveBudget']);
    });

    Route::middleware(['menu_access:budget:2'])->group(function () {
        // armada budget upload
        Route::get('/armadabudget', [ArmadaBudgetUploadController::class, 'armadaBudgetView']);
        Route::middleware(['menu_access:budget:16'])->group(function () {
            Route::get('/armadabudget/monitoring', [ArmadaBudgetUploadController::class, 'monitoringBudget']);
            Route::get('/armadabudget/monitoring/export', [ArmadaBudgetUploadController::class, 'monitoringBudgetExport']);
        });
        Route::get('/armadabudget/create', [ArmadaBudgetUploadController::class, 'addArmadaBudgetView']);
        Route::get('/armadabudget/create/template', [ArmadaBudgetUploadController::class, 'getBudgetTemplate']);
        Route::post('/armadabudget/create/readtemplate', [ArmadaBudgetUploadController::class, 'readTemplate']);

        Route::get('/armadabudget/{budget_upload_code}', [ArmadaBudgetUploadController::class, 'armadaBudgetDetailView']);
        Route::post('/armadabudget/approvebudgetauthorization', [ArmadaBudgetUploadController::class, 'approveBudgetAuthorization']);
        Route::post('/armadabudget/rejectbudgetauthorization', [ArmadaBudgetUploadController::class, 'rejectBudgetAuthorization']);
        Route::post('/armadabudget/reviseBudget', [ArmadaBudgetUploadController::class, 'reviseBudget']);
        Route::post('/armadabudget/terminateBudget', [ArmadaBudgetUploadController::class, 'terminateBudget']);
        Route::post('/createBudgetRequest/armada', [ArmadaBudgetUploadController::class, 'createBudgetRequest']);
        Route::get('/armadabudget/nonActiveBudget/{budget_upload_code}', [ArmadaBudgetUploadController::class, 'nonActiveBudget']);
    });

    Route::middleware(['menu_access:budget:4'])->group(function () {
        // assumption budget upload
        Route::get('/assumptionbudget', [AssumptionBudgetUploadController::class, 'assumptionBudgetView']);
        Route::middleware(['menu_access:budget:16'])->group(function () {
            Route::get('/assumptionbudget/monitoring', [AssumptionBudgetUploadController::class, 'monitoringBudget']);
            Route::get('/assumptionbudget/monitoring/export', [AssumptionBudgetUploadController::class, 'monitoringBudgetExport']);
        });
        Route::get('/assumptionbudget/create', [AssumptionBudgetUploadController::class, 'addAssumptionBudgetView']);
        Route::get('/assumptionbudget/create/template', [AssumptionBudgetUploadController::class, 'getBudgetTemplate']);
        Route::post('/assumptionbudget/create/readtemplate', [AssumptionBudgetUploadController::class, 'readTemplate']);
        Route::get('/assumptionbudget/{budget_upload_code}', [AssumptionBudgetUploadController::class, 'assumptionBudgetDetailView']);
        Route::post('/assumptionbudget/approvebudgetauthorization', [AssumptionBudgetUploadController::class, 'approveBudgetAuthorization']);
        Route::post('/assumptionbudget/rejectbudgetauthorization', [AssumptionBudgetUploadController::class, 'rejectBudgetAuthorization']);
        Route::post('/assumptionbudget/reviseBudget', [AssumptionBudgetUploadController::class, 'reviseBudget']);
        Route::post('/assumptionbudget/terminateBudget', [AssumptionBudgetUploadController::class, 'terminateBudget']);
        Route::post('/createBudgetRequest/assumption', [AssumptionBudgetUploadController::class, 'createBudgetRequest']);
        Route::get('/assumptionbudget/nonActiveBudget/{budget_upload_code}', [AssumptionBudgetUploadController::class, 'nonActiveBudget']);
    });

    Route::middleware(['menu_access:budget:8'])->group(function () {
        // ho budget upload
        Route::get('/ho_budget', [HOBudgetUploadController::class, 'hoBudgetView']);
        Route::middleware(['menu_access:budget:16'])->group(function () {
            Route::get('/ho_budget/monitoring', [HOBudgetUploadController::class, 'monitoringBudget']);
            Route::get('/ho_budget/monitoring/export', [HOBudgetUploadController::class, 'monitoringBudgetExport']);
        });
        Route::get('/ho_budget/create', [HOBudgetUploadController::class, 'addHOBudgetView']);
        Route::get('/ho_budget/create/template', [HOBudgetUploadController::class, 'getBudgetTemplate']);
        Route::post('/ho_budget/create/readtemplate', [HOBudgetUploadController::class, 'readTemplate']);
        Route::get('/ho_budget/{budget_upload_code}', [HOBudgetUploadController::class, 'hoBudgetDetailView']);
        Route::post('/ho_budget/approvebudgetauthorization', [HOBudgetUploadController::class, 'approveBudgetAuthorization']);
        Route::post('/ho_budget/rejectbudgetauthorization', [HOBudgetUploadController::class, 'rejectBudgetAuthorization']);
        Route::post('/ho_budget/reviseBudget', [HOBudgetUploadController::class, 'reviseBudget']);
        Route::post('/ho_budget/terminateBudget', [HOBudgetUploadController::class, 'terminateBudget']);
        Route::post('/createBudgetRequest/ho', [HOBudgetUploadController::class, 'createBudgetRequest']);
        Route::get('/ho_budget/nonActiveBudget/{budget_upload_code}', [HOBudgetUploadController::class, 'nonActiveBudget']);
    });

    // OPERATIONAL

    // Peremajaan Armada
    Route::middleware(['menu_access:operational:128'])->group(function () {
        Route::get('/vendorRequest', [AuctionController::class, 'AuctionRegisterVendor']);
        Route::get('/renewalarmada', [RenewalArmadaController::class, 'renewalArmadaView']);
        Route::get('/renewalarmada/data', [RenewalArmadaController::class, 'renewalArmadaData']);
        Route::post('/updaterenewalarmada', [RenewalArmadaController::class, 'updateRenewalArmada']);
        Route::post('/addrenewalarmada', [RenewalArmadaController::class, 'addRenewalArmada']);
        Route::middleware(['superadmin'])->group(function () {
            // superadmin only
            Route::post('/renewalarmada/confirm', [RenewalArmadaController::class, 'confirmRenewal']);
            Route::post('/renewalarmada/reject', [RenewalArmadaController::class, 'rejectRenewal']);
        });
    });

    // Pengadaan
    Route::middleware(['menu_access:operational:1'])->group(function () {
        // Custom Ticket
        Route::post('/customticketing/ticket/create', [TicketingController::class, 'createTicket']);
        Route::get('/getarmadabysalespoint/{salespoint_id}', [RenewalArmadaController::class, 'getArmadabySalespoint']);
        Route::get('/getarmadatype/{armada_type_id}', [RenewalArmadaController::class, 'getArmadaTypebyID']);
        Route::get('/getarmadabyplate/{plate}', [RenewalArmadaController::class, 'getArmadaByPlate']);
        Route::post('/renewalarmada/terminate', [RenewalArmadaController::class, 'terminateRenewal']);

        // Barang Jasa
        Route::get('/ticketing', [TicketingController::class, 'ticketingView']);
        Route::get('/ticketing/data', [TicketingController::class, 'ticketingData']);
        Route::middleware(['superadmin'])->group(function () {
            // superadmin only
            Route::get('/ticketing/BAVerification', [TicketingBlockController::class, 'BAVerification']);
            Route::post('/ticketing/BAVerification/confirm', [TicketingBlockController::class, 'BAVerificationConfirm']);
            Route::post('/ticketing/BAVerification/reject', [TicketingBlockController::class, 'BAVerificationReject']);
        });
        Route::get('/ticketing/{code}', [TicketingController::class, 'ticketingDetailView']);
        Route::get('/getsalespointauthorization/{salespoint_id}', [SalesPointController::class, 'getSalesAuthorization']);
        Route::get('/addnewticket', [TicketingController::class, 'addNewTicket']);
        Route::post('/addticket', [TicketingController::class, 'addTicket']);
        Route::patch('/startauthorization', [TicketingController::class, 'startAuthorization']);
        Route::delete('/deleteticket', [TicketingController::class, 'deleteTicket']);
        Route::patch('/approveticket', [TicketingController::class, 'approveTicket']);
        Route::patch('/rejectticket', [TicketingController::class, 'rejectTicket']);
        Route::patch('/uploadticketfilerevision', [TicketingController::class, 'uploadFileRevision']);
        Route::post('/uploadconfirmationfile', [TicketingController::class, 'uploadConfirmationFile']);
        Route::post('/uploadfilelegal/{id}', [TicketingController::class, 'uploadFileLegal']);
        Route::post('/uploadbastkcop/{id}', [TicketingController::class, 'uploadBastkCop']);
        Route::post('/uploadevidancetransferoverplatform/{id}', [TicketingController::class, 'uploadEvidanceRransferOverPlatform']);
        Route::post('/reuploadbastkfilecop/{id}', [TicketingController::class, 'reUploadBastkFileCop']);
        Route::post('/reuploadagreementfilecop/{id}', [TicketingController::class, 'reUploadAgreementFileCop']);
        Route::post('/reuploadtorfilecop/{id}', [TicketingController::class, 'reUploadTorFileCop']);
        Route::post('/reuploadsphfilecop/{id}', [TicketingController::class, 'reUploadSphFileCop']);
        Route::post('/reuploaduseragreementfilecop/{id}', [TicketingController::class, 'reUploadUserAgreementFileCop']);
        Route::post('/uploadarmadatypecop/{id}', [TicketingController::class, 'uploadArmadaTypeCop']);
        Route::post('/revisionconfirmationfile', [TicketingController::class, 'revisionConfirmationFile']);
        Route::post('/ticketing/issuePO', [TicketingController::class, 'issuePO']);
        Route::get('/printfriform/{fri_form_id}', [TicketingController::class, 'printFRIForm']);
        Route::post('/ticketing/terminate', [TicketingController::class, 'terminateTicket']);
        Route::post('/ticketing/changeoverplafon/{id}', [TicketingController::class, 'changeOverPlafonTicket']);
        Route::post('/ticketing/approve-over-plafon/{id}', [TicketingController::class, 'approveOverPlafonTicket']);
        Route::post('/ticketing/rejectoverplafon/{id}', [TicketingController::class, 'rejectOverPlafonTicket']);
        Route::post('/ticketing/revision-over-plafon/{id}', [TicketingController::class, 'revisionOverPlafonTicket']);
        Route::post('/useruploadfileaggrement/{id}', [TicketingController::class, 'userUploadFileAggrementTicket']);
        Route::post('/uploadmissingfile/{id}', [TicketingController::class, 'uploadMissingFile']);
        Route::post('/revisionmissingfile/{id}', [TicketingController::class, 'revisionMissingFile']);
        Route::post('/ticketing/approve-agreement-cop/{id}', [TicketingController::class, 'approveAgreementCOP']);
        Route::post('/ticketing/approve-tor-cop/{id}', [TicketingController::class, 'approveTorCOP']);
        Route::post('/ticketing/approve-sph-cop/{id}', [TicketingController::class, 'approveSphCOP']);
        Route::post('/ticketing/approve-user-agreement-cop/{id}', [TicketingController::class, 'approveUserAgreementCOP']);
        Route::post('/ticketing/reject-agreement-cop/{id}', [TicketingController::class, 'rejectAgreementCOP']);
        Route::post('/ticketing/reject-tor-cop/{id}', [TicketingController::class, 'rejectTorCOP']);
        Route::post('/ticketing/reject-sph-cop/{id}', [TicketingController::class, 'rejectSphCOP']);
        Route::post('/ticketing/reject-user-agreement-cop/{id}', [TicketingController::class, 'rejectUserAgreementCOP']);
        Route::post('/ticketing/uploadrevisionlpbcop/{id}', [TicketingController::class, 'uploadRevisionLpbCop']);
        Route::post('/ticketing/uploadlpbcop/{id}', [TicketingController::class, 'uploadLpbCop']);
        Route::post('/ticketing/showlpbcop/{id}', [TicketingController::class, 'showLpbCop']);
        

        // Armada
        Route::post('/createarmadaticket', [ArmadaTicketingController::class, 'createArmadaticket']);
        Route::post('/armadaticketing/{code}/uploadBAPerpanjangan', [ArmadaTicketingController::class, 'uploadBAPerpanjangan']);
        Route::post('/armadaticketing/{code}/setMutasiLocation', [ArmadaTicketingController::class, 'setMutasiLocation']);
        Route::get('/armadaticketing/{code}', [ArmadaTicketingController::class, 'armadaTicketDetail']);
        Route::post('/startarmadaauthorization', [ArmadaTicketingController::class, 'startArmadaAuthorization']);
        Route::post('/approvearmadaauthorization', [ArmadaTicketingController::class, 'approveArmadaAuthorization']);
        Route::post('/rejectarmadaauthorization', [ArmadaTicketingController::class, 'rejectArmadaAuthorization']);
        Route::post('/addfacilityform', [ArmadaTicketingController::class, 'addFacilityForm']);
        Route::get('/printfacilityform/{armadaticket_code}', [ArmadaTicketingController::class, 'printFacilityForm']);
        Route::post('/addperpanjanganform', [ArmadaTicketingController::class, 'addPerpanjanganForm']);
        Route::get('/printperpanjanganform/{armadaticket_code}', [ArmadaTicketingController::class, 'printPerpanjanganForm']);
        Route::post('/addmutasiform', [ArmadaTicketingController::class, 'addMutasiForm']);
        Route::get('/printmutasiform/{armadaticket_code}', [ArmadaTicketingController::class, 'printMutasiForm']);
        Route::post('/completearmadabookedby', [ArmadaTicketingController::class, 'completeArmadaBookedBy']);
        Route::post('/approveperpanjanganform', [ArmadaTicketingController::class, 'approvePerpanjanganForm']);
        Route::post('/rejectperpanjanganform', [ArmadaTicketingController::class, 'rejectPerpanjanganForm']);
        Route::post('/approvefacilityform', [ArmadaTicketingController::class, 'approveFacilityForm']);
        Route::post('/rejectfacilityform', [ArmadaTicketingController::class, 'rejectFacilityForm']);
        Route::post('/approvemutasiform', [ArmadaTicketingController::class, 'approveMutasiForm']);
        Route::post('/rejectmutasiform', [ArmadaTicketingController::class, 'rejectMutasiForm']);
        Route::post('/uploadbastk', [ArmadaTicketingController::class, 'uploadBASTK']);
        Route::post('/uploadbastkgt', [ArmadaTicketingController::class, 'uploadBASTKGT']);
        Route::post('/revisebastk', [ArmadaTicketingController::class, 'reviseBASTK']);
        Route::post('/uploadoldbastk/{id}', [ArmadaTicketingController::class, 'uploadOldBASTK']);
        Route::post('/reviseoldbastk', [ArmadaTicketingController::class, 'reviseOldBASTK']);
        Route::post('/verifyPO', [ArmadaTicketingController::class, 'verifyPO']);
        Route::get('/getarmadatypebyniaga/{isNiaga}', [ArmadaController::class, 'getArmadaTypebyNiaga']);
        Route::get('/getArmadaAuthorizationbySalespoint/{salespoint_id}', [ArmadaController::class, 'getArmadaAuthorizationbySalespoint']);
        Route::get('/getSecurityAuthorizationbySalespoint/{salespoint_id}', [ArmadaController::class, 'getSecurityAuthorizationbySalespoint']);
        Route::get('/getarmada', [ArmadaController::class, 'getArmada']);
        Route::post('/terminateArmadaTicket', [ArmadaTicketingController::class, 'terminateArmadaTicket']);

        // Security
        Route::post('/createsecurityticket', [SecurityTicketingController::class, 'createSecurityTicket']);
        Route::post('/securityticketing/{code}/uploadBAPerpanjangan', [SecurityTicketingController::class, 'uploadBAPerpanjangan']);
        Route::get('/securityticketing/{code}', [SecurityTicketingController::class, 'securityTicketDetail']);
        Route::post('/terminatesecurityticketing', [SecurityTicketingController::class, 'terminateSecurityTicketing']);
        Route::post('/startsecurityauthorization', [SecurityTicketingController::class, 'startSecurityAuthorization']);
        Route::post('/addevaluasiform', [SecurityTicketingController::class, 'addEvaluasiForm']);
        Route::get('/printevaluasiform/{evaluasi_form_id_crypt}', [SecurityTicketingController::class, 'printEvaluasiForm']);
        Route::post('/approveevaluasiform', [SecurityTicketingController::class, 'approveEvaluasiForm']);
        Route::post('/noteevaluasiform/{id}', [SecurityTicketingController::class, 'noteEvaluasiForm']);
        Route::post('/rejectevaluasiform', [SecurityTicketingController::class, 'rejectEvaluasiForm']);
        Route::post('/approvesecurityauthorization', [SecurityTicketingController::class, 'approveSecurityAuthorization']);
        Route::post('/rejectsecurityauthorization', [SecurityTicketingController::class, 'rejectSecurityAuthorization']);
        Route::post('/uploadsecurityba', [SecurityTicketingController::class, 'uploadSecurityBA']);
        Route::post('/uploadsecuritylpb', [SecurityTicketingController::class, 'uploadSecurityLPB']);
        Route::post('/uploadsecurityendkontrak', [SecurityTicketingController::class, 'uploadSecurityEndKontrak']);

        // Additional
        Route::post('/additionalticketing/create', [AdditionalTicketingController::class, 'createAdditionalTicket']);
        Route::get('/additionalticketing/{code}', [AdditionalTicketingController::class, 'additionalTicketDetail']);
        Route::get('/getAuthorization', [AdditionalTicketingController::class, 'getAuthorization']);
        Route::post('/cancelEndKontrakPEST/{id}', [AdditionalTicketingController::class, 'cancelEndKontrakPEST']);

        // AJAX
        Route::get('/getActivePO', [POController::class, 'getActivePO']);

        // Approval Management
        Route::get('/vendor-approve-register', [VendorApprovalController::class, 'vendorApprovalView']);
        Route::get('/vendor-approve-register-detail', [VendorApprovalController::class, 'vendorApprovalDetail']);
        Route::post('/vendor-approve-register-approved', [VendorApprovalController::class, 'vendorApprovalApprove']);
        Route::post('/vendor-approve-register-reject', [VendorApprovalController::class, 'vendorApprovalReject']);
        Route::get('/approve-auction-be', [AuctionbeapprovalController::class, 'auctionView']);
        Route::get('/approve-auction-be/{type}/{auction_id}', [AuctionbeapprovalController::class, 'auctionDetailView']);
        Route::post('/approve-auction-be/approve', [AuctionbeapprovalController::class, 'approveAuction']);
        Route::post('/approve-auction-be/reject', [AuctionbeapprovalController::class, 'rejectAuction']);
    });

    // Bidding
    Route::middleware(['menu_access:operational:2'])->group(function () {
        Route::get('/bidding', [BiddingController::class, 'biddingView']);
        Route::post('/bidding/addvendor', [BiddingController::class, 'addVendor']);
        Route::post('/bidding/revisecustombidding', [BiddingController::class, 'reviseCustomBidding']);
        Route::post('/bidding/removevendor', [BiddingController::class, 'removeVendor']);
        Route::get('/bidding/{ticket_code}', [BiddingController::class, 'biddingDetailView']);
        Route::get('/bidding/{ticket_code}/{ticket_item_id}', [BiddingController::class, 'vendorSelectionView']);
        Route::post('/bidding/{ticket_code}/{ticket_item_id}/extendexpireddate', [BiddingController::class, 'extendExpiredDate']);
        Route::post('/bidding/{ticket_code}/{ticket_item_id}/revise', [BiddingController::class, 'revise']);
        Route::patch('/confirmticketfilerequirement', [BiddingController::class, 'confirmFileRequirement']);
        Route::patch('/rejectticketfilerequirement', [BiddingController::class, 'rejectFileRequirement']);
        Route::post('/bidding/reviseconfirmedfilerequirement', [BiddingController::class, 'reviseConfirmedFileRequirement']);
        Route::delete('/removeticketitem', [BiddingController::class, 'removeTicketItem']);
        Route::post('/addbiddingform', [BiddingController::class, 'addBiddingForm']);
        Route::patch('/approvebidding', [BiddingController::class, 'approveBidding']);
        Route::patch('/rejectbidding', [BiddingController::class, 'rejectBidding']);
        Route::patch('/uploadsignedfile', [BiddingController::class, 'uploadSignedFile']);
        Route::patch('/uploadCopFile', [BiddingController::class, 'uploadCopFile']);
        Route::post('/bidding/uploadbiddingfile', [BiddingController::class, 'uploadBiddingFile']);
        Route::patch('/terminateticket', [BiddingController::class, 'terminateTicket']);
        Route::post('/bidding/manualsplit', [BiddingController::class, 'manualSplitFRI']);
        Route::post('/confirm-missingfile/{ticket_item_id}', [BiddingController::class, 'confirmMissingFile']);
        Route::post('/reject-missingfile/{ticket_item_id}', [BiddingController::class, 'rejectMissingFile']);
    });

    // Purchase Requisition
    Route::middleware(['menu_access:operational:4'])->group(function () {
        Route::get('/pr', [PRController::class, 'prView']);
        Route::get('/pr/{ticket_code}', [PRController::class, 'prDetailView']);
        Route::get('/auctionbe', [AuctionbeController::class, 'auctionView']);
        Route::get('/auctionbe/{type}/{ticket_code}', [AuctionbeController::class, 'auctionDetailView']);
        Route::post('/auctionbe/publish', [AuctionbeController::class, 'publishAuction']);
        Route::post('/auctionbe/unpublish', [AuctionbeController::class, 'unpublishAuction']);

        Route::middleware(['superadmin'])->group(function () {
            // superadmin only
            Route::get('/pr/{ticket_code}/updateassetnumber', [PRController::class, 'updateAssetNumberView']);
            Route::post('/pr/{ticket_code}/updateassetnumber/update', [PRController::class, 'updateAssetNumber']);
            Route::get('/pr/{ticket_code}/updateprdata', [PRController::class, 'updatePRDataView']);
            Route::post('/pr/{ticket_code}/updateprdata/update', [PRController::class, 'updatePRData']);
        });

        Route::post('/addnewpr', [PRController::class, 'addNewPR']);
        Route::patch('/approvepr', [PRController::class, 'approvePR']);
        Route::patch('/rejectpr', [PRController::class, 'rejectPR']);
        Route::post('/submitassetnumber', [PRController::class, 'submitAssetNumber']);
        Route::post('/revisePR', [PRController::class, 'revisePR']);
        Route::post('/resendrequestassetnumber', [PRController::class, 'resendRequestAssetNumber']);
        // Route::get('/printPR/{ticket_code}',[PRController::class, 'printPR']);
    });

    // Purchase Order
    Route::middleware(['menu_access:operational:8|16'])->group(function () {
        Route::get('/po', [POController::class, 'poView']);
        Route::get('/po/data', [POController::class, 'poData']);
        Route::get('/po/{ticket_code}', [POController::class, 'poDetailView']);

        Route::middleware(['menu_access:operational:8'])->group(function () {
            Route::post('/po/setuppo', [POController::class, 'newSetupPO']);
            Route::post('/po/quickrefresh', [POController::class, 'quickRefresh']);
            Route::post('/setupPO', [POController::class, 'setupPO']);
        });
        Route::middleware(['menu_access:operational:16'])->group(function () {
            Route::post('/revisepodata', [POController::class, 'revisePOData']);
            Route::post('/revisePO', [POController::class, 'revisePO']);
            Route::post('/submitPO', [POController::class, 'submitPO']);
            Route::get('/printPO', [POController::class, 'printPO']);
            Route::patch('/uploadinternalsignedfile', [POController::class, 'uploadInternalSignedFile']);
            Route::post('/cancelvendorconfirmation/{id}', [POController::class, 'cancelVendorConfirmation']);
            Route::post('/confirmvendorconfirmation/{id}', [POController::class, 'confirmVendorConfirmation']);
            Route::patch('/rejectposigned', [POController::class, 'rejectPosigned']);
            Route::patch('/confirmposigned', [POController::class, 'confirmPosigned']);
            Route::post('/sendemail', [POController::class, 'sendEmail']);
            Route::get('/po/{ticket_code}/compare', [POController::class, 'poCompareView']);
            Route::post('/po/{ticket_code}/reminderupdate', [POController::class, 'poReminderUpdate']);
        });
        Route::get('/getPrSapbyTicketCode', [POController::class, 'getPrSapbyTicketCode']);
        Route::get('/getPoSap', [POController::class, 'getPoSap']);
    });

    // Form Validation
    Route::middleware(['menu_access:operational:32'])->group(function () {
        Route::get('/form-validation', [FormValidationController::class, 'formValidationView']);
        Route::get('/form-validation/validate', [FormValidationController::class, 'formValidationDetailView']);
        Route::post('/form-validation/approve', [FormValidationController::class, 'formValidationApprove']);
        Route::post('/form-validation/reject', [FormValidationController::class, 'formValidationReject']);
    });

    // Vendor Evaluation
    Route::middleware(['menu_access:operational:1'])->group(function () {
        Route::get('/vendor-evaluation', [VendorEvaluationController::class, 'vendorEvaluationView']);
        Route::get('/vendor-evaluation/create', [VendorEvaluationController::class, 'createVendorEvaluation']);
        Route::get('/vendor-evaluation/{code}', [VendorEvaluationController::class, 'vendorEvaluationDetail']);
        Route::post('/vendor-evaluation/addvendorevaluation', [VendorEvaluationController::class, 'addVendorEvaluation']);
        Route::post('/vendor-evaluation/addvendorevaluationdetail/{id}', [VendorEvaluationController::class, 'addVendorEvaluationDetail']);
        Route::post('/vendor-evaluation/approve/{id}', [VendorEvaluationController::class, 'approveVendorEvaluation']);
        Route::post('/vendor-evaluation/reject/{id}', [VendorEvaluationController::class, 'rejectVendorEvaluation']);
        Route::post('/vendor-evaluation/approverevision/{id}', [VendorEvaluationController::class, 'ApproveRevisionVendorEvaluation']);
        Route::get('/printVendorEvaluation/{id}', [VendorEvaluationController::class, 'printVendorEvaluation']);
        Route::post('/vendor-evaluation/terminated/{id}', [VendorEvaluationController::class, 'terminatedVendorEvaluation']);
    });

    // MONITORING
    Route::middleware(['menu_access:monitoring:1'])->group(function () {
        Route::get('/ticketmonitoring', [MonitoringController::class, 'ticketMonitoringView']);
        Route::get('/ticketmonitoringlogs/{ticket_id}', [MonitoringController::class, 'ticketMonitoringLogs']);
        Route::post('/ticketmonitoring/uploadfileattachmentticketmonitoring', [MonitoringController::class, 'uploadFileAttachmentTicket']);
    });

    Route::middleware(['menu_access:monitoring:2'])->group(function () {
        Route::get('/armadamonitoring', [MonitoringController::class, 'armadaMonitoringView']);
        Route::get('/armadamonitoring/getGSArmada', [MonitoringController::class, 'armadaGSExport']);
        Route::get('/armadamonitoring/getMonthlyArmadaReport', [MonitoringController::class, 'armadaMonthlyReport']);
        Route::get('/armadamonitoringpologs/{po_number}', [MonitoringController::class, 'armadaMonitoringPOLogs']);
        Route::get('/armadamonitoringticketlogs/{armada_ticket_id}', [MonitoringController::class, 'armadaMonitoringTicketLogs']);
        Route::post('/armadamonitoring/updategtplate', [MonitoringController::class, 'updateGTPlate']);
        Route::post('/armadamonitoring/uploadfileattachmentarmadamonitoring', [MonitoringController::class, 'uploadFileAttachmentArmada']);
    });

    Route::middleware(['menu_access:monitoring:4'])->group(function () {
        Route::get('/securitymonitoring', [MonitoringController::class, 'securityMonitoringView']);
        Route::get('/securitymonitoring/securityPOByArea', [MonitoringController::class, 'securityPOByArea']);
        Route::get('/securitymonitoringpologs/{po_number}', [MonitoringController::class, 'securityMonitoringPOLogs']);
        Route::get('/securitymonitoringticketlogs/{security_ticket_id}', [MonitoringController::class, 'securityMonitoringTicketLogs']);
        Route::post('/securitymonitoring/uploadfileattachmentsecuritymonitoring', [MonitoringController::class, 'uploadFileAttachmentSecurity']);
    });

    Route::middleware(['menu_access:monitoring:8'])->group(function () {
        Route::get('/citmonitoring', [MonitoringController::class, 'citMonitoringView']);
        Route::post('/citmonitoring/updatepo', [MonitoringController::class, 'updatePO']);
        Route::post('/citmonitoring/uploadfileattachmentcitmonitoring', [MonitoringController::class, 'uploadFileAttachmentCit']);
    });

    Route::middleware(['menu_access:monitoring:16'])->group(function () {
        Route::get('/pestmonitoring', [MonitoringController::class, 'pestMonitoringView']);
        Route::post('/pestmonitoring/updatepo', [MonitoringController::class, 'updatePO']);
        Route::post('/pestmonitoring/uploadfileattachmentpestmonitoring', [MonitoringController::class, 'uploadFileAttachmentPest']);
    });

    Route::middleware(['menu_access:monitoring:32'])->group(function () {
        Route::get('/merchandisermonitoring', [MonitoringController::class, 'merchandiserMonitoringView']);
        Route::post('/merchandisermonitoring/updatepo', [MonitoringController::class, 'updatePO']);
        Route::post('/merchandisermonitoring/uploadfileattachmentmerchendiser', [MonitoringController::class, 'uploadFileAttachmentMerchendiser']);
    });

    // REPORTING
    Route::middleware(['menu_access:reporting:1'])->group(function () {
        Route::get('/armadaaccident', [ArmadaAccidentController::class, 'armadaAccidentView']);
        Route::get('/armadaaccident/{armada_accident_id}', [ArmadaAccidentController::class, 'armadaAccidentDetailView']);
        Route::post('/armadaaccident/create', [ArmadaAccidentController::class, 'armadaAccidentCreate']);
        Route::post('/armadaaccident/update', [ArmadaAccidentController::class, 'armadaAccidentUpdate']);
    });
    Route::middleware(['menu_access:reporting:2'])->group(function () {
        Route::post('/armadaaccident/opencase', [ArmadaAccidentController::class, 'openCase']);
        Route::post('/armadaaccident/closecase', [ArmadaAccidentController::class, 'closeCase']);
    });
    Route::middleware(['menu_access:reporting:4'])->group(function () {
        Route::get('/uploadreport', [UploadReportController::class, 'view']);
        Route::post('/uploadreport/create', [UploadReportController::class, 'create']);
        Route::post('/uploadreport/createfile', [UploadReportController::class, 'createFile']);
    });
    Route::middleware(['menu_access:reporting:8'])->group(function () {
        Route::get('/downloadreport', [DownloadReportController::class, 'view']);
        Route::get('/downloadreport/hobudget', [DownloadReportController::class, 'hobudget']);
        Route::get('/downloadreport/hobudgetnonactive', [DownloadReportController::class, 'hoBudgetNonActive']);
        Route::get('/downloadreport/areabudget', [DownloadReportController::class, 'areaBudget']);
        Route::get('/downloadreport/areabudgetnonactive', [DownloadReportController::class, 'areaBudgetNonActive']);
        Route::get('/downloadreport/getActiveBudget', [DownloadReportController::class, 'getActiveBudget']);
        Route::get('/downloadreport/getNonActiveBudget', [DownloadReportController::class, 'getNonActiveBudget']);
        Route::get('/downloadreport/nonbudget', [DownloadReportController::class, 'nonBudget']);
        Route::get('/downloadreport/poreport', [DownloadReportController::class, 'poReport']);
        Route::get('/downloadreport/exportreportticketing', [DownloadReportController::class, 'reportTicketing']);
        Route::get('/downloadreport/exportmonitoring', [DownloadReportController::class, 'reportMonitoring']);
    });
});
