<?php

namespace App\Http\Resources;

use App\Models\UserGroup;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserGroupResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //$usergroup = DB::UserGroup();

        $getGroup = DB::table('usergroup')->where('user_id', '=', $this->id)->get();
        //UserGroup::where('user_id', $this->id)->get();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'firstName' => $this->firstName,
            'lastName' => $this->createdBy,
            'phone' => $this->phone,
            'email' => $this->email,
            'identity' => $this->identity,
            'group' => UserGroupResource::collection($getGroup),
            'isStaff' => $this->isStaff,
            'created_at' => (new \DateTime($this->created_at))->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime($this->updated_at))->format('Y-m-d H:i:s'),
        ];
    }
}
