<x-filament-widgets::widget>
    @php($isArabic = app()->getLocale() !== 'en')

    <style>
        :root {
            --supplier-bg: #fbf7ef;
            --supplier-panel: #fffdf8;
            --supplier-panel-soft: #fff9ee;
            --supplier-border: rgba(148, 116, 38, .14);
            --supplier-text: #1c2430;
            --supplier-muted: #8f8a80;
            --supplier-gold: #d2ad2e;
            --supplier-shadow: 0 12px 35px rgba(103, 79, 24, .08);
        }

        .dark {
            --supplier-bg: #07111f;
            --supplier-panel: #0b1727;
            --supplier-panel-soft: #0e1d30;
            --supplier-border: rgba(126, 154, 190, .13);
            --supplier-text: #f5f7fb;
            --supplier-muted: #8ea0b6;
            --supplier-gold: #d4aa29;
            --supplier-shadow: 0 18px 45px rgba(0, 0, 0, .22);
        }

        body, .fi-body, .fi-main-ctn, .fi-layout, .fi-main {
            background: var(--supplier-bg) !important;
        }

        .fi-main {
            max-width: none !important;
        }

        .fi-sidebar, .fi-topbar nav {
            background: color-mix(in srgb, var(--supplier-panel) 95%, transparent) !important;
            border-color: var(--supplier-border) !important;
        }

        .fi-sidebar-item.fi-active > a,
        .fi-sidebar-item-button:hover {
            background: color-mix(in srgb, var(--supplier-gold) 14%, transparent) !important;
        }

        .fi-sidebar-item.fi-active svg,
        .fi-sidebar-item.fi-active span {
            color: var(--supplier-gold) !important;
        }

        .fi-section, .fi-wi-stats-overview-stat, .fi-ta-ctn, .fi-wi-chart {
            background: linear-gradient(145deg, var(--supplier-panel), var(--supplier-panel-soft)) !important;
            border: 1px solid var(--supplier-border) !important;
            box-shadow: var(--supplier-shadow) !important;
            border-radius: 18px !important;
        }

        .fi-wi-stats-overview-stat {
            overflow: hidden;
            position: relative;
            transition: transform .2s ease, border-color .2s ease;
        }

        .fi-wi-stats-overview-stat:hover {
            transform: translateY(-3px);
            border-color: color-mix(in srgb, var(--supplier-gold) 42%, var(--supplier-border)) !important;
        }

        .supplier-dashboard-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            border: 1px solid var(--supplier-border);
            border-radius: 20px;
            background: linear-gradient(135deg, var(--supplier-panel), var(--supplier-panel-soft));
            box-shadow: var(--supplier-shadow);
            color: var(--supplier-text);
        }

        .supplier-dashboard-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .supplier-dashboard-logo {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: color-mix(in srgb, var(--supplier-gold) 16%, var(--supplier-panel));
            color: var(--supplier-gold);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--supplier-gold) 24%, transparent);
        }

        .supplier-dashboard-logo svg { width: 27px; height: 27px; }

        .supplier-dashboard-title {
            margin: 0;
            color: var(--supplier-text);
            font-size: clamp(1.1rem, 2vw, 1.55rem);
            font-weight: 850;
        }

        .supplier-dashboard-subtitle {
            margin: 4px 0 0;
            color: var(--supplier-muted);
            font-size: .88rem;
        }

        .supplier-dashboard-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .supplier-dashboard-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 14px;
            border-radius: 13px;
            border: 1px solid var(--supplier-border);
            background: var(--supplier-panel);
            color: var(--supplier-text);
            font-weight: 750;
            cursor: pointer;
            transition: .2s ease;
        }

        .supplier-dashboard-action:hover {
            border-color: color-mix(in srgb, var(--supplier-gold) 46%, var(--supplier-border));
            background: color-mix(in srgb, var(--supplier-gold) 10%, var(--supplier-panel));
        }

        .supplier-dashboard-action svg { width: 18px; height: 18px; }

        .supplier-dashboard-language {
            border-color: transparent;
            background: linear-gradient(135deg, #e7cf63, var(--supplier-gold));
            color: #171717;
        }

        @media (max-width: 760px) {
            .supplier-dashboard-toolbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .supplier-dashboard-actions { width: 100%; }
            .supplier-dashboard-action { flex: 1; }
        }
    </style>

    <div class="supplier-dashboard-toolbar" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
        <div class="supplier-dashboard-title-wrap">
            <div class="supplier-dashboard-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 19V9.5L12 4l8 5.5V19a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1Z"/>
                    <path d="M8 10h8"/>
                </svg>
            </div>

            <div>
                <h2 class="supplier-dashboard-title">
                    {{ $isArabic ? 'مركز قيادة المنصة' : 'Platform control center' }}
                </h2>
                <p class="supplier-dashboard-subtitle">
                    {{ $isArabic
                        ? 'الشركات والطلبات والمبيعات وأرباح المنصة في مكان واحد'
                        : 'Companies, orders, sales, and platform profit in one place' }}
                </p>
            </div>
        </div>

        <div class="supplier-dashboard-actions">
            <button type="button" class="supplier-dashboard-action" onclick="window.supplierToggleTheme()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.8 6.8 0 0 0 21 12.8Z"/>
                </svg>
                <span>{{ $isArabic ? 'ليلي / نهاري' : 'Dark / light' }}</span>
            </button>

            <button type="button" class="supplier-dashboard-action supplier-dashboard-language" onclick="window.supplierToggleLocale()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/>
                </svg>
                <span>{{ $isArabic ? 'English' : 'العربية' }}</span>
            </button>
        </div>
    </div>

    <script>
        (() => {
            document.documentElement.dir = @js($isArabic ? 'rtl' : 'ltr');
            document.documentElement.lang = @js($isArabic ? 'ar' : 'en');

            window.supplierToggleTheme = () => {
                const root = document.documentElement;
                const dark = !root.classList.contains('dark');

                root.classList.toggle('dark', dark);
                localStorage.setItem('theme', dark ? 'dark' : 'light');
                window.dispatchEvent(new CustomEvent('theme-changed', {
                    detail: dark ? 'dark' : 'light',
                }));
            };

            window.supplierToggleLocale = () => {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', @js($isArabic ? 'en' : 'ar'));
                window.location.href = url.toString();
            };
        })();
    </script>
</x-filament-widgets::widget>
