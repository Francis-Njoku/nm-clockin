<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use App\Http\Resources\LeaveCommentResource;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'approved' => new UserResource($this->approved),
            'leave_type' => $this->leave_type,
            'is_approved' => $this->is_approved,
            'start' => $this->start,
            'end' => $this->end,
            'reason' => $this->reason,
            'file_attachment' => $this->file_attachment ? asset('storage/' . $this->file_attachment) : null, // Include full URL to the file
            'status' => $this->status,
            'comments' => new LeaveCommentResource($this->comments),
            'created_at' => (new \DateTime($this->created_at))->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime($this->updated_at))->format('Y-m-d H:i:s'),
        ];
    }
}
