<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToPartner extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner', function (Blueprint $table) {
            $table->string('slug', 1000)->nullable()->after('partnerName');
            $table->string('createdBy', 1000)->nullable()->after('partnerDetails');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('createdBy');
        });
    }
}
