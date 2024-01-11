<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventgalleryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventgallery', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Event::class, 'eventId');
            $table->string('image', 1000);
            $table->boolean('isFeatured');
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
        Schema::dropIfExists('eventgallery');
    }
}
