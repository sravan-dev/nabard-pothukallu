<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();
// Only Super Admin and Admin can access
check_role(['super_admin', 'admin']);

$pageTitle = "Users Management";
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
                <h2 class="text-3xl font-bold text-white">Users Management</h2>
                <div class="flex items-center space-x-3">
                    <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add User
                    </button>
                    <button onclick="openRoleModal()" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Add Role
                    </button>
                </div>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="usersTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Full Name</th>
                            <th class="px-6 py-4">Username</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Created At</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5" id="usersTableBody">
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Add User Modal -->
<div id="userModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-md border border-slate-700 transform scale-95 transition-transform duration-300" id="modalContent">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add New User</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="userForm" class="p-6 space-y-4">
            <input type="hidden" name="id" id="userId">
            <input type="hidden" name="action" id="userAction" value="create">
            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Full Name</label>
                <input type="text" name="full_name" id="full_name" required
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Username</label>
                <input type="text" name="username" id="username" required
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                 <label class="block text-sm font-medium text-slate-300 mb-1">Role</label>
                 <select name="role" id="role" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                     <!-- Populated via AJAX -->
                 </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Password <span class="text-xs text-slate-500 font-normal ml-1" id="passHint">(Leave blank to keep current)</span></label>
                <input type="password" name="password" id="password"
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors" id="saveBtn">Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Role Modal -->
<div id="roleModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-sm border border-slate-700 transform scale-95 transition-transform duration-300" id="roleModalContent">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-white">Add New Role</h3>
            <button onclick="closeRoleModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="roleForm" class="p-6 space-y-4">
            <input type="hidden" name="action" value="add_role">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Role Name</label>
                <input type="text" name="role_name" required placeholder="e.g. manager"
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>
            <div class="pt-4 flex justify-end space-x-3">
                 <button type="button" onclick="closeRoleModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                 <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition-colors">Save Role</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Load Users and Roles
    document.addEventListener('DOMContentLoaded', () => {
        loadUsers();
        loadRoles();
    });

    async function loadRoles() {
        const formData = new FormData();
        formData.append('action', 'fetch_roles');
        try {
            const response = await fetch('api/users_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const select = document.getElementById('role');
                select.innerHTML = '';
                result.data.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role;
                    option.textContent = role.charAt(0).toUpperCase() + role.slice(1).replace('_', ' ');
                    select.appendChild(option);
                });
            }
        } catch (error) { console.error(error); }
    }

    async function loadUsers() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        
        try {
            const response = await fetch('api/users_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                const tbody = document.getElementById('usersTableBody');
                tbody.innerHTML = '';
                result.data.forEach(user => {
                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">#${user.id}</td>
                            <td class="px-6 py-4 font-medium text-white">${user.full_name || '-'}</td>
                            <td class="px-6 py-4">${user.username}</td>
                            <td class="px-6 py-4">${user.email}</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 bg-blue-500/10 text-blue-300 rounded text-xs uppercase font-bold tracking-wider">${user.role}</span></td>
                            <td class="px-6 py-4 text-xs">${user.created_at}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="editUser(${user.id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteUser(${user.id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                // Initialize DataTable
                initializeDataTable('#usersTable');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Modal Logic
    const modal = document.getElementById('userModal');
    const modalContent = document.getElementById('modalContent');
    
    // Role Modal Logic
    const roleModal = document.getElementById('roleModal');
    const roleModalContent = document.getElementById('roleModalContent');

    function openModal(isEdit = false) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);

        if (!isEdit) {
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('userAction').value = 'create';
            document.getElementById('modalTitle').innerText = 'Add New User';
            document.getElementById('passHint').innerText = '(Required)';
            document.getElementById('password').required = true;
        } else {
            document.getElementById('modalTitle').innerText = 'Edit User';
            document.getElementById('passHint').innerText = '(Leave blank to keep current)';
            document.getElementById('password').required = false;
        }
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    function openRoleModal() {
        roleModal.classList.remove('hidden');
        setTimeout(() => {
            roleModal.classList.remove('opacity-0');
            roleModalContent.classList.remove('scale-95');
            roleModalContent.classList.add('scale-100');
        }, 10);
    }
    
    function closeRoleModal() {
        roleModal.classList.add('opacity-0');
        roleModalContent.classList.remove('scale-100');
        roleModalContent.classList.add('scale-95');
        setTimeout(() => { roleModal.classList.add('hidden'); }, 300);
    }

    // Role Form Submit
    document.getElementById('roleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const response = await fetch('api/users_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                closeRoleModal();
                loadRoles(); // Refresh dropdown
                document.getElementById('roleForm').reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message,
                    background: '#1e293b',
                    color: '#fff',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message, background: '#1e293b', color: '#fff' });
            }
        } catch (error) { console.error(error); }
    });

    // User Form Submit
    document.getElementById('userForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/users_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                closeModal();
                loadUsers();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message,
                    background: '#1e293b',
                    color: '#fff',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message,
                    background: '#1e293b',
                    color: '#fff'
                });
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // Edit User
    async function editUser(id) {
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        try {
            const response = await fetch('api/users_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const user = result.data;
                document.getElementById('userId').value = user.id;
                document.getElementById('full_name').value = user.full_name;
                document.getElementById('username').value = user.username;
                document.getElementById('email').value = user.email;
                document.getElementById('role').value = user.role;
                document.getElementById('userAction').value = 'update';
                
                openModal(true);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Delete User
    function deleteUser(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it!',
            background: '#1e293b',
            color: '#fff'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                try {
                    const response = await fetch('api/users_action.php', { method: 'POST', body: formData });
                    const res = await response.json();

                    if (res.status === 'success') {
                        loadUsers();
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: res.message,
                            background: '#1e293b',
                            color: '#fff'
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        })
    }
</script>

<?php require_once 'includes/footer.php'; ?>
