<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Leave;
use App\Models\LeaveUser;
use App\Models\LeaveComment;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Http\Resources\LeaveResource;
use App\Http\Requests\StoreLeaveCommentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $status = $request->get('status');
        if ($filter && $status) {
            return LeaveResource::collection(
                Leave::where(function ($query) use ($filter) {
                        $query->where('name', 'like', '%'.$filter.'%')
                              ->orWhereHas('user', function ($query) use ($filter) {
                                  $query->where('firstName', 'like', '%'.$filter.'%')
                                        ->orWhere('lastName', 'like', '%'.$filter.'%')
                                        ->orWhere('name', 'like', '%'.$filter.'%');
                              });
                    })
                    ->where('status', 'like', '%'.$status.'%') // Ensure the status is correctly filtered
                    ->paginate(10)
            );
        }
        elseif($filter)
        {
            return LeaveResource::collection(
                Leave::where(function ($query) use ($filter) {
                        $query->where('name', 'like', '%'.$filter.'%')
                              ->orWhereHas('user', function ($query) use ($filter) {
                                  $query->where('firstName', 'like', '%'.$filter.'%')
                                        ->orWhere('lastName', 'like', '%'.$filter.'%')
                                        ->orWhere('name', 'like', '%'.$filter.'%');
                              });
                    })->paginate(10)
            );
        }
        elseif($status)
        {
            return LeaveResource::collection(
                Leave::where('status', 'like', '%'.$status.'%') // Ensure the status is correctly filtered
                    ->paginate(10)
            );
        }
        else {
            return LeaveResource::collection(Leave::all());
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexSingle(Request $request)
    {
        $filter = $request->get('s');
        $status = $request->get('status');
        if ($filter && $status) {
            return LeaveResource::collection(
                Leave::where(function ($query) use ($filter) {
                        $query->where('name', 'like', '%'.$filter.'%')
                              ->orWhereHas('user', function ($query) use ($filter) {
                                  $query->where('firstName', 'like', '%'.$filter.'%')
                                        ->orWhere('lastName', 'like', '%'.$filter.'%')
                                        ->orWhere('name', 'like', '%'.$filter.'%');
                              });
                    })
                    ->where('status', 'like', '%'.$status.'%') // Ensure the status is correctly filtered
                    ->where('user_id', Auth::id())
                    ->paginate(10)
            );
        }
        elseif ($filter) {
            return LeaveResource::collection(
                Leave::where(function ($query) use ($filter) {
                        $query->where('name', 'like', '%'.$filter.'%')
                              ->orWhereHas('user', function ($query) use ($filter) {
                                  $query->where('firstName', 'like', '%'.$filter.'%')
                                        ->orWhere('lastName', 'like', '%'.$filter.'%')
                                        ->orWhere('name', 'like', '%'.$filter.'%');
                              });
                    })
                    ->where('user_id', Auth::id())
                    ->paginate(10)
            );
        }
        elseif ($status) {
            return LeaveResource::collection(
                Leave::where('status', 'like', '%'.$status.'%') // Ensure the status is correctly filtered
                    ->where('user_id', Auth::id())
                    ->paginate(10)
            );
        }
        else {
            return LeaveResource::collection(
                Leave::where('user_id', Auth::id()));
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
        // Get the authenticated user's ID
        $userId = auth()->id();

        // Check if the leave belongs to the authenticated user
        if ($leave->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        // If the check passes, return the leave resource
        return new LeaveResource($leave);
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
        // Get the authenticated user's ID
        $userId = auth()->id();
    
        // Check if the authenticated user is the owner of the leave
        if ($leave->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }
    
        // Validate and update the leave details
        $validatedData = $request->validated();
    
        // If the request has only 'status', update the status field
        if ($request->has('status') && count($validatedData) === 1) {
            $leave->update(['status' => $validatedData['status']]);
    
            return response()->json([
                'message' => 'Leave status updated successfully.',
                'data' => new LeaveResource($leave),
            ]);
        }
    
        // For full updates, update all provided fields
        $leave->update($validatedData);
    
        return response()->json([
            'message' => 'Leave updated successfully.',
            'data' => new LeaveResource($leave),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLeaveRequest  $request
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function updateAdmin(UpdateLeaveRequest $request, Leave $leave)
    {
        // Validate and update the leave details
        $validatedData = $request->validated();
    
        // If the request has only 'status', update the status field
        if ($request->has('status') && count($validatedData) === 1) {
            $leave->update(['status' => $validatedData['status']]);
    
            return response()->json([
                'message' => 'Leave status updated successfully.',
                'data' => new LeaveResource($leave),
            ]);
        }
    
        // For full updates, update all provided fields
        $leave->update($validatedData);
    
        return response()->json([
            'message' => 'Leave updated successfully.',
            'data' => new LeaveResource($leave),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function destroy(Leave $leave)
    {
        // Get the authenticated user's ID
        $userId = auth()->id();

        // Check if the authenticated user is the owner of the leave
        if ($leave->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        // Delete the leave record
        $leave->delete();

        // Return a success response
        return response()->json(['message' => 'Leave deleted successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function destroyAdmin(Leave $leave)
    {
        // Delete the leave record
        $leave->delete();

        // Return a success response
        return response()->json(['message' => 'Leave deleted successfully.'], 200);
    }

    public function storeLeaveComment(StoreLeaveCommentRequest $request, Leave $leave)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Validate the user's association with the leave_id in the LeaveUser model
        $isUserTiedToLeave = LeaveUser::where('user_id', $user->id)
            ->where('leave_id', $leave->id)
            ->exists();

        // Validate the user's association with the leave_id in the LeaveUser model
        $isUserLeaveOwner = Leave::where('user_id', $user->id)
        ->where('leave_id', $leave->id)
        ->exists();


        if (!$isUserTiedToLeave || !$isUserLeaveOwner) {
            return response()->json([
                'message' => 'You are not authorized to comment on this leave.',
            ], 403);
        }

        // Validate the request data
        $validatedData = $request->validated();

        // Create the comment and associate it with the leave
        $leaveComment = $leave->comments()->create([
            'user_id' => $user->id,
            'comment' => $validatedData['comment'],
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $leaveComment,
        ], 201);
    }
    
    public function storeLeaveCommentAdmin(StoreLeaveCommentRequest $request, Leave $leave)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Validate the request data
        $validatedData = $request->validated();

        // Create the comment and associate it with the leave
        $leaveComment = $leave->comments()->create([
            'user_id' => $user->id,
            'comment' => $validatedData['comment'],
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $leaveComment,
        ], 201);
    }
}
