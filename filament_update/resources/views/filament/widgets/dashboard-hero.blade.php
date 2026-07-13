<x-filament-widgets::widget>
    <section class="mandoob-hero" dir="rtl">
        <div class="mandoob-hero__glow mandoob-hero__glow--one"></div>
        <div class="mandoob-hero__glow mandoob-hero__glow--two"></div>

        <div class="mandoob-hero__main">
            <div class="mandoob-hero__copy">
                <span class="mandoob-hero__eyebrow">{{ $greeting }} 👋</span>
                <h2>مركز قيادة المنصة</h2>
                <p>راقب أداء الشركات والطلبات والتحصيلات واتخذ قراراتك من شاشة واحدة.</p>
                <span class="mandoob-hero__date">{{ $dateLabel }}</span>
            </div>

            <div class="mandoob-hero__actions">
                <button type="button" class="mandoob-icon-button" onclick="window.mandoobToggleTheme()" title="تبديل المظهر">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.8 6.8 0 0 0 21 12.8Z"/></svg>
                    <span>ليلي / نهاري</span>
                </button>

                <a class="mandoob-icon-button mandoob-icon-button--gold" href="{{ request()->fullUrlWithQuery(['lang' => $isArabic ? 'en' : 'ar']) }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>
                    <span>{{ $isArabic ? 'English' : 'العربية' }}</span>
                </a>
            </div>
        </div>

        <div class="mandoob-hero__pulse">
            <div class="mandoob-pulse-card">
                <span class="mandoob-pulse-card__icon mandoob-pulse-card__icon--green">✓</span>
                <div><small>طلبات اليوم</small><strong>{{ number_format($todayOrders) }}</strong></div>
            </div>
            <div class="mandoob-pulse-card">
                <span class="mandoob-pulse-card__icon mandoob-pulse-card__icon--blue">↗</span>
                <div><small>قيد التنفيذ</small><strong>{{ number_format($pendingOrders) }}</strong></div>
            </div>
            <div class="mandoob-pulse-card">
                <span class="mandoob-pulse-card__icon mandoob-pulse-card__icon--amber">$</span>
                <div><small>مبيعات اليوم</small><strong>{{ number_format($todaySales, 0) }} <em>{{ $currency }}</em></strong></div>
            </div>
            <div class="mandoob-pulse-card">
                <span class="mandoob-pulse-card__icon mandoob-pulse-card__icon--violet">◆</span>
                <div><small>ربح اليوم</small><strong>{{ number_format($todayProfit, 0) }} <em>{{ $currency }}</em></strong></div>
            </div>
        </div>

        <div class="mandoob-quick-actions">
            <span>إضافة سريعة</span>
            <a href="{{ $links['company'] }}">+ شركة</a>
            <a href="{{ $links['store'] }}">+ متجر</a>
            <a href="{{ $links['product'] }}">+ منتج</a>
            <a href="{{ $links['order'] }}">+ طلب</a>
        </div>
    </section>

    <script>
        (() => {
            const root = document.documentElement;
            window.mandoobToggleTheme = () => {
                const dark = root.classList.toggle('dark');
                localStorage.setItem('theme', dark ? 'dark' : 'light');
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: dark ? 'dark' : 'light' }));
            };
        })();
    </script>

    <style>
        :root {
            --md-bg: #f8f5ee;
            --md-card: rgba(255,255,255,.88);
            --md-card-solid: #fff;
            --md-text: #111827;
            --md-muted: #737780;
            --md-border: rgba(15,23,42,.08);
            --md-gold: #d4a817;
            --md-gold-soft: #f4e7aa;
            --md-shadow: 0 16px 46px rgba(43,37,20,.07);
        }
        .dark {
            --md-bg: #050b14;
            --md-card: rgba(11,22,37,.9);
            --md-card-solid: #0b1625;
            --md-text: #f8fafc;
            --md-muted: #94a3b8;
            --md-border: rgba(148,163,184,.12);
            --md-gold: #e1bb36;
            --md-gold-soft: rgba(225,187,54,.14);
            --md-shadow: 0 18px 52px rgba(0,0,0,.28);
        }
        .fi-body, .fi-main { background: var(--md-bg) !important; }
        .fi-main-ctn { background: transparent !important; }
        .fi-sidebar { background: color-mix(in srgb, var(--md-card-solid) 95%, transparent) !important; border-color: var(--md-border) !important; }
        .dark .fi-sidebar { background: #070f1b !important; }
        .fi-sidebar-item-button { border-radius: .85rem !important; }
        .fi-sidebar-item-active .fi-sidebar-item-button { background: var(--md-gold-soft) !important; color: var(--md-gold) !important; }
        .fi-topbar nav { background: color-mix(in srgb, var(--md-card-solid) 92%, transparent) !important; border-color: var(--md-border) !important; backdrop-filter: blur(18px); }
        .fi-section, .fi-wi-chart, .fi-wi-stats-overview-stat, .fi-ta-ctn {
            background: var(--md-card) !important;
            border: 1px solid var(--md-border) !important;
            border-radius: 1.35rem !important;
            box-shadow: var(--md-shadow) !important;
            backdrop-filter: blur(16px);
        }
        .fi-wi-stats-overview-stat { position: relative; overflow: hidden; transition: transform .2s ease, box-shadow .2s ease; }
        .fi-wi-stats-overview-stat:hover { transform: translateY(-4px); }
        .platform-stat::before { content: ''; position: absolute; inset-inline-start: 0; top: 0; bottom: 0; width: 4px; border-radius: 999px; }
        .platform-stat--emerald::before { background: #10b981; }
        .platform-stat--blue::before { background: #3b82f6; }
        .platform-stat--amber::before { background: #f59e0b; }
        .platform-stat--violet::before { background: #8b5cf6; }
        .fi-btn { border-radius: .85rem !important; }
        .fi-ta-row { transition: background .18s ease, transform .18s ease; }
        .fi-ta-row:hover { background: color-mix(in srgb, var(--md-gold) 5%, transparent) !important; }
        .mandoob-hero { position: relative; overflow: hidden; padding: 1.45rem; border: 1px solid var(--md-border); border-radius: 1.6rem; color: var(--md-text); background: linear-gradient(135deg, color-mix(in srgb, var(--md-card-solid) 94%, var(--md-gold) 6%), var(--md-card-solid)); box-shadow: var(--md-shadow); isolation: isolate; }
        .dark .mandoob-hero { background: linear-gradient(135deg, #0d1b2c 0%, #07111f 72%, #16160d 100%); }
        .mandoob-hero__glow { position: absolute; border-radius: 999px; filter: blur(2px); opacity: .7; z-index: -1; }
        .mandoob-hero__glow--one { width: 310px; height: 310px; inset-inline-end: -110px; top: -170px; background: radial-gradient(circle, rgba(212,168,23,.28), transparent 68%); }
        .mandoob-hero__glow--two { width: 230px; height: 230px; inset-inline-start: -90px; bottom: -150px; background: radial-gradient(circle, rgba(16,185,129,.16), transparent 70%); }
        .mandoob-hero__main { display: flex; justify-content: space-between; align-items: flex-start; gap: 1.25rem; }
        .mandoob-hero__eyebrow { display: inline-flex; padding: .35rem .65rem; border-radius: 999px; color: #8a6a05; background: var(--md-gold-soft); font-size: .76rem; font-weight: 850; }
        .dark .mandoob-hero__eyebrow { color: #f6db70; }
        .mandoob-hero__copy h2 { margin: .7rem 0 .25rem; font-size: clamp(1.55rem,3vw,2.35rem); font-weight: 950; letter-spacing: -.04em; }
        .mandoob-hero__copy p { margin: 0; color: var(--md-muted); font-size: .92rem; }
        .mandoob-hero__date { display: block; margin-top: .55rem; color: var(--md-muted); font-size: .78rem; }
        .mandoob-hero__actions { display: flex; gap: .55rem; flex-wrap: wrap; }
        .mandoob-icon-button { display: inline-flex; align-items: center; gap: .5rem; min-height: 2.75rem; padding: .65rem .85rem; border: 1px solid var(--md-border); border-radius: .9rem; color: var(--md-text); background: color-mix(in srgb, var(--md-card-solid) 82%, transparent); font-size: .78rem; font-weight: 800; transition: .2s ease; }
        .mandoob-icon-button:hover { transform: translateY(-2px); border-color: rgba(212,168,23,.55); }
        .mandoob-icon-button svg { width: 1.15rem; height: 1.15rem; }
        .mandoob-icon-button--gold { color: #17120a; background: linear-gradient(135deg,#f2dc78,var(--md-gold)); border-color: transparent; }
        .mandoob-hero__pulse { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: .75rem; margin-top: 1.2rem; }
        .mandoob-pulse-card { display: flex; align-items: center; gap: .7rem; padding: .85rem; border: 1px solid var(--md-border); border-radius: 1rem; background: color-mix(in srgb,var(--md-card-solid) 72%,transparent); }
        .mandoob-pulse-card__icon { display:grid;place-items:center;width:2.35rem;height:2.35rem;border-radius:.8rem;font-weight:950; }
        .mandoob-pulse-card__icon--green{color:#047857;background:rgba(16,185,129,.14)}
        .mandoob-pulse-card__icon--blue{color:#2563eb;background:rgba(59,130,246,.14)}
        .mandoob-pulse-card__icon--amber{color:#a16207;background:rgba(245,158,11,.16)}
        .mandoob-pulse-card__icon--violet{color:#7c3aed;background:rgba(139,92,246,.14)}
        .mandoob-pulse-card small { display:block;color:var(--md-muted);font-size:.7rem; }
        .mandoob-pulse-card strong { display:block;margin-top:.15rem;font-size:1.05rem;white-space:nowrap; }
        .mandoob-pulse-card em { font-size:.62rem;color:var(--md-muted);font-style:normal; }
        .mandoob-quick-actions { display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-top:1rem;padding-top:1rem;border-top:1px dashed var(--md-border); }
        .mandoob-quick-actions > span { color:var(--md-muted);font-size:.75rem;font-weight:800;margin-inline-end:.2rem; }
        .mandoob-quick-actions a { padding:.42rem .68rem;border-radius:.7rem;color:var(--md-text);background:color-mix(in srgb,var(--md-gold) 10%,transparent);font-size:.75rem;font-weight:800;transition:.18s ease; }
        .mandoob-quick-actions a:hover { color:#17120a;background:var(--md-gold);transform:translateY(-2px); }
        @media(max-width:900px){.mandoob-hero__pulse{grid-template-columns:repeat(2,minmax(0,1fr));}.mandoob-hero__main{flex-direction:column}.mandoob-hero__actions{width:100%}}
        @media(max-width:540px){.mandoob-hero{padding:1.05rem}.mandoob-hero__pulse{grid-template-columns:1fr}.mandoob-icon-button{flex:1;justify-content:center}}
    </style>
</x-filament-widgets::widget>
