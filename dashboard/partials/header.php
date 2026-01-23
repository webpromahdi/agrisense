<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriSense - Dashboard</title>

    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        /* ========================================
                           AgriSense - Professional Agriculture Palette
                           Strong, Confident, Natural Colors
                           ======================================== */

                        /* Primary - Deep Forest/Emerald Green */
                        primary: '#166534',
                        'primary-light': '#15803d',
                        'primary-dark': '#14532d',
                        'primary-deeper': '#052e16',
                        'primary-foreground': '#FFFFFF',

                        /* Secondary - Warm Neutral Tones */
                        secondary: '#FAFAF9',
                        'secondary-dark': '#F5F5F4',
                        'secondary-darker': '#E7E5E4',
                        'secondary-foreground': '#166534',

                        /* Accent - Harvest Amber/Golden */
                        accent: '#D97706',
                        'accent-light': '#F59E0B',
                        'accent-dark': '#B45309',
                        'accent-deeper': '#92400E',
                        'accent-foreground': '#FFFFFF',

                        /* Backgrounds - Clean & Neutral */
                        background: '#FFFFFF',
                        'background-alt': '#FAFAF9',
                        'background-subtle': '#F5F5F4',
                        foreground: '#1C1917',
                        card: '#FFFFFF',
                        'card-foreground': '#1C1917',

                        /* Text - Strong Hierarchy with High Contrast */
                        'text-heading': '#1C1917',
                        'text-subheading': '#166534',
                        'text-body': '#44403C',
                        'text-muted': '#78716C',
                        'text-subtle': '#A8A29E',

                        /* UI Elements */
                        muted: '#F5F5F4',
                        'muted-foreground': '#78716C',
                        border: '#E7E5E4',
                        'border-strong': '#D6D3D1',

                        /* Navbar - Deep Forest Green */
                        navbar: '#166534',
                        'navbar-dark': '#14532d',
                        'navbar-foreground': '#FFFFFF',
                        subnav: '#FFFFFF',
                        'subnav-foreground': '#44403C',
                        'subnav-active': '#166534',

                        /* Status Colors - Vibrant & Clear */
                        success: '#16A34A',
                        'success-dark': '#15803D',
                        'success-light': '#DCFCE7',
                        'success-foreground': '#FFFFFF',
                        
                        warning: '#D97706',
                        'warning-dark': '#B45309',
                        'warning-light': '#FEF3C7',
                        'warning-foreground': '#FFFFFF',
                        
                        destructive: '#DC2626',
                        'destructive-dark': '#B91C1C',
                        'destructive-light': '#FEE2E2',
                        'destructive-foreground': '#FFFFFF',
                        
                        info: '#2563EB',
                        'info-dark': '#1D4ED8',
                        'info-light': '#DBEAFE',
                        'info-foreground': '#FFFFFF',

                        /* Earth Tones for Agriculture */
                        earth: '#78350F',
                        'earth-light': '#A16207',
                        'earth-lighter': '#FEF3C7',
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px -1px rgba(0, 0, 0, 0.08)',
                        'card-hover': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1)',
                        'card-strong': '0 4px 12px -2px rgba(0, 0, 0, 0.12), 0 2px 6px -2px rgba(0, 0, 0, 0.08)',
                        'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)',
                        'navbar': '0 2px 8px -2px rgba(22, 101, 52, 0.2)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-background">

    <!-- Primary Navbar - Deep Forest Green -->
    <nav class="bg-navbar h-[60px] flex items-center justify-between px-6 shadow-navbar">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-white/15 flex items-center justify-center border border-white/20">
                <span class="text-xl text-white">🌾</span>
            </div>
            <div class="text-navbar-foreground text-xl font-semibold tracking-tight">
                AgriSense
            </div>
        </div>

        <div class="flex items-center gap-4">
            <!-- Farmer Portal Button - Accent Color -->
            <a href="/agrisense/farmer/verify_code.php"
                class="flex items-center gap-2 bg-accent hover:bg-accent-dark text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all shadow-md hover:shadow-lg">
                <span>👨‍🌾</span>
                <span>Farmer Portal</span>
            </a>
        </div>
    </nav>

    <!-- Secondary Navbar - Clean White with Strong Contrast -->
    <nav class="bg-subnav h-[50px] flex items-center px-6 shadow-sm border-b border-border">
        <div class="flex items-center gap-6">
            <!-- Dashboard (active) -->
            <a href="/agrisense/" class="flex items-center gap-2 text-sm text-primary font-semibold border-b-2 border-primary pb-1 -mb-1">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Smart Market -->
            <a href="/agrisense/pages/smart_market.php"
                class="flex items-center gap-2 text-sm text-text-body hover:text-primary font-medium transition-colors">
                <span>🎯</span>
                <span>Smart Market</span>
            </a>

            <!-- Seasonal Price -->
            <a href="/agrisense/pages/seasonal_price_memory.php"
                class="flex items-center gap-2 text-sm text-text-body hover:text-primary font-medium transition-colors">
                <span>📅</span>
                <span>Price Memory</span>
            </a>

            <!-- Over-Supply Alert -->
            <a href="/agrisense/pages/oversupply_alert.php"
                class="flex items-center gap-2 text-sm text-text-body hover:text-primary font-medium transition-colors">
                <span>⚠️</span>
                <span>Over-Supply</span>
            </a>

            <!-- Climate Risk -->
            <a href="/agrisense/pages/climate_risk_dashboard.php"
                class="flex items-center gap-2 text-sm text-text-body hover:text-primary font-medium transition-colors">
                <span>🌦️</span>
                <span>Climate Risk</span>
            </a>

            <!-- Price Gap -->
            <a href="/agrisense/pages/market_price_gap.php"
                class="flex items-center gap-2 text-sm text-text-body hover:text-primary font-medium transition-colors">
                <span>🔄</span>
                <span>Price Gap</span>
            </a>

            <!-- Price Trend -->
            <a href="/agrisense/pages/price_trend.php"
                class="flex items-center gap-2 text-sm text-text-body hover:text-primary font-medium transition-colors">
                <span>📈</span>
                <span>Price Trend</span>
            </a>
        </div>
    </nav>