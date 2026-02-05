<?php

namespace Database\Seeders;

use App\Models\RequestForm;
use App\Models\RequestItem;
use App\Models\RequestHistory;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;

class RequestFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 40 örnek talep kaydı oluşturur.
     */
    public function run(): void
    {
        $users = User::whereNotNull('department_id')->get();
        if ($users->isEmpty()) {
            $this->command->warn('Önce kullanıcı ve departman oluşturun: php artisan db:seed');

            return;
        }

        $statuses = ['pending_chief', 'pending_manager', 'pending_purchasing', 'approved', 'rejected'];
        $titles = [
            'Dizüstü bilgisayar talebi',
            'Ofis sandalyesi alımı',
            'Yazılım lisansı yenileme',
            'Projeksiyon cihazı',
            'Kablosuz kulaklık seti',
            'Ekran kartı yükseltmesi',
            'Sunucu donanımı',
            'Antivirüs yazılımı',
            'Çok fonksiyonlu yazıcı',
            'Masaüstü bilgisayar',
            'USB hub ve kablolar',
            'Web kamerası',
            'Klavye ve mouse seti',
            'Monitör desteği',
            'Ağ switch cihazı',
            'Yedekleme diski (SSD)',
            'Taşınabilir hard disk',
            'Tablet cihaz talebi',
            'Akıllı tahta',
            'Klima bakım ve onarım',
            'Yangın söndürme tüpü',
            'İlk yardım dolabı malzemeleri',
            'Beyaz tahta ve kalem seti',
            'Dolap ve arşiv malzemesi',
            'Kağıt ve toner siparişi',
            'Zımba ve dosya malzemeleri',
            'Sunum kabloları (HDMI, VGA)',
            'Mobil cihaz (telefon)',
            'Güvenlik yazılımı lisansı',
            'Eğitim kitabı ve doküman',
            'Çay-kahve makinesi',
            'Bulaşık ve temizlik malzemesi',
            'Çalışma masası',
            'Dosya arşivleme sistemi',
            'Mühür ve damga',
            'Kırtasiye paketi',
            'Elektrik prizi uzatma',
            'Laptop çantası',
            'Ekran filtresi',
            'Doküman tarayıcı (scanner)',
        ];

        $itemContents = [
            'Teknik şartname ektedir.',
            'Acil ihtiyaç - proje için gerekli.',
            'Mevcut ekipman eskidi, değişim talep edilmektedir.',
            'Yeni personel için standart donanım.',
            'Departman bütçesi dahilinde.',
            'İş güvenliği gerekliliği.',
            'Lisans süresi doldu.',
            'Performans artışı için gerekli.',
            'Ekip kullanımı için toplu alım.',
            'Yedek parça / yedek malzeme.',
        ];

        $runSuffix = \Illuminate\Support\Str::random(6);

        for ($i = 0; $i < 40; $i++) {
            $user = $users->random();
            $departmentId = $user->department_id ?? Department::first()?->id;
            if (! $departmentId) {
                continue;
            }

            $status = $statuses[$i % 5];
            if ($i >= 5 && $i < 15) {
                $status = 'pending_chief';
            } elseif ($i >= 15 && $i < 25) {
                $status = $statuses[array_rand($statuses)];
            }
            // Dağılım: 8'er tane her durumdan
            $status = $statuses[$i % 5];

            $requestNo = 'REQ-' . now()->format('Ymd') . '-' . $runSuffix . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

            $form = RequestForm::create([
                'user_id' => $user->id,
                'department_id' => $departmentId,
                'request_no' => $requestNo,
                'title' => $titles[$i % count($titles)] . ' #' . ($i + 1),
                'description' => 'Örnek talep açıklaması. Talep no: ' . $requestNo,
                'status' => $status,
                'rejection_reason' => $status === 'rejected' ? 'Örnek red sebebi – bütçe uygun değil.' : null,
            ]);

            $numItems = rand(1, 3);
            for ($j = 0; $j < $numItems; $j++) {
                RequestItem::create([
                    'request_form_id' => $form->id,
                    'content' => $itemContents[array_rand($itemContents)],
                    'link' => $j === 0 && rand(0, 1) ? 'https://example.com/spec' : null,
                ]);
            }

            RequestHistory::create([
                'request_form_id' => $form->id,
                'user_id' => $user->id,
                'action' => 'created',
                'note' => 'Talep oluşturuldu.',
            ]);
        }

        $this->command->info('40 örnek talep kaydı oluşturuldu.');
    }
}
