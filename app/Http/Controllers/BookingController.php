<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;

class BookingController extends Controller
{
    /***
     * Generate user Identity
     * @param No params
     * @return unique Identity
     */
    private function generateIdentity()
    {
        $randomNumber = random_int(10000000000000000, 99999999999999999);
        if (Booking::where('identity', '=', $randomNumber)->exists()) {
            return $this->generateIdentity();
         }
         else{
            return $randomNumber;
         }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $name)
    {
        if (User::where('identity', '=', $name)->where('id', Auth::id())->exists()) {
            $filter = $request->get('s');
            echo "school";
            if ($filter)
            {
                $checkSearch = Event::select('id')->where('state', 'like', '%'.$filter.'%')
                ->orWhere('country', 'like', '%'.$filter.'%')->orWhere('name', 'like', '%'.$filter.'%');
                if($checkSearch != null)
                {
                    $news = BookingResource::collection(
                        Booking::join('events', 'bookings.event_id', '=', 'events.id')
                        ->select('bookings.*')
                        ->where('bookings.user_id',Auth::id())
                        ->where(function($query) use ($request){
                            $query->where('events.state', 'like', '%'.$request->get('s').'%')
                            ->orWhere('events.country', 'like', '%'.$request->get('s').'%')
                            ->orWhere('events.name', 'like', '%'.$request->get('s').'%');
                        })
                        ->paginate(10));
                        
                    return $news;
                }
                else{
                    return BookingResource::collection(
                    DB::table('bookings')
                    ->join('events', 'bookings.event_id', '=', 'events.id')
                    ->select('bookings.*' )
                    ->where('bookings.user_id',Auth::id())
                    ->where(function($query) use ($request){
                        $query->where('events.state', 'like', '%'.$filter.'%')
                        ->orWhere('events.country', 'like', '%'.$filter.'%')
                        ->orWhere('events.name', 'like', '%'.$filter.'%');
                    })->paginate(10));
                }
            }
            else
            {
                echo "lexis";
                return BookingResource::collection(Booking::where('identity', '=', $name)
                ->where('user_id', Auth::id())
                ->paginate(10));
            } 
        }
        else{
            return response()->json(['message' => 'User not found'], 404);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBookingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBookingRequest $request, $slug)
    {
        $data = $request->validated();

        $getUser = DB::table('events')
        ->select('id','registration','amount','quantity','booked')
        ->where('slug',$slug)
        ->get()
        ->toArray();
        if (!$getUser) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        foreach ($getUser as $gets)
        {
            $event_id = $gets->id;
            $registration = $get->registration;
            $amount = $get->amount;
            $quantity = $get->quantity;
            $booked = $get->booked;
            //echo $checker;
        };

        if ($booked >= $quantity)
        {
            return response()->json(['message' => 'Ticket sold out'], 422);
        }else{
            $checkBooked = DB::table('bookings')
            ->where('event_id',$event_id)
            ->get();
            if ($registration == 'single' && $checkBooked === null)
            {
                $newBooked = $booked + 1;
                $updateBooked = Event::find($event_id);
                $updateBooked->booked = $newBooked;
                $updateBooked->save();
            }
            elseif ($registration == 'single' && $checkBooked != null){
                return response()->json(['message' => 'You have already purchased a ticket'], 422);
            }else
            {
                if($booked + $data['ticket'] <= $quantity)
                {
                    $newBooked = $booked + $data['ticket'];
                    $updateBooked = Event::find($event_id);
                    $updateBooked->booked = $newBooked;
                    $updateBooked->save();
                }
                else
                {
                    return response()->json(['message' => 'You cannot purchase more than the available ticket'], 422);

                }
                
            }
        }


        $data['event_id'] = $event_id;
        $data['identity'] = $this->generateIdentity();

        $booking = Booking::create($data);

        return new BookingResource($booking);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking, $identity)
    {
        if (Booking::where('identity', '=', $identity)->where('user_id', Auth::id())->exists())
        {
            $getEvents = DB::table('bookings')
            ->select('id')
            ->where('identity',$identity)
            ->get()
            ->toArray();
            foreach ($getEvents as $gets)
            {
                $checker = $gets->id;
                //echo $checker;
            };
            $item = Booking::find($checker);

            return new BookingResource($item);
        }
        else{
            return response()->json(['message' => 'Page not found'], 404);

        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBookingRequest  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
