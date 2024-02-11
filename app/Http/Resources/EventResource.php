<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $getEventType = DB::table('eventtype')->where('id', '=', $this->eventTypeId)->get();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'eventTypeId' => new EventTypeResource($getEventType),
            'createdBy' => $this->createdBy,
            'slug' => $this->slug,
            'status' => !!$this->status,
            'location' => $this->location,
            'excerpt' => $this->excerpt,
            'description' => $this->description,
            'start' => (new \DateTime($this->start))->format('Y-m-d H:i:s'),
            'end' => (new \DateTime($this->end))->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTime($this->created_at))->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime($this->updated_at))->format('Y-m-d H:i:s'),
        ];
    }
}
