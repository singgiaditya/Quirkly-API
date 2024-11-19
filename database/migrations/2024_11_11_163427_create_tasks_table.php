<?php

use App\Models\Project;
use App\Models\User;
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
            $table->foreignIdFor(Project::class, 'project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->string('title');
            $table->string('description');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['pending', 'in_progress', 'completed']);
            $table->dateTime('due_date');
            $table->foreignIdFor(User::class, 'assign_to');
            $table->timestamps();
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
