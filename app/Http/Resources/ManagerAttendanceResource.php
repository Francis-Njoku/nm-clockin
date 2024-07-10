<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ManagerAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //$getUser = DB::table('users')->select('firstName','lastName')->where('id', '=', $this->user_id)->get();

        return [
            'id' => $this->id,
            //'attendance' =>  new AttendanceResource($this->attendance),
            'attendance' => [
                'name' => $this->attendance_name,
                'status' => $this->attendance_status,
                'created_at' => $this->attendance_created_at,
                'updated_at' => $this->attendance_updated_at,
            ],
            //'user_id' => $getUser,  
            'user' => [
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
            ], 
            'ipAddress' => $this->ipAddress,
            'clock' => (new \DateTime($this->clock))->format('Y-m-d H:i:s'),
            'comment' => $this->comment,
            'attended' => $this->attended
        ];
    }
}
