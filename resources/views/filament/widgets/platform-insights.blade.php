<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">مؤشرات تشغيلية</x-slot>
        <x-slot name="description">قراءة سريعة لصحة المنصة والتحصيل</x-slot>

        <div class="insights-grid" dir="rtl">
            <div class="insight-card insight-card--wide">
                <div class="insight-head"><span>معدل إتمام الطلبات</span><strong>{{ number_format($deliveryRate, 1) }}٪</strong></div>
                <div class="insight-progress"><i style="width: {{ min(100, $deliveryRate) }}%"></i></div>
                <small>نسبة الطلبات المسلّمة من إجمالي الطلبات غير الملغاة</small>
            </div>
            <div class="insight-card"><span class="insight-icon insight-icon--red">!</span><small>المبالغ المتبقية</small><strong>{{ number_format($outstanding, 0) }}</strong><em>{{ $currency }}</em></div>
            <div class="insight-card"><span class="insight-icon insight-icon--green">✓</span><small>دفعات هذا الشهر</small><strong>{{ number_format($monthlyPayments, 0) }}</strong><em>{{ $currency }}</em></div>
            <div class="insight-card"><span class="insight-icon insight-icon--blue">◆</span><small>اشتراكات فعالة</small><strong>{{ number_format($activeSubscriptions) }}</strong><em>شركة</em></div>
            <div class="insight-card"><span class="insight-icon insight-icon--violet">3D</span><small>نماذج جاهزة</small><strong>{{ number_format($completedModels) }}</strong><em>نموذج</em></div>
        </div>
    </x-filament::section>

    <style>
        .insights-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem}.insight-card{position:relative;padding:.9rem;border:1px solid var(--md-border);border-radius:1rem;background:color-mix(in srgb,var(--md-card-solid) 76%,transparent);overflow:hidden}.insight-card--wide{grid-column:1/-1}.insight-head{display:flex;align-items:center;justify-content:space-between;gap:1rem}.insight-head span{color:var(--md-text);font-size:.8rem;font-weight:850}.insight-head strong{color:#10b981;font-size:1.1rem}.insight-progress{height:7px;margin:.7rem 0 .45rem;border-radius:999px;background:color-mix(in srgb,var(--md-border) 85%,transparent);overflow:hidden}.insight-progress i{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#10b981,#34d399)}.insight-card>small{display:block;color:var(--md-muted);font-size:.68rem}.insight-card>strong{display:block;margin-top:.45rem;color:var(--md-text);font-size:1.15rem}.insight-card>em{color:var(--md-muted);font-size:.6rem;font-style:normal}.insight-icon{position:absolute;inset-inline-end:.75rem;top:.75rem;display:grid;place-items:center;width:1.8rem;height:1.8rem;border-radius:.6rem;font-size:.65rem;font-weight:900}.insight-icon--red{color:#be123c;background:rgba(244,63,94,.13)}.insight-icon--green{color:#047857;background:rgba(16,185,129,.14)}.insight-icon--blue{color:#2563eb;background:rgba(59,130,246,.14)}.insight-icon--violet{color:#7c3aed;background:rgba(139,92,246,.14)}
    </style>
</x-filament-widgets::widget>
