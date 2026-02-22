<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Dashboard";
$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Fetch Stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM families");
    $totalFamilies = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM hamlets");
    $totalHamlets = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM staff");
    $totalStaff = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM committee_members");
    $totalCommittee = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM targets WHERE status != 'Completed'");
    $pendingTargets = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT (SELECT COUNT(*) FROM ptdc_meetings) + (SELECT COUNT(*) FROM nvs_meetings)");
    $totalMeetings = $stmt->fetchColumn();
} catch (PDOException $e) {
    $totalFamilies = $totalHamlets = $totalStaff = $totalCommittee = $pendingTargets = $totalMeetings = 0;
}

require_once 'includes/header.php';
?>

<div class="flex h-screen overflow-hidden bg-slate-900">
    
    <!-- Sidebar -->
    <?php require_once 'includes/nav.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-y-auto">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Top Bar -->
        <?php require_once 'includes/topbar.php'; ?>

        <!-- Main Area -->
        <main class="p-6 space-y-6">
            <!-- Welcome Header -->
            <div class="flex items-end justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-white">Dashboard Overview</h2>
                    <p class="text-slate-400 mt-1">Here's what's happening with the NABARD project.</p>
                </div>
                <div class="hidden sm:block">
                   <span class="px-4 py-2 bg-blue-500/10 text-blue-300 rounded-full text-sm font-medium border border-blue-500/20">
                    <?php echo date('l, F j, Y'); ?>
                   </span> 
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat Card 1: Families -->
                <a href="family_details.php" class="glass p-6 rounded-xl border border-white/5 relative overflow-hidden group block transition-all hover:scale-[1.02] hover:shadow-lg hover:shadow-blue-500/10 cursor-pointer">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all duration-500"></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <div class="text-slate-400 text-sm font-medium">Beneficiaries</div>
                            <div class="text-3xl font-bold text-white mt-1"><?php echo number_format($totalFamilies); ?></div>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-400">
                             <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                    </div>
                    <div class="text-blue-400 text-xs mt-4 flex items-center">Families in Database</div>
                </a>
                
                <!-- Stat Card 2: Staff -->
                <a href="staff.php" class="glass p-6 rounded-xl border border-white/5 relative overflow-hidden group block transition-all hover:scale-[1.02] hover:shadow-lg hover:shadow-purple-500/10 cursor-pointer">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all duration-500"></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <div class="text-slate-400 text-sm font-medium">Total Staff</div>
                            <div class="text-3xl font-bold text-white mt-1"><?php echo number_format($totalStaff); ?></div>
                        </div>
                         <div class="h-12 w-12 rounded-lg bg-purple-500/20 flex items-center justify-center text-purple-400">
                             <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                    </div>
                     <div class="text-purple-400 text-xs mt-4 flex items-center">Active Personnel</div>
                </a>

                <!-- Stat Card 3: Committee -->
                <a href="committee.php" class="glass p-6 rounded-xl border border-white/5 relative overflow-hidden group block transition-all hover:scale-[1.02] hover:shadow-lg hover:shadow-emerald-500/10 cursor-pointer">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <div class="text-slate-400 text-sm font-medium">Committee Members</div>
                            <div class="text-3xl font-bold text-white mt-1"><?php echo number_format($totalCommittee); ?></div>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                             <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                    </div>
                     <div class="text-emerald-400 text-xs mt-4 flex items-center">In PTDC</div>
                </a>

                <!-- Stat Card 4: Pending Targets -->
                <a href="targets.php" class="glass p-6 rounded-xl border border-white/5 relative overflow-hidden group block transition-all hover:scale-[1.02] hover:shadow-lg hover:shadow-pink-500/10 cursor-pointer">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-pink-500/10 rounded-full blur-2xl group-hover:bg-pink-500/20 transition-all duration-500"></div>
                     <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <div class="text-slate-400 text-sm font-medium">Pending Targets</div>
                            <div class="text-3xl font-bold text-white mt-1"><?php echo number_format($pendingTargets); ?></div>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-pink-500/20 flex items-center justify-center text-pink-400">
                             <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                        </div>
                    </div>
                     <div class="text-pink-400 text-xs mt-4 flex items-center">Requires Attention</div>
                </a>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Targets -->
                <div class="lg:col-span-2 glass rounded-xl border border-white/5 p-6">
                    <h3 class="text-xl font-bold text-white mb-4">Pending Staff Targets</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-400">
                            <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 rounded-l-lg">Staff</th>
                                    <th class="px-4 py-3">Target</th>
                                    <th class="px-4 py-3 text-center">Deadline</th>
                                    <th class="px-4 py-3 rounded-r-lg text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php
                                try {
                                    $stmt = $pdo->query("SELECT t.*, s.name as staff_name FROM targets t JOIN staff s ON t.staff_id = s.id WHERE t.status != 'Completed' ORDER BY t.proposed_completion_date ASC LIMIT 5");
                                    $recentTargets = $stmt->fetchAll();
                                    
                                    if (count($recentTargets) > 0) {
                                        foreach ($recentTargets as $target) {
                                            $overdue = (strtotime($target['proposed_completion_date']) < time()) ? 'text-red-400' : 'text-slate-400';
                                            echo "<tr class='hover:bg-white/5 transition-colors'>
                                                <td class='px-4 py-3 font-medium text-white'>{$target['staff_name']}</td>
                                                <td class='px-4 py-3 truncate max-w-[200px]'>{$target['target_description']}</td>
                                                <td class='px-4 py-3 text-center text-xs {$overdue}'>".date('M d, Y', strtotime($target['proposed_completion_date']))."</td>
                                                <td class='px-4 py-3 text-right'><a href='targets.php' class='text-blue-400 hover:text-blue-300'>View</a></td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='px-4 py-8 text-center text-slate-500 italic'>No pending targets found.</td></tr>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<tr><td colspan='4' class='px-4 py-3 text-center'>Error loading targets.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Side Panel (Role Based) -->
                <div class="glass rounded-xl border border-white/5 p-6">
                    <h3 class="text-xl font-bold text-white mb-4">
                        <?php 
                        if ($role === 'super_admin') echo 'System Info';
                        elseif ($role === 'admin') echo 'Quick Actions';
                        else echo 'Notifications';
                        ?>
                    </h3>
                    
                    <div class="space-y-4">
                        <?php if ($role === 'super_admin'): ?>
                            <!-- Database Status -->
                            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                                <h4 class="font-bold text-emerald-300 text-sm flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                                    Database Status
                                </h4>
                                <div class="mt-2 space-y-1 text-xs text-emerald-200/70">
                                    <div class="flex justify-between">
                                        <span>Status:</span>
                                        <span class="text-white">Online</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Families:</span>
                                        <span class="text-white"><?php echo $totalFamilies; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Staff:</span>
                                        <span class="text-white"><?php echo $totalStaff; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Last Login -->
                            <div class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                                <h4 class="font-bold text-blue-300 text-sm">Last Login</h4>
                                <div class="mt-2 space-y-1">
                                    <div class="text-white font-medium text-sm"><?php echo htmlspecialchars(ucfirst($username)); ?></div>
                                    <div class="text-xs text-blue-200/70">
                                        <?php echo isset($_SESSION['last_login']) ? date('M d, Y h:i A', $_SESSION['last_login']) : date('M d, Y h:i A'); ?>
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 border-t border-blue-500/20 text-xs text-blue-400">
                                    IP: <?php echo $_SERVER['REMOTE_ADDR']; ?>
                                </div>
                            </div>
                        <?php elseif ($role === 'admin'): ?>
                            <div class="p-4 bg-purple-500/10 border border-purple-500/20 rounded-lg">
                                <h4 class="font-bold text-purple-300 text-sm">Generate Report</h4>
                                <p class="text-xs text-purple-200/70 mt-1">Export modules done record.</p>
                                <a href="modules_report_pdf.php" class="mt-2 w-full inline-block text-center bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 text-xs py-2 rounded transition-colors">Download PDF</a>
                            </div>
                        <?php else: ?>
                            <div class="flex items-start space-x-3 p-3 hover:bg-white/5 rounded-lg transition-colors cursor-pointer">
                                <div class="h-2 w-2 mt-2 rounded-full bg-blue-500"></div>
                                <div>
                                    <p class="text-sm text-slate-300">New guidelines for water conservation published.</p>
                                    <span class="text-xs text-slate-500">2 hours ago</span>
                                </div>
                            </div>
                             <div class="flex items-start space-x-3 p-3 hover:bg-white/5 rounded-lg transition-colors cursor-pointer">
                                <div class="h-2 w-2 mt-2 rounded-full bg-green-500"></div>
                                <div>
                                    <p class="text-sm text-slate-300">Your report was approved by Admin.</p>
                                    <span class="text-xs text-slate-500">Yesterday</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
