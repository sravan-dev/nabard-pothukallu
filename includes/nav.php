    <!-- Mobile Backdrop -->
    <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm hidden md:hidden transition-opacity"></div>

    <!-- Sidebar -->
    <aside id="mainSidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 border-r border-white/10 transition-transform transform -translate-x-full md:translate-x-0 md:static md:flex md:w-80 glass flex flex-col pt-4">
        <div class="h-16 flex items-center justify-center border-b border-white/10 relative">
            <div class="absolute inset-0 bg-blue-500/10 blur-xl"></div>
            <div class="relative z-10 flex items-center space-x-3">
                <img src="<?php echo SITE_LOGO; ?>" alt="Logo" class="h-8 w-8 object-contain">
                <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-purple-400">
                    <?php echo htmlspecialchars(SITE_TITLE); ?><sup style="color:white;font-size:16px;"> <?php echo htmlspecialchars(SITE_SUP); ?></sup>
                </h1>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Menu</div>
            <nav class="space-y-2">
                <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
                
                <a href="dashboard.php" class="<?php echo $currentPage == 'dashboard.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    <span>Dashboard</span>
                </a>
                <a href="modules_report_pdf.php" class="<?php echo $currentPage == 'modules_report_pdf.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 3h5l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" /></svg>
                    <span>Modules PDF</span>
                </a>

                <!-- Project at a Glance Submenu -->
                <div>
                    <button onclick="toggleSubmenu('projectGlance')" class="w-full flex items-center justify-between px-4 py-3 text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                        <div class="flex items-center space-x-3">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                            <span>Project At Glance</span>
                        </div>
                        <svg id="arrow-projectGlance" class="h-4 w-4 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div id="submenu-projectGlance" class="<?php echo ($currentPage == 'hamlets.php' || $currentPage == 'settlement_hamlet_details.php' || $currentPage == 'family_details.php') ? '' : 'hidden'; ?> pl-11 pr-4 space-y-1 mt-1">
                        <a href="hamlets.php" class="<?php echo $currentPage == 'hamlets.php' ? 'text-blue-200' : 'text-slate-500 hover:text-slate-300'; ?> flex items-center space-x-2 py-2 text-sm transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 00-2-2H9a2 2 0 00-2 2v2h4zm4-2a2 2 0 002-2V7a2 2 0 00-2-2h-3" /></svg>
                            <span>Hamlets</span>
                        </a>
                        <a href="settlement_hamlet_details.php" class="<?php echo $currentPage == 'settlement_hamlet_details.php' ? 'text-blue-200' : 'text-slate-500 hover:text-slate-300'; ?> flex items-center space-x-2 py-2 text-sm transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Settlement/Hamlet</span>
                        </a>
                        <a href="family_details.php" class="<?php echo $currentPage == 'family_details.php' ? 'text-blue-200' : 'text-slate-500 hover:text-slate-300'; ?> flex items-center space-x-2 py-2 text-sm transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            <span>Family Details</span>
                        </a>
                    </div>
                </div>
                
                
                <a href="committee.php" class="<?php echo $currentPage == 'committee.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span>PTDC</span>
                </a>

                <a href="ptdc_meetings.php" class="<?php echo $currentPage == 'ptdc_meetings.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>PTDC Meetings</span>
                </a>

                <a href="vikasana_committee.php" class="<?php echo $currentPage == 'vikasana_committee.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    <span>NVS</span>
                </a>

                <a href="nvs_meetings.php" class="<?php echo $currentPage == 'nvs_meetings.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span>NVS Meetings</span>
                </a>

                <a href="programmes.php" class="<?php echo $currentPage == 'programmes.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                    <span>Programmes / Activities</span>
                </a>

                <a href="success_stories.php" class="<?php echo $currentPage == 'success_stories.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Success Stories</span>
                </a>

                <a href="staff.php" class="<?php echo $currentPage == 'staff.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    <span>Staff Entry</span>
                </a>

                <a href="targets.php" class="<?php echo $currentPage == 'targets.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                    <span>Target Entry</span>
                </a>

                <a href="project_components.php" class="<?php echo $currentPage == 'project_components.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    <span>Project Components</span>
                </a>

                <a href="individual_distribution_details.php" class="<?php echo $currentPage == 'individual_distribution_details.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h8m0 0l-3-3m3 3l-3 3M5 7h12M5 12h7M5 17h2" /></svg>
                    <span>Individual Distribution Details</span>
                </a>

                <a href="jlg_group_details.php" class="<?php echo $currentPage == 'jlg_group_details.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span>JLG/Group Details</span>
                </a>

                <a href="group_jlg_distribution_details.php" class="<?php echo $currentPage == 'group_jlg_distribution_details.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h8m0 0l-3-3m3 3l-3 3M5 7h12M5 12h7M5 17h2" /></svg>
                    <span>Group/JLG Distribution Details</span>
                </a>

                <a href="general_activity_meetings.php" class="<?php echo $currentPage == 'general_activity_meetings.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span>General Activity Meetings</span>
                </a>

                <a href="social_auditing.php" class="<?php echo $currentPage == 'social_auditing.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span>Social Auditing</span>
                </a>

                <?php if ($role === 'super_admin' || $role === 'admin'): ?>
                <a href="users.php" class="<?php echo $currentPage == 'users.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    <span>Users</span>
                </a>
                <?php endif; ?>

                <?php if ($role === 'super_admin'): ?>
                <a href="settings.php" class="<?php echo $currentPage == 'settings.php' ? 'bg-blue-600/20 text-blue-200 border border-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?> flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span>Settings</span>
                </a>
                <?php endif; ?>
            </nav>
        </div>
        
        <div class="mt-auto p-4 border-t border-white/10">
            <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 text-red-300 hover:bg-red-500/10 rounded-lg transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <script>
        function toggleSubmenu(id) {
            const submenu = document.getElementById('submenu-' + id);
            const arrow = document.getElementById('arrow-' + id);
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                submenu.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }
        
        // Auto-expand if active child
        document.addEventListener('DOMContentLoaded', () => {
           // We handled initial state via PHP class logic, but ensuring arrow rotation matches:
           if (!document.getElementById('submenu-projectGlance').classList.contains('hidden')) {
               document.getElementById('arrow-projectGlance').classList.add('rotate-180');
           }
        });
    </script>
