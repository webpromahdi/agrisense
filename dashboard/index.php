<?php
/**
 * Agroxa Dashboard - Main Dashboard Page
 * Plain PHP with Tailwind CSS CDN
 */

// ============================================
// DATA ARRAYS (Dummy Data)
// ============================================

// KPI Cards Data
$kpiCards = [
    [
        'label' => 'ORDERS',
        'value' => '1,587',
        'change' => '+11%',
        'changeText' => 'From previous period',
        'iconPath' => 'M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z', // ShoppingCart simplified
    ],
    [
        'label' => 'REVENUE',
        'value' => '$46,785',
        'change' => '+29%',
        'changeText' => 'From previous period',
        'iconPath' => 'M12 2v20 M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6', // DollarSign
    ],
    [
        'label' => 'AVERAGE PRICE',
        'value' => '15.9',
        'change' => '0%',
        'changeText' => 'From previous period',
        'iconPath' => 'M12 2 L2 7 L12 12 L22 7 Z M2 17 L12 22 L22 17 M2 12 L12 17 L22 12', // Tag simplified
    ],
    [
        'label' => 'PRODUCT SOLD',
        'value' => '1890',
        'change' => '+89%',
        'changeText' => 'From previous period',
        'iconPath' => 'M16.5 9.4l-9-5.19 M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z', // Package
    ],
];

// Inbox Messages Data
$inboxMessages = [
    ['name' => 'Irene', 'message' => "Hey! there I'm available...", 'time' => '13:40 PM', 'color' => 'bg-primary'],
    ['name' => 'Jennifer', 'message' => "I've finished it! See you so...", 'time' => '13:34 PM', 'color' => 'bg-success'],
    ['name' => 'Richard', 'message' => 'This theme is awesome!', 'time' => '13:17 PM', 'color' => 'bg-destructive'],
    ['name' => 'Martin', 'message' => 'Nice to meet you', 'time' => '12:20 PM', 'color' => 'bg-warning'],
    ['name' => 'Sean', 'message' => "Hey! there I'm available...", 'time' => '11:47 AM', 'color' => 'bg-primary'],
];

// Activity Feed Dates
$activityDates = [
    ['date' => '21 Sep', 'active' => true],
    ['date' => '22 Sep', 'active' => false],
    ['date' => '23 Sep', 'active' => false],
    ['date' => '24 Sep', 'active' => false],
];

// Top Product Sales Data
$topProducts = [
    ['name' => 'Computers', 'description' => 'The languages only differ', 'percentage' => 70, 'color' => 'destructive'],
    ['name' => 'Laptops', 'description' => 'Maecenas tempus tellus', 'percentage' => 84, 'color' => 'primary'],
    ['name' => 'Ipad', 'description' => 'Donec pede justo', 'percentage' => 62, 'color' => 'primary'],
    ['name' => 'Mobile', 'description' => 'Aenean leo ligula', 'percentage' => 89, 'color' => 'primary'],
];

// Transactions Data
$transactions = [
    ['id' => '#15236', 'name' => 'Jeanette James', 'date' => '14/8/2018', 'amount' => '$104', 'status' => 'Delivered'],
    ['id' => '#15237', 'name' => 'Christopher Taylor', 'date' => '15/8/2018', 'amount' => '$112', 'status' => 'Pending'],
    ['id' => '#15238', 'name' => 'Edward Vazquez', 'date' => '15/8/2018', 'amount' => '$116', 'status' => 'Delivered'],
    ['id' => '#15239', 'name' => 'Michael Flannery', 'date' => '16/8/2018', 'amount' => '$109', 'status' => 'Cancel'],
    ['id' => '#15240', 'name' => 'Jamie Fishbourne', 'date' => '17/8/2018', 'amount' => '$120', 'status' => 'Delivered'],
];

// Orders Data
$orders = [
    ['id' => '#14562', 'name' => 'Matthew Drapeau', 'date' => '17/8/2018', 'time' => '8:26AM', 'amount' => '$104', 'status' => 'Delivered'],
    ['id' => '#14563', 'name' => 'Ralph Shockey', 'date' => '18/8/2018', 'time' => '10:18AM', 'amount' => '$112', 'status' => 'Pending'],
    ['id' => '#14564', 'name' => 'Alexander Pierson', 'date' => '18/8/2018', 'time' => '12:36PM', 'amount' => '$116', 'status' => 'Delivered'],
    ['id' => '#14565', 'name' => 'Robert Rankin', 'date' => '19/8/2018', 'time' => '11:47AM', 'amount' => '$109', 'status' => 'Cancel'],
    ['id' => '#14566', 'name' => 'Myrna Shields', 'date' => '20/8/2018', 'time' => '02:52PM', 'amount' => '$120', 'status' => 'Delivered'],
];

// Sales Chart Data (for SVG rendering)
$salesData = [
    ['year' => '2011', 'sales' => 30],
    ['year' => '2012', 'sales' => 90],
    ['year' => '2013', 'sales' => 180],
    ['year' => '2014', 'sales' => 300],
    ['year' => '2015', 'sales' => 250],
    ['year' => '2016', 'sales' => 380],
    ['year' => '2017', 'sales' => 320],
];

// Status color mapping
$statusColors = [
    'Delivered' => 'bg-success text-success-foreground',
    'Pending' => 'bg-warning text-warning-foreground',
    'Cancel' => 'bg-destructive text-destructive-foreground',
];

// Helper function to get first letter
function getInitial($name)
{
    return strtoupper(substr($name, 0, 1));
}

// Helper function for circular progress stroke dasharray
function getStrokeDasharray($percentage)
{
    return ($percentage * 1.256) . ' 125.6';
}
?>

<?php include 'partials/header.php'; ?>

<!-- Header Banner -->
<div class="relative bg-primary h-[100px] overflow-hidden">
    <!-- Diagonal decorative element -->
    <div class="absolute inset-0 bg-gradient-to-r from-primary to-[hsl(200,80%,55%)]"></div>
    <div class="absolute right-0 top-0 w-1/3 h-full bg-[hsl(220,80%,45%)]"
        style="clip-path: polygon(30% 0, 100% 0, 100% 100%, 0 100%);"></div>

    <div class="relative z-10 h-full flex items-center justify-between px-6">
        <div>
            <h1 class="text-primary-foreground text-lg font-medium">Horizontal</h1>
            <div class="flex items-center gap-2 text-primary-foreground/80 text-sm">
                <span>Agroxa</span>
                <span>›</span>
                <span class="text-primary-foreground">Layouts</span>
                <span>›</span>
                <span class="text-primary-foreground/70">Horizontal</span>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 text-primary-foreground">
                <!-- BarChart3 Icon -->
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path d="M3 3v18h18"></path>
                    <path d="M18 17V9"></path>
                    <path d="M13 17V5"></path>
                    <path d="M8 17v-3"></path>
                </svg>
                <div class="text-right">
                    <div class="text-sm opacity-80">Item Sold 1230</div>
                </div>
            </div>
            <div class="flex items-center gap-2 text-primary-foreground">
                <!-- BarChart3 Icon -->
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path d="M3 3v18h18"></path>
                    <path d="M18 17V9"></path>
                    <path d="M13 17V5"></path>
                    <path d="M8 17v-3"></path>
                </svg>
                <div class="text-right">
                    <div class="text-sm opacity-80">Balance $ 2,317</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-4 gap-4 -mt-8 relative z-10 px-6">
    <?php foreach ($kpiCards as $kpi): ?>
        <div
            class="relative bg-gradient-to-r from-primary to-[hsl(200,80%,55%)] rounded-lg p-5 overflow-hidden shadow-card">
            <!-- Rotated label on right side -->
            <div class="absolute right-0 top-0 h-full flex items-center">
                <span class="text-primary-foreground/20 text-xs font-semibold tracking-wider"
                    style="writing-mode: vertical-rl; text-orientation: mixed; transform: rotate(180deg); padding-right: 8px;">
                    <?= htmlspecialchars($kpi['label']) ?>
                </span>
            </div>

            <!-- Icon -->
            <div class="absolute right-8 top-4">
                <svg class="w-12 h-12 text-primary-foreground/20" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path d="<?= $kpi['iconPath'] ?>"></path>
                </svg>
            </div>

            <div class="relative z-10">
                <p class="text-primary-foreground/80 text-xs font-medium uppercase tracking-wide">
                    <?= htmlspecialchars($kpi['label']) ?>
                </p>
                <p class="text-primary-foreground text-2xl font-semibold mt-1">
                    <?= htmlspecialchars($kpi['value']) ?>
                </p>
                <div class="flex items-center gap-2 mt-3">
                    <span class="bg-primary-foreground/20 text-primary-foreground text-xs px-2 py-0.5 rounded">
                        <?= htmlspecialchars($kpi['change']) ?>
                    </span>
                    <span class="text-primary-foreground/70 text-xs">
                        <?= htmlspecialchars($kpi['changeText']) ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Main Content -->
<div class="px-6 py-6 space-y-6">

    <!-- Row 1: Charts -->
    <div class="grid grid-cols-12 gap-6">

        <!-- Sales Report Chart -->
        <div class="col-span-5">
            <div class="bg-card rounded-lg shadow-card p-5">
                <h3 class="text-card-foreground font-medium text-base mb-4">Sales Report</h3>

                <div class="h-[280px] relative">
                    <!-- SVG Area Chart -->
                    <svg class="w-full h-full" viewBox="0 0 400 280" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="salesGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stop-color="hsl(200, 80%, 55%)" stop-opacity="0.4" />
                                <stop offset="95%" stop-color="hsl(200, 80%, 55%)" stop-opacity="0.05" />
                            </linearGradient>
                        </defs>

                        <!-- Y-axis labels -->
                        <text x="15" y="30" fill="hsl(220, 10%, 50%)" font-size="11">400</text>
                        <text x="15" y="90" fill="hsl(220, 10%, 50%)" font-size="11">300</text>
                        <text x="15" y="150" fill="hsl(220, 10%, 50%)" font-size="11">200</text>
                        <text x="15" y="210" fill="hsl(220, 10%, 50%)" font-size="11">100</text>
                        <text x="25" y="260" fill="hsl(220, 10%, 50%)" font-size="11">0</text>

                        <!-- X-axis labels -->
                        <text x="55" y="275" fill="hsl(220, 10%, 50%)" font-size="11">2011</text>
                        <text x="110" y="275" fill="hsl(220, 10%, 50%)" font-size="11">2012</text>
                        <text x="165" y="275" fill="hsl(220, 10%, 50%)" font-size="11">2013</text>
                        <text x="220" y="275" fill="hsl(220, 10%, 50%)" font-size="11">2014</text>
                        <text x="275" y="275" fill="hsl(220, 10%, 50%)" font-size="11">2015</text>
                        <text x="330" y="275" fill="hsl(220, 10%, 50%)" font-size="11">2016</text>
                        <text x="375" y="275" fill="hsl(220, 10%, 50%)" font-size="11">2017</text>

                        <!-- Area path (approximated from data) -->
                        <path d="M55 245 L110 225 L165 170 L220 90 L275 115 L330 55 L380 85 L380 255 L55 255 Z"
                            fill="url(#salesGradient)" />

                        <!-- Line stroke -->
                        <polyline points="55,245 110,225 165,170 220,90 275,115 330,55 380,85" fill="none"
                            stroke="hsl(200, 80%, 55%)" stroke-width="2" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Yearly Sales Report -->
        <div class="col-span-3">
            <div class="bg-card rounded-lg shadow-card p-5">
                <h3 class="text-card-foreground font-medium text-base mb-4">Yearly Sales Report</h3>

                <!-- Year tabs -->
                <div class="flex gap-2 mb-6">
                    <button class="px-5 py-2 text-sm rounded bg-primary text-primary-foreground">2015</button>
                    <button
                        class="px-5 py-2 text-sm rounded bg-muted text-muted-foreground hover:bg-muted/80">2016</button>
                    <button
                        class="px-5 py-2 text-sm rounded bg-muted text-muted-foreground hover:bg-muted/80">2017</button>
                </div>

                <!-- Stats -->
                <div class="mb-4">
                    <p class="text-3xl font-semibold text-card-foreground">$17562</p>
                    <p class="text-muted-foreground text-sm mt-3 leading-relaxed">
                        Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus
                        Nullam quis ante.
                    </p>
                    <a href="#" class="text-primary text-sm hover:underline mt-2 inline-block">
                        Read more...
                    </a>
                </div>
            </div>
        </div>

        <!-- Sales Analytics -->
        <div class="col-span-4">
            <div class="bg-card rounded-lg shadow-card p-5">
                <h3 class="text-card-foreground font-medium text-base mb-4">Sales Analytics</h3>

                <div class="h-[200px] relative flex items-center justify-center">
                    <!-- SVG Donut Chart -->
                    <svg class="w-[170px] h-[170px]" viewBox="0 0 170 170">
                        <!-- Background circle -->
                        <circle cx="85" cy="85" r="60" stroke="hsl(220, 14%, 90%)" stroke-width="20" fill="none" />
                        <!-- In-Store Sales (30%) -->
                        <circle cx="85" cy="85" r="60" stroke="hsl(142, 71%, 45%)" stroke-width="20" fill="none"
                            stroke-dasharray="113.1 376.99" stroke-dashoffset="0" transform="rotate(-90 85 85)" />
                    </svg>

                    <!-- Center label -->
                    <div class="absolute text-center">
                        <p class="text-sm text-muted-foreground">In-Store Sales</p>
                        <p class="text-2xl font-semibold text-card-foreground">30</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Inbox, Activity, Products -->
    <div class="grid grid-cols-12 gap-6">

        <!-- Inbox -->
        <div class="col-span-4">
            <div class="bg-card rounded-lg shadow-card p-5">
                <h3 class="text-card-foreground font-medium text-base mb-4">Inbox</h3>

                <div class="space-y-4">
                    <?php foreach ($inboxMessages as $msg): ?>
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 rounded-full <?= $msg['color'] ?> flex items-center justify-center text-white text-sm font-medium">
                                <?= getInitial($msg['name']) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-card-foreground text-sm font-medium"><?= htmlspecialchars($msg['name']) ?>
                                </p>
                                <p class="text-muted-foreground text-sm truncate"><?= htmlspecialchars($msg['message']) ?>
                                </p>
                            </div>
                            <span
                                class="text-muted-foreground text-xs whitespace-nowrap"><?= htmlspecialchars($msg['time']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="col-span-4">
            <div class="bg-card rounded-lg shadow-card p-5">
                <h3 class="text-card-foreground font-medium text-base mb-4">Recent Activity Feed</h3>

                <!-- Timeline dots -->
                <div class="flex items-center justify-between mb-6">
                    <?php foreach ($activityDates as $d): ?>
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full <?= $d['active'] ? 'bg-primary' : 'bg-muted' ?>"></div>
                            <?php if ($d['active']): ?>
                                <div class="mt-2 bg-primary text-primary-foreground text-xs px-3 py-1 rounded">
                                    <?= htmlspecialchars($d['date']) ?>
                                </div>
                            <?php else: ?>
                                <span class="mt-2 text-muted-foreground text-xs"><?= htmlspecialchars($d['date']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Activity content -->
                <div class="border-l-2 border-muted pl-4 ml-1">
                    <p class="text-muted-foreground text-xs mb-1">21 Sep, 2018</p>
                    <p class="text-card-foreground text-sm font-medium mb-2">
                        Responded to need "Volunteer Activities"
                    </p>
                    <p class="text-muted-foreground text-sm italic mb-3">
                        Aenean vulputate eleifend tellus
                    </p>
                    <p class="text-muted-foreground text-sm leading-relaxed">
                        Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus
                        Nullam quis ante.
                    </p>
                    <a href="#" class="text-primary text-sm hover:underline mt-2 inline-block">
                        Read More...
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Product Sales -->
        <div class="col-span-4">
            <div class="bg-card rounded-lg shadow-card p-5">
                <h3 class="text-card-foreground font-medium text-base mb-4">Top product sales</h3>

                <div class="space-y-5">
                    <?php foreach ($topProducts as $product): ?>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <p class="text-card-foreground text-sm font-medium">
                                    <?= htmlspecialchars($product['name']) ?></p>
                                <p class="text-muted-foreground text-xs"><?= htmlspecialchars($product['description']) ?>
                                </p>
                            </div>

                            <!-- Circular progress indicator -->
                            <div class="relative w-12 h-12">
                                <svg class="w-12 h-12 transform -rotate-90">
                                    <circle cx="24" cy="24" r="20" stroke="hsl(220, 14%, 92%)" stroke-width="4"
                                        fill="none" />
                                    <circle cx="24" cy="24" r="20"
                                        stroke="<?= $product['color'] === 'destructive' ? 'hsl(0, 72%, 51%)' : 'hsl(231, 76%, 62%)' ?>"
                                        stroke-width="4" fill="none"
                                        stroke-dasharray="<?= getStrokeDasharray($product['percentage']) ?>"
                                        stroke-linecap="round" />
                                </svg>
                            </div>

                            <div class="text-right w-12">
                                <p class="text-card-foreground text-sm font-semibold"><?= $product['percentage'] ?>%</p>
                                <p class="text-muted-foreground text-xs">Sales</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Tables -->
    <div class="grid grid-cols-2 gap-6">

        <!-- Latest Transaction -->
        <div class="bg-card rounded-lg shadow-card p-5">
            <h3 class="text-card-foreground font-medium text-base mb-4">Latest Transaction</h3>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-muted-foreground text-sm">
                            <th class="pb-3 font-medium">(#) Id</th>
                            <th class="pb-3 font-medium">Name</th>
                            <th class="pb-3 font-medium">Date</th>
                            <th class="pb-3 font-medium">Amount</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($transactions as $tx): ?>
                            <tr class="border-t border-border">
                                <td class="py-4 text-primary font-medium"><?= htmlspecialchars($tx['id']) ?></td>
                                <td class="py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary text-xs font-medium">
                                            <?= getInitial($tx['name']) ?>
                                        </div>
                                        <span class="text-card-foreground"><?= htmlspecialchars($tx['name']) ?></span>
                                    </div>
                                </td>
                                <td class="py-4 text-muted-foreground"><?= htmlspecialchars($tx['date']) ?></td>
                                <td class="py-4 text-card-foreground"><?= htmlspecialchars($tx['amount']) ?></td>
                                <td class="py-4">
                                    <span class="px-2 py-1 rounded text-xs font-medium <?= $statusColors[$tx['status']] ?>">
                                        <?= htmlspecialchars($tx['status']) ?>
                                    </span>
                                </td>
                                <td class="py-4">
                                    <button
                                        class="bg-primary text-primary-foreground px-3 py-1 rounded text-xs hover:bg-primary/90">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Latest Order -->
        <div class="bg-card rounded-lg shadow-card p-5">
            <h3 class="text-card-foreground font-medium text-base mb-4">Latest Order</h3>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-muted-foreground text-sm">
                            <th class="pb-3 font-medium">(#) Id</th>
                            <th class="pb-3 font-medium">Name</th>
                            <th class="pb-3 font-medium">Date/Time</th>
                            <th class="pb-3 font-medium">Amount</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($orders as $order): ?>
                            <tr class="border-t border-border">
                                <td class="py-4 text-primary font-medium"><?= htmlspecialchars($order['id']) ?></td>
                                <td class="py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-muted flex items-center justify-center text-muted-foreground text-xs font-medium">
                                            <?= getInitial($order['name']) ?>
                                        </div>
                                        <span class="text-card-foreground"><?= htmlspecialchars($order['name']) ?></span>
                                    </div>
                                </td>
                                <td class="py-4 text-muted-foreground text-xs">
                                    <?= htmlspecialchars($order['date']) ?><br><?= htmlspecialchars($order['time']) ?>
                                </td>
                                <td class="py-4 text-card-foreground"><?= htmlspecialchars($order['amount']) ?></td>
                                <td class="py-4">
                                    <span
                                        class="px-2 py-1 rounded text-xs font-medium <?= $statusColors[$order['status']] ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td class="py-4">
                                    <button
                                        class="bg-primary text-primary-foreground px-3 py-1 rounded text-xs hover:bg-primary/90">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>