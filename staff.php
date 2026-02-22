<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Staff Entry";
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
                    <h2 class="text-3xl font-bold text-white">Staff Entry</h2>
                    <p class="text-slate-400 text-sm">Manage Staff & Accounts</p>
                </div>
                
                <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Staff
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="staffTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Staff ID</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Designation</th>
                            <th class="px-6 py-4">Mobile</th>
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
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-2xl border border-slate-700 transform scale-95 transition-transform duration-300 overflow-y-auto max-h-[90vh]" id="modalContent">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800 z-10 sticky top-0">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add Staff</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6">
            <form id="dataForm" class="space-y-6">
                <input type="hidden" name="id" id="dataId">
                <input type="hidden" name="action" id="dataAction" value="create">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Name of Staff</label>
                        <input type="text" name="name" id="name" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Designation</label>
                        <select name="designation" id="designation" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">Select Designation</option>
                            <option value="Project Manager">Project Manager</option>
                            <option value="Programme Co-ordinator">Programme Co-ordinator</option>
                            <option value="Veterinary Expert">Veterinary Expert</option>
                             <option value="Agril./Horticulture Officer">Agril./Horticulture Officer</option>
                             <option value="Field Coordinator">Field Coordinator</option>
                             <option value="Community Mobiliser">Community Mobiliser</option>
                             <option value="Accountant cum MIS">Accountant cum MIS</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date of Appointment</label>
                        <input type="date" name="date_of_appointment" id="date_of_appointment" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none scheme-dark">
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-1">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none scheme-dark">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Address</label>
                        <textarea name="address" id="address" rows="2" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Mobile Number</label>
                        <input type="tel" name="mobile" id="mobile" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Status</label>
                        <select name="status" id="status" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- User Account Section -->
                    <div class="md:col-span-2 border-t border-slate-700 pt-4 mt-2">
                        <h4 class="text-white font-medium mb-4">Login Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">User ID (Username)</label>
                                <input type="text" name="user_name" id="user_name" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
                                <input type="email" name="email" id="email" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                                <input type="password" name="password" id="password" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <p class="text-xs text-slate-500 mt-1" id="passHelp">At least 8 characters recommended.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors">Save Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', loadData);

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        try {
            const response = await fetch('api/staff_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                result.data.forEach(row => {
                    const statusClass = row.status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400';
                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-indigo-400 font-mono text-xs">${row.staff_id}</td>
                            <td class="px-6 py-4 text-white font-medium">${row.name}
                                <div class="text-xs text-slate-500 font-normal">${row.user_account || 'No Account'}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-300">${row.designation || '-'}</td>
                             <td class="px-6 py-4 text-slate-300">${row.mobile}</td>
                            <td class="px-6 py-4">
                                <span class="${statusClass} px-2 py-1 rounded text-xs font-bold uppercase">${row.status}</span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="editData(${row.id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteData(${row.id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                initializeDataTable('#staffTable');
            }
        } catch (e) { console.error(e); }
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
            document.getElementById('modalTitle').innerText = 'Add Staff';
             
            // Enable User Fields
            document.getElementById('user_name').disabled = false;
            document.getElementById('password').required = true;
            document.getElementById('passHelp').innerText = "At least 8 characters recommended.";
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Staff';
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
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        try {
            const response = await fetch('api/staff_action.php', { method: 'POST', body: formData });
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
        finally {
             submitBtn.disabled = false;
             submitBtn.innerText = originalText;
        }
    });

    async function editData(id) {
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        try {
            const response = await fetch('api/staff_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                document.getElementById('dataId').value = data.id;
                document.getElementById('name').value = data.name;
                document.getElementById('designation').value = data.designation;
                document.getElementById('date_of_appointment').value = data.date_of_appointment;
                document.getElementById('date_of_birth').value = data.date_of_birth;
                document.getElementById('address').value = data.address;
                document.getElementById('mobile').value = data.mobile;
                document.getElementById('status').value = data.status;
                
                document.getElementById('user_name').value = data.user_account;
                document.getElementById('email').value = data.email || ''; 
                document.getElementById('user_name').disabled = true; // Cannot edit username once set
                document.getElementById('password').value = '';
                document.getElementById('password').required = false;
                document.getElementById('passHelp').innerText = "Leave blank to keep current password.";

                document.getElementById('dataAction').value = 'update';
                openModal(true);
            }
        } catch (error) { console.error('Error:', error); }
    }

    function deleteData(id) {
        Swal.fire({
            title: 'Delete Staff?', 
            text: "This will also delete the associated User Account!",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#indigo-600', cancelButtonColor: '#ef4444', confirmButtonText: 'Yes, delete both!', background: '#1e293b', color: '#fff'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                try {
                    const response = await fetch('api/staff_action.php', { method: 'POST', body: formData });
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
