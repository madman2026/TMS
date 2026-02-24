<?php

use App\DeviceTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\DriverTypeEnum;
use App\InternetSpeedEnum;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name')->unique();
            $table->json('extra')->nullable();
            $table->string('driver')->default(DriverTypeEnum::CHROME);
            $table->string('internet_speed')->default(InternetSpeedEnum::NORMAL);
            $table->string('device')->default(DeviceTypeEnum::DESKTOP);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
