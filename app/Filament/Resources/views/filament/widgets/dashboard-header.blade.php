<x-filament-widgets::widget>
    <div
        x-data="{
            dark: document.documentElement.classList.contains('dark'),
            toggleTheme() {
                this.dark = !this.dark;
                document.documentElement.classList.toggle('dark', this.dark);
                localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: this.dark ? 'dark' : 'light' }));
            }
        }"
        x-init="
            const saved = localStorage.getItem('theme');
            if (saved) {
                dark = saved === 'dark';
                document.documentElement.classList.toggle('dark', dark);
            }
        "
        class="supplier-dashboard-header"
        dir="{{ $isArabic ? 'rtl' : 'ltr' }}"
    >
        <div>
            <p class="supplier-dashboard-eyebrow">
                {{ $isArabic ? 'نظرة شاملة على أداء المنصة' : 'A complete view of platform performance' }}
            </p>
            <h1>{{ $isArabic ? 'لوحة القيادة' : 'Dashboard' }}</h1>
            <p class="supplier-dashboard-subtitle">
                {{ $isArabic ? 'تابع الطلبات، الشركات، المدفوعات وأرباح المنصة من مكان واحد.' : 'Track orders, companies, payments, and platform profit in one place.' }}
            </p>
        </div>

        <div class="supplier-dashboard-actions">
            <button type="button" class="supplier-dashboard-action" @click="toggleTheme()">
                <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 15.5A9 9 0 0 1 8.5 2.25a9 9 0 1 0 13.25 13.25Z" />
                </svg>
                <svg x-show="dark" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="4" />
                    <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
                </svg>
                <span x-text="dark ? '{{ $isArabic ? 'الوضع النهاري' : 'Light mode' }}' : '{{ $isArabic ? 'الوضع الليلي' : 'Dark mode' }}'"></span>
            </button>

            <button
                type="button"
                wire:click="switchLocale('{{ $isArabic ? 'en' : 'ar' }}')"
                class="supplier-dashboard-action supplier-dashboard-language"
            >
                <span class="supplier-language-symbol">{{ $isArabic ? 'EN' : 'ع' }}</span>
                <span>{{ $isArabic ? 'English' : 'العربية' }}</span>
            </button>
        </div>
    </div>

    <style>
        :root {
            --supplier-gold: #d5b22f;
            --supplier-gold-dark: #aa8413;
            --supplier-cream: #fbf8f2;
            --supplier-card: #ffffff;
            --supplier-border: rgba(15, 23, 42, .08);
            --supplier-text: #111827;
            --supplier-muted: #7c7f86;
        }

        .dark {
            --supplier-cream: #07111f;
            --supplier-card: #0d1929;
            --supplier-border: rgba(148, 163, 184, .12);
            --supplier-text: #f8fafc;
            --supplier-muted: #94a3b8;
        }

        .fi-main, .fi-body {
            background: var(--supplier-cream) !important;
        }

        .supplier-dashboard-header {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.55rem 1.7rem;
            border: 1px solid var(--supplier-border);
            border-radius: 1.35rem;
            color: var(--supplier-text);
            background:
                radial-gradient(circle at 8% 15%, rgba(213, 178, 47, .18), transparent 28%),
                linear-gradient(135deg, var(--supplier-card), color-mix(in srgb, var(--supplier-card) 88%, var(--supplier-gold) 12%));
            box-shadow: 0 14px 40px rgba(15, 23, 42, .06);
        }

        .dark .supplier-dashboard-header {
            background:
                radial-gradient(circle at 8% 15%, rgba(213, 178, 47, .15), transparent 30%),
                linear-gradient(135deg, #0d1929, #0a1423);
            box-shadow: 0 18px 50px rgba(0, 0, 0, .22);
        }

        .supplier-dashboard-header::after {
            content: '';
            position: absolute;
            inset-inline-end: -70px;
            top: -100px;
            width: 250px;
            height: 250px;
            border-radius: 999px;
            border: 42px solid rgba(213, 178, 47, .07);
            pointer-events: none;
        }

        .supplier-dashboard-eyebrow {
            margin: 0 0 .35rem;
            color: var(--supplier-gold-dark);
            font-weight: 800;
            font-size: .78rem;
        }

        .dark .supplier-dashboard-eyebrow { color: #e2c95c; }

        .supplier-dashboard-header h1 {
            margin: 0;
            font-size: clamp(1.55rem, 3vw, 2.25rem);
            font-weight: 900;
            letter-spacing: -.03em;
        }

        .supplier-dashboard-subtitle {
            margin: .45rem 0 0;
            color: var(--supplier-muted);
            font-size: .94rem;
        }

        .supplier-dashboard-actions {
            z-index: 1;
            display: flex;
            align-items: center;
            gap: .65rem;
            flex-wrap: wrap;
        }

        .supplier-dashboard-action {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            min-height: 2.75rem;
            padding: .65rem .9rem;
            border: 1px solid var(--supplier-border);
            border-radius: .9rem;
            color: var(--supplier-text);
            background: color-mix(in srgb, var(--supplier-card) 88%, transparent);
            font-weight: 750;
            font-size: .82rem;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .supplier-dashboard-action:hover {
            transform: translateY(-2px);
            border-color: rgba(213, 178, 47, .6);
            box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
        }

        .supplier-dashboard-action svg { width: 1.2rem; height: 1.2rem; }

        .supplier-dashboard-language {
            color: #111827;
            border-color: transparent;
            background: linear-gradient(135deg, #e3ca59, var(--supplier-gold));
        }

        .supplier-language-symbol {
            display: grid;
            place-items: center;
            width: 1.55rem;
            height: 1.55rem;
            border-radius: .48rem;
            background: rgba(255,255,255,.55);
            font-size: .72rem;
            font-weight: 900;
        }

        .fi-wi-stats-overview-stat, .fi-wi-chart, .fi-section {
            border-color: var(--supplier-border) !important;
            border-radius: 1.2rem !important;
            background: var(--supplier-card) !important;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .045) !important;
        }

        .dark .fi-wi-stats-overview-stat,
        .dark .fi-wi-chart,
        .dark .fi-section {
            box-shadow: 0 15px 35px rgba(0, 0, 0, .18) !important;
        }

        @media (max-width: 760px) {
            .supplier-dashboard-header { align-items: flex-start; flex-direction: column; padding: 1.25rem; }
            .supplier-dashboard-actions { width: 100%; }
            .supplier-dashboard-action { flex: 1; justify-content: center; }
        }
    </style>
</x-filament-widgets::widget>
