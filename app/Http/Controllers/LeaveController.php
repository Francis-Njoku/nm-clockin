<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Leave;
use App\Models\LeaveUser;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Http\Resources\LeaveResource;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->get('s');
        if ($filter)
        {
            return LeaveResource::collection(
                Leave::where('name', 'like', '%'.$filter.'%')
                ->paginate(10));
        }
        else {
            return LeaveResource::collection(Leave::all());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreLeaveRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeaveRequest $request)
    {
        // Handle file upload with a timestamped filename
        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $originalName = $request->file('file_attachment')->getClientOriginalName(); // Get the original file name
            $extension = $request->file('file_attachment')->getClientOriginalExtension(); // Get the file extension
            $timestamp = now()->format('YmdHis'); // Generate a timestamp
            $filename = pathinfo($originalName, PATHINFO_FILENAME) . "_{$timestamp}.{$extension}"; // Append timestamp to filename
            
            $filePath = $request->file('file_attachment')->storeAs('leave_attachments', $filename, 'public'); // Store file with new name
        }
        // Create a leave record using validated data

        $leave = Leave::create(array_merge($request->validated(), [
            'status' => 'pending',
            'approved_by' => null,
            'file_attachment' => $filePath, // Save file path to the database
        ]));

        // parse the user_recipients from the request (if provided)
        if ($request->has('user_recipients')) 
        {
            $emails = explode(',', $request->user_recipients);

            // Fetch user IDs for the provided emails
            $userIds = User::whereIn('email', $emails)->pluck('id')->toArray();

            // Get the authenticated user's ID
            $authId = auth()->id();

            // Prepare data for bulk insertion
            $leaveUsers = array_map(fn($userId) => [
                'user_id' => $userId,
                'leave_id' => $leave->id,
            ], $userIds);

            // Bulk insert the records into leave_users
            //\DB::table('leave_users')->insert('leaveUsers')
            LeaveUser::insert($leaveUsers);
        }


        return (new LeaveResource($leave))
        ->additional(['message' => 'Leave request created successfully.'])
        ->response()
        ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function show(Leave $leave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLeaveRequest  $request
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLeaveRequest $request, Leave $leave)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function destroy(Leave $leave)
    {
        //
    }
}
