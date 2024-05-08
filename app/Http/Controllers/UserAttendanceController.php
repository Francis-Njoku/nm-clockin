<?php

namespace App\Http\Controllers;
use DateTime;
use App\Models\UserAttendance;
use App\Models\User;
use App\Http\Requests\StoreUserAttendanceRequest;
use App\Http\Requests\UpdateUserAttendanceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


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
        //
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
}
