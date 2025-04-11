<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('customers', 'tax_number')) {
                $table->string('tax_number')->nullable();
            }
            
            if (!Schema::hasColumn('customers', 'contact_person')) {
                $table->string('contact_person')->nullable();
            }
            
            if (!Schema::hasColumn('customers', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['tax_number', 'contact_person', 'status']);
        });
    }
};
