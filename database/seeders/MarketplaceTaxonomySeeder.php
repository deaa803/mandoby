<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Feature;
use Illuminate\Database\Seeder;

class MarketplaceTaxonomySeeder extends Seeder
{
    /**
     * Seed all marketplace categories and reusable product/service features.
     *
     * The seeder is idempotent: it can be executed more than once without
     * creating duplicate rows.
     */
    public function run(): void
    {
        $this->removeExcludedFeatures();
        $this->seedCategories();
        $this->seedFeatures();

        $this->command?->info('Marketplace categories and features seeded successfully.');
    }

    /**
     * Remove features explicitly excluded by the project owner.
     *
     * Deleting a feature also removes its linked product-detail values through
     * the existing database cascade on feature_product_details.feature_id.
     */
    private function removeExcludedFeatures(): void
    {
        Feature::query()
            ->whereIn('name', [
                'اللون',
                'درجة اللون',
                'الباركود',
                'نوع الأداة الموسيقية',
                'نوع الترخيص الرقمي',
                'نوع الشعر',
                'نوع التغليف',
                'مدة التخزين',
                'نوع الغلاف',
                'نوع الفن',
                'نوع الفن أو الهواية',
                'رقم الموديل',
                'المادة',
                'الخامة الفنية',
                'عدد السرعات',
            ])
            ->delete();
    }

    private function seedCategories(): void
    {
        $categories = [
            // Existing categories.
            'منزل وحديقة' => 'الأدوات المنزلية، مستلزمات المطبخ، الديكور، الزراعة والحدائق.',
            'ألبسة وإكسسوارات' => 'الملابس، الأحذية، الحقائب، الساعات والإكسسوارات.',
            'إلكترونيات' => 'الأجهزة الإلكترونية، الهواتف، الحواسيب والملحقات التقنية.',
            'أطفال' => 'ملابس الأطفال، مستلزمات الرضع ومنتجات العناية بالأطفال.',
            'أثاث' => 'الأثاث المنزلي والمكتبي ومستلزمات التجهيز والديكور.',
            'ألعاب' => 'ألعاب الأطفال، الألعاب التعليمية والترفيهية.',
            'قرطاسية' => 'الدفاتر، الأقلام، اللوازم المدرسية ومستلزمات المكاتب.',
            'كاميرات وبصريات' => 'الكاميرات، العدسات، المناظير وملحقات التصوير.',
            'مستلزمات الحيوانات' => 'الأغذية، أدوات العناية وإكسسوارات الحيوانات الأليفة.',

            // New categories requested by the user.
            'رياضة ولياقة بدنية' => 'أدوات الجيم، الملابس الرياضية، الدراجات ومستلزمات التدريب.',
            'عناية شخصية وتجميل' => 'المكياج، العطور، العناية بالبشرة والشعر وأدوات التجميل.',
            'كتب ومجلات' => 'الكتب والمجلات الورقية والإلكترونية والمواد التعليمية.',
            'معدات وأدوات صناعية' => 'أدوات الصيانة والكهرباء والنجارة والمعدات المهنية والصناعية.',
            'أطعمة ومشروبات' => 'المنتجات الغذائية المغلفة، المعلبات، المشروبات والمواد التموينية.',
            'هوايات وفنون' => 'أدوات الرسم، الموسيقى، الحرف اليدوية ومستلزمات الهوايات.',
            'خدمات' => 'الخدمات المهنية، الصيانة، التدريب، التصميم والخدمات القابلة للحجز.',
        ];

        foreach ($categories as $name => $description) {
            Category::updateOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
        }
    }

    private function seedFeatures(): void
    {
        $features = [
            // General features.
            'العلامة التجارية',
            'اسم الشركة المصنعة',
            'بلد المنشأ',
            'النوع',
            'حالة المنتج',
            'المقاس',
            'الوزن',
            'الأبعاد',
            'السعة',
            'الكمية داخل العبوة',
            'محتويات العبوة',
            'الضمان',
            'مدة الضمان',
            'تعليمات الاستخدام',
            'تعليمات العناية',

            // Clothing, sport and children.
            'الجنس المستهدف',
            'الفئة العمرية',
            'نوع القماش',
            'الموسم',
            'نوع الرياضة',
            'مستوى الاستخدام',
            'الحمولة القصوى',
            'مقاس العجلة',
            'مقاومة الماء',

            // Personal care and beauty.
            'نوع البشرة',
            'الرائحة',
            'المكونات',
            'طريقة الاستخدام',
            'خالٍ من',
            'مختبر جلدياً',

            // Books and magazines.
            'المؤلف',
            'الناشر',
            'اللغة',
            'عدد الصفحات',
            'سنة النشر',
            'رقم ISBN',
            'صيغة المحتوى',

            // Industrial tools and electronics.
            'مصدر الطاقة',
            'الجهد الكهربائي',
            'القدرة',
            'التردد',
            'سرعة الدوران',
            'نوع البطارية',
            'سعة البطارية',
            'مدة التشغيل',
            'معايير السلامة',
            'الاستخدام الموصى به',

            // Food and beverages.
            'النكهة',
            'تاريخ الإنتاج',
            'تاريخ الانتهاء',
            'مدة الصلاحية',
            'شروط التخزين',
            'القيمة الغذائية',
            'مسببات الحساسية',
            'حلال',
            'عضوي',

            // Hobbies, arts and music.
            'مستوى المهارة',
            'عدد القطع',

            // Services.
            'نوع الخدمة',
            'مدة تنفيذ الخدمة',
            'مكان تقديم الخدمة',
            'نطاق التغطية',
            'يتطلب حجزاً مسبقاً',
            'نوع التسعير',
            'الخبرة المطلوبة',
            'ما تشمله الخدمة',
            'ما لا تشمله الخدمة',
            'سياسة الإلغاء',
        ];

        foreach (array_unique($features) as $name) {
            Feature::firstOrCreate(['name' => $name]);
        }
    }
}
