<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Project Components";
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
                    <h2 class="text-3xl font-bold text-white">Project Components</h2>
                    <p class="text-slate-400 text-sm">Manage Main & Sub Components</p>
                </div>
                
                <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Component
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="componentsTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4 w-1/3">Main Component</th>
                            <th class="px-6 py-4">Sub Components</th>
                            <th class="px-6 py-4 text-right w-32">Actions</th>
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
<div id="dataModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-lg border border-slate-700 transform scale-95 transition-transform duration-300 transform scale-95" id="modalContent">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800/50 rounded-t-xl">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add Component</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="dataForm" class="p-6 space-y-4">
            <input type="hidden" name="id" id="dataId">
            <input type="hidden" name="action" id="dataAction" value="create">
            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Main Component Name</label>
                <input type="text" name="component_name" id="component_name" required
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. Livelihood Interventions">
            </div>
            
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-slate-300">Sub Components</label>
                    <button type="button" onclick="addSubComponentField()" class="text-xs bg-emerald-600/20 text-emerald-400 hover:bg-emerald-600/30 px-2 py-1 rounded flex items-center transition-colors">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Sub-Component
                    </button>
                </div>
                <div id="subComponentContainer" class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                    <!-- Dynamic Fields go here -->
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-700 mt-4">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors shadow-lg">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', loadData);

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        
        try {
            const response = await fetch('api/components_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                if(result.data.length === 0) {
                     tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-8 text-center italic">No components found.</td></tr>';
                }
                
                result.data.forEach(row => {
                    let subList = '';
                    if(row.sub_components && row.sub_components.length > 0) {
                        subList = '<ul class="list-disc list-inside space-y-1">';
                        row.sub_components.forEach(sub => {
                            subList += `<li>${sub.name}</li>`;
                        });
                        subList += '</ul>';
                    } else {
                        subList = '<span class="text-slate-600 italic">No sub-components</span>';
                    }

                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors align-top">
                            <td class="px-6 py-4 font-medium text-white text-lg">${row.name}</td>
                            <td class="px-6 py-4 text-slate-300">${subList}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="editData(${row.id})" class="text-blue-400 hover:text-blue-300 transition-colors bg-blue-500/10 p-2 rounded hover:bg-blue-500/20" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                <button onclick="deleteData(${row.id})" class="text-red-400 hover:text-red-300 transition-colors bg-red-500/10 p-2 rounded hover:bg-red-500/20" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                // Initialize DataTable if needed, or stick to simple table for variable height rows
            }
        } catch (error) { console.error('Error:', error); }
    }

    // Dynamic Fields Logic
    function addSubComponentField(value = '') {
        const container = document.getElementById('subComponentContainer');
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2 animate-fadeIn';
        div.innerHTML = `
            <input type="text" name="sub_components[]" value="${value}" placeholder="Sub-Component Name"
                class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:bg-red-500/10 p-1 rounded transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;
        container.appendChild(div);
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
            document.getElementById('modalTitle').innerText = 'Add Component';
            document.getElementById('subComponentContainer').innerHTML = '';
            // Add one empty field by default
            addSubComponentField();
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Component';
        }
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    // Form Submit
    document.getElementById('dataForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/components_action.php', { method: 'POST', body: formData });
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
            const response = await fetch('api/components_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                document.getElementById('dataId').value = data.id;
                document.getElementById('component_name').value = data.name;
                document.getElementById('dataAction').value = 'update';
                
                // Populate Sub-Components
                const container = document.getElementById('subComponentContainer');
                container.innerHTML = '';
                if(data.sub_components && data.sub_components.length > 0) {
                    data.sub_components.forEach(sub => addSubComponentField(sub.name));
                } else {
                    addSubComponentField();
                }

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
                    const response = await fetch('api/components_action.php', { method: 'POST', body: formData });
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
