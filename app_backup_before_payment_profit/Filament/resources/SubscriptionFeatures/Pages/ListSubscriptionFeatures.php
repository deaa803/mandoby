<?php

namespace App\Filament\Resources\SubscriptionFeatures\Pages;

use App\Filament\Resources\SubscriptionFeatures\SubscriptionFeatureResource;
use App\Models\SubscriptionFeature;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionFeatures extends ListRecords
{
    protected static string $resource = SubscriptionFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createDefaultFeatures')
                ->label('إضافة الميزات الثلاث معًا')
                ->icon('heroicon-o-squares-plus')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('إضافة ميزات الاشتراك الأساسية')
                ->modalDescription('سيتم إنشاء أو تحديث ميزات: موديلات 3D، التقارير المتقدمة، والإعلانات والعروض.')
                ->modalSubmitActionLabel('نعم، أضف الميزات الثلاث')
                ->modalCancelActionLabel('إلغاء')
                ->action(function (): void {
                    foreach (SubscriptionFeatureResource::defaultFeatures() as $key => $feature) {
                        SubscriptionFeature::query()->updateOrCreate(
                            ['key' => $key],
                            [
                                'name' => $feature['name'],
                                'description' => $feature['description'],
                                'is_active' => true,
                            ],
                        );
                    }

                    Notification::make()
                        ->success()
                        ->title('تم تجهيز ميزات الاشتراك')
                        ->body('تم إنشاء أو تحديث ميزات 3D والتقارير والإعلانات بنجاح.')
                        ->send();
                }),

            CreateAction::make()
                ->label('إضافة ميزة واحدة'),
        ];
    }
}
