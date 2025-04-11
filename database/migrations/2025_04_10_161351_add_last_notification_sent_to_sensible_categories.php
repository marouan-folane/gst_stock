<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastNotificationSentToSensibleCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensible_categories', function (Blueprint $table) {
            $table->timestamp('last_notification_sent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sensible_categories', function (Blueprint $table) {
            $table->dropColumn('last_notification_sent');
        });
    }
}
