<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">أحدث الطلبات</x-slot>
        <x-slot name="description">آخر الحركات المسجلة على المنصة</x-slot>
        <x-slot name="headerEnd"><a href="{{ $allOrdersUrl }}" class="orders-all-link">عرض الكل ←</a></x-slot>

        <div class="recent-orders" dir="rtl">
            @forelse ($orders as $order)
                @php
                    $status = match ($order->status) {
                        'pending' => ['قيد الانتظار', 'amber'],
                        'preparing' => ['قيد التحضير', 'blue'],
                        'delivering' => ['قيد التوصيل', 'violet'],
                        'delivered' => ['تم التسليم', 'green'],
                        'cancelled' => ['ملغي', 'red'],
                        default => [$order->status, 'gray'],
                    };
                @endphp
                <a href="{{ $order->admin_url }}" class="recent-order-row">
                    <span class="recent-order-id">#{{ $order->id }}</span>
                    <span class="recent-order-main">
                        <strong>{{ $order->store?->name_store ?? $order->store?->user?->name ?? 'متجر غير محدد' }}</strong>
                        <small>{{ $order->created_at?->diffForHumans() }}</small>
                    </span>
                    <span class="recent-order-status recent-order-status--{{ $status[1] }}">{{ $status[0] }}</span>
                    <span class="recent-order-value">{{ number_format((float) $order->total_price, 0) }}<small>{{ $currency }}</small></span>
                </a>
            @empty
                <div class="recent-orders-empty">لا توجد طلبات بعد.</div>
            @endforelse
        </div>
    </x-filament::section>

    <style>
        .orders-all-link{color:var(--md-gold);font-size:.75rem;font-weight:850}.recent-orders{display:flex;flex-direction:column;gap:.55rem}.recent-order-row{display:grid;grid-template-columns:3rem minmax(0,1fr) auto auto;align-items:center;gap:.7rem;padding:.76rem;border:1px solid var(--md-border);border-radius:1rem;background:color-mix(in srgb,var(--md-card-solid) 76%,transparent);transition:.18s ease}.recent-order-row:hover{transform:translateY(-2px);border-color:rgba(212,168,23,.4)}.recent-order-id{color:var(--md-gold);font-size:.73rem;font-weight:900}.recent-order-main strong{display:block;color:var(--md-text);font-size:.82rem}.recent-order-main small{display:block;color:var(--md-muted);font-size:.67rem;margin-top:.1rem}.recent-order-status{padding:.28rem .48rem;border-radius:999px;font-size:.64rem;font-weight:850;white-space:nowrap}.recent-order-status--amber{color:#a16207;background:rgba(245,158,11,.14)}.recent-order-status--blue{color:#2563eb;background:rgba(59,130,246,.14)}.recent-order-status--violet{color:#7c3aed;background:rgba(139,92,246,.14)}.recent-order-status--green{color:#047857;background:rgba(16,185,129,.14)}.recent-order-status--red{color:#be123c;background:rgba(244,63,94,.13)}.recent-order-status--gray{color:var(--md-muted);background:rgba(148,163,184,.13)}.recent-order-value{text-align:left;color:var(--md-text);font-size:.8rem;font-weight:900;white-space:nowrap}.recent-order-value small{display:block;color:var(--md-muted);font-size:.56rem}.recent-orders-empty{padding:2rem;text-align:center;color:var(--md-muted)}@media(max-width:600px){.recent-order-row{grid-template-columns:2.6rem minmax(0,1fr) auto}.recent-order-status{display:none}}
    </style>
</x-filament-widgets::widget>
