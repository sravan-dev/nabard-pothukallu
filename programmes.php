<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Programmes / Activities";
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
                    <h2 class="text-3xl font-bold text-white">Programmes / Activities</h2>
                    <p class="text-slate-400 text-sm">Events and Activities Log</p>
                </div>
                
                <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Programme
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="programmesTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">About the Program</th>
                            <th class="px-6 py-4">Photo</th>
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
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800 z-10">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add Programme</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="dataForm" class="p-6 space-y-6">
            <input type="hidden" name="id" id="dataId">
            <input type="hidden" name="action" id="dataAction" value="create">
            
            <div class="space-y-4">
                <div>
                     <label class="block text-sm font-medium text-slate-300 mb-1">Date</label>
                    <input type="date" name="program_date" id="program_date" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none scheme-dark">
                </div>
                
                <div>
                     <label class="block text-sm font-medium text-slate-300 mb-1">About the Program</label>
                     <textarea name="description" id="description" rows="4" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                     <label class="block text-sm font-medium text-slate-300 mb-1">Photo</label>
                     <input type="file" name="photo" id="photo" accept="image/*"
                         class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
                     <input type="hidden" name="old_photo" id="old_photo">
                     <div id="photoPreview" class="mt-2 hidden">
                         <img src="" class="h-20 w-32 rounded-lg object-cover border border-slate-600">
                     </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 z-[60] hidden bg-black/90 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300" onclick="closeLightbox()">
    <img id="lightboxImg" src="" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl transform scale-90 transition-transform duration-300 border border-slate-700">
    <button class="absolute top-4 right-4 text-white/50 hover:text-white transition-colors">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', loadData);

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        try {
            const response = await fetch('api/programmes_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                result.data.forEach(row => {
                    const photoHtml = row.photo 
                        ? `<img src="uploads/programmes/${row.photo}" onclick="openLightbox('uploads/programmes/${row.photo}')" class="h-16 w-16 object-cover rounded-lg cursor-pointer border border-slate-600 hover:border-blue-400">`
                        : '<span class="text-xs text-slate-600">No Photo</span>';

                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-white align-top whitespace-nowrap">${row.program_date}</td>
                            <td class="px-6 py-4 text-slate-300 align-top">${row.description}</td>
                            <td class="px-6 py-4 align-top">${photoHtml}</td>
                            <td class="px-6 py-4 text-right space-x-2 align-top">
                                <button onclick="editData(${row.id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteData(${row.id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                initializeDataTable('#programmesTable');
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
            document.getElementById('modalTitle').innerText = 'Add Programme';
            document.getElementById('photoPreview').classList.add('hidden');
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Programme';
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
            const response = await fetch('api/programmes_action.php', { method: 'POST', body: formData });
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
            const response = await fetch('api/programmes_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                document.getElementById('dataId').value = data.id;
                document.getElementById('program_date').value = data.program_date;
                document.getElementById('description').value = data.description;
     
                document.getElementById('old_photo').value = data.photo || '';
                const preview = document.getElementById('photoPreview');
                const img = preview.querySelector('img');
                if (data.photo) {
                    img.src = 'uploads/programmes/' + data.photo;
                    preview.classList.remove('hidden');
                } else {
                    preview.classList.add('hidden');
                }

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
                    const response = await fetch('api/programmes_action.php', { method: 'POST', body: formData });
                    const res = await response.json();
                    if (res.status === 'success') {
                        loadData();
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, background: '#1e293b', color: '#fff', timer: 1500, showConfirmButton: false });
                    }
                } catch (error) { console.error('Error:', error); }
            }
        })
    }
    
    // Lightbox Logic
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    
    function openLightbox(src) {
        lightboxImg.src = src;
        lightbox.classList.remove('hidden');
        setTimeout(() => {
            lightbox.classList.remove('opacity-0');
            lightboxImg.classList.remove('scale-90');
            lightboxImg.classList.add('scale-100');
        }, 10);
    }
    
    function closeLightbox() {
        lightbox.classList.add('opacity-0');
        lightboxImg.classList.remove('scale-100');
        lightboxImg.classList.add('scale-90');
        setTimeout(() => { lightbox.classList.add('hidden'); }, 300);
    }
</script>

<?php require_once 'includes/footer.php'; ?>
