<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\LeaveController;
use App\Http\Resources\LeaveResource;



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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/auth/list-users/', [UserController::class, 'listUsers']);
Route::post('/auth/refresh', [UserController::class, 'refresh']);
Route::post('/auth/register', [UserController::class, 'createUser']);
Route::post('/auth/logout', [UserController::class, 'logout']);
Route::post('/auth/forgot', [UserController::class, 'forgot']);
Route::post('/auth/reset', [UserController::class, 'reset']);
//Route::match(['get', 'post'], '/auth/login', [UserController::class, 'loginUser'])->name('login');
Route::post('/auth/login', [UserController::class, 'loginUser']);
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    #return redirect('/');
    return redirect()->route('home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post(
    '/forgot-password',
    [UserController::class, 'forgotPassword']
);
Route::post(
    '/reset-password',
    [UserController::class, 'resetPassword']
);
Route::get('/events', [EventController::class, 'index']);
Route::get('/event/list-types', [EventController::class, 'listEventType']);
Route::post('/event/create-type', [EventController::class, 'storeEventType']);
Route::patch('/event/type/{id}', [EventController::class, 'updateEventType']);
Route::get('/event/{slug}', [EventController::class, 'show']);
Route::post('/group', [EventController::class, 'index']);
Route::get('/attendance/{attendance}', [AttendanceController::class, 'show']);
Route::get('/attendance/', [AttendanceController::class, 'index']);
Route::get('/department/{department}', [DepartmentController::class, 'show']);
Route::get('/department/', [DepartmentController::class, 'index']);

Route::group(['middleware' => ['auth.jwt']], function () {
    Route::post('/event/new/', [EventController::class, 'store']);
    Route::post('/event/image/new/{id}', [EventController::class, 'storeImage']);
    Route::post('/event/{slug}', [BookingController::class, 'store']);
    Route::get('/u/{name}/', [BookingController::class, 'index']);
    Route::get('/b/{identity}/', [BookingController::class, 'show']);
    Route::post('/punch/', [UserAttendanceController::class, 'store']);
    //Route::get('/user/attendance/', [UserAttendanceController::class, 'index']);

});

Route::group(['middleware' => ['auth.jwt']], function () {
    Route::get('/user/clock/status/', [UserAttendanceController::class, 'attendanceStatus']);
    Route::get('/auth/profile/', [UserController::class, 'profile']);
    Route::get('/user/attendance/', [UserAttendanceController::class, 'index']);
    Route::get('/manager/attendance/', [UserAttendanceController::class, 'userManagerAttendanceList']);
    Route::post('/auth/signout/', [UserController::class, 'signout']);
    Route::post('/user/clock/register/', [UserAttendanceController::class, 'userRegisterClock']);
    Route::post('/leave/apply/', [LeaveController::class, 'store']);
    //Route::post('/leave/apply/', [LeaveController::class, 'store'])->withoutMiddleware(['auth', 'csrf']);
    Route::get('/leave/all/', [LeaveController::class, 'index']);
    Route::get('/leave/user/', [LeaveController::class, 'indexSingle']);
    Route::get('/leave/user/', [LeaveController::class, 'indexSingle']);
    Route::get('/leaves/{leave}/', [LeaveController::class, 'show']);
    Route::put('/leaves/{leave}', [LeaveController::class, 'update']);
    Route::delete('/leaves/{leave}', [LeaveController::class, 'destroy']);
    Route::post('/leaves/{leave}/comments', [LeaveController::class, 'storeLeaveComment']);
});

Route::group(['middleware' => ['auth.jwt','admin' ]], function () {
    Route::post('/admin/create/user/', [UserController::class, 'adminCreateUser']);
    Route::get('/admin/user/attendance/', [UserAttendanceController::class, 'adminAttendanceHistory']);
    Route::put('/admin/leaves/{leave}', [LeaveController::class, 'updateAdmin']);
    Route::put('/admin/leaves/{leave}', [LeaveController::class, 'destroyAdmin']);
    Route::post('/admin/leaves/{leave}/comments', [LeaveController::class, 'storeLeaveComment']);


});

Route::middleware('auth.jwt')->group(function () {
    Route::get('/details', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });
});

Route::group(['as'=>'admin','prefix' => 'admin','namespace'=>'admin','middleware'=>['auth:sanctum','admin']], function () {
    Route::post('/log/users/', [UserAttendanceController::class, 'listAll'])->name('listAll');
});

Route::get('public', function () {
    return response()->json(['message' => 'This is a public endpoint.']);
});

Route::middleware('auth.jwt')->get('private', [UserAttendanceController::class, 'index']);
