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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type' , ['Bug','Feature','Improvement']);
            $table->enum('status', ['Open', 'In_Progress', 'Completed', 'Blocked']);
            $table->enum('priority',['Low', 'Medium', 'High']);
            $table->date('due_date');
            $table->foreignId('assign_to')->nullable()->constrained('users','id')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users','id')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
            // Indexes for optimization
            $table->index('title');
            $table->index('priority');
            $table->index('status');
            $table->index('due_date');
            $table->index('assign_to');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
