<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $isArabic ? 'أرباح المنصة حسب الشركة' : 'Platform profit by company' }}
        </x-slot>
        <x-slot name="description">
            {{ $isArabic ? 'العمولة المتوقعة من كل شركة، باستثناء الطلبات الملغاة.' : 'Expected commission from each company, excluding cancelled orders.' }}
        </x-slot>

        <div class="space-y-3" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
            @forelse ($companies as $index => $company)
                <div class="company-profit-row">
                    <div class="company-profit-rank">{{ $index + 1 }}</div>

                    <div class="company-profit-avatar">
                        @if ($company->logo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($company->logo) }}" alt="{{ $company->name_company }}">
                        @else
                            <span>{{ mb_substr($company->name_company ?: '?', 0, 1) }}</span>
                        @endif
                    </div>

                    <div class="company-profit-info">
                        <strong>{{ $company->name_company }}</strong>
                        <small>
                            {{ number_format((int) $company->orders_count) }}
                            {{ $isArabic ? 'طلب' : 'orders' }}
                        </small>
                    </div>

                    <div class="company-profit-value">
                        <strong>{{ number_format((float) $company->platform_profit, 2) }}</strong>
                        <small>{{ $currency }}</small>
                    </div>
                </div>
            @empty
                <div class="company-profit-empty">
                    {{ $isArabic ? 'لا توجد أرباح مسجلة حتى الآن.' : 'No recorded profit yet.' }}
                </div>
            @endforelse
        </div>

        <style>
            .company-profit-row {
                display: grid;
                grid-template-columns: 1.8rem 2.65rem minmax(0, 1fr) auto;
                align-items: center;
                gap: .7rem;
                padding: .7rem;
                border: 1px solid var(--supplier-border, rgba(15,23,42,.08));
                border-radius: .9rem;
                background: color-mix(in srgb, var(--supplier-card, #fff) 94%, var(--supplier-gold, #d5b22f) 6%);
            }
            .company-profit-rank {
                display: grid;
                place-items: center;
                width: 1.55rem;
                height: 1.55rem;
                border-radius: 999px;
                color: #6d5400;
                background: rgba(213,178,47,.2);
                font-size: .72rem;
                font-weight: 900;
            }
            .company-profit-avatar {
                display: grid;
                place-items: center;
                width: 2.55rem;
                height: 2.55rem;
                overflow: hidden;
                border-radius: .72rem;
                color: #111827;
                background: linear-gradient(135deg, #f2df86, #d5b22f);
                font-weight: 900;
            }
            .company-profit-avatar img { width: 100%; height: 100%; object-fit: cover; }
            .company-profit-info { min-width: 0; }
            .company-profit-info strong {
                display: block;
                overflow: hidden;
                color: var(--supplier-text, #111827);
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: .85rem;
            }
            .company-profit-info small,
            .company-profit-value small { color: var(--supplier-muted, #7c7f86); font-size: .7rem; }
            .company-profit-value { text-align: end; }
            .company-profit-value strong { display: block; color: #b18a0d; font-size: .88rem; }
            .dark .company-profit-value strong { color: #e2c95c; }
            .company-profit-empty { padding: 2rem 1rem; text-align: center; color: var(--supplier-muted, #7c7f86); }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>
