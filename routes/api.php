<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Resources\LeaveResource;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Api\UserController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::post('/auth/register', [UserController::class, 'createUser']);
//Route::match(['get', 'post'], '/auth/login', [UserController::class, 'loginUser'])->name('login');
Route::post('/auth/login', [UserController::class, 'loginUser']);
Route::post('/auth/refresh', [UserController::class, 'refresh']);
Route::post('/auth/logout', [UserController::class, 'logout']);
Route::post('/auth/forgot', [UserController::class, 'forgot']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/auth/reset', [UserController::class, 'reset']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);

Route::prefix('email')->group(function () {

    Route::get('/verify', function () {
        return view('auth.verify-email');
    })->middleware('auth')->name('verification.notice');

    Route::get('/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        #return redirect('/');
        return redirect()->route('home');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
});

Route::group(['middleware' => ['auth.jwt']], function () {

    Route::post('/auth/signout/', [UserController::class, 'signout']);
    Route::get('/auth/profile/', [UserController::class, 'profile']);
    Route::get('/auth/basic/user/details/', [UserController::class, 'listUserBasic']);

    //Route::post('/leave/apply/', [LeaveController::class, 'store'])->withoutMiddleware(['auth', 'csrf']);
    Route::post('/leave/apply/',    [LeaveController::class, 'store']);
    Route::get('/leaves/{leave}/', [LeaveController::class, 'show']);
    Route::put('/leaves/{leave}', [LeaveController::class, 'update']);
    Route::get('/leave/user/', [LeaveController::class, 'indexSingle']);
    Route::get('/leave/manager/', [LeaveController::class, 'indexManage']);
    Route::patch('/leave/approve/{leave}', [LeaveController::class, 'managerApproveLeave']);
    Route::delete('/leaves/{leave}', [LeaveController::class, 'destroy']);
    Route::post('/leaves/{leave}/comments', [LeaveController::class, 'storeLeaveComment']);

    Route::get('/attendance/', [AttendanceController::class, 'index']);
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'show']);
    Route::get('private', [UserAttendanceController::class, 'index']);
    Route::post('/punch/', [UserAttendanceController::class, 'store']);
    Route::get('/user/clock/status/', [UserAttendanceController::class, 'attendanceStatus']);
    Route::post('/user/clock/register/', [UserAttendanceController::class, 'userRegisterClock']);
    //Route::get('/user/attendance/', [UserAttendanceController::class, 'index']);
    Route::get('/user/attendance/', [UserAttendanceController::class, 'index']);
    Route::get('/manager/attendance/', [UserAttendanceController::class, 'userManagerAttendanceList']);

    Route::post('/event/new/', [EventController::class, 'store']);
    Route::post('/event/image/new/{id}', [EventController::class, 'storeImage']);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/event/list-types', [EventController::class, 'listEventType']);
    Route::post('/event/create-type', [EventController::class, 'storeEventType']);
    Route::patch('/event/type/{id}', [EventController::class, 'updateEventType']);
    Route::get('/event/{slug}', [EventController::class, 'show']);
    Route::post('/group', [EventController::class, 'index']);

    Route::post('/event/{slug}', [BookingController::class, 'store']);
    Route::get('/u/{name}/', [BookingController::class, 'index']);
    Route::get('/b/{identity}/', [BookingController::class, 'show']);

    Route::get('/department/{department}', [DepartmentController::class, 'show']);
    Route::get('/department/', [DepartmentController::class, 'index']);
});

Route::group(['middleware' => ['auth.jwt', 'admin']], function () {
    Route::post('/admin/create/user/', [UserController::class, 'adminCreateUser']);
    Route::get('/admin/user/attendance/', [UserAttendanceController::class, 'adminAttendanceHistory']);
    Route::put('/admin/leaves/{leave}', [LeaveController::class, 'updateAdmin']);
    Route::put('/admin/leaves/{leave}', [LeaveController::class, 'destroyAdmin']);
    Route::post('/admin/leaves/{leave}/comments', [LeaveController::class, 'storeLeaveComment']);
    Route::get('/leave/all/', [LeaveController::class, 'index']);
    Route::get('/admin/leave/all/', [LeaveController::class, 'index']);
    Route::get('/admin/user/{user}', [UserController::class, 'show']);
    Route::get('/auth/list-users/', [UserController::class, 'listUsers']);
    // Update user completely (PUT)
    Route::put('/admin/users/{id}', [UserController::class, 'adminUpdateUser']);
    // Update user partially (PATCH)
    Route::patch('/admin/users/{id}', [UserController::class, 'adminPatchUser']);
    // Delete a user (DELETE)
    Route::delete('/admin/users/{id}', [UserController::class, 'adminDeleteUser']);
});

Route::group(['as' => 'admin', 'prefix' => 'admin', 'namespace' => 'admin', 'middleware' => ['auth:sanctum', 'admin']], function () {
    Route::post('/log/users/', [UserAttendanceController::class, 'listAll'])->name('listAll');
});


Route::get('public', function () {
    return response()->json(['message' => 'This is a public endpoint.']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth.jwt')->group(function () {
    Route::get('/details', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });
});
