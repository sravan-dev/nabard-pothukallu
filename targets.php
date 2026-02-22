<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Target Entry";
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

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
                    <h2 class="text-3xl font-bold text-white">Target Entry</h2>
                    <p class="text-slate-400 text-sm">Assign and Track Targets</p>
                </div>
                
                <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Target
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="targetsTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Staff Name</th>
                            <th class="px-6 py-4">Target</th>
                            <th class="px-6 py-4">Assigned On</th>
                            <th class="px-6 py-4">Proposed End</th>
                            <th class="px-6 py-4">Status</th>
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

<!-- Modal -->
<div id="dataModal" class="fixed inset-0 z-40 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-lg border border-slate-700 transform scale-95 transition-transform duration-300 overflow-y-auto max-h-[90vh]" id="modalContent">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800 z-10 sticky top-0">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Assign Target</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6">
            <form id="dataForm" class="space-y-6">
                <input type="hidden" name="id" id="dataId">
                <input type="hidden" name="action" id="dataAction" value="create">
                
                <div>
                     <label class="block text-sm font-medium text-slate-300 mb-1">Name of Staff</label>
                     <select name="staff_id" id="staff_id" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-indigo-500">
                         <option value="">Select Staff</option>
                     </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Target</label>
                    <textarea name="target_description" id="target_description" rows="3" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date of Assignment</label>
                        <input type="date" name="assignment_date" id="assignment_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none scheme-dark">
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-1">Proposed Completion</label>
                        <input type="date" name="proposed_completion_date" id="proposed_completion_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none scheme-dark">
                    </div>
                </div>
                
                <!-- Status Update Section (Visible when editing) -->
                <div id="statusSection" class="border-t border-slate-700 pt-4 hidden">
                    <h4 class="text-white font-medium mb-3">Update Progress</h4>
                     <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Action</label>
                        <textarea name="action_taken" id="action_taken" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                     <div class="mt-4">
                         <label class="block text-sm font-medium text-slate-300 mb-1">Actual Date of Completion</label>
                        <input type="date" name="actual_completion_date" id="actual_completion_date" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none scheme-dark">
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadStaff();
        loadData();
    });

    async function loadStaff() {
        const formData = new FormData();
        formData.append('action', 'fetch_staff');
        try {
            const response = await fetch('api/targets_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const select = document.getElementById('staff_id');
                result.data.forEach(staff => {
                    const option = document.createElement('option');
                    option.value = staff.id;
                    option.text = staff.name;
                    select.appendChild(option);
                });
            }
        } catch(e) {}
    }

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        try {
            const response = await fetch('api/targets_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                result.data.forEach(row => {
                    let statusColor = 'bg-yellow-500/10 text-yellow-400';
                    if(row.status === 'In Progress') statusColor = 'bg-blue-500/10 text-blue-400';
                    if(row.status === 'Completed') statusColor = 'bg-emerald-500/10 text-emerald-400';

                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-white font-medium">${row.staff_name}</td>
                            <td class="px-6 py-4 text-slate-300 truncate max-w-xs">${row.target_description}</td>
                            <td class="px-6 py-4 text-slate-400 text-xs">${row.assignment_date}</td>
                            <td class="px-6 py-4 text-slate-400 text-xs">${row.proposed_completion_date}</td>
                            <td class="px-6 py-4">
                                <span class="${statusColor} px-2 py-1 rounded text-xs font-bold uppercase">${row.status}</span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="editData(${row.id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteData(${row.id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                initializeDataTable('#targetsTable');
            }
        } catch (e) { console.error(e); }
    }

    // Modal Logic
    const modal = document.getElementById('dataModal');
    const modalContent = document.getElementById('modalContent');
    const statusSection = document.getElementById('statusSection');

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
            document.getElementById('modalTitle').innerText = 'Assign Target';
            statusSection.classList.add('hidden');
        } else {
            document.getElementById('modalTitle').innerText = 'Update Target';
            statusSection.classList.remove('hidden');
        }
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    document.getElementById('dataForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        try {
            const response = await fetch('api/targets_action.php', { method: 'POST', body: formData });
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

    async function editData(id) {
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        try {
            const response = await fetch('api/targets_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                document.getElementById('dataId').value = data.id;
                document.getElementById('staff_id').value = data.staff_id;
                document.getElementById('target_description').value = data.target_description;
                document.getElementById('assignment_date').value = data.assignment_date;
                document.getElementById('proposed_completion_date').value = data.proposed_completion_date;
                document.getElementById('action_taken').value = data.action_taken || '';
                document.getElementById('actual_completion_date').value = data.actual_completion_date || '';

                document.getElementById('dataAction').value = 'update';
                openModal(true);
            }
        } catch (error) { console.error('Error:', error); }
    }

    function deleteData(id) {
        Swal.fire({
            title: 'Are you sure?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#indigo-600', cancelButtonColor: '#ef4444', confirmButtonText: 'Yes, delete it!', background: '#1e293b', color: '#fff'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                try {
                    const response = await fetch('api/targets_action.php', { method: 'POST', body: formData });
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
