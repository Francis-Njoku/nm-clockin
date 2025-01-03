<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LeaveUser;
use App\Models\Leave;
use App\Mail\LeaveNotification;
use App\Http\Resources\LeaveResource;
use App\Http\Requests\UpdateLeaveRequest;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\StoreLeaveCommentRequest;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query    = Leave::query();
        $filter   = $request->get('s');
        $statuses = $request->get('status'); // Can receive single status or comma-separated statuses

        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter . '%')
                    ->orWhereHas('user', function ($query) use ($filter) {
                        $query->where('firstName', 'like', '%' . $filter . '%')
                            ->orWhere('lastName', 'like', '%' . $filter . '%')
                            ->orWhere('name', 'like', '%' . $filter . '%');
                    });
            });
        }

        if ($statuses) {
            $statusArray = array_map('trim', explode(',', $statuses));
            $query->whereIn('status', $statusArray);
        }

        return LeaveResource::collection(
            $statuses || $filter ? $query->paginate(10) : Leave::all()
        );
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
        $query  = Leave::where('user_id', Auth::id());

        if ($filter) {
            $query->where(function ($subQuery) use ($filter) {
                $subQuery->where('name', 'like', '%' . $filter . '%')
                    ->orWhereHas('user', function ($userQuery) use ($filter) {
                        $userQuery->where('firstName', 'like', '%' . $filter . '%')
                            ->orWhere('lastName', 'like', '%' . $filter . '%')
                            ->orWhere('name', 'like', '%' . $filter . '%');
                    });
            });
        }

        if ($status) {
            $query->where('status', 'like', '%' . $status . '%');
        }

        // Paginate the results
        $results = $query->paginate(10);

        // Return the paginated resource collection
        return LeaveResource::collection($results);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexManage(Request $request)
    {
        $filter = $request->get('s');
        $status = $request->get('status');

        $query = Leave::query();

        // Apply filter based on 's' parameter (if provided)
        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter . '%')
                    ->orWhereHas('user', function ($query) use ($filter) {
                        $query->where('firstName', 'like', '%' . $filter . '%')
                            ->orWhere('lastName', 'like', '%' . $filter . '%')
                            ->orWhere('name', 'like', '%' . $filter . '%');
                    });
            });
        }

        // Apply filter based on 'status' parameter (if provided)
        if ($status) {
            $query->where('status', 'like', '%' . $status . '%');
        }

        // Check manager relationship (for manager_id in User and user_id in LeaveUser)
        $query->where(function ($query) {
            // Check if the authenticated user is the manager of the leave
            $query->whereHas('user', function ($query) {
                $query->where('manager_id', Auth::id());
            })
                // OR check if the authenticated user is associated with the leave through the leave_users pivot
                ->orWhereHas('manager', function ($query) {
                    // Adjusted this part to correctly check for the user_id in the pivot table
                    $query->where('user_id', Auth::id());
                });
        });

        // Return paginated results
        return LeaveResource::collection(
            $query->paginate(10)
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreLeaveRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreLeaveRequest $request)
    {
        Log::info($request->user()->manager_id);

        // Handle file upload with a timestamped filename
        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $originalName = $request->file('file_attachment')->getClientOriginalName(); // Get the original file name
            $extension    = $request->file('file_attachment')->getClientOriginalExtension(); // Get the file extension
            $timestamp    = now()->format('YmdHis'); // Generate a timestamp
            $filename     = pathinfo($originalName, PATHINFO_FILENAME) . "_{$timestamp}.{$extension}"; // Append timestamp to filename

            $filePath = $request->file('file_attachment')->storeAs('leave_attachments', $filename, 'public'); // Store file with new name
        }

        // Create a leave record using validated data
        $leave = Leave::create(array_merge($request->validated(), [
            'status' => 'pending',
            'file_attachment' => $filePath, // Save file path to the database
        ]));

        // parse the user_recipients from the request (if provided)
        if ($request->has('user_recipients')) {
            $emails = explode(',', $request->user_recipients);

            // Fetch user IDs for the provided emails
            $userIds = User::whereIn('email', $emails)->pluck('id')->toArray();

            // Get the authenticated user's ID
            $authId = auth()->id();

            // Prepare data for bulk insertion
            $leaveUsers = array_map(function ($userId) use ($leave) {
                return [
                    'user_id' => $userId,
                    'leave_id' => $leave->id,
                ];
            }, $userIds);

            // Bulk insert the records into leave_users
            //\DB::table('leave_users')->insert('leaveUsers')
            LeaveUser::insert($leaveUsers);
        }

        // Fetch the owner, recipients, and manager details
        $owner      = $leave->user; // Owner of the leave
        $recipients = User::whereIn('id', $userIds)->get(); // Recipients
        $manager    = User::find($leave->user->manager_id); // Manager (if available)

        // Send email notifications
        // 1. Notify the owner
        Mail::to($owner->email)->send(new LeaveNotification($leave, 'owner'));

        // 2. Notify the recipients
        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(new LeaveNotification($leave, 'recipient'));
        }

        // 3. Notify the manager (if assigned)
        if ($manager) {
            Mail::to($manager->email)->send(new LeaveNotification($leave, 'manager'));
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
     * @return \App\Http\Resources\LeaveResource
     */
    public function show(Leave $leave)
    {
        // Get the authenticated user's ID
        $userId            = auth()->id();
        $isUserTiedToLeave = LeaveUser::where('user_id', $userId)
            ->where('leave_id', $leave->id)
            ->exists();


        // Check if the leave belongs to the authenticated user
        if ($leave->user_id !== $userId && $leave->user->manager_id !== $userId && $isUserTiedToLeave === false) {
            return response()->json(['error' => 'Unauthorizess access.'], 403);
        }

        // If the check passes, return the leave resource
        return new LeaveResource($leave);
        ;
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
    public function managerApproveLeave(UpdateLeaveRequest $request, Leave $leave)
    {
        // Get the authenticated user's ID
        $userId = auth()->id();

        // Check if the authenticated user is the owner of the leave
        if ($leave->user->manager_id !== $userId && $leave->manager->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        // Validate and update the leave details
        $validatedData = $request->validated();

        // If the request has only 'status', update the status field
        if ($request->has('status') && count($validatedData) === 1) {
            $leave->update(['status' => $validatedData['status'], 'approved_by' => $userId]);

            return response()->json([
                'message' => 'Leave status updated successfully.',
                'data' => new LeaveResource($leave),
            ]);
        }

        // For PUT requests, update all provided fields
        // Since PUT usually represents a full resource update,
        // you might want to ensure all relevant fields are included.
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

        // Validate the user's association with the user_id in the Leave model
        $isUserLeaveOwner = Leave::where('user_id', $user->id)
            ->where('id', $leave->id)
            ->exists();

        // Validate the user's association with the manager_id in the User model
        $isUserLeaveOwnerManager = User::where('id', $leave->user->id)
            ->where('manager_id', $user->id)
            ->exists();


        if (!$isUserTiedToLeave && !$isUserLeaveOwner && !$isUserLeaveOwnerManager) {
            return response()->json([
                'message' => 'You are not authorized to comment on this leave.',
            ], 403);
        }

        // Validate the request data
        $validatedData = $request->validated();

        // Create the comment and associate it with the leave
        $leaveComment = $leave->comments()->create([
            'user_id' => $user->id,
            'leave_id' => $leave->id,
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
            'leave_id' => $leave->id,
            'comment' => $validatedData['comment'],
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $leaveComment,
        ], 201);
    }
}
