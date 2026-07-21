<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        if (User::where('email', 'pxpm88@gmail.com')->exists()) {
            return;
        }

        User::create([
            'name' => 'Super Admin',
            'email' => 'pxpm88@gmail.com',
            'password' => Hash::make('Liaweb2026!'),
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);
    }

    public function down(): void
    {
        User::where('email', 'pxpm88@gmail.com')->delete();
    }
};
