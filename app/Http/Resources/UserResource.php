<?php

namespace App\Http\Resources;

use App\Models\UserGroup;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserGroupResource;
use Carbon\Carbon;

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

        $getGroup = DB::table('user_groups')->where('user_id', '=', $this->id)->get();
        $getUser = DB::table('users')->select('firstName','lastName','email')->where('id', '=', $this->manager_id)->get();
        //UserGroup::where('user_id', $this->id)->get();

        return [
            //'id' => $this->id,
            'name' => $this->name,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'phone' => $this->phone,
            'email' => $this->email,
            'department' => $this->department,
            'identity' => $this->identity,
            'group' => UserGroupResource::collection($getGroup),
            'isStaff' => $this->isStaff,
            'hasManager' => $this->hasManager,
            'manager' => $getUser,
            'gmt' => $this->gmt,
            'joined' => $this->joined,  //(new \Date($this->joined))->format('Y-m-d'),
            'created_at' => (new \DateTime($this->created_at))->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime($this->updated_at))->format('Y-m-d H:i:s'),
        ];
    }
}
