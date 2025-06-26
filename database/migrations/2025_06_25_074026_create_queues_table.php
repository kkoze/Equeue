<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->integer('number');
            $table->string('dept');
            $table->enum('status', ['waiting', 'serving', 'completed', 'cancelled'])->default('waiting');
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['dept', 'status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('queues');
    }
};