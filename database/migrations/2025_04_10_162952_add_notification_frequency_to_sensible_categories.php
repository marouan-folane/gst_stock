<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationFrequencyToSensibleCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensible_categories', function (Blueprint $table) {
            // Add min_quantity column if threshold doesn't exist
            if (!Schema::hasColumn('sensible_categories', 'min_quantity')) {
                $table->integer('min_quantity')->default(0)->after('category_id');
            }
            
            // Add notification_frequency column
            if (!Schema::hasColumn('sensible_categories', 'notification_frequency')) {
                $table->enum('notification_frequency', ['daily', 'weekly', 'monthly'])
                      ->default('weekly')
                      ->after('notification_email');
            }
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
            if (Schema::hasColumn('sensible_categories', 'notification_frequency')) {
                $table->dropColumn('notification_frequency');
            }
            
            // Only drop min_quantity if we created it (don't drop threshold)
            if (Schema::hasColumn('sensible_categories', 'min_quantity') && 
                !Schema::hasColumn('sensible_categories', 'threshold')) {
                $table->dropColumn('min_quantity');
            }
        });
    }
}
