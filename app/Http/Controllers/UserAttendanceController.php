<?php

namespace App\Http\Controllers;
use DateTime;
use App\Models\UserAttendance;
use App\Models\User;
use App\Http\Requests\StoreUserAttendanceRequest;
use App\Http\Requests\UpdateUserAttendanceRequest;
use App\Http\Resources\UserAttendanceResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;


class UserAttendanceController extends Controller
{
    private function getDateTimeFromTimeZone()
    {

        //$usertimezone="Africa/Lagos"; 

        date_default_timezone_set(Auth::user()->gmt); 

        //new date and time
        $ndate= new datetime();
        //split into date and time seperate
        $nndatetime = $ndate->format("Y-m-d H:i:s");
        //$nntime= $ndate->format("H:i:s");
        //here you can test it
        return $nndatetime;
    }

    private function getDateFromTimeZone()
    {

        //$usertimezone="Africa/Lagos"; 

        date_default_timezone_set(Auth::user()->gmt); 

        //new date and time
        $ndate= new datetime();
        //split into date and time seperate
        $nndatetime = $ndate->format("Y-m-d");
        //$nntime= $ndate->format("H:i:s");
        //here you can test it
        return $nndatetime;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserAttendanceResource::collection(
            UserAttendance::where('user_id',Auth::id())
            ->orderBy('clock', 'desc')
            ->paginate(50)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserAttendanceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserAttendanceRequest $request)
    {
        $data = $request->validated();

        //$clockStore = [];
        
        if(!UserAttendance::where('user_id', Auth::id())->exists())
        {
            $clock = UserAttendance::create([
                'user_id' => Auth::id(),
                'attendance_id' => '1',
                'clock' => $this->getDateTimeFromTimeZone(),
                'status' => 'approved',
                'ipAddress' => \Request::ip()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User clocked successfully',
            ], 200);
        }
        $getLastEnter = UserAttendance::latest('clock')->first()->get();

        foreach($getLastEnter as $getLast)
        {
            $clockedTime = $getLast->clock;
            $attendance = $getLast->attendance_id;
        }
        //$s = '8/29/2011 11:16:12 AM';
        $dt = new DateTime($clockedTime);

        $AdDate = $dt->format('Y-m-d');

        $current = strtotime($this->getDateFromTimeZone());
        $date    = strtotime($AdDate);

        $datediff = $date - $current;
        $difference = floor($datediff/(60*60*24));
        //$time = $dt->format('H:i:s');
        if($difference < 0)
        {
            $clock = UserAttendance::create([
                'user_id' => Auth::id(),
                'attendance_id' => '1',
                'clock' => $this->getDateTimeFromTimeZone(),
                'status' => 'approved',
                'ipAddress' => \Request::ip()
            ]);
        }elseif($difference == 0 && $attendance == 1)
        {
            $clock = UserAttendance::create([
                'user_id' => Auth::id(),
                'attendance_id' => '2',
                'clock' => $this->getDateTimeFromTimeZone(),
                'status' => 'approved',
                'ipAddress' => \Request::ip()
            ]);
        }else{
            $clock = UserAttendance::create([
                'user_id' => Auth::id(),
                'attendance_id' => '1 ',
                'clock' => $this->getDateTimeFromTimeZone(),
                'status' => 'approved',
                'ipAddress' => \Request::ip()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'User clocked successfully',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function attendanceStatus()
    {
        // Get the current date in the specified timezone
        $today = Carbon::now(Auth::user()->gmt)->startOfDay();
        $tomorrow = $today->copy()->addDay();

        //print_r(UserAttendance::where('user_id', Auth::id())->where('attendance_id', '2')->whereBetween('created_at', [$today, $tomorrow])->latest()->first());

        if(!UserAttendance::where('user_id', Auth::id())->exists())
        {
            return response()->json([
                'clock' => 'clock in',
                'gmt' => Auth::user()->gmt
                //'user' => $user
            ], 200);
        }elseif(!UserAttendance::whereBetween('created_at', [$today, $tomorrow])->where('user_id', Auth::id())->exists() )
        {
            return response()->json([
                'clock' => 'clock in',
                'gmt' => Auth::user()->gmt
                //'user' => $user
            ], 200);
        }
        else{
            $getQuery = UserAttendance::where('user_id', Auth::id())->whereBetween('created_at', [$today, $tomorrow])->latest()->first()->get();
            foreach($getQuery as $lat)
            {
                $attendance_id = $lat->attendance_id;
            }
            if($attendance_id == '1')
            {
                return response()->json([
                    'clock' => 'clock out',
                    'gmt' => Auth::user()->gmt
                    //'user' => $user
                ], 200);
            }
            else{
                return response()->json([
                    'clock' => 'clock in',
                    'gmt' => Auth::user()->gmt
                    //'user' => $user
                ], 200);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserAttendance  $userAttendance
     * @return \Illuminate\Http\Response
     */
    public function show(UserAttendance $userAttendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserAttendanceRequest  $request
     * @param  \App\Models\UserAttendance  $userAttendance
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserAttendanceRequest $request, UserAttendance $userAttendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserAttendance  $userAttendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserAttendance $userAttendance)
    {
        //
    }

    /**
     * List attendance by employee.
     *
     * @param  \App\Models\UserAttendance  $userAttendance
     * @return \Illuminate\Http\Response
     */

     public function adminAttendanceHistory()
     {
        /*return UserAttendanceResource::collection(
            UserAttendance::orderBy('clock', 'desc')
            ->paginate(50)
        );*/

        // Retrieve and sort the data
        $userAttendances = UserAttendance::orderByRaw('DATE(clock) desc')->get();

        // Sort the collection by firstName safely
        $sortedUserAttendances = $userAttendances->sortBy(function ($item) {
            // Check if user_id is an array and contains the expected data
            if (is_array($item->user_id) && isset($item->user_id[0]['firstName'])) {
                return $item->user_id[0]['firstName'];
            }
            // Return a default value if not
            return '';
        })->values();

        // Manual pagination
        $perPage = 50;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $sortedUserAttendances->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedItems = new LengthAwarePaginator($currentItems, $sortedUserAttendances->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath()
        ]);

        // Return the resource collection
        return UserAttendanceResource::collection($paginatedItems);
     }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function userManagerAttendanceList()
    {
        return UserAttendanceResource::collection(
            UserAttendance::join('users', 'user_attendances.user_id', '=', 'users.id')
        ->where('users.manager_id', Auth::id()) // Adjust the column and value as needed
        ->select(
            'user_attendances.id',
            'user_attendances.user_id',
            'user_attendances.clock', // Include the original clock
            DB::raw('DATE(user_attendances.clock) as clock_date'), // Extract the date part
            'user_attendances.ipAddress',
            'user_attendances.comment',
            //'user_attendances.attended',
            'users.firstName'
        )
        ->orderBy('clock_date', 'desc') // Order by extracted date in descending order
        ->orderBy('users.firstName', 'desc') // Order by extracted date in descending order
        ->groupBy('users.firstName', 'user_attendances.id', 'user_attendances.clock', 'user_attendances.user_id', 'user_attendances.ipAddress', 'user_attendances.comment') // Group by necessary columns
        ->paginate(50)
        );
    }
}
