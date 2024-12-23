<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\LeaveComment;
use App\Http\Resources\UserResource;

use App\Http\Resources\LeaveCommentResource;

class LeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {



        return 
        [
            'id' => $this->id,
            'user' =>  new UserResource($this->user),
            'name' => $this->name,
            'approved_by' => new UserResource($this->approved),
            'leave_type' => $this->leave_type,
            'start' => $this->start,
            'end' => $this->end,
            'reason' => $this->reason,
            'file_attachment' => $this->file_attachment ? asset('storage/' . $this->file_attachment) : null, // Include full URL to the file
            'status' => $this->status,
            'comments' => LeaveCommentResource::collection( LeaveComment::where('leave_id', $this->id)->get()),
            'created_at' => (new \DateTime($this->created_at))->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime($this->updated_at))->format('Y-m-d H:i:s'),
        ];
    }
}
