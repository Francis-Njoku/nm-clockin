<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\EventTypeRequest;
use App\Http\Resources\EventTypeResource;
use App\Http\Resources\UpdateEventTypeResource;
use App\Models\EventType;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeEventType(EventTypeRequest $request)
    {
        $data = $request->validated();
        echo "il";

        $eventType = EventType::create($data);

        return new EventTypeResource($eventType);
    }

    public function listEventType(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Number of items per page, default is 10
        $resources = EventType::paginate($perPage);

        return response()->json([
            'data' => $resources->items(),
            'meta' => [
                'total' => $resources->total(),
                'per_page' => $resources->perPage(),
                'current_page' => $resources->currentPage(),
                // Add more meta information if needed
            ],
        ]);
        //return EventTypeResource::collection(EventType);
    }

    /**
     * Update event type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateEventType(EventTypeRequest $request, $id)
    {
        $data = $request->validated();

        // update survey in the database
        $find = EventType::find($id);
        $updated = $find->update($data);
        //$updated = $eventType->update($data);
        return response()->json([
            'message' => 'Event type has been Updated',
        ]);


        //return new UpdateEventTypeResource($updated);
    }
}