<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">مؤشرات تشغيلية</x-slot>
        <x-slot name="description">ملخص سريع لصحة العمليات والالتزامات المالية.</x-slot>

        <div class="insight-grid" dir="rtl">
            <div class="insight-card insight-card--green">
                <span class="insight-icon"><x-filament::icon icon="heroicon-o-check-circle" /></span>
                <div><small>معدل إتمام الطلبات</small><strong>{{ number_format($deliveryRate, 1) }}٪</strong></div>
            </div>
            <div class="insight-card insight-card--rose">
                <span class="insight-icon"><x-filament::icon icon="heroicon-o-clock" /></span>
                <div><small>المبالغ المتبقية</small><strong>{{ number_format($outstanding, 2) }} <em>{{ $currency }}</em></strong></div>
            </div>
            <div class="insight-card insight-card--blue">
                <span class="insight-icon"><x-filament::icon icon="heroicon-o-banknotes" /></span>
                <div><small>دفعات هذا الشهر</small><strong>{{ number_format($monthlyPayments, 2) }} <em>{{ $currency }}</em></strong></div>
            </div>
            <div class="insight-card insight-card--gold">
                <span class="insight-icon"><x-filament::icon icon="heroicon-o-credit-card" /></span>
                <div><small>الاشتراكات الفعالة</small><strong>{{ number_format($activeSubscriptions) }}</strong></div>
            </div>
            <div class="insight-card insight-card--violet">
                <span class="insight-icon"><x-filament::icon icon="heroicon-o-cube-transparent" /></span>
                <div><small>نماذج 3D الجاهزة</small><strong>{{ number_format($completedModels) }}</strong></div>
            </div>
        </div>

        <style>
            .insight-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; }
            .insight-card { display:flex; align-items:center; gap:.7rem; min-height:5rem; padding:.82rem; border:1px solid var(--mandoby-border,rgba(15,23,42,.08)); border-radius:1rem; background:color-mix(in srgb,var(--mandoby-card,#fff) 95%,var(--accent,#64748b) 5%); }
            .insight-icon { display:grid; place-items:center; flex:0 0 2.45rem; width:2.45rem; height:2.45rem; border-radius:.8rem; color:var(--accent,#64748b); background:color-mix(in srgb,var(--accent,#64748b) 14%,transparent); }
            .insight-icon svg { width:1.25rem; height:1.25rem; }
            .insight-card small { display:block; color:var(--mandoby-muted,#7c7f86); font-size:.7rem; }
            .insight-card strong { display:block; margin-top:.18rem; color:var(--mandoby-text,#111827); font-size:.95rem; font-weight:900; }
            .insight-card em { color:var(--mandoby-muted,#7c7f86); font-size:.62rem; font-style:normal; }
            .insight-card--green{--accent:#16a34a}.insight-card--rose{--accent:#e11d48}.insight-card--blue{--accent:#0284c7}.insight-card--gold{--accent:#d4aa29}.insight-card--violet{--accent:#7c3aed}
            @media(max-width:560px){ .insight-grid { grid-template-columns:1fr; } }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>
