<x-filament-widgets::widget>
    <div class="mandoby-hero" dir="rtl">
        <div class="mandoby-hero__glow mandoby-hero__glow--one"></div>
        <div class="mandoby-hero__glow mandoby-hero__glow--two"></div>

        <div class="mandoby-hero__content">
            <div class="mandoby-hero__intro">
                <span class="mandoby-hero__badge">مركز قيادة المنصة</span>
                <h2>{{ $greeting }} 👋</h2>
                <p>{{ $dateLabel }} — تابع أهم مؤشرات منصتك واتخذ قراراتك بسرعة.</p>
            </div>

            <div class="mandoby-hero__metrics">
                <div class="mandoby-mini-metric">
                    <span>طلبات اليوم</span>
                    <strong>{{ number_format($todayOrders) }}</strong>
                </div>
                <div class="mandoby-mini-metric">
                    <span>قيد التنفيذ</span>
                    <strong>{{ number_format($pendingOrders) }}</strong>
                </div>
                <div class="mandoby-mini-metric">
                    <span>مبيعات اليوم</span>
                    <strong>{{ number_format($todaySales, 2) }}</strong>
                    <small>{{ $currency }}</small>
                </div>
                <div class="mandoby-mini-metric mandoby-mini-metric--gold">
                    <span>ربح المنصة اليوم</span>
                    <strong>{{ number_format($todayProfit, 2) }}</strong>
                    <small>{{ $currency }}</small>
                </div>
            </div>

            <div class="mandoby-hero__actions">
                <a href="{{ $links['company'] }}" class="mandoby-quick-action">
                    <x-filament::icon icon="heroicon-o-building-office-2" />
                    <span>إضافة شركة</span>
                </a>
                <a href="{{ $links['store'] }}" class="mandoby-quick-action">
                    <x-filament::icon icon="heroicon-o-building-storefront" />
                    <span>إضافة متجر</span>
                </a>
                <a href="{{ $links['product'] }}" class="mandoby-quick-action">
                    <x-filament::icon icon="heroicon-o-cube" />
                    <span>إضافة منتج</span>
                </a>
                <a href="{{ $links['order'] }}" class="mandoby-quick-action mandoby-quick-action--primary">
                    <x-filament::icon icon="heroicon-o-plus" />
                    <span>إنشاء طلب</span>
                </a>
            </div>
        </div>
    </div>

    <style>
        :root {
            --mandoby-bg: #faf7ef;
            --mandoby-card: #ffffff;
            --mandoby-card-soft: #fffaf0;
            --mandoby-text: #171a21;
            --mandoby-muted: #777b84;
            --mandoby-border: rgba(111, 88, 28, .13);
            --mandoby-gold: #d4aa29;
            --mandoby-gold-dark: #9b7410;
            --mandoby-shadow: 0 18px 48px rgba(83, 62, 14, .08);
        }
        .dark {
            --mandoby-bg: #06101d;
            --mandoby-card: #0b1727;
            --mandoby-card-soft: #0e1d30;
            --mandoby-text: #f5f7fb;
            --mandoby-muted: #91a0b4;
            --mandoby-border: rgba(133, 154, 181, .14);
            --mandoby-gold: #dfbc43;
            --mandoby-gold-dark: #f0d36c;
            --mandoby-shadow: 0 20px 55px rgba(0, 0, 0, .25);
        }
        .fi-body, .fi-layout, .fi-main-ctn { background: var(--mandoby-bg) !important; }
        .fi-sidebar, .fi-topbar nav {
            border-color: var(--mandoby-border) !important;
            background: color-mix(in srgb, var(--mandoby-card) 96%, transparent) !important;
        }
        .fi-sidebar-item.fi-active > a {
            background: color-mix(in srgb, var(--mandoby-gold) 15%, transparent) !important;
        }
        .fi-sidebar-item.fi-active svg, .fi-sidebar-item.fi-active span { color: var(--mandoby-gold) !important; }
        .fi-section, .fi-wi-chart, .fi-wi-stats-overview-stat, .fi-ta-ctn {
            border: 1px solid var(--mandoby-border) !important;
            border-radius: 1.25rem !important;
            background: linear-gradient(145deg, var(--mandoby-card), var(--mandoby-card-soft)) !important;
            box-shadow: var(--mandoby-shadow) !important;
        }
        .fi-wi-stats-overview-stat { overflow: hidden; transition: transform .2s ease, border-color .2s ease; }
        .fi-wi-stats-overview-stat:hover { transform: translateY(-3px); border-color: color-mix(in srgb, var(--mandoby-gold) 40%, var(--mandoby-border)) !important; }
        .mandoby-hero {
            position: relative; overflow: hidden; border: 1px solid var(--mandoby-border); border-radius: 1.6rem;
            color: var(--mandoby-text); background:
                radial-gradient(circle at 5% 15%, rgba(212,170,41,.20), transparent 26%),
                radial-gradient(circle at 92% 85%, rgba(44,123,229,.14), transparent 28%),
                linear-gradient(135deg, var(--mandoby-card), var(--mandoby-card-soft));
            box-shadow: var(--mandoby-shadow);
        }
        .mandoby-hero__content { position: relative; z-index: 2; padding: 1.45rem; }
        .mandoby-hero__intro h2 { margin: .55rem 0 .25rem; font-size: clamp(1.45rem, 3vw, 2.25rem); font-weight: 900; letter-spacing: -.03em; }
        .mandoby-hero__intro p { margin: 0; color: var(--mandoby-muted); }
        .mandoby-hero__badge { display: inline-flex; padding: .38rem .7rem; border-radius: 999px; color: var(--mandoby-gold-dark); background: rgba(212,170,41,.14); font-size: .76rem; font-weight: 900; }
        .mandoby-hero__metrics { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: .75rem; margin-top: 1.25rem; }
        .mandoby-mini-metric { position: relative; padding: .9rem; border: 1px solid var(--mandoby-border); border-radius: 1rem; background: color-mix(in srgb, var(--mandoby-card) 84%, transparent); backdrop-filter: blur(10px); }
        .mandoby-mini-metric span { display: block; color: var(--mandoby-muted); font-size: .76rem; font-weight: 700; }
        .mandoby-mini-metric strong { display: inline-block; margin-top: .28rem; font-size: 1.35rem; font-weight: 900; }
        .mandoby-mini-metric small { margin-inline-start: .3rem; color: var(--mandoby-muted); font-size: .68rem; }
        .mandoby-mini-metric--gold { border-color: rgba(212,170,41,.38); background: linear-gradient(135deg, rgba(212,170,41,.15), rgba(212,170,41,.05)); }
        .mandoby-hero__actions { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: 1rem; }
        .mandoby-quick-action { display: inline-flex; align-items: center; gap: .45rem; min-height: 2.55rem; padding: .55rem .8rem; border: 1px solid var(--mandoby-border); border-radius: .85rem; color: var(--mandoby-text); background: var(--mandoby-card); font-size: .8rem; font-weight: 800; transition: .2s ease; }
        .mandoby-quick-action:hover { transform: translateY(-2px); border-color: rgba(212,170,41,.55); }
        .mandoby-quick-action svg { width: 1.1rem; height: 1.1rem; }
        .mandoby-quick-action--primary { color: #17130a; border-color: transparent; background: linear-gradient(135deg, #ead36f, var(--mandoby-gold)); }
        @media (max-width: 980px) { .mandoby-hero__metrics { grid-template-columns: repeat(2,minmax(0,1fr)); } }
        @media (max-width: 560px) { .mandoby-hero__content { padding: 1rem; } .mandoby-hero__metrics { grid-template-columns: 1fr; } .mandoby-quick-action { flex: 1; justify-content: center; } }
    </style>
</x-filament-widgets::widget>
