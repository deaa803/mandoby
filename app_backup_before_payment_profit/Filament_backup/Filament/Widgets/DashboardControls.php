<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardControls extends Widget
{
    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-controls';
}
