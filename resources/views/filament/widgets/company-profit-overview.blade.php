<x-filament-widgets::widget>
    <x-filament::section class="profit-widget">
        <x-slot name="heading">أرباح المنصة حسب الشركة</x-slot>
        <x-slot name="description">أعلى الشركات مساهمة في عمولة المنصة</x-slot>

        <div class="profit-list" dir="rtl">
            @forelse ($companies as $index => $company)
                @php($percent = min(100, ((float) $company->platform_profit / $maxProfit) * 100))
                <a href="{{ $company->admin_url }}" class="profit-row">
                    <span class="profit-rank">{{ $index + 1 }}</span>
                    <span class="profit-avatar">
                        @if ($company->logo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($company->logo) }}" alt="">
                        @else
                            {{ mb_substr($company->name_company, 0, 1) }}
                        @endif
                    </span>
                    <span class="profit-copy">
                        <strong>{{ $company->name_company }}</strong>
                        <small>{{ number_format($company->orders_count) }} طلبات</small>
                        <i><b style="width: {{ $percent }}%"></b></i>
                    </span>
                    <span class="profit-value">{{ number_format($company->platform_profit, 2) }}<small>{{ $currency }}</small></span>
                </a>
            @empty
                <div class="profit-empty">لا توجد بيانات أرباح بعد.</div>
            @endforelse
        </div>
    </x-filament::section>

    <style>
        .profit-list{display:flex;flex-direction:column;gap:.55rem;max-height:320px;overflow:auto;padding-inline-end:.2rem}
        .profit-row{display:grid;grid-template-columns:2rem 2.6rem minmax(0,1fr) auto;align-items:center;gap:.7rem;padding:.72rem;border:1px solid var(--md-border);border-radius:1rem;background:color-mix(in srgb,var(--md-card-solid) 76%,transparent);transition:.18s ease}
        .profit-row:hover{transform:translateX(-3px);border-color:rgba(212,168,23,.45);background:color-mix(in srgb,var(--md-gold) 6%,var(--md-card-solid))}
        .profit-rank{display:grid;place-items:center;width:1.7rem;height:1.7rem;border-radius:.55rem;color:#8a6500;background:var(--md-gold-soft);font-size:.72rem;font-weight:900}
        .profit-avatar{display:grid;place-items:center;width:2.5rem;height:2.5rem;border-radius:.8rem;overflow:hidden;color:#8a6500;background:var(--md-gold-soft);font-weight:900}.profit-avatar img{width:100%;height:100%;object-fit:cover}
        .profit-copy{min-width:0}.profit-copy strong{display:block;color:var(--md-text);font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.profit-copy small{display:block;margin-top:.12rem;color:var(--md-muted);font-size:.68rem}.profit-copy i{display:block;height:3px;margin-top:.42rem;border-radius:999px;background:color-mix(in srgb,var(--md-border) 85%,transparent);overflow:hidden}.profit-copy i b{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#f0cf55,var(--md-gold))}
        .profit-value{text-align:left;color:var(--md-gold);font-size:.8rem;font-weight:900;white-space:nowrap}.profit-value small{display:block;color:var(--md-muted);font-size:.58rem;font-weight:700}.profit-empty{padding:2rem;text-align:center;color:var(--md-muted)}
    </style>
</x-filament-widgets::widget>
