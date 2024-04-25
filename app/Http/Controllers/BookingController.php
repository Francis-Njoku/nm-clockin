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

class BookingController extends Controller
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

        $booking = Booking::create($data);

        return new BookingResource($booking);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        //
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
