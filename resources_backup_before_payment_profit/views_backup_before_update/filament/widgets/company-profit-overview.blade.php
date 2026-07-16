<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">أرباح المنصة حسب الشركة</x-slot>
        <x-slot name="description">العمولة المحققة من كل شركة مع استبعاد الطلبات الملغاة.</x-slot>

        <div class="space-y-2.5" dir="rtl">
            @forelse ($companies as $index => $company)
                <a href="{{ $company->admin_url }}" class="profit-company-row">
                    <span class="profit-company-rank">{{ $index + 1 }}</span>
                    <span class="profit-company-avatar">
                        @if ($company->logo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($company->logo) }}" alt="{{ $company->name_company }}">
                        @else
                            {{ mb_substr($company->name_company ?: '؟', 0, 1) }}
                        @endif
                    </span>
                    <span class="profit-company-info">
                        <strong>{{ $company->name_company }}</strong>
                        <small>{{ number_format((int) $company->orders_count) }} طلب</small>
                        <span class="profit-company-bar"><i style="width: {{ min(100, ((float) $company->platform_profit / $maxProfit) * 100) }}%"></i></span>
                    </span>
                    <span class="profit-company-value">
                        <strong>{{ number_format((float) $company->platform_profit, 2) }}</strong>
                        <small>{{ $currency }}</small>
                    </span>
                </a>
            @empty
                <div class="profit-empty">لا توجد أرباح مسجلة حتى الآن.</div>
            @endforelse
        </div>

        <style>
            .profit-company-row { display:grid; grid-template-columns:1.7rem 2.6rem minmax(0,1fr) auto; align-items:center; gap:.65rem; padding:.68rem; border:1px solid var(--mandoby-border,rgba(15,23,42,.08)); border-radius:.95rem; color:var(--mandoby-text,#111827); background:color-mix(in srgb,var(--mandoby-card,#fff) 94%,var(--mandoby-gold,#d4aa29) 6%); transition:.18s ease; }
            .profit-company-row:hover { transform:translateY(-2px); border-color:rgba(212,170,41,.45); }
            .profit-company-rank { display:grid; place-items:center; width:1.55rem; height:1.55rem; border-radius:999px; color:#6e5200; background:rgba(212,170,41,.2); font-size:.7rem; font-weight:900; }
            .profit-company-avatar { display:grid; place-items:center; width:2.5rem; height:2.5rem; overflow:hidden; border-radius:.72rem; color:#111827; background:linear-gradient(135deg,#f0db7b,#d4aa29); font-weight:900; }
            .profit-company-avatar img { width:100%; height:100%; object-fit:cover; }
            .profit-company-info { min-width:0; }
            .profit-company-info strong { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:.83rem; }
            .profit-company-info small,.profit-company-value small { color:var(--mandoby-muted,#7c7f86); font-size:.68rem; }
            .profit-company-bar { display:block; height:3px; margin-top:.38rem; overflow:hidden; border-radius:999px; background:rgba(148,163,184,.16); }
            .profit-company-bar i { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,#d4aa29,#f0d66a); }
            .profit-company-value { text-align:left; }
            .profit-company-value strong { display:block; color:var(--mandoby-gold-dark,#9b7410); font-size:.84rem; }
            .profit-empty { padding:2rem 1rem; text-align:center; color:var(--mandoby-muted,#7c7f86); }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>
