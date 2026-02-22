<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "NVS";
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
                    <h2 class="text-3xl font-bold text-white">NVS</h2>
                    <p class="text-slate-400 text-sm">Manage Committee Members</p>
                </div>
                
                <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Member
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="vikasanaTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Photo</th>
                            <th class="px-6 py-4">Designation</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Age</th>
                            <th class="px-6 py-4">Settlement</th>
                            <th class="px-6 py-4">Net Plan #</th>
                            <th class="px-6 py-4">Entry Date</th>
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

<!-- Main Modal -->
<div id="dataModal" class="fixed inset-0 z-40 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-2xl border border-slate-700 transform scale-95 transition-transform duration-300 overflow-y-auto max-h-[90vh]" id="modalContent">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center sticky top-0 bg-slate-800 z-10">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add Member</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="dataForm" class="p-6 space-y-6">
            <input type="hidden" name="id" id="dataId">
            <input type="hidden" name="action" id="dataAction" value="create">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <!-- Designation with Add New -->
                 <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1">Designation</label>
                    <div class="flex space-x-2">
                        <select name="designation_id" id="designation_id" required class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <!-- Populated via AJAX -->
                        </select>
                        <button type="button" onclick="openDesignationModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-2 rounded-lg text-sm whitespace-nowrap">
                            Add New
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Name</label>
                    <input type="text" name="name" id="name" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                 <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Age</label>
                    <input type="number" name="age" id="age" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                     <label class="block text-sm font-medium text-slate-300 mb-1">Date of Entry</label>
                    <input type="date" name="date_of_entry" id="date_of_entry" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none scheme-dark">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Settlement</label>
                    <select name="hamlet_id" id="hamlet_id" required onchange="loadNetPlans(this.value)" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <!-- Populated via AJAX -->
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Net Plan Number</label>
                    <input type="hidden" name="family_id" id="family_id">
                    <input type="text" name="net_plan_number" id="net_plan_number" list="netPlanList" required placeholder="Select Settlement First" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <datalist id="netPlanList"></datalist>
                </div>

                <div class="md:col-span-2">
                     <label class="block text-sm font-medium text-slate-300 mb-1">Photo</label>
                     <input type="file" name="photo" id="photo" accept="image/*"
                         class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
                     <input type="hidden" name="old_photo" id="old_photo">
                     <div id="photoPreview" class="mt-2 hidden">
                         <img src="" class="h-20 w-20 rounded-lg object-cover border border-slate-600">
                     </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors">Save Member</button>
            </div>
        </form>
    </div>
</div>

<!-- Designation Modal (Nested) -->
<div id="desigModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-sm border border-slate-700 transform scale-95 transition-transform duration-300" id="desigModalContent">
        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-lg font-bold text-white">Add Designation</h3>
        </div>
        <div class="p-6">
            <input type="text" id="new_designation" placeholder="e.g. Vice President" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none mb-4">
            <div class="flex justify-end space-x-3">
                <button onclick="closeDesignationModal()" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm">Cancel</button>
                <button onclick="saveDesignation()" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm">Add</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let familyOptions = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadData();
        loadDesignations();
        loadHamlets();
        const netPlanInput = document.getElementById('net_plan_number');
        if (netPlanInput) {
            netPlanInput.addEventListener('input', () => setSelectedFamilyFromNetPlan(netPlanInput.value));
            netPlanInput.addEventListener('change', () => setSelectedFamilyFromNetPlan(netPlanInput.value));
        }
    });

    function normalizeNetPlanValue(value) {
        return String(value || '').trim().replace(/^#\s*/, '').toUpperCase();
    }

    function findFamilyByNetPlan(netPlanValue) {
        const clean = normalizeNetPlanValue(netPlanValue);
        if (!clean) return null;
        return familyOptions.find(f => normalizeNetPlanValue(f.net_plan_number) === clean) || null;
    }

    function setSelectedFamilyFromNetPlan(netPlanValue) {
        const hiddenFamilyId = document.getElementById('family_id');
        if (!hiddenFamilyId) return;
        const match = findFamilyByNetPlan(netPlanValue);
        hiddenFamilyId.value = match ? String(match.id) : '';
    }

    function setNetPlanByFamilyId(familyId) {
        const numericId = Number(familyId) || 0;
        const netPlanInput = document.getElementById('net_plan_number');
        const hiddenFamilyId = document.getElementById('family_id');
        if (!netPlanInput || !hiddenFamilyId) return;

        const match = familyOptions.find(f => Number(f.id) === numericId);
        if (match) {
            netPlanInput.value = String(match.net_plan_number);
            hiddenFamilyId.value = String(match.id);
        } else {
            netPlanInput.value = '';
            hiddenFamilyId.value = '';
        }
    }

    // Load Data
    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        try {
            const response = await fetch('api/vikasana_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                result.data.forEach(row => {
                    const photoHtml = row.photo 
                        ? `<img src="uploads/vikasana/${row.photo}" class="h-10 w-10 rounded-full object-cover border border-slate-600">`
                        : `<div class="h-10 w-10 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold border border-indigo-500/30">${row.name.charAt(0)}</div>`;

                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">${photoHtml}</td>
                            <td class="px-6 py-4 font-medium text-emerald-400">${row.designation_name}</td>
                            <td class="px-6 py-4 text-white">${row.name}</td>
                            <td class="px-6 py-4 text-slate-300">${row.age}</td>
                            <td class="px-6 py-4 text-slate-400">${row.settlement_name}</td>
                            <td class="px-6 py-4 font-mono text-indigo-300">#${row.net_plan_number || 'N/A'}</td>
                            <td class="px-6 py-4 text-slate-400">${row.date_of_entry}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="editData(${row.id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteData(${row.id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                initializeDataTable('#vikasanaTable');
            }
        } catch (e) { console.error(e); }
    }

    // Dropdowns
    async function loadDesignations() { fetchAndPopulate('fetch_designations', 'designation_id', 'id', 'name'); }
    
    // Helper function to actually fetch (reused)
    async function fetchAndPopulate(action, elementId, key, valueKey) {
        const formData = new FormData();
        formData.append('action', action);
        try {
            const response = await fetch('api/vikasana_action.php', { method: 'POST', body: formData });
            const result = await response.json();
             if (result.status === 'success') {
                const select = document.getElementById(elementId);
                const currentVal = select.value;
                select.innerHTML = '<option value="">Select</option>';
                result.data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item[valueKey];
                    select.appendChild(option);
                });
                if(currentVal) select.value = currentVal; // Restore value if needed
            }
        } catch (e) {}
    }


    async function loadHamlets() {
         const formData = new FormData();
        formData.append('action', 'fetch_all');
        try {
            const response = await fetch('api/hamlets_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const select = document.getElementById('hamlet_id');
                select.innerHTML = '<option value="">Select Settlement</option>';
                result.data.forEach(h => {
                    const option = document.createElement('option');
                    option.value = h.id;
                    option.textContent = h.settlement_name;
                    select.appendChild(option);
                });
            }
        } catch (e) {}
    }

    async function loadNetPlans(hamletId) {
        const list = document.getElementById('netPlanList');
        const netPlanInput = document.getElementById('net_plan_number');
        const hiddenFamilyId = document.getElementById('family_id');
        familyOptions = [];
        if (list) list.innerHTML = '';
        if (hiddenFamilyId) hiddenFamilyId.value = '';
        if (netPlanInput) {
            netPlanInput.value = '';
            netPlanInput.placeholder = hamletId ? 'Loading Net Plans...' : 'Select Settlement First';
        }
        if (!hamletId) {
            return;
        }
        const formData = new FormData();
        formData.append('action', 'fetch_families_by_hamlet');
        formData.append('hamlet_id', hamletId);
        
        try {
            const response = await fetch('api/vikasana_action.php', { method: 'POST', body: formData });
            const result = await response.json();
             if (result.status === 'success') {
                familyOptions = Array.isArray(result.data) ? result.data : [];
                if (list) {
                    list.innerHTML = '';
                    familyOptions.forEach(f => {
                        const option = document.createElement('option');
                        option.value = String(f.net_plan_number);
                        option.label = String(f.beneficiary_name || '');
                        list.appendChild(option);
                    });
                }
                if (netPlanInput) {
                    netPlanInput.value = '';
                    netPlanInput.placeholder = familyOptions.length > 0 ? 'Type Net Plan Number' : 'No Net Plans in this settlement';
                }
                if (hiddenFamilyId) hiddenFamilyId.value = '';
            }
        } catch (e) {}
    }

    // Main Modal Logic
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
            document.getElementById('modalTitle').innerText = 'Add Member';
            familyOptions = [];
            document.getElementById('family_id').value = '';
            document.getElementById('net_plan_number').value = '';
            document.getElementById('net_plan_number').placeholder = 'Select Settlement First';
            document.getElementById('netPlanList').innerHTML = '';
            document.getElementById('photoPreview').classList.add('hidden');
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Member';
        }
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    // Designation Modal Logic
    const desigModal = document.getElementById('desigModal');
    const desigModalContent = document.getElementById('desigModalContent');
    
    function openDesignationModal() {
        desigModal.classList.remove('hidden');
        setTimeout(() => {
            desigModal.classList.remove('opacity-0');
            desigModalContent.classList.remove('scale-95');
            desigModalContent.classList.add('scale-100');
        }, 10);
        document.getElementById('new_designation').value = '';
    }

    function closeDesignationModal() {
        desigModal.classList.add('opacity-0');
        desigModalContent.classList.remove('scale-100');
        desigModalContent.classList.add('scale-95');
        setTimeout(() => { desigModal.classList.add('hidden'); }, 300);
    }

    async function saveDesignation() {
        const name = document.getElementById('new_designation').value;
        if (!name) return;

        const formData = new FormData();
        formData.append('action', 'add_designation');
        formData.append('name', name);

        try {
            const response = await fetch('api/vikasana_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                closeDesignationModal();
                loadDesignations(); // Refresh dropdown
                Swal.fire({ icon: 'success', title: 'Added', text: 'Designation added', timer: 1000, showConfirmButton: false, background: '#1e293b', color: '#fff'});
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        } catch (e) { Swal.fire('Error', 'Failed to add', 'error'); }
    }

    // Create/Update Member
     document.getElementById('dataForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const netPlanValue = document.getElementById('net_plan_number').value;
        setSelectedFamilyFromNetPlan(netPlanValue);
        if (!document.getElementById('family_id').value && familyOptions.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Net Plan Number',
                text: 'Select a valid Net Plan Number from the selected settlement.',
                background: '#1e293b',
                color: '#fff'
            });
            return;
        }
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/vikasana_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                closeModal();
                loadData();
                Swal.fire({
                    icon: 'success', title: 'Success', text: result.message, background: '#1e293b', color: '#fff', timer: 1500, showConfirmButton: false
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message, background: '#1e293b', color: '#fff' });
            }
        } catch (error) { console.error('Error:', error); }
    });

    // Edit Member
    async function editData(id) {
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        try {
            const response = await fetch('api/vikasana_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                document.getElementById('dataId').value = data.id;
                document.getElementById('designation_id').value = data.designation_id;
                document.getElementById('name').value = data.name;
                document.getElementById('age').value = data.age;
                document.getElementById('date_of_entry').value = data.date_of_entry;
                document.getElementById('hamlet_id').value = data.hamlet_id;
                
                // Load Net Plans and set selected
                await loadNetPlans(data.hamlet_id);
                setNetPlanByFamilyId(data.family_id);

                document.getElementById('old_photo').value = data.photo || '';
                const preview = document.getElementById('photoPreview');
                const img = preview.querySelector('img');
                if (data.photo) {
                    img.src = 'uploads/vikasana/' + data.photo;
                    preview.classList.remove('hidden');
                } else {
                    preview.classList.add('hidden');
                }

                document.getElementById('dataAction').value = 'update';
                openModal(true);
            }
        } catch (error) { console.error('Error:', error); }
    }

    // Delete
    function deleteData(id) {
        Swal.fire({
            title: 'Are you sure?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#indigo-600', cancelButtonColor: '#ef4444', confirmButtonText: 'Yes, delete it!', background: '#1e293b', color: '#fff'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                try {
                    const response = await fetch('api/vikasana_action.php', { method: 'POST', body: formData });
                    const res = await response.json();
                    if (res.status === 'success') {
                        loadData();
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, background: '#1e293b', color: '#fff', timer: 1500, showConfirmButton: false });
                    }
                } catch (error) { console.error('Error:', error); }
            }
        })
    }
</script>

<?php require_once 'includes/footer.php'; ?>
