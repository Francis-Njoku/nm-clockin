<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserBasicResource;

class LeaveCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $getUser = User::select('id', 'firstName', 'lastName', 'email')->where('id', $this->user_id)->first();

        return
            [
                'id' => $this->id,
                'comment' => $this->comment,
                'user' => $getUser,
                'created_at' => (new \DateTime($this->created_at))->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime($this->updated_at))->format('Y-m-d H:i:s'),
            ];
    }
}
