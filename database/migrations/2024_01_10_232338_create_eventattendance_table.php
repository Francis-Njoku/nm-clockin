<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventattendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventattendance', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class, 'userId');
            $table->foreignIdFor(\App\Models\Event::class, 'eventId');
            $table->boolean('attended');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventattendance');
    }
}
