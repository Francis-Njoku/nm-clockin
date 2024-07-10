<?php

namespace App\Http\Controllers;
use DateTime;
use App\Models\UserAttendance;
use App\Models\User;
use App\Models\UserGroup;
use App\Http\Requests\StoreUserAttendanceRequest;
use App\Http\Requests\UpdateUserAttendanceRequest;
use App\Http\Resources\UserAttendanceResource;
use App\Http\Resources\ManagerAttendanceResource;
use App\Http\Requests\StoreAttendanceRequest;
use App\Helpers\NotificationHelper;
use App\Events\NotificationDefaultEvent;
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
    // Send notification to individual
    
    // Send notification to admin
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
        return ManagerAttendanceResource::collection(
            UserAttendance::join('users', 'user_attendances.user_id', '=', 'users.id')
            ->join('attendances', 'user_attendances.attendance_id', '=', 'attendances.id') // Adjust the join condition if necessary
        ->where('users.manager_id', Auth::id()) // Adjust the column and value as needed
        ->select(
            'user_attendances.id',
            'user_attendances.user_id',
            'user_attendances.clock', // Include the original clock
            DB::raw('DATE(user_attendances.clock) as clock_date'), // Extract the date part
            'user_attendances.ipAddress',
            'user_attendances.comment',
            //'user_attendances.attended',
            'users.firstName',
            'users.lastName',
            'attendances.name as attendance_name', // Select attendance name
            'attendances.status as attendance_status', // Select attendance status
            'attendances.created_at as attendance_created_at', // Select attendance created_at
            'attendances.updated_at as attendance_updated_at' // Select attendance updated_at
        )
        ->orderBy('clock_date', 'desc') // Order by extracted date in descending order
        ->orderBy('users.firstName', 'desc') // Order by extracted date in descending order
        ->groupBy('users.firstName', 'user_attendances.id', 'user_attendances.clock', 
        'user_attendances.user_id', 'user_attendances.ipAddress', 
        'user_attendances.comment',
        'attendances.name',
        'users.firstName',
            'users.lastName',
            'attendances.status',
            'attendances.created_at',
            'attendances.updated_at') // Group by necessary columns
        ->paginate(50)
        );
    }

    /**
     * modify clock in.
     *
     * @param  \App\Http\Requests\StoreUserAttendanceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function userRegisterClock(StoreAttendanceRequest $request)
    {
        $data = $request->validated();

        // enter clockin
        $clockin = UserAttendance::create([
            'user_id' => Auth::id(),
            'attendance_id' => '1',
            'clock' => $data['clockIn'],
            'status' => 'pending',
            'comment' => $data['comment'],
            'ipAddress' => \Request::ip()
        ]);

        //enter clockout
        $clockout = UserAttendance::create([
            'user_id' => Auth::id(),
            'attendance_id' => '2',
            'clock' => $data['clockOut'],
            'status' => 'pending',
            'comment' => $data['comment'],
            'ipAddress' => \Request::ip()
        ]);
        NotificationDefaultEvent::dispatch($data['comment']);
        return response()->json([
            'status' => 'Successful',
        ], 201);


        // Get the currently authenticated user
        $user = Auth::user();
        //echo $user->manager_id;
        //echo "chima";
        echo $user->name;

        // Check if the manager ID is empty
        if (!empty($user->manager_id)) {
            $message = $user->firstName." ".$user->lastName." created a clockin";
            $userId = $user->name;
            $messageType = "clock";
            $result = NotificationHelper::single($message, $messageType, $userId);
            //event(new NotificationEvent($message, $messageType, $userId));
            return response()->json([
                'status' => 'Successful',
            ], 201);
        }
        else{
            return response()->json([
                'status' => 'failed',
            ], 400);
        }

        // send notification to admin

        $getAdmin = UserGroup::where('group_id', '2')
        ->get();

        foreach($getAdmin as $admin)
        {
            $userAdmin = User::find($admin->id);
            // Check if user exists
            if ($userAdmin) {
                // Get the user's first name
                //$firstName = $user->firstName;
                $message = $user->firstName." ".$user->lastName." created a clockin";
                $userId = $userAdmin->name;
                $messageType = "clock";
                $result = NotificationHelper::single($message, $messageType, $userId);
                //return response()->json(['first_name' => $firstName]);
            }
        }
        
        return response()->json([
            'success' => 'Successful',
        ], 201);
    }

}
