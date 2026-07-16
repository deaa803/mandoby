<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'لوحة التحكم';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected ?string $heading = 'لوحة التحكم';

    protected ?string $subheading = 'نظرة عامة على أداء المنصة';

    /**
     * عمود واحد على الهاتف، وعمودان على الشاشات الأكبر.
     */
    public function getColumns(): array|int
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }
}
