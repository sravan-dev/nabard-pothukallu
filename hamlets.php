<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Hamlets - Project at a Glance";
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
        <main class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-white">Hamlets</h2>
                    <p class="text-slate-400 text-sm">Settlement & Hamlet Details</p>
                </div>
                
                <button onclick="openModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Settlement
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="hamletsTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Settlement Name</th>
                            <th class="px-6 py-4">Locality (Ward/Panchayat)</th>
                            <th class="px-6 py-4">NVS President</th>
                            <th class="px-6 py-4">Population</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5" id="dataBody">
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Large Modal for Data Entry -->
<div id="dataModal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-900 rounded-xl shadow-2xl w-full max-w-4xl border border-slate-700 transform scale-95 transition-transform duration-300 max-h-[90vh] flex flex-col" id="modalContent">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800 rounded-t-xl">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add Settlement Details</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-white/10 bg-slate-800/50">
            <button class="px-6 py-3 text-sm font-medium text-emerald-400 border-b-2 border-emerald-400 focus:outline-none tab-btn" data-tab="tab-location">Location</button>
            <button class="px-6 py-3 text-sm font-medium text-slate-400 hover:text-white focus:outline-none tab-btn" data-tab="tab-demographics">Demographics</button>
            <button class="px-6 py-3 text-sm font-medium text-slate-400 hover:text-white focus:outline-none tab-btn" data-tab="tab-nvs">NVS Details</button>
            <button class="px-6 py-3 text-sm font-medium text-slate-400 hover:text-white focus:outline-none tab-btn" data-tab="tab-infra">Infrastructure</button>
            <button class="px-6 py-3 text-sm font-medium text-slate-400 hover:text-white focus:outline-none tab-btn" data-tab="tab-media">Media</button>
        </div>

        <!-- Form Content (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <form id="dataForm" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="id" id="dataId">
                <input type="hidden" name="action" id="dataAction" value="create">

                <!-- Tab: Location -->
                <div id="tab-location" class="tab-content space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Name of Settlement *</label>
                            <input type="text" name="settlement_name" id="settlement_name" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Block</label>
                            <input type="text" name="block" id="block" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Panchayat</label>
                            <input type="text" name="panchayat" id="panchayat" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Ward Name</label>
                            <input type="text" name="ward" id="ward" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Ward Number</label>
                            <input type="text" name="ward_number" id="ward_number" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Total Area (Acres)</label>
                            <input type="text" name="total_area" id="total_area" placeholder="e.g. 5.5" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Tab: Demographics -->
                <div id="tab-demographics" class="tab-content hidden space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                         <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">No. of Families</label>
                            <input type="number" name="total_families" id="total_families" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">No. of Households</label>
                            <input type="number" name="households" id="households" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Tribal Category</label>
                            <input type="text" name="tribal_category" id="tribal_category" placeholder="e.g. Paniya" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>
                    <div class="p-4 bg-slate-800/50 rounded-lg border border-white/5">
                        <h4 class="text-sm font-bold text-slate-400 mb-3 uppercase tracking-wider">Population Breakdown</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Total Population</label>
                                <input type="number" name="population_total" id="population_total" class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Male</label>
                                <input type="number" name="population_male" id="population_male" class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Female</label>
                                <input type="number" name="population_female" id="population_female" class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: NVS Details -->
                <div id="tab-nvs" class="tab-content hidden space-y-4">
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">NVS Formation Date</label>
                            <input type="date" name="nvs_formation_date" id="nvs_formation_date" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                             <!-- Spacer or other field -->
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">NVS President Name</label>
                            <input type="text" name="nvs_president" id="nvs_president" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">NVS Secretary Name</label>
                            <input type="text" name="nvs_secretary" id="nvs_secretary" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Animator Name</label>
                            <input type="text" name="animator_name" id="animator_name" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Animator Mobile</label>
                            <input type="text" name="animator_mobile" id="animator_mobile" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Tab: Infrastructure -->
                <div id="tab-infra" class="tab-content hidden space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Public Facilities Available</label>
                        <textarea name="public_facilities" id="public_facilities" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Road Access Details</label>
                        <input type="text" name="road_access" id="road_access" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Major Crops</label>
                        <textarea name="major_crops" id="major_crops" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Major Issues / Challenges</label>
                        <textarea name="major_issues" id="major_issues" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500"></textarea>
                    </div>
                </div>

                <!-- Tab: Media -->
                <div id="tab-media" class="tab-content hidden space-y-4">
                     <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Google Maps Link</label>
                        <input type="url" name="map_link" id="map_link" placeholder="https://maps.google.com/..." class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-1 focus:ring-emerald-500">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Photo 1</label>
                            <input type="file" name="photo1" accept="image/*" class="w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Photo 2</label>
                            <input type="file" name="photo2" accept="image/*" class="w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Photo 3</label>
                            <input type="file" name="photo3" accept="image/*" class="w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-white">
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-700 bg-slate-800 rounded-b-xl flex justify-between items-center">
            <div class="text-xs text-slate-500 italic">* Required fields</div>
            <div class="space-x-3">
                 <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                <button type="button" onclick="submitForm()" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-colors shadow-lg">Save Settlement</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadData();
        setupTabs();
    });

    function setupTabs() {
        const tabs = document.querySelectorAll('.tab-btn');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Deactivate all
                document.querySelectorAll('.tab-btn').forEach(t => {
                    t.classList.remove('text-emerald-400', 'border-b-2', 'border-emerald-400');
                    t.classList.add('text-slate-400');
                });
                document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));

                // Activate clicked
                tab.classList.remove('text-slate-400');
                tab.classList.add('text-emerald-400', 'border-b-2', 'border-emerald-400');
                document.getElementById(tab.dataset.tab).classList.remove('hidden');
            });
        });
    }

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        
        try {
            const response = await fetch('api/hamlets_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                result.data.forEach(row => {
                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-white">${row.settlement_name}</div>
                                <div class="text-xs text-slate-500">${row.tribal_category || ''}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-300">
                                ${row.ward ? row.ward : '-'} <span class="text-slate-500 text-xs">(${row.panchayat || 'N/A'})</span>
                            </td>
                            <td class="px-6 py-4 text-slate-300">
                                ${row.nvs_president || '-'}
                            </td>
                            <td class="px-6 py-4 font-mono text-emerald-300">
                                ${row.population_total || 0}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="editData(${row.id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteData(${row.id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                initializeDataTable('#hamletsTable');
            }
        } catch (error) { console.error('Error:', error); }
    }

    // Modal Logic
    const modal = document.getElementById('dataModal');
    const modalContent = document.getElementById('modalContent');

    function openModal(isEdit = false) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);

        if (!isEdit) {
            document.getElementById('dataForm').reset();
            document.getElementById('dataId').value = '';
            document.getElementById('dataAction').value = 'create';
            document.getElementById('modalTitle').innerText = 'Add Settlement Details';
            // Reset Tabs
            document.querySelector('[data-tab="tab-location"]').click();
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Settlement Details';
        }
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    function submitForm() {
        document.getElementById('dataForm').dispatchEvent(new Event('submit'));
    }

    // Form Submit
    document.getElementById('dataForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/hamlets_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                closeModal();
                loadData();
                Swal.fire({
                    icon: 'success', title: 'Success', text: result.message,
                    background: '#1e293b', color: '#fff', timer: 1500, showConfirmButton: false
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message, background: '#1e293b', color: '#fff' });
            }
        } catch (error) { console.error('Error:', error); }
    });

    // Edit
    async function editData(id) {
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        try {
            const response = await fetch('api/hamlets_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                const setVal = (id, val) => { if(document.getElementById(id)) document.getElementById(id).value = val || ''; }

                document.getElementById('dataId').value = data.id;
                document.getElementById('dataAction').value = 'update';

                // Populate Fields
                setVal('settlement_name', data.settlement_name);
                setVal('block', data.block);
                setVal('panchayat', data.panchayat);
                setVal('ward', data.ward);
                setVal('ward_number', data.ward_number);
                setVal('total_area', data.total_area);
                
                setVal('total_families', data.total_families);
                setVal('households', data.households);
                setVal('tribal_category', data.tribal_category);
                setVal('population_total', data.population_total);
                setVal('population_male', data.population_male);
                setVal('population_female', data.population_female);

                setVal('nvs_formation_date', data.nvs_formation_date);
                setVal('nvs_president', data.nvs_president);
                setVal('nvs_secretary', data.nvs_secretary);
                setVal('animator_name', data.animator_name);
                setVal('animator_mobile', data.animator_mobile);
                
                setVal('public_facilities', data.public_facilities);
                setVal('road_access', data.road_access);
                setVal('major_crops', data.major_crops);
                setVal('major_issues', data.major_issues);
                
                setVal('map_link', data.map_link);

                openModal(true);
            }
        } catch (error) { console.error('Error:', error); }
    }

    // Delete
    function deleteData(id) {
        Swal.fire({
            title: 'Are you sure?', icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#10b981', cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it!', background: '#1e293b', color: '#fff'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                try {
                    const response = await fetch('api/hamlets_action.php', { method: 'POST', body: formData });
                    const res = await response.json();
                    if (res.status === 'success') {
                        loadData();
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, background: '#1e293b', color: '#fff' });
                    }
                } catch (error) { console.error('Error:', error); }
            }
        })
    }
</script>

<?php require_once 'includes/footer.php'; ?>
