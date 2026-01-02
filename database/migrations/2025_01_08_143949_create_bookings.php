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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();  
            $table->string('name', 100);  
            $table->string('email', 100);  
            $table->string('phone', 15);  
            $table->integer('number_of_people'); 
            $table->date('booking_date');  
            $table->time('booking_time');  
            $table->timestamps();  
            $table->integer('status')->default(1);  
            $table->text('note')->nullable();  
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};