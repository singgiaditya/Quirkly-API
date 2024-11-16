<?php

use App\Models\Companies;
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
        Schema::create('user_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignIdFor(Companies::class, 'company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->string('role');
            $table->dateTime('joined_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_companies');
    }
};
