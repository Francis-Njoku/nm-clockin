<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\EventResource;
use App\Models\Event;

class BookingResource extends JsonResource
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
            'event' =>  new EventResource($this->event),
            'user_id' => $getUser,   
            'ticket' => $this->ticket,
            'attended' => $this->attended
        ];
    }
}
