<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;

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
            'status' => $this->status,
            'created_at' => (new \DateTime($this->created_at))->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime($this->updated_at))->format('Y-m-d H:i:s'),
        ];
    }
}
