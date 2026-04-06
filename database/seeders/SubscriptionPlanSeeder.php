<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('subscription_plans')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('subscription_plans')->insert([
            [
                'name' => '1-Day Free Trial', // This will be ID 1
                'duration_days' => 1,
                'original_price' => 0.00,
                'promo_price' => 0.00,
                'collaborator_limit' => 1,
                'details' => '<ul class="list-unstyled text-muted small">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Full access for 24 hours</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>1 Collaborator limit</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Try all features</li>
                              </ul>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '7 Days Plan', // This will be ID 2
                'duration_days' => 7,
                'original_price' => 399.00,
                'promo_price' => 199.00,
                'collaborator_limit' => 2,
                'details' => '<ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Full access for 1 week</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>2 Collaborators per deck</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Unlimited deck creation</li>
                              </ul>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Monthly Plan', // This will be ID 3
                'duration_days' => 30,
                'original_price' => 999.00,
                'promo_price' => 499.00,
                'collaborator_limit' => 5,
                'details' => '<ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Full access for 30 days</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>5 Collaborators per deck</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Priority email support</li>
                              </ul>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Yearly Plan', // This will be ID 4
                'duration_days' => 365,
                'original_price' => 5999.00,
                'promo_price' => 2499.00,
                'collaborator_limit' => 20,
                'details' => '<ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Best Value - 1 Year access</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>20 Collaborators per deck</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Custom deck themes</li>
                              </ul>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}