<?php

namespace App\Filament\Resources\Product3DModels\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product3DModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_detail_id')
                    ->relationship(
                        name: 'productDetail',
                        titleAttribute: 'id',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->with(['product', 'company']),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (Model $record): string => sprintf(
                            '%s — %s — #%d',
                            $record->product?->name ?? 'منتج',
                            $record->company?->name_company ?? 'شركة',
                            $record->id,
                        ),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('تفاصيل المنتج'),

                FileUpload::make('source_image')
                    ->image()
                    ->disk('public')
                    ->directory('product-3d/source-images')
                    ->visibility('public')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->label('الصورة المصدر'),

                FileUpload::make('model_file')
                    ->disk('public')
                    ->directory('product-3d/models')
                    ->visibility('public')
                    ->downloadable()
                    ->nullable()
                    ->label('ملف المودل'),

                FileUpload::make('thumbnail')
                    ->image()
                    ->disk('public')
                    ->directory('product-3d/thumbnails')
                    ->visibility('public')
                    ->nullable()
                    ->label('صورة المعاينة'),

                Select::make('status')
                    ->options([
                        'pending' => 'بانتظار المعالجة',
                        'processing' => 'قيد المعالجة',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false)
                    ->label('الحالة'),

                TextInput::make('progress')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->required()
                    ->suffix('%')
                    ->label('نسبة التقدم'),

                Textarea::make('error_message')
                    ->rows(3)
                    ->nullable()
                    ->columnSpanFull()
                    ->label('رسالة الخطأ'),

                KeyValue::make('metadata')
                    ->nullable()
                    ->columnSpanFull()
                    ->label('بيانات إضافية'),

                DateTimePicker::make('generated_at')
                    ->nullable()
                    ->label('تاريخ اكتمال التوليد'),
            ]);
    }
}
