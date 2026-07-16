<x-filament-widgets::widget>
    <style>
        :root {
            --dash-bg: #fbf7ef;
            --dash-panel: #fffdf8;
            --dash-panel-2: #fff9ee;
            --dash-border: rgba(148, 116, 38, .14);
            --dash-text: #1c2430;
            --dash-muted: #8f8a80;
            --dash-accent: #d2ad2e;
            --dash-shadow: 0 12px 35px rgba(103, 79, 24, .08);
        }

        .dark {
            --dash-bg: #07111f;
            --dash-panel: #0b1727;
            --dash-panel-2: #0e1d30;
            --dash-border: rgba(126, 154, 190, .13);
            --dash-text: #f5f7fb;
            --dash-muted: #8ea0b6;
            --dash-accent: #d4aa29;
            --dash-shadow: 0 18px 45px rgba(0, 0, 0, .22);
        }

        body, .fi-body, .fi-main-ctn, .fi-layout {
            background: var(--dash-bg) !important;
        }

        .fi-sidebar, .fi-topbar nav {
            background: color-mix(in srgb, var(--dash-panel) 94%, transparent) !important;
            border-color: var(--dash-border) !important;
        }

        .fi-sidebar-item.fi-active > a,
        .fi-sidebar-item-button:hover {
            background: color-mix(in srgb, var(--dash-accent) 14%, transparent) !important;
        }

        .fi-sidebar-item.fi-active svg,
        .fi-sidebar-item.fi-active span {
            color: var(--dash-accent) !important;
        }

        .fi-section, .fi-wi-stats-overview-stat, .fi-ta-ctn, .fi-wi-chart {
            background: linear-gradient(145deg, var(--dash-panel), var(--dash-panel-2)) !important;
            border: 1px solid var(--dash-border) !important;
            box-shadow: var(--dash-shadow) !important;
            border-radius: 18px !important;
        }

        .fi-wi-stats-overview-stat {
            overflow: hidden;
            position: relative;
            transition: transform .2s ease, border-color .2s ease;
        }

        .fi-wi-stats-overview-stat:hover {
            transform: translateY(-3px);
            border-color: color-mix(in srgb, var(--dash-accent) 42%, var(--dash-border)) !important;
        }

        .fi-wi-stats-overview-stat::after {
            content: '';
            position: absolute;
            inset-inline-end: -28px;
            top: -28px;
            width: 92px;
            height: 92px;
            border-radius: 999px;
            background: radial-gradient(circle, color-mix(in srgb, var(--dash-accent) 20%, transparent), transparent 72%);
            pointer-events: none;
        }

        .dashboard-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            border: 1px solid var(--dash-border);
            border-radius: 20px;
            background: linear-gradient(135deg, var(--dash-panel), var(--dash-panel-2));
            box-shadow: var(--dash-shadow);
        }

        .dashboard-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .dashboard-logo {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: color-mix(in srgb, var(--dash-accent) 16%, var(--dash-panel));
            color: var(--dash-accent);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--dash-accent) 24%, transparent);
        }

        .dashboard-title {
            margin: 0;
            color: var(--dash-text);
            font-size: clamp(1.1rem, 2vw, 1.55rem);
            font-weight: 800;
        }

        .dashboard-subtitle {
            margin: 4px 0 0;
            color: var(--dash-muted);
            font-size: .88rem;
        }

        .dashboard-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .dashboard-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 14px;
            border-radius: 13px;
            border: 1px solid var(--dash-border);
            background: var(--dash-panel);
            color: var(--dash-text);
            font-weight: 700;
            cursor: pointer;
            transition: .2s ease;
        }

        .dashboard-action:hover {
            border-color: color-mix(in srgb, var(--dash-accent) 46%, var(--dash-border));
            background: color-mix(in srgb, var(--dash-accent) 10%, var(--dash-panel));
        }

        .dashboard-action svg {
            width: 18px;
            height: 18px;
        }

        .dashboard-stat--gold { --dash-accent: #d2ad2e; }
        .dashboard-stat--green { --dash-accent: #27ae60; }
        .dashboard-stat--blue { --dash-accent: #2f80ed; }
        .dashboard-stat--emerald { --dash-accent: #10b981; }
        .dashboard-stat--purple { --dash-accent: #9b51e0; }
        .dashboard-stat--orange { --dash-accent: #f2994a; }
        .dashboard-stat--cyan { --dash-accent: #22b8cf; }

        .fi-ta-table thead {
            background: color-mix(in srgb, var(--dash-accent) 6%, var(--dash-panel));
        }

        @media (max-width: 700px) {
            .dashboard-toolbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .dashboard-actions {
                width: 100%;
            }

            .dashboard-action {
                flex: 1;
            }
        }
    </style>

    @php($isEnglish = request()->cookie('admin_locale', 'ar') === 'en')

    <div class="dashboard-toolbar" dir="{{ $isEnglish ? 'ltr' : 'rtl' }}">
        <div class="dashboard-title-wrap">
            <div class="dashboard-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 19V9.5L12 4l8 5.5V19a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1Z"/>
                    <path d="M8 10h8"/>
                </svg>
            </div>
            <div>
                <h1 class="dashboard-title">{{ $isEnglish ? 'Platform control center' : 'مركز قيادة المنصة' }}</h1>
                <p class="dashboard-subtitle">{{ $isEnglish ? 'Companies, orders, revenue and platform profit at a glance' : 'الشركات والطلبات والمبيعات وأرباح المنصة في مكان واحد' }}</p>
            </div>
        </div>

        <div class="dashboard-actions">
            <button type="button" class="dashboard-action" onclick="window.dashboardToggleTheme()" aria-label="Toggle theme">
                <svg class="theme-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.8 6.8 0 0 0 21 12.8Z"/></svg>
                <span>{{ $isEnglish ? 'Theme' : 'المظهر' }}</span>
            </button>

            <button type="button" class="dashboard-action" onclick="window.dashboardToggleLocale()" aria-label="Toggle language">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>
                <span>{{ $isEnglish ? 'العربية' : 'English' }}</span>
            </button>
        </div>
    </div>

    <script>
        (() => {
            const root = document.documentElement;
            const savedTheme = localStorage.getItem('dashboard-theme');

            if (savedTheme === 'dark') root.classList.add('dark');
            if (savedTheme === 'light') root.classList.remove('dark');

            window.dashboardToggleTheme = () => {
                const dark = root.classList.toggle('dark');
                localStorage.setItem('dashboard-theme', dark ? 'dark' : 'light');
                localStorage.setItem('theme', dark ? 'dark' : 'light');
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: dark ? 'dark' : 'light' }));
            };

            window.dashboardToggleLocale = () => {
                const current = document.cookie.match(/(?:^|; )admin_locale=([^;]*)/)?.[1] ?? 'ar';
                const next = current === 'en' ? 'ar' : 'en';
                document.cookie = `admin_locale=${next}; path=/; max-age=31536000; SameSite=Lax`;
                window.location.reload();
            };
        })();
    </script>
</x-filament-widgets::widget>
