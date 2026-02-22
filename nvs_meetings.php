<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "NVS Meetings";
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
                    <h2 class="text-3xl font-bold text-white">NVS Meetings</h2>
                    <p class="text-slate-400 text-sm">Meeting Records & Minutes</p>
                </div>
                
                <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Meeting
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="nvsTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Settlement</th>
                            <th class="px-6 py-4">Net Plans</th>
                             <th class="px-6 py-4">Documents</th>
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

<!-- Participants View Modal -->
<div id="participantsModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-lg border border-slate-700 transform scale-95 transition-transform duration-300">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
             <h3 class="text-lg font-bold text-white">Meeting Participants</h3>
             <button onclick="closeParticipantsModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <ul id="participantsViewList" class="space-y-3">
                <!-- Loaded via JS -->
            </ul>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="dataModal" class="fixed inset-0 z-40 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-4xl border border-slate-700 transform scale-95 transition-transform duration-300 overflow-y-auto max-h-[90vh]" id="modalContent">
         <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center sticky top-0 bg-slate-800 z-10">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add Meeting</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="dataForm" class="p-6 space-y-6">
            <input type="hidden" name="id" id="dataId">
            <input type="hidden" name="action" id="dataAction" value="create">
            <input type="hidden" name="participant_ids" id="participant_ids">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column: Details -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Settlement</label>
                        <select name="hamlet_id" id="hamlet_id" required onchange="loadFamilies(this.value)" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-indigo-500">
                             <option value="">Select Settlement</option>
                             <!-- Loaded via API -->
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date of Meeting</label>
                        <input type="date" name="meeting_date" id="meeting_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-indigo-500 scheme-dark">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Major Decisions</label>
                        <textarea name="major_decisions" id="major_decisions" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                     <div class="grid grid-cols-2 gap-4">
                         <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Meeting Photo 2</label>
                            <input type="file" name="photo" id="photo" accept="image/*" class="w-full text-xs text-slate-400">
                         </div>
                         <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Minutes (PDF)</label>
                            <input type="file" name="minutes" id="minutes" accept="application/pdf" class="w-full text-xs text-slate-400">
                         </div>
                    </div>
                    <div id="fileList" class="text-xs text-slate-400 mt-2 space-y-1"></div>
                </div>

                <!-- Right Column: Participants -->
                <div class="flex flex-col h-full border border-slate-700 rounded-lg bg-slate-900/50">
                    <div class="p-3 border-b border-slate-700 bg-slate-800 rounded-t-lg">
                        <label class="block text-sm font-medium text-white mb-2">Select Participants (Filtered by Settlement)</label>
                         <p class="text-xs text-slate-400" id="listStatus">Select a settlement above to see families.</p>
                    </div>
                    <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar" id="participantList" style="max-height: 300px;">
                        <!-- List loaded via JS -->
                    </div>
                    <div class="p-3 border-t border-slate-700 bg-slate-800 rounded-b-lg">
                        <div class="text-xs text-slate-400">Selected: <span id="selectedCount" class="text-white font-bold">0</span></div>
                        <div id="selectedNames" class="mt-2 text-xs text-slate-300 italic max-h-16 overflow-y-auto"></div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-700">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors">Save Meeting</button>
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
    let allFamilies = [];
    let selectedParticipants = new Set();

    document.addEventListener('DOMContentLoaded', () => {
        loadData();
        loadHamlets();
    });

    async function loadData() {
         const formData = new FormData();
        formData.append('action', 'fetch_all');
        try {
            const response = await fetch('api/nvs_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                result.data.forEach(row => {
                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-white">${row.meeting_date}</td>
                            <td class="px-6 py-4 text-emerald-400 font-medium">${row.settlement_name}</td>
                             <td class="px-6 py-4 text-slate-400">
                                <button onclick="viewParticipants(${row.id})" class="bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 px-3 py-1 rounded text-xs font-bold transition-colors">
                                    View (${row.real_participants_count})
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                ${row.cover_photo ? `<a href="#" onclick="openLightbox('uploads/nvs/photos/${row.cover_photo}'); return false;" class="text-blue-400 hover:underline text-xs">View Photo</a>` : '<span class="text-slate-600 text-xs">No Photo</span>'}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="editData(${row.id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteData(${row.id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                initializeDataTable('#nvsTable');
            }
        } catch (e) { console.error(e); }
    }

    async function loadHamlets() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        try {
            const response = await fetch('api/hamlets_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const select = document.getElementById('hamlet_id');
                // preserve current value if any
                const val = select.value;
                select.innerHTML = '<option value="">Select Settlement</option>';
                result.data.forEach(h => {
                    const option = document.createElement('option');
                    option.value = h.id;
                    option.textContent = h.settlement_name;
                    select.appendChild(option);
                });
                if(val) select.value = val;
            }
        } catch (e) {}
    }

    async function loadFamilies(hamletId) {
        if(!hamletId) {
            document.getElementById('participantList').innerHTML = '';
            document.getElementById('listStatus').innerText = "Select a settlement above to see families.";
            return;
        }
        
        document.getElementById('listStatus').innerText = "Loading families...";
        const formData = new FormData();
        formData.append('action', 'fetch_families_by_hamlet');
        formData.append('hamlet_id', hamletId);
        
        try {
            const response = await fetch('api/nvs_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                allFamilies = result.data;
                renderParticipantList();
                document.getElementById('listStatus').innerText = "";
            }
        } catch (e) {}
    }

    function renderParticipantList() {
        const list = document.getElementById('participantList');
        list.innerHTML = '';
        
        if (allFamilies.length === 0) {
            list.innerHTML = '<div class="text-gray-500 italic p-2">No families found in this settlement.</div>';
            return;
        }

        allFamilies.forEach(f => {
            const label = `${f.net_plan_number} - ${f.beneficiary_name}`;
            const div = document.createElement('div');
            div.className = "flex items-center space-x-2 p-1 hover:bg-white/5 rounded cursor-pointer";
            const isSelected = selectedParticipants.has(f.id.toString());
            div.innerHTML = `
                <input type="checkbox" value="${f.id}" ${isSelected ? 'checked' : ''} class="rounded border-slate-600 bg-slate-800 text-indigo-600 focus:ring-0">
                <span class="text-xs text-slate-300">${label}</span>
            `;
            div.querySelector('input').addEventListener('change', (e) => {
                toggleParticipant(f.id, f.beneficiary_name, e.target.checked);
            });
            list.appendChild(div);
        });
        updateSelectedDisplay();
    }

    function toggleParticipant(id, name, checked) {
        if (checked) {
            selectedParticipants.add(id.toString());
        } else {
            selectedParticipants.delete(id.toString());
        }
        updateSelectedDisplay();
    }

    function updateSelectedDisplay() {
        document.getElementById('selectedCount').innerText = selectedParticipants.size;
        document.getElementById('participant_ids').value = Array.from(selectedParticipants).join(',');
        
        const names = [];
        allFamilies.forEach(f => {
            if (selectedParticipants.has(f.id.toString())) {
                names.push(f.beneficiary_name);
            }
        });
        document.getElementById('selectedNames').innerText = names.join(', ');
    }
    
    // View Participants Logic
    const pModal = document.getElementById('participantsModal');
    const pList = document.getElementById('participantsViewList');
    
    async function viewParticipants(id) {
        pList.innerHTML = '<li class="text-slate-500 text-center py-4">Loading...</li>';
        pModal.classList.remove('hidden');
        setTimeout(() => pModal.classList.remove('opacity-0'), 10);
        
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);
        
        try {
            const response = await fetch('api/nvs_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if(result.status === 'success' && result.participants) {
                pList.innerHTML = '';
                if(result.participants.length === 0) {
                     pList.innerHTML = '<li class="text-slate-500 text-center py-4">No participants found.</li>';
                }
                result.participants.forEach(p => {
                    pList.innerHTML += `
                        <li class="flex items-center space-x-3 bg-slate-700/30 p-3 rounded-lg border border-slate-700/50">
                            <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-xs">${p.beneficiary_name.charAt(0)}</div>
                            <div>
                                <div class="text-white font-medium text-sm">${p.beneficiary_name}</div>
                                <div class="text-indigo-400 text-xs font-mono">Net Plan #${p.net_plan_number}</div>
                            </div>
                        </li>
                    `;
                });
            }
        } catch(e) { console.error(e); }
    }
    
    function closeParticipantsModal() {
        pModal.classList.add('opacity-0');
        setTimeout(() => pModal.classList.add('hidden'), 300);
    }

    // Main Modal
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
            document.getElementById('modalTitle').innerText = 'Add Meeting';
            document.getElementById('fileList').innerHTML = '';
            selectedParticipants.clear();
             document.getElementById('participantList').innerHTML = '';
            document.getElementById('listStatus').innerText = "Select a settlement above to see families.";
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Meeting';
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
            const response = await fetch('api/nvs_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                closeModal();
                loadData();
                Swal.fire({ icon: 'success', title: 'Success', text: result.message, background: '#1e293b', color: '#fff', timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message, background: '#1e293b', color: '#fff' });
            }
        } catch (error) { console.error(error); }
    });

    async function editData(id) {
         const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        try {
            const response = await fetch('api/nvs_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const data = result.data;
                const participants = result.participants || [];
                const files = result.files || [];

                document.getElementById('dataId').value = data.id;
                document.getElementById('hamlet_id').value = data.hamlet_id;
                document.getElementById('meeting_date').value = data.meeting_date;
                document.getElementById('major_decisions').value = data.major_decisions;
                document.getElementById('dataAction').value = 'update';

                // Load families for this hamlet FIRST, then set selection
                await loadFamilies(data.hamlet_id);
                
                selectedParticipants = new Set(participants.map(p => p.family_id.toString()));
                renderParticipantList();

                // Files
                const fileList = document.getElementById('fileList');
                fileList.innerHTML = '<strong>Attached Files:</strong><br>';
                files.forEach(f => {
                    const isPhoto = f.file_type === 'photo';
                    const subDir = isPhoto ? 'photos' : 'minutes';
                    const filePath = `uploads/nvs/${subDir}/${f.file_path}`;
                    
                    let previewHtml = '';
                    if (isPhoto) {
                        previewHtml = `<img src="${filePath}" class="h-10 w-10 object-cover rounded cursor-pointer border border-slate-600 hover:border-blue-400" onclick="openLightbox('${filePath}')">`;
                    } else {
                        previewHtml = `<span class="text-xs text-slate-400 uppercase border border-slate-600 px-1 rounded">PDF</span>`;
                    }

                    fileList.innerHTML += `
                        <div class="flex items-center justify-between bg-slate-700/50 p-2 rounded mb-1">
                             <div class="flex items-center space-x-3">
                                ${previewHtml}
                                <span class="text-xs text-slate-300">
                                    <a href="${filePath}" target="_blank" class="hover:text-white truncate max-w-[150px] inline-block align-middle">${f.file_type}</a>
                                </span>
                            </div>
                            <button type="button" onclick="deleteFile(${f.id}, this)" class="text-red-400 hover:text-red-300 text-xs px-2 py-1 rounded hover:bg-red-900/20 transition-colors">Remove</button>
                        </div>
                    `;
                });

                openModal(true);
            }
        } catch (error) { console.error(error); }
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
    
    async function deleteFile(fileId, btn) {
        if(!confirm('Delete this file?')) return;
        const formData = new FormData();
        formData.append('action', 'delete_file');
        formData.append('file_id', fileId); 
        try {
            const response = await fetch('api/nvs_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if(result.status === 'success') {
                btn.closest('div').remove();
            }
        } catch(e) {}
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
                    const response = await fetch('api/nvs_action.php', { method: 'POST', body: formData });
                    const res = await response.json();
                    if (res.status === 'success') {
                        loadData();
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, background: '#1e293b', color: '#fff', timer: 1500, showConfirmButton: false });
                    }
                } catch (error) {}
            }
        })
    }

</script>

<?php require_once 'includes/footer.php'; ?>
