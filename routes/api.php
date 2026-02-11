<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Customer\AddonController as CustomerAddonController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\PackageController as CustomerPackageController;
use App\Http\Controllers\Customer\BillingController as CustomerBillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DateController;
use App\Http\Controllers\Employee\WorkloadController as EmployeeWorkloadController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GenerateReportController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\User\Customer\CustomerAccountController;
use App\Http\Controllers\User\Employee\EmployeeAccountController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\WorkloadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {

    //Dashboard
    Route::prefix('/dashboard')->group(function () {
        Route::get('/packages', [DashboardController::class, 'fetchPackages']);
        Route::get('/feedbacks', [DashboardController::class, 'fetchFeedbacks']);
    });

    Route::prefix('/users')->group(function () {
        //Authenticated
        Route::get('/current', [AuthController::class, 'user']);
        Route::post('/update', [UserController::class, 'updateCurrent']);
        Route::post('/update-password', [UserController::class, 'updatePassword']);
    });

    //Admin/Staff Routes

    //Employees
    Route::prefix('/employees')->group(function () {
        Route::get('', [EmployeeAccountController::class, 'getEmployees']);
        Route::post('/add', [EmployeeAccountController::class, 'addUser']);
        Route::prefix('/{userId}')->group(function () {
            Route::get('/view', [EmployeeAccountController::class, 'viewEmployee']);
            Route::post('/update', [EmployeeAccountController::class, 'updateUser']);
        });
    });

    //Customers
    Route::prefix('/customers')->group(function () {
        Route::get('', [CustomerAccountController::class, 'getCustomers']);
        Route::post('/add', [CustomerAccountController::class, 'addUser']);
        Route::prefix('/{userId}')->group(function () {
            Route::get('/view', [CustomerAccountController::class, 'viewCustomer']);
            Route::post('/update', [CustomerAccountController::class, 'updateUser']);
        });
    });

    //Package
    Route::prefix('/packages')->group(function () {
        Route::get('', [PackageController::class, 'getPackages']);
        Route::post('/add', [PackageController::class, 'addPackage']);
        Route::post('/{id}', [PackageController::class, 'updatePackage']);
        Route::post('/{id}/inactive', [PackageController::class, 'setPackageInactive']);
    });

    //Addon
    Route::prefix('/addons')->group(function () {
        Route::get('', [AddonController::class, 'getAddons']);
        Route::post('/add', [AddonController::class, 'addAddon']);
        Route::post('/{id}', [AddonController::class, 'updateAddon']);
        Route::post('/{id}/inactive', [AddonController::class, 'setAddonInactive']);
    });

    //Unavailable Dates
    Route::prefix('/unavailable-dates')->group(function () {
        Route::get('', [DateController::class, 'getUnavailableDate']);
        Route::post('/mark', [DateController::class, 'markUnavailableDate']);
        Route::post('/{id}/unmark', [DateController::class, 'unmarkUnavailableDate']);
    });

    //Bookings
    Route::prefix('/bookings')->group(function () {
        Route::get('/', [BookingController::class, 'getBookings']);
        Route::get('/approved', [BookingController::class, 'getApprovedBookings']);
        Route::post('/add', [BookingController::class, 'addBooking']);
        Route::get('/packages', [BookingController::class, 'getAvailablePackages']);

        Route::prefix('/{id}')->group(function () {
            Route::get('/', [BookingController::class, 'viewBooking']);
            Route::get('/addons', [BookingController::class, 'getAvailableAddons']);
            Route::post('/update', [BookingController::class, 'updateBooking']);
            Route::post('/cancel', [BookingController::class, 'cancelBooking']);
            Route::post('/approve', [BookingController::class, 'setBookingToApprove']);
            Route::post('/reject', [BookingController::class, 'setBookingToReject']);
            Route::post('/reschedule', [BookingController::class, 'rescheduleBooking']);
        });
    });

    //Billings
    Route::prefix('/billings')->group(function () {
        Route::get('/', [BillingController::class, 'getBillings']);
        Route::get('/{id}', [BillingController::class, 'viewBilling'])->name('billing.view');
        Route::post('/{id}/add-payment', [BillingController::class, 'addPayment']);
    });

    //Workload
    Route::prefix('/workload')->group(function () {
        Route::get('/', [WorkloadController::class, 'getWorkloads']);
        Route::get('/{id}', [WorkloadController::class, 'viewWorkload']);
        Route::get('/{id}/employees', [WorkloadController::class, 'getAvailableEmployee']);
        Route::post('/{id}/assign', [WorkloadController::class, 'assignWorkload']);
    });

    //Feedback
    Route::prefix('/feedbacks')->group(function () {
        Route::get('/', [FeedbackController::class, 'getFeedbacks']);
        Route::get('/{id}', [FeedbackController::class, 'viewFeedback'])->name('feedback.detail');
        Route::post('/{id}/mark-as-posted', [FeedbackController::class, 'markFeedbackAsPosted']);
        Route::post('/{id}/mark-as-unposted', [FeedbackController::class, 'markFeedBackAsUnposted']);
    });

    //Reports
    Route::prefix('/report')->group(function () {
        Route::get('/bookings', [ReportController::class, 'getNoOfBookings']);
        Route::get('/packages', [ReportController::class, 'getNoOfPackages']);
        Route::get('/addons', [ReportController::class, 'getNoOfAddOns']);
        Route::get('/transactions', [ReportController::class, 'getTransactions']);
        Route::get('/billings', [ReportController::class, 'getBillingInformation']);
    });

    Route::get('/generate-report', [GenerateReportController::class, 'generatePdf']);

    //Customer Routes
    Route::prefix('/customer')->group(function () {

        //Packages
        Route::prefix('/packages',)->group(function () {
            Route::get('/', [CustomerPackageController::class, 'getPackages']);
        });

        //Addons
        Route::prefix('/addons')->group(function () {
            Route::get('/', [CustomerAddonController::class, 'getAddons']);
        });

        //Bookings
        Route::prefix('/bookings')->group(function () {
            Route::get('/', [CustomerBookingController::class, 'getBookings']);
            Route::post('/create', [CustomerBookingController::class, 'createBooking']);
            Route::post('/{id}/update', [CustomerBookingController::class, 'updateBooking']);
            Route::post('/{id}/delete', [CustomerBookingController::class, 'deleteBooking']);

            Route::prefix('/{id}')->group(function () {
                Route::get('/', [CustomerBookingController::class, 'viewBooking']);

                Route::prefix('/feedback')->group(function () {
                    Route::get('/view', [CustomerBookingController::class, 'viewFeedback'])->name('feedback.view');
                    Route::post('/add', [CustomerBookingController::class, 'addFeedback'])->name('feedback.add');
                });
            });
        });

        //Billings
        Route::prefix('/billings')->group(function () {
            Route::get('/', [CustomerBillingController::class, 'getBillings']);
            Route::get('/{id}', [CustomerBillingController::class, 'viewBilling']);
        });
    });

    //Employee Routes (Photographer/Editor)
    Route::prefix('/employee')->group(function () {

        // Workload
        Route::prefix('/workloads')->group(function () {
            Route::get('/', [EmployeeWorkloadController::class, 'getWorkloads']);
            Route::get('/{id}/view', [EmployeeWorkloadController::class, 'viewWorkload'])->name('workload.detail');
            Route::post('/{id}/update', [EmployeeWorkloadController::class, 'updateWorkload'])->name('workload.update');
        });
    });
});