<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\EventRequest;
use App\Http\Requests\EventTypeRequest;
use App\Http\Requests\EventGalleryRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventTypeResource;
use App\Http\Resources\UpdateEventTypeResource;
use App\Models\Event;
use App\Models\EventType;
use App\Models\EventGallery;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->get('s');
        if($filter)
        {
            echo $filter;
            return EventResource::collection(
                Event::where('state', 'like', '%'.$filter.'%')
                ->orWhere('country', 'like', '%'.$filter.'%')
                ->paginate(10));
        }
        else{
            return EventResource::collection(Event::all());
        }
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EventRequest $request)
    {
        $data = $request->validated();

        // Check if image was given and save on local file system

        $event = Event::create($data);

        return new EventResource($event);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */   
    public function storeImage(Request $request, $id) 
    {
        $checkID = Event::findOrFail($id);
        $getUser = DB::table('events')
        ->select('createdBy')
        ->where('id',$id)
        ->get()
        ->toArray();
        foreach ($getUser as $gets)
        {
            $checker = $gets->createdBy;
            echo $checker;
        };

        if (Auth::id() != $checker)
        {
                return response()->json([
                'message' => 'Not authorized to process this'], 401);
        }

        $newdata = [];
        
        $postObj = new EventGallery;

        if($request->hasFile('featured')) {
            //echo "emeka";
            $filename = $request->file('featured')->getClientOriginalName(); // get the file name
            $getfilenamewitoutext = pathinfo($filename, PATHINFO_FILENAME); // get the file name without extension
            $getfileExtension = $request->file('featured')->getClientOriginalExtension(); // get the file extension
            $createnewFileName = time().'_'.str_replace(' ','_', $getfilenamewitoutext).'.'.$getfileExtension; // create new random file name
            $img_path = $request->file('featured')->storeAs('public/featured_img', $createnewFileName); // get the image path
            $postObj->image = $createnewFileName; // pass file name with column
            $postObj->isFeatured = 1;
            //echo "emeka";
            $postObj->event_id = $id; 
        }

            $files = $request->allFiles('images');

            $uploadedImagePaths = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $fileName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('public/featured_img', $fileName);
    
                    // Create a new record in the database
                    $imageRecord = new EventGallery();
                    $imageRecord->image = $fileName;
                    $imageRecord->isFeatured = 0;
                    $imageRecord->event_id = $id;
                    //$imageRecord->path = $path;
                    $imageRecord->save();
    
                    $uploadedImagePaths[] = ['image' => $fileName, 'isFeatured' => 0, 'event_id' => $id];
                }
            }

        if($postObj->save()) { // save file in databse
            return ['status' => true, 'message' => "Image uploded successfully"];       
        }
        else {
            return ['status' => false, 'message' => "Error : Image not uploded successfully"];       

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $getUser = DB::table('events')
        ->select('id')
        ->where('slug',$slug)
        ->get()
        ->toArray();
        if (!$getUser) {
            return response()->json(['message' => 'Event not found'], 404);
        }
        foreach ($getUser as $gets)
        {
            $checker = $gets->id;
            echo $checker;
        };

        //echo $slug;
        $item = Event::find($checker);
    /*if (!$item) {
        return response()->json(['message' => 'Item not found'], 404);
    }*/
    return new EventResource($item);
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

    private function saveImage($image)
    {
        // Check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            // Take out the base64 encoded text without mime type
            $image = substr($image, strpos($image, ',') + 1);

            // Get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $dir = 'images/';
        $file = Str::random() . '.' . $type;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $file;
        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }
        file_put_contents($relativePath, $image);

        return $relativePath;
    }
}