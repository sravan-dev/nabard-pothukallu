<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();
// Only Super Admin should access
check_role(['super_admin']);

$pageTitle = "Settings";
$role = $_SESSION['role'];
$username = $_SESSION['username'];

require_once 'includes/header.php';
?>

<div class="flex h-screen overflow-hidden bg-slate-900">
    
    <?php require_once 'includes/nav.php'; ?>

    <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Top Bar -->
        <?php require_once 'includes/topbar.php'; ?>

        <!-- Main Area -->
        <main class="p-6 space-y-6">
            <h2 class="text-3xl font-bold text-white">System Settings</h2>

            <!-- General Settings -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Branding & General -->
                 <div class="glass rounded-xl border border-white/5 p-6 space-y-6">
                    <h3 class="text-xl font-semibold text-white border-b border-white/10 pb-2">General & Branding</h3>
                    <form id="generalForm" class="space-y-4">
                        <input type="hidden" name="action" value="update_general">
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Site Title</label>
                            <input type="text" name="site_title" value="<?php echo htmlspecialchars(SITE_TITLE); ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Sup Tag (e.g. Location)</label>
                            <input type="text" name="site_sup" value="<?php echo htmlspecialchars(SITE_SUP); ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Timezone</label>
                            <select name="timezone" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <?php
                                $zones = DateTimeZone::listIdentifiers();
                                $currentTz = date_default_timezone_get();
                                foreach($zones as $zone) {
                                    $selected = $zone == $currentTz ? 'selected' : '';
                                    echo "<option value='$zone' $selected>$zone</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="pt-2">
                             <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2 rounded-lg transition-colors">Save General Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Logo Settings -->
                <div class="glass rounded-xl border border-white/5 p-6 space-y-6">
                    <h3 class="text-xl font-semibold text-white border-b border-white/10 pb-2">Logo</h3>
                     <div class="flex items-center space-x-6">
                        <div class="h-24 w-24 bg-slate-800 rounded-lg border border-slate-700 flex items-center justify-center overflow-hidden">
                            <img src="<?php echo SITE_LOGO; ?>" id="logoPreview" class="object-contain h-full w-full">
                        </div>
                        <form id="logoForm" class="flex-1 space-y-4">
                             <input type="hidden" name="action" value="update_logo">
                             <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Upload New Logo (PNG/JPG)</label>
                                <input type="file" name="logo" accept="image/*" class="w-full text-slate-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500">
                             </div>
                             <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm transition-colors">Update Logo</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Utilities -->
            <div class="glass rounded-xl border border-white/5 p-6 space-y-6">
                <h3 class="text-xl font-semibold text-white border-b border-white/10 pb-2">System Utilities</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- DB Status -->
                     <div class="space-y-4">
                        <h4 class="text-lg text-slate-200">Database Status</h4>
                        <div id="dbStats" class="grid grid-cols-2 gap-4 text-sm text-slate-400">
                            <!-- Populated via JS -->
                            <div class="col-span-2 text-slate-500 italic">Click check to load...</div>
                        </div>
                        <button onclick="checkDbStatus()" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">Check Status</button>
                     </div>

                     <!-- Export -->
                     <div class="space-y-4 border-l border-white/5 pl-0 md:pl-6">
                        <h4 class="text-lg text-slate-200">Backup</h4>
                        <p class="text-sm text-slate-400">Download a full SQL dump of the database.</p>
                        <button onclick="exportDb()" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export Database
                        </button>
                     </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('generalForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/settings_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                Swal.fire({
                    icon: 'success', title: 'Saved', text: 'Settings updated. Refresh to see changes.', 
                    background: '#1e293b', color: '#fff', showConfirmButton: true, confirmButtonText: 'Refresh'
                }).then((r) => { if(r.isConfirmed) location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message, background: '#1e293b', color: '#fff' });
            }
        } catch (error) { console.error(error); }
    });

    document.getElementById('logoForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/settings_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                document.getElementById('logoPreview').src = result.path + '?t=' + new Date().getTime();
                Swal.fire({
                    icon: 'success', title: 'Updated', text: 'Logo updated successfully.', 
                    background: '#1e293b', color: '#fff', timer: 1500, showConfirmButton: false
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message, background: '#1e293b', color: '#fff' });
            }
        } catch (error) { console.error(error); }
    });

    async function checkDbStatus() {
        const formData = new FormData();
        formData.append('action', 'check_db_status');
        try {
            const response = await fetch('api/settings_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const container = document.getElementById('dbStats');
                container.innerHTML = '';
                for (const [table, count] of Object.entries(result.data)) {
                    container.innerHTML += `
                        <div class="flex justify-between items-center bg-slate-800 p-2 rounded border border-slate-700">
                             <span class="capitalize text-slate-300">${table.replace('_', ' ')}</span>
                             <span class="font-mono text-indigo-400 font-bold">${count}</span>
                        </div>
                    `;
                }
            }
        } catch (error) { console.error(error); }
    }

    async function exportDb() {
         const formData = new FormData();
        formData.append('action', 'export_db');
        try {
            Swal.fire({ title: 'Generating...', text: 'Please wait...', allowOutsideClick: false, didOpen: () => Swal.showLoading(), background: '#1e293b', color: '#fff' });
            
            const response = await fetch('api/settings_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                Swal.close();
                const a = document.createElement('a');
                a.href = result.download_url;
                a.download = result.filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message, background: '#1e293b', color: '#fff' });
            }
        } catch (error) { console.error(error); }
    }
</script>

<?php require_once 'includes/footer.php'; ?>
