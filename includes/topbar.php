<header class="h-16 glass flex items-center justify-between px-6 border-b border-white/10 relative z-20">
    <div class="flex items-center md:hidden">
        <button onclick="toggleSidebar()" class="text-slate-400 hover:text-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
    </div>

    <!-- Global Search Bar -->
    <div class="hidden md:flex flex-1 max-w-lg mx-auto relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input type="text" id="globalSearch" 
            class="block w-full pl-10 pr-3 py-2 border border-slate-700 rounded-lg leading-5 bg-slate-800 text-slate-300 placeholder-slate-500 focus:outline-none focus:bg-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 sm:text-sm transition duration-150 ease-in-out"
            placeholder="Search Net Plan # or Beneficiary Name..."
            autocomplete="off">
        
        <!-- Search Results Dropdown -->
        <div id="globalSearchResults" class="absolute top-full left-0 w-full mt-2 bg-slate-800 border border-slate-700 rounded-lg shadow-xl hidden overflow-hidden z-50">
            <!-- Results injected here -->
        </div>
    </div>

    <div class="ml-auto flex items-center space-x-4">
        <div class="text-right hidden sm:block">
            <div class="text-sm font-medium text-white"><?php echo htmlspecialchars(ucfirst($username)); ?></div>
            <div class="text-xs text-slate-400"><?php echo str_replace('_', ' ', htmlspecialchars(ucfirst($role))); ?></div>
        </div>
        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg">
            <?php echo strtoupper(substr($username, 0, 1)); ?>
        </div>
    </div>
</header>
