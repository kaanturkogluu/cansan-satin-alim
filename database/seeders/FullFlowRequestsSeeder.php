<?php

namespace Database\Seeders;

use App\Models\RequestForm;
use App\Models\RequestItem;
use App\Models\RequestHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Mühendis → Şef → Müdür → Satın Alma onayına kadar giden 4 tam akış talebi oluşturur.
 */
class FullFlowRequestsSeeder extends Seeder
{
    public function run(): void
    {
        $engineer = User::where('role', 'engineer')->first();
        $chief = User::where('role', 'chief')->first();
        $manager = User::where('role', 'manager')->first();
        $purchasing = User::where('role', 'purchasing')->first();

        if (! $engineer || ! $chief || ! $manager || ! $purchasing) {
            $this->command->warn('Gerekli roller bulunamadı. Önce: php artisan db:seed');

            return;
        }

        $titles = [
            'Dizüstü bilgisayar talebi (tam akış)',
            'Yazılım lisansı yenileme (tam akış)',
            'Projeksiyon cihazı alımı (tam akış)',
            'Ofis donanımı paketi (tam akış)',
        ];

        $departmentId = $engineer->department_id;

        for ($i = 0; $i < 4; $i++) {
            $baseDate = now()->subDays(10 - $i)->setHour(9)->setMinute(0)->setSecond(0);

            $form = RequestForm::create([
                'user_id' => $engineer->id,
                'department_id' => $departmentId,
                'request_no' => 'REQ-FULL-' . now()->format('YmdHis') . '-' . ($i + 1) . '-' . \Illuminate\Support\Str::random(4),
                'title' => $titles[$i],
                'description' => 'Mühendisten satın alma onayına kadar tam akış örneği #' . ($i + 1),
                'status' => 'approved',
                'rejection_reason' => null,
                'created_at' => $baseDate,
                'updated_at' => $baseDate->copy()->addDays(2),
            ]);

            RequestItem::create([
                'request_form_id' => $form->id,
                'content' => 'Teknik şartname ve bütçe uygun.',
                'link' => null,
            ]);

            $t1 = $baseDate;
            $t2 = $baseDate->copy()->addHours(3);
            $t3 = $baseDate->copy()->addDay()->addHours(2);
            $t4 = $baseDate->copy()->addDays(2)->addHours(1);

            RequestHistory::create([
                'request_form_id' => $form->id,
                'user_id' => $engineer->id,
                'action' => 'created',
                'note' => 'Talep oluşturuldu.',
                'created_at' => $t1,
                'updated_at' => $t1,
            ]);
            RequestHistory::create([
                'request_form_id' => $form->id,
                'user_id' => $chief->id,
                'action' => 'approved',
                'note' => 'Approved by chief',
                'created_at' => $t2,
                'updated_at' => $t2,
            ]);
            RequestHistory::create([
                'request_form_id' => $form->id,
                'user_id' => $manager->id,
                'action' => 'approved',
                'note' => 'Approved by manager',
                'created_at' => $t3,
                'updated_at' => $t3,
            ]);
            RequestHistory::create([
                'request_form_id' => $form->id,
                'user_id' => $purchasing->id,
                'action' => 'approved',
                'note' => 'Approved by purchasing',
                'created_at' => $t4,
                'updated_at' => $t4,
            ]);
        }

        $this->command->info('Mühendis → Şef → Müdür → Satın Alma tam akışında 4 talep oluşturuldu.');
    }
}
