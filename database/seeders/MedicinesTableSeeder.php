<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicinesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // التأكد من وجود فئات
        $categories = DB::table('categories')->pluck('id', 'name')->toArray();

        // إذا لم توجد فئات، قم بإنشائها
        if (empty($categories)) {
            $this->command->info('📦 جاري إنشاء الفئات...');

            $categories['مسكنات'] = DB::table('categories')->insertGetId([
                'name' => 'مسكنات',
                'avatar' => 'categories/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $categories['مضادات حيوية'] = DB::table('categories')->insertGetId([
                'name' => 'مضادات حيوية',
                'avatar' => 'categories/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $categories['فيتامينات'] = DB::table('categories')->insertGetId([
                'name' => 'فيتامينات',
                'avatar' => 'categories/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $categories['مكملات غذائية'] = DB::table('categories')->insertGetId([
                'name' => 'مكملات غذائية',
                'avatar' => 'categories/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $categories['أدوية ضغط'] = DB::table('categories')->insertGetId([
                'name' => 'أدوية ضغط',
                'avatar' => 'categories/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $categories['أدوية سكري'] = DB::table('categories')->insertGetId([
                'name' => 'أدوية سكري',
                'avatar' => 'categories/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $categories['مضادات هيستامين'] = DB::table('categories')->insertGetId([
                'name' => 'مضادات هيستامين',
                'avatar' => 'categories/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $categories['عناية بالبشرة'] = DB::table('categories')->insertGetId([
                'name' => 'عناية بالبشرة',
                'avatar' => 'categories/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->command->info('✅ تم إنشاء 8 فئات بنجاح!');
        } else {
            $this->command->info('✅ تم العثور على فئات موجودة مسبقاً');
        }

        // طباعة الفئات للتأكد
        $this->command->info('📋 الفئات المتاحة: ' . implode(', ', array_keys($categories)));

        $medicines = [
            // مسكنات
            [
                'name' => 'بنادول أدفانس',
                'description' => 'مسكن فعال للألم وخافض للحرارة، يحتوي على مادة الباراسيتامول بتركيز 500 ملغ. يستخدم لتسكين آلام الصداع، آلام الأسنان، آلام العضلات والمفاصل، وخفض درجة الحرارة.',
                'general_stock' => 100,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مسكنات',
            ],
            [
                'name' => 'بروفين 400 ملغ',
                'description' => 'مضاد للالتهابات غير ستيرويدي، يحتوي على الإيبوبروفين. يستخدم لتسكين الآلام المتوسطة إلى الشديدة، والتهابات المفاصل، وآلام الدورة الشهرية.',
                'general_stock' => 80,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مسكنات',
            ],
            [
                'name' => 'أدول 500 ملغ',
                'description' => 'مسكن للألم وخافض للحرارة، يحتوي على الباراسيتامول. سريع المفعول وآمن للاستخدام لجميع الأعمار.',
                'general_stock' => 120,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مسكنات',
            ],
            [
                'name' => 'فولتارين 50 ملغ',
                'description' => 'مضاد للالتهابات، يحتوي على ديكلوفيناك صوديوم. يستخدم لعلاج الالتهابات والتورم وآلام المفاصل والعضلات.',
                'general_stock' => 60,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مسكنات',
            ],
            [
                'name' => 'سبازموكان 40 ملغ',
                'description' => 'مضاد للتشنجات، يستخدم لعلاج آلام المعدة والأمعاء، والمغص الكلوي.',
                'general_stock' => 100,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مسكنات',
            ],
            [
                'name' => 'بوسكوبان 10 ملغ',
                'description' => 'مضاد للتشنجات، يستخدم لعلاج آلام البطن والتشنجات العضلية.',
                'general_stock' => 95,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مسكنات',
            ],
            [
                'name' => 'موتليوم 10 ملغ',
                'description' => 'علاج للغثيان والقيء، يساعد على تحسين حركة المعدة.',
                'general_stock' => 85,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مسكنات',
            ],

            // مضادات حيوية
            [
                'name' => 'أموكسيسيلين 500 ملغ',
                'description' => 'مضاد حيوي واسع المجال من مجموعة البنسلينات، يستخدم لعلاج الالتهابات البكتيرية في الجهاز التنفسي، المسالك البولية، والأذن.',
                'general_stock' => 150,
                'image' => null,
                'expiration_date' => now()->addYears(1),
                'is_active' => true,
                'category_name' => 'مضادات حيوية',
            ],
            [
                'name' => 'أزيثروميسين 500 ملغ',
                'description' => 'مضاد حيوي من مجموعة الماكروليدات، يستخدم لعلاج التهابات الجهاز التنفسي العلوي والسفلي، والتهابات الجلد.',
                'general_stock' => 90,
                'image' => null,
                'expiration_date' => now()->addYears(1),
                'is_active' => true,
                'category_name' => 'مضادات حيوية',
            ],
            [
                'name' => 'سيفالكسين 500 ملغ',
                'description' => 'مضاد حيوي من مجموعة السيفالوسبورينات، يستخدم لعلاج التهابات الجهاز التنفسي، المسالك البولية، والجلد.',
                'general_stock' => 70,
                'image' => null,
                'expiration_date' => now()->addYears(1),
                'is_active' => true,
                'category_name' => 'مضادات حيوية',
            ],
            [
                'name' => 'دوكسيسيكلين 100 ملغ',
                'description' => 'مضاد حيوي واسع المجال، يستخدم لعلاج حب الشباب، التهابات الجهاز التنفسي، والتهابات المسالك البولية.',
                'general_stock' => 60,
                'image' => null,
                'expiration_date' => now()->addYears(1),
                'is_active' => true,
                'category_name' => 'مضادات حيوية',
            ],

            // فيتامينات
            [
                'name' => 'فيتامين سي 1000 ملغ',
                'description' => 'مكمل غذائي لتقوية المناعة، يساعد في مقاومة نزلات البرد والإنفلونزا، ويعزز صحة الجلد والأوعية الدموية.',
                'general_stock' => 200,
                'image' => null,
                'expiration_date' => now()->addYears(3),
                'is_active' => true,
                'category_name' => 'فيتامينات',
            ],
            [
                'name' => 'فيتامين د 50000 وحدة',
                'description' => 'مكمل غذائي لتعزيز صحة العظام والأسنان، يساعد في امتصاص الكالسيوم، ويدعم جهاز المناعة.',
                'general_stock' => 150,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'فيتامينات',
            ],
            [
                'name' => 'فيتامين ب المركب',
                'description' => 'مكمل غذائي يحتوي على مجموعة فيتامينات ب (B1, B2, B3, B5, B6, B12)، يدعم الطاقة والأعصاب.',
                'general_stock' => 120,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'فيتامينات',
            ],
            [
                'name' => 'فيتامين E 400 وحدة',
                'description' => 'مضاد أكسدة قوي، يدعم صحة الجلد والشعر، ويحمي الخلايا من التلف.',
                'general_stock' => 100,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'فيتامينات',
            ],

            // مكملات غذائية
            [
                'name' => 'أوميغا 3 1000 ملغ',
                'description' => 'مكمل غذائي يحتوي على أحماض دهنية أساسية، يدعم صحة القلب والأوعية الدموية، ويحسن الذاكرة.',
                'general_stock' => 180,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مكملات غذائية',
            ],
            [
                'name' => 'زنك 50 ملغ',
                'description' => 'مكمل غذائي لتعزيز المناعة، يدعم صحة الجلد والشعر، ويساعد في التئام الجروح.',
                'general_stock' => 130,
                'image' => null,
                'expiration_date' => now()->addYears(3),
                'is_active' => true,
                'category_name' => 'مكملات غذائية',
            ],
            [
                'name' => 'مغنيسيوم 400 ملغ',
                'description' => 'مكمل غذائي لتحسين النوم، يقلل التوتر، ويدعم صحة العضلات والأعصاب.',
                'general_stock' => 110,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مكملات غذائية',
            ],
            [
                'name' => 'حديد 50 ملغ',
                'description' => 'مكمل غذائي لعلاج فقر الدم، يزيد من إنتاج خلايا الدم الحمراء، ويمنع التعب والإرهاق.',
                'general_stock' => 90,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مكملات غذائية',
            ],

            // أدوية ضغط
            [
                'name' => 'لوبريسور 50 ملغ',
                'description' => 'علاج لارتفاع ضغط الدم، يحتوي على ميتوبرولول، يعمل على خفض معدل ضربات القلب وضغط الدم.',
                'general_stock' => 70,
                'image' => null,
                'expiration_date' => now()->addYears(1),
                'is_active' => true,
                'category_name' => 'أدوية ضغط',
            ],
            [
                'name' => 'نورفاسك 5 ملغ',
                'description' => 'علاج لارتفاع ضغط الدم وأمراض القلب، يحتوي على أملوديبين، يعمل على توسيع الأوعية الدموية.',
                'general_stock' => 80,
                'image' => null,
                'expiration_date' => now()->addYears(1),
                'is_active' => true,
                'category_name' => 'أدوية ضغط',
            ],
            [
                'name' => 'كابتوبريل 25 ملغ',
                'description' => 'علاج لارتفاع ضغط الدم وفشل القلب، مثبط للإنزيم المحول للأنجيوتنسين (ACE inhibitor).',
                'general_stock' => 60,
                'image' => null,
                'expiration_date' => now()->addYears(1),
                'is_active' => true,
                'category_name' => 'أدوية ضغط',
            ],

            // أدوية سكري
            [
                'name' => 'ميتفورمين 500 ملغ',
                'description' => 'علاج لمرض السكري من النوع الثاني، يساعد على خفض مستوى السكر في الدم.',
                'general_stock' => 120,
                'image' => null,
                'expiration_date' => now()->addYears(1),
                'is_active' => true,
                'category_name' => 'أدوية سكري',
            ],
            [
                'name' => 'دياميكرون 60 ملغ',
                'description' => 'علاج لمرض السكري من النوع الثاني، يحفز البنكرياس على إفراز الأنسولين.',
                'general_stock' => 85,
                'image' => null,
                'expiration_date' => now()->addYears(1),
                'is_active' => true,
                'category_name' => 'أدوية سكري',
            ],

            // مضادات هيستامين
            [
                'name' => 'كلاريتين 10 ملغ',
                'description' => 'مضاد للهيستامين لعلاج الحساسية، يخفف أعراض العطس، سيلان الأنف، والحكة.',
                'general_stock' => 140,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مضادات هيستامين',
            ],
            [
                'name' => 'زيرتيك 10 ملغ',
                'description' => 'مضاد للهيستامين لعلاج الحساسية الموسمية، يخفف حكة العينين، والعطس.',
                'general_stock' => 130,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مضادات هيستامين',
            ],
            [
                'name' => 'تلفاست 180 ملغ',
                'description' => 'مضاد للهيستامين غير مسبب للنعاس، لعلاج أعراض الحساسية المزمنة.',
                'general_stock' => 100,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'مضادات هيستامين',
            ],

            // عناية بالبشرة
            [
                'name' => 'كريم فيوسيدين',
                'description' => 'مضاد حيوي موضعي لعلاج التهابات الجلد البكتيرية، الجروح، والحروق السطحية.',
                'general_stock' => 90,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'عناية بالبشرة',
            ],
            [
                'name' => 'كريم كيناكومب',
                'description' => 'كريم مضاد للالتهابات والحساسية، لعلاج الأكزيما والتهابات الجلد.',
                'general_stock' => 80,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'عناية بالبشرة',
            ],
            [
                'name' => 'مرهم نيسبورين',
                'description' => 'مرهم مضاد حيوي للوقاية من التهابات الجروح والحروق البسيطة.',
                'general_stock' => 110,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'عناية بالبشرة',
            ],
            [
                'name' => 'كريم ديفرين',
                'description' => 'علاج لحب الشباب، يحتوي على أدا بالين، يساعد على تنظيف المسام.',
                'general_stock' => 70,
                'image' => null,
                'expiration_date' => now()->addYears(2),
                'is_active' => true,
                'category_name' => 'عناية بالبشرة',
            ],
        ];

        $count = 0;
        foreach ($medicines as $medicine) {
            // الحصول على ID الفئة من الاسم
            $categoryId = $categories[$medicine['category_name']] ?? null;

            if (!$categoryId) {
                $this->command->error("❌ الفئة '{$medicine['category_name']}' غير موجودة! تخطي الدواء: {$medicine['name']}");
                continue;
            }

            DB::table('medicines')->insert([
                'name' => $medicine['name'],
                'description' => $medicine['description'],
                'general_stock' => $medicine['general_stock'],
                'image' => $medicine['image'],
                'expiration_date' => $medicine['expiration_date'],
                'is_active' => $medicine['is_active'],
                'category_id' => $categoryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->command->info("✅ تم إضافة {$count} دواء بنجاح!");
    }
}
