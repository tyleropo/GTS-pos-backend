<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            return;
        }

        // Create sample audit logs
        $sampleLogs = [
            [
                'user_id' => $users->first()->id,
                'action' => 'created',
                'model_type' => 'App\\Models\\User',
                'model_id' => $users->first()->id,
                'ip_address' => '127.0.0.1',
                'new_values' => json_encode([
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ]),
            ],
            [
                'user_id' => $users->first()->id,
                'action' => 'updated',
                'model_type' => 'App\\Models\\Product',
                'model_id' => 1,
                'ip_address' => '127.0.0.1',
                'new_values' => json_encode([
                    'price' => ['old' => 100, 'new' => 120],
                ]),
            ],
            [
                'user_id' => $users->first()->id,
                'action' => 'created',
                'model_type' => 'App\\Models\\Transaction',
                'model_id' => 1,
                'ip_address' => '192.168.1.1',
                'new_values' => json_encode([
                    'total' => 250.00,
                    'customer_id' => 1,
                ]),
            ],
            [
                'user_id' => $users->skip(1)->first()->id ?? $users->first()->id,
                'action' => 'deleted',
                'model_type' => 'App\\Models\\Product',
                'model_id' => 5,
                'ip_address' => '192.168.1.5',
                'new_values' => json_encode([
                    'name' => 'Deleted Product',
                ]),
            ],
            [
                'user_id' => $users->first()->id,
                'action' => 'updated',
                'model_type' => 'App\\Models\\Customer',
                'model_id' => 2,
                'ip_address' => '127.0.0.1',
                'new_values' => json_encode([
                    'phone' => ['old' => '1234567890', 'new' => '0987654321'],
                ]),
            ],
        ];

        foreach ($sampleLogs as $log) {
            AuditLog::create($log);
        }
    }
}
