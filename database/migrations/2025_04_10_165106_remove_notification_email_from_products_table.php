<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNotificationEmailFromProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Check if columns exist before dropping them
            if (Schema::hasColumn('products', 'notification_email')) {
                $table->dropColumn('notification_email');
            }
            
            if (Schema::hasColumn('products', 'is_sensible')) {
                $table->dropColumn('is_sensible');
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
        Schema::table('products', function (Blueprint $table) {
            // Add back columns if needed
            $table->string('notification_email')->nullable()->after('min_stock');
            $table->boolean('is_sensible')->default(false)->after('notification_email');
        });
    }
}
