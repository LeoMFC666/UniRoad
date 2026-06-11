<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_leads', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('email')->nullable();
            $table->string('integration_status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_leads');
    }
};
