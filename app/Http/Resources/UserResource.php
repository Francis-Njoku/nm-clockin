<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\UserGroup;
use App\Models\User;
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
        $getGroup = DB::table('user_groups')->where('user_id', '=', $this->id)->get();
        $getUser  = DB::table('users')->select('firstName', 'lastName', 'email')->where('id', '=', $this->manager_id)->get();
        //UserGroup::where('user_id', $this->id)->get();

        $group = UserGroup::where('user_id', Auth::id())->get();
        foreach ($group as $groups) {
            $group_id = $groups->group_id;
        }

        return [
            'id' => $this->id,
            'identity' => $this->identity,
            'name' => $this->name,
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'phone' => $this->phone,
            'department' => $this->department,
            'manager' => $getUser,
            'group' => UserGroupResource::collection($getGroup),
            'joined' => $this->joined,  //(new \Date($this->joined))->format('Y-m-d'),
            'gmt' => $this->gmt,
            'created_at' => (new \DateTime($this->created_at))->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime($this->updated_at))->format('Y-m-d H:i:s'),
        ];
    }
}
