<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\EventGalleryResource;
use App\Models\EventGallery;

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
        $getEventType = DB::table('event_types')->where('id', '=', $this->eventTypeId)->get();
        $getImage  = DB::table('event_galleries')->where('event_id', '=', $this->id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'eventTypeId' => $getEventType,
            'createdBy' => $this->createdBy,
            'slug' => $this->slug,
            'amount' => $this->amount,
            //'images' => !!EventGalleryResource::collection($getImage),
            //'images' => !!EventGalleryResource::collection(EventGallery::all()),
            'images' => EventGalleryResource::collection($this->images),
            //'images' =>  $getImage,
            //'images' => EventGallery::resource(this->whenLoaded('images')),
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
