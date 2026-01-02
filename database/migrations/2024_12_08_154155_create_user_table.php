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
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('username',255);
            $table->string('password',255);
            $table->string('fullname',30);
            $table->string('gender',1000);
            $table->string('thumbnail',1000);
            $table->string('email',1000);
            $table->string('phone',1000);
            $table->string('address',1000);
            $table->enum('roles',['admin','customer'])->default('customer');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->unsignedInteger('status');
            $table->string('google_id')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};