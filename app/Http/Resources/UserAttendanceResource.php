<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use App\Http\Resources\AttendanceResource;
use App\Models\User;


use Illuminate\Http\Resources\Json\JsonResource;

class UserAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {        
        $getUser = DB::table('users')->select('firstName','lastName')->where('id', '=', $this->user_id)->get();

        return [
            'id' => $this->id,
            'attendance' =>  new AttendanceResource($this->attendance),
            'user_id' => $getUser,   
            'ipAddress' => $this->ipAddress,
            'clock' => (new \DateTime($this->clock))->format('Y-m-d H:i:s'),
            'comment' => $this->comment,
            'attended' => $this->attended
        ];
    }
}
