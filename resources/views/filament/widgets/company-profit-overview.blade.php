<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            مستحقات المنصة حسب الشركة
        </x-slot>

        <x-slot name="description">
            العمولة المستحقة بنسبة {{ number_format((float) $commissionPercentage, 2) }}٪ من دفعات الطلبات، مع بيان المقبوض والمتبقي.
        </x-slot>

        <div class="space-y-3" dir="rtl">
            @forelse ($companies as $index => $company)
                <a href="{{ $company->admin_url }}" class="company-profit-row">
                    <div class="company-profit-rank">{{ $index + 1 }}</div>

                    <div class="company-profit-avatar">
                        @if ($company->logo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($company->logo) }}" alt="{{ $company->name_company }}">
                        @else
                            <span>{{ mb_substr($company->name_company ?: '?', 0, 1) }}</span>
                        @endif
                    </div>

                    <div class="company-profit-info">
                        <div class="company-profit-title">
                            <strong>{{ $company->name_company }}</strong>
                            <span class="company-profit-status company-profit-status--{{ $company->platform_status }}">
                                {{ $company->platform_status_label }}
                            </span>
                        </div>

                        <div class="company-profit-numbers">
                            <small>
                                المستحق:
                                <b>{{ number_format((float) $company->platform_profit, 2) }}</b>
                            </small>
                            <small>
                                المدفوع:
                                <b>{{ number_format((float) $company->platform_collected, 2) }}</b>
                            </small>
                            @if ((float) $company->platform_pending > 0)
                                <small>
                                    قيد المراجعة:
                                    <b>{{ number_format((float) $company->platform_pending, 2) }}</b>
                                </small>
                            @endif
                        </div>
                    </div>

                    <div class="company-profit-value">
                        <small>المتبقي</small>
                        <strong>{{ number_format((float) $company->platform_remaining, 2) }}</strong>
                        <small>{{ $currency }}</small>
                    </div>
                </a>
            @empty
                <div class="company-profit-empty">
                    لا توجد شركات أو مستحقات مسجلة حتى الآن.
                </div>
            @endforelse
        </div>

        <style>
            .company-profit-row {
                display: grid;
                grid-template-columns: 1.8rem 2.65rem minmax(0, 1fr) auto;
                align-items: center;
                gap: .7rem;
                padding: .78rem;
                border: 1px solid var(--supplier-border, rgba(15,23,42,.08));
                border-radius: .95rem;
                background: color-mix(in srgb, var(--supplier-card, #fff) 94%, var(--supplier-gold, #d5b22f) 6%);
                text-decoration: none;
                transition: transform .16s ease, border-color .16s ease;
            }
            .company-profit-row:hover {
                transform: translateY(-1px);
                border-color: rgba(213,178,47,.48);
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
            .company-profit-title { display: flex; align-items: center; gap: .45rem; flex-wrap: wrap; }
            .company-profit-title strong {
                overflow: hidden;
                color: var(--supplier-text, #111827);
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: .84rem;
            }
            .company-profit-status {
                padding: .13rem .42rem;
                border-radius: 999px;
                font-size: .61rem;
                font-weight: 800;
                background: rgba(100,116,139,.12);
                color: #64748b;
            }
            .company-profit-status--paid { color: #15803d; background: rgba(34,197,94,.13); }
            .company-profit-status--partial { color: #a16207; background: rgba(234,179,8,.14); }
            .company-profit-status--unpaid { color: #be123c; background: rgba(244,63,94,.13); }
            .company-profit-status--overpaid { color: #0369a1; background: rgba(14,165,233,.13); }
            .company-profit-numbers { display: flex; gap: .55rem; flex-wrap: wrap; margin-top: .25rem; }
            .company-profit-numbers small,
            .company-profit-value small { color: var(--supplier-muted, #7c7f86); font-size: .67rem; }
            .company-profit-numbers b { color: var(--supplier-text, #111827); }
            .company-profit-value { text-align: end; }
            .company-profit-value strong { display: block; color: #be123c; font-size: .9rem; }
            .dark .company-profit-value strong { color: #fb7185; }
            .company-profit-empty { padding: 2rem 1rem; text-align: center; color: var(--supplier-muted, #7c7f86); }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>
