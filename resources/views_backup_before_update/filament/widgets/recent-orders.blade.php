<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">أحدث الطلبات</x-slot>
        <x-slot name="description">آخر العمليات المسجلة على المنصة.</x-slot>
        <x-slot name="headerEnd">
            <a href="{{ $allOrdersUrl }}" class="text-sm font-bold text-primary-600 dark:text-primary-400">عرض الكل</a>
        </x-slot>

        @php
            $statusMap = [
                'pending' => ['قيد الانتظار', 'warning'],
                'preparing' => ['قيد التحضير', 'info'],
                'delivering' => ['قيد التوصيل', 'primary'],
                'delivered' => ['تم التسليم', 'success'],
                'cancelled' => ['ملغي', 'danger'],
            ];
        @endphp

        <div class="space-y-2.5" dir="rtl">
            @forelse ($orders as $order)
                @php([$statusLabel, $statusColor] = $statusMap[$order->status] ?? [$order->status, 'gray'])
                <a href="{{ $order->admin_url }}" class="recent-order-row">
                    <span class="recent-order-id">#{{ $order->id }}</span>
                    <span class="recent-order-main">
                        <strong>{{ $order->store?->name_store ?? $order->store?->user?->name ?? 'متجر غير محدد' }}</strong>
                        <small>{{ $order->created_at?->diffForHumans() }}</small>
                    </span>
                    <span class="recent-order-status recent-order-status--{{ $statusColor }}">{{ $statusLabel }}</span>
                    <span class="recent-order-value">
                        <strong>{{ number_format((float) $order->total_price, 2) }}</strong>
                        <small>{{ $currency }}</small>
                    </span>
                </a>
            @empty
                <div class="recent-orders-empty">لا توجد طلبات حتى الآن.</div>
            @endforelse
        </div>

        <style>
            .recent-order-row { display:grid; grid-template-columns:auto minmax(0,1fr) auto auto; align-items:center; gap:.7rem; padding:.72rem; border:1px solid var(--mandoby-border,rgba(15,23,42,.08)); border-radius:.95rem; color:var(--mandoby-text,#111827); transition:.18s ease; }
            .recent-order-row:hover { transform:translateY(-2px); background:color-mix(in srgb,var(--mandoby-gold,#d4aa29) 6%,transparent); }
            .recent-order-id { color:var(--mandoby-gold-dark,#9b7410); font-size:.75rem; font-weight:900; }
            .recent-order-main { min-width:0; }
            .recent-order-main strong { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:.83rem; }
            .recent-order-main small,.recent-order-value small { color:var(--mandoby-muted,#7c7f86); font-size:.68rem; }
            .recent-order-status { padding:.3rem .5rem; border-radius:999px; font-size:.68rem; font-weight:800; background:rgba(148,163,184,.14); }
            .recent-order-status--success { color:#15803d; background:rgba(34,197,94,.13); }
            .recent-order-status--warning { color:#a16207; background:rgba(234,179,8,.14); }
            .recent-order-status--info { color:#0369a1; background:rgba(14,165,233,.13); }
            .recent-order-status--primary { color:#92400e; background:rgba(245,158,11,.13); }
            .recent-order-status--danger { color:#be123c; background:rgba(244,63,94,.13); }
            .recent-order-value { text-align:left; }
            .recent-order-value strong { display:block; font-size:.82rem; }
            .recent-orders-empty { padding:2rem; text-align:center; color:var(--mandoby-muted,#7c7f86); }
            @media(max-width:560px){ .recent-order-row { grid-template-columns:auto minmax(0,1fr) auto; } .recent-order-status { display:none; } }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>
