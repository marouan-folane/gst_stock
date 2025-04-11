<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Supplier;

class SupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    
       

        // Create manager user
        Supplier::create([
            'name' => 'Supplier 1',
            'email' => 'supplier1@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St, Anytown, USA',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '12345',
            'country' => 'USA',
            'notes' => 'This is a note',
        ]);

        
    }
}
