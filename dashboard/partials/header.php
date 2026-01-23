<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agroxa - Dashboard</title>
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
                        background: 'hsl(210, 20%, 97%)',
                        foreground: 'hsl(220, 20%, 20%)',
                        card: 'hsl(0, 0%, 100%)',
                        'card-foreground': 'hsl(220, 20%, 20%)',
                        primary: 'hsl(231, 76%, 62%)',
                        'primary-foreground': 'hsl(0, 0%, 100%)',
                        muted: 'hsl(220, 14%, 96%)',
                        'muted-foreground': 'hsl(220, 10%, 50%)',
                        destructive: 'hsl(0, 72%, 51%)',
                        'destructive-foreground': 'hsl(0, 0%, 100%)',
                        success: 'hsl(142, 71%, 45%)',
                        'success-foreground': 'hsl(0, 0%, 100%)',
                        warning: 'hsl(38, 92%, 50%)',
                        'warning-foreground': 'hsl(0, 0%, 100%)',
                        border: 'hsl(220, 13%, 91%)',
                        navbar: 'hsl(225, 24%, 22%)',
                        'navbar-foreground': 'hsl(0, 0%, 100%)',
                        subnav: 'hsl(0, 0%, 100%)',
                        'subnav-foreground': 'hsl(220, 10%, 40%)',
                    },
                    boxShadow: {
                        card: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
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

    <!-- Primary Navbar -->
    <nav class="bg-navbar h-[60px] flex items-center justify-between px-6">
        <div class="text-navbar-foreground text-xl font-semibold italic">
            Agroxa
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Search Input -->
            <div class="relative">
                <input
                    type="text"
                    placeholder="Search..."
                    class="bg-white/10 text-navbar-foreground placeholder-white/60 rounded px-4 py-1.5 text-sm w-48 focus:outline-none focus:ring-1 focus:ring-primary"
                />
                <!-- Search Icon (SVG) -->
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/60" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </div>
            
            <!-- Notification Bell -->
            <button class="relative p-2 text-white/80 hover:text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                </svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-success rounded-full"></span>
            </button>
            
            <!-- User Avatar -->
            <div class="w-8 h-8 rounded-full bg-primary/30 flex items-center justify-center">
                <span class="text-navbar-foreground text-sm">M</span>
            </div>
            
            <!-- Settings Icon -->
            <button class="p-2 text-white/80 hover:text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </button>
        </div>
    </nav>
    
    <!-- Secondary Navbar -->
    <nav class="bg-subnav h-[50px] flex items-center px-6 shadow-sm">
        <div class="flex items-center gap-8">
            <!-- Dashboards (active) -->
            <button class="flex items-center gap-2 text-sm text-primary font-medium">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Dashboards</span>
            </button>
            
            <!-- UI Elements -->
            <button class="flex items-center gap-2 text-sm text-subnav-foreground hover:text-primary">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                    <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                    <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                    <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                </svg>
                <span>UI Elements</span>
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </button>
            
            <!-- Components -->
            <button class="flex items-center gap-2 text-sm text-subnav-foreground hover:text-primary">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"></path>
                    <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"></path>
                    <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"></path>
                </svg>
                <span>Components</span>
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </button>
            
            <!-- Charts -->
            <button class="flex items-center gap-2 text-sm text-subnav-foreground hover:text-primary">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                    <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                </svg>
                <span>Charts</span>
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </button>
            
            <!-- Pages -->
            <button class="flex items-center gap-2 text-sm text-subnav-foreground hover:text-primary">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                </svg>
                <span>Pages</span>
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </button>
            
            <!-- Email -->
            <button class="flex items-center gap-2 text-sm text-subnav-foreground hover:text-primary">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                </svg>
                <span>Email</span>
            </button>
            
            <!-- Layouts -->
            <button class="flex items-center gap-2 text-sm text-subnav-foreground hover:text-primary">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                    <line x1="3" x2="21" y1="9" y2="9"></line>
                    <line x1="9" x2="9" y1="21" y2="9"></line>
                </svg>
                <span>Layouts</span>
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </button>
        </div>
    </nav>
