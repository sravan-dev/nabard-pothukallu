<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Family Details - Project at a Glance";
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
                    <h2 class="text-3xl font-bold text-white">Family Details</h2>
                    <p class="text-slate-400 text-sm">Project at a Glance</p>
                </div>
                
                <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Family Detail
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="familyTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Settlement</th>
                            <th class="px-6 py-4">Net Plan #</th>
                            <th class="px-6 py-4">Beneficiary Name</th>
                            <th class="px-6 py-4">Age</th>
                            <th class="px-6 py-4">Members</th>
                            <th class="px-6 py-4">Photos</th>
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
<div id="dataModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-2xl border border-slate-700 transform scale-95 transition-transform duration-300 overflow-y-auto max-h-[90vh]" id="modalContent">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center sticky top-0 bg-slate-800 z-10">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add Family Detail</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="dataForm" class="p-6 space-y-6">
            <input type="hidden" name="id" id="dataId">
            <input type="hidden" name="action" id="dataAction" value="create">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Name of Settlement</label>
                    <select name="hamlet_id" id="hamlet_id" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <!-- Populated via AJAX -->
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Net Plan Number</label>
                    <input type="text" name="net_plan_number" id="net_plan_number" required inputmode="text" pattern="[A-Za-z0-9]+"
                        placeholder="Enter Net Plan Number"
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                 <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Name of Beneficiary</label>
                    <input type="text" name="beneficiary_name" id="beneficiary_name" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Age</label>
                    <input type="number" name="age" id="age" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Total Members in Family</label>
                    <input type="number" name="total_members" id="total_members" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div class="border-t border-slate-700 pt-4">
                <h4 class="text-white font-semibold mb-4">Photos (Upload Images)</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php
                    $fields = [
                        'photo_initial' => 'Front of the House (Initial)',
                        'photo_year1' => '1st Year Completion',
                        'photo_year2' => '2nd Year Completion',
                        'photo_year3' => '3rd Year Completion',
                        'photo_year4' => '4th Year Completion',
                        'photo_year5' => '5th Year Completion',
                    ];
                    foreach ($fields as $key => $label) {
                        echo "
                        <div>
                            <label class='block text-xs font-medium text-slate-400 mb-1'>$label</label>
                            <input type='file' name='$key' id='$key' accept='image/*'
                                class='block w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500'>
                            <input type='hidden' name='old_$key' id='old_$key'>
                        </div>";
                    }
                    ?>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors" id="saveBtn">Save Details</button>
            </div>
        </form>
    </div>
</div>

<!-- QR Modal -->
<div id="qrModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-sm text-center transform scale-95 transition-transform duration-300" id="qrModalContent">
        <h3 class="text-xl font-bold text-slate-800 mb-4">Net Plan QR Code</h3>
        <div id="qrcode" class="flex justify-center mb-4"></div>
        <p class="text-slate-500 mb-6" id="qrText">#</p>
        <button onclick="closeQrModal()" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700">Close</button>
    </div>
</div>

<!-- Photo Management Modal -->
<div id="photoModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-4xl border border-slate-700 transform scale-95 transition-transform duration-300 overflow-hidden flex flex-col max-h-[90vh]" id="photoModalContent">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800 z-10">
            <h3 class="text-xl font-bold text-white">Family Photos</h3>
            <button onclick="closePhotoModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto custom-scrollbar" id="photoGrid">
            <!-- Dynamic Content -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    let currentFamilyData = null; // Store current family data for photo modal

    document.addEventListener('DOMContentLoaded', () => {
        loadHamlets();
        loadData();

        // Check for deep link
        const urlParams = new URLSearchParams(window.location.search);
        const openId = urlParams.get('open_id');
        if (openId) {
            editData(openId);
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

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
        } catch (error) { console.error(error); }
    }

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        
        try {
            const response = await fetch('api/family_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                result.data.forEach(row => {
                    const hasPhotos = row.photo_initial || row.photo_year1 || row.photo_year2 || row.photo_year3 || row.photo_year4 || row.photo_year5;
                    // Escape single quotes in JSON string for onclick
                    const rowJson = JSON.stringify(row).replace(/'/g, "&#39;");
                    
                    const photoBadge = hasPhotos 
                        ? `<button onclick='viewPhotos(${rowJson})' class="px-2 py-1 bg-green-500/10 text-green-400 text-xs rounded hover:bg-green-500/20 transition-colors cursor-pointer">Available</button>` 
                        : '<span class="text-slate-600 text-xs">None</span>';

                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-white">${row.settlement_name}</td>
                            <td class="px-6 py-4 font-mono text-indigo-300 font-bold">${row.net_plan_number}</td>
                            <td class="px-6 py-4 text-slate-300">${row.beneficiary_name}</td>
                            <td class="px-6 py-4 text-slate-400">${row.age}</td>
                            <td class="px-6 py-4 text-slate-400">${row.total_members}</td>
                            <td class="px-6 py-4">${photoBadge}</td>
                            <td class="px-6 py-4 text-right space-x-2 flex justify-end items-center">
                                <button onclick="generateQR(${row.id}, '${row.net_plan_number}', '${row.beneficiary_name}')" class="p-1 text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded" title="Generate QR">
                                   <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM5 8h2v2H5V8zm2-4H5a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V6a2 2 0 00-2-2zm0 12H5a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2zm12-4h-2v2h2v-2zm-2-4h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2z" /></svg>
                                </button>
                                <button onclick="editData(${row.id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteData(${row.id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                initializeDataTable('#familyTable');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Modal Logic
    const modal = document.getElementById('dataModal');
    const modalContent = document.getElementById('modalContent');
    const qrModal = document.getElementById('qrModal');
    const qrModalContent = document.getElementById('qrModalContent');
    const photoModal = document.getElementById('photoModal');
    const photoModalContent = document.getElementById('photoModalContent');

    async function openModal(isEdit = false) {
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
            document.getElementById('modalTitle').innerText = 'Add Family Detail';
            
            // Fetch Next Net Plan Number
            try {
                const formData = new FormData();
                formData.append('action', 'fetch_next_net_plan');
                const response = await fetch('api/family_action.php', { method: 'POST', body: formData });
                const result = await response.json();
                const netPlanInput = document.getElementById('net_plan_number');
                if (result.status === 'success' && netPlanInput && !String(netPlanInput.value || '').trim()) {
                    netPlanInput.value = result.next_net_plan;
                }
            } catch (e) { console.error(e); }

        } else {
            document.getElementById('modalTitle').innerText = 'Edit Family Detail';
        }
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }
    
    // Photo Modal Functions
    function viewPhotos(data) {
        currentFamilyData = data;
        const grid = document.getElementById('photoGrid');
        grid.innerHTML = '';

        const photoFields = {
            'photo_initial': 'Front of the House (Initial)',
            'photo_year1': '1st Year Completion',
            'photo_year2': '2nd Year Completion',
            'photo_year3': '3rd Year Completion',
            'photo_year4': '4th Year Completion',
            'photo_year5': '5th Year Completion'
        };

        for (const [key, label] of Object.entries(photoFields)) {
            const hasPhoto = !!data[key];
            const imgSrc = hasPhoto ? `uploads/families/${data[key]}` : '';
            
            let cardHtml = `
                <div class="bg-slate-700/50 rounded-lg p-4 border border-slate-600 flex flex-col items-center">
                    <h4 class="text-sm font-semibold text-slate-300 mb-3">${label}</h4>
            `;

            if (hasPhoto) {
                cardHtml += `
                    <div class="relative w-full aspect-video rounded-lg overflow-hidden mb-3 group">
                        <img src="${imgSrc}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="${label}">
                         <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <a href="${imgSrc}" target="_blank" class="text-white bg-slate-900/50 p-2 rounded-full hover:bg-slate-900/80">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </div>
                    </div>
                    <div class="flex space-x-2 w-full">
                        <button onclick="triggerReplace('${key}')" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-1.5 rounded text-xs font-medium transition-colors">Replace</button>
                        <button onclick="deletePhoto('${key}')" class="flex-1 bg-red-600 hover:bg-red-500 text-white py-1.5 rounded text-xs font-medium transition-colors">Delete</button>
                    </div>
                `;
            } else {
                 cardHtml += `
                    <div class="w-full aspect-video rounded-lg bg-slate-800 border-2 border-dashed border-slate-600 flex items-center justify-center mb-3">
                        <span class="text-slate-500 text-xs">No Photo</span>
                    </div>
                    <button onclick="triggerReplace('${key}')" class="w-full bg-slate-600 hover:bg-slate-500 text-white py-1.5 rounded text-xs font-medium transition-colors">Upload</button>
                `;
            }
            cardHtml += `</div>`;
            grid.insertAdjacentHTML('beforeend', cardHtml);
        }
        
        // Grid Layout fix
        grid.className = "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6 overflow-y-auto custom-scrollbar";

        photoModal.classList.remove('hidden');
        setTimeout(() => {
            photoModal.classList.remove('opacity-0');
            photoModalContent.classList.remove('scale-95');
            photoModalContent.classList.add('scale-100');
        }, 10);
    }

    function closePhotoModal() {
        photoModal.classList.add('opacity-0');
        photoModalContent.classList.remove('scale-100');
        photoModalContent.classList.add('scale-95');
        setTimeout(() => { photoModal.classList.add('hidden'); }, 300);
    }

    // Hidden file input for replacement
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'file';
    hiddenInput.accept = 'image/*';
    hiddenInput.style.display = 'none';
    document.body.appendChild(hiddenInput);

    function triggerReplace(type) {
        hiddenInput.value = ''; // Reset
        hiddenInput.onchange = async (e) => {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const formData = new FormData();
                formData.append('action', 'upload_photo');
                formData.append('id', currentFamilyData.id);
                formData.append('type', type);
                formData.append('photo', file);

                try {
                    const response = await fetch('api/family_action.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        // Update local data and refresh view
                        currentFamilyData[type] = result.file;
                        viewPhotos(currentFamilyData); 
                        loadData(); // Refresh main table in background
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: 'Photo updated successfully',
                            timer: 1000,
                            showConfirmButton: false,
                            background: '#1e293b', 
                            color: '#fff'
                        });
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'Upload failed', 'error');
                }
            }
        };
        hiddenInput.click();
    }

    function deletePhoto(type) {
        Swal.fire({
            title: 'Delete Photo?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Yes, delete',
            background: '#1e293b',
            color: '#fff'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete_photo');
                formData.append('id', currentFamilyData.id);
                formData.append('type', type);

                try {
                    const response = await fetch('api/family_action.php', { method: 'POST', body: formData });
                    const res = await response.json();

                    if (res.status === 'success') {
                         currentFamilyData[type] = null;
                         viewPhotos(currentFamilyData);
                         loadData();
                         Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            timer: 1000,
                            showConfirmButton: false,
                            background: '#1e293b', 
                            color: '#fff'
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                } catch (error) {
                    console.error(error);
                }
            }
        });
    }



    // Form Submit
    document.getElementById('dataForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const netPlanNumber = String(formData.get('net_plan_number') || '').trim();

        if (!/^[A-Za-z0-9]+$/.test(netPlanNumber)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Net Plan Number',
                text: 'Use only letters and numbers for Net Plan Number.',
                background: '#1e293b',
                color: '#fff'
            });
            return;
        }
        
        try {
            const response = await fetch('api/family_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                closeModal();
                loadData();
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

    // Edit
    async function editData(id) {
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        try {
            const response = await fetch('api/family_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                document.getElementById('dataId').value = data.id;
                document.getElementById('hamlet_id').value = data.hamlet_id;
                document.getElementById('net_plan_number').value = data.net_plan_number;
                document.getElementById('beneficiary_name').value = data.beneficiary_name;
                document.getElementById('age').value = data.age;
                document.getElementById('total_members').value = data.total_members;
                document.getElementById('dataAction').value = 'update';
                
                // Set old photos (hidden inputs) to handle replacement
                document.getElementById('old_photo_initial').value = data.photo_initial || '';
                document.getElementById('old_photo_year1').value = data.photo_year1 || '';
                document.getElementById('old_photo_year2').value = data.photo_year2 || '';
                document.getElementById('old_photo_year3').value = data.photo_year3 || '';
                document.getElementById('old_photo_year4').value = data.photo_year4 || '';
                document.getElementById('old_photo_year5').value = data.photo_year5 || '';

                openModal(true);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Delete
    function deleteData(id) {
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#indigo-600',
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
                    const response = await fetch('api/family_action.php', { method: 'POST', body: formData });
                    const res = await response.json();

                    if (res.status === 'success') {
                        loadData();
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: res.message,
                            background: '#1e293b',
                            color: '#fff',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        })
    }

    // QR Code
    function generateQR(id, netPlan, name) {
        document.getElementById('qrcode').innerHTML = "";
        
        // Construct URL
        // Assuming view_family.php is in the same directory
        const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
        const url = `${baseUrl}/view_family.php?id=${id}`;

        new QRCode(document.getElementById('qrcode'), {
            text: url,
            width: 128,
            height: 128,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
        document.getElementById('qrText').innerHTML = `Scan to view details<br><span class="text-xs text-indigo-400 mt-1 block">Net Plan #${netPlan}</span>`;
        
        qrModal.classList.remove('hidden');
        setTimeout(() => {
            qrModal.classList.remove('opacity-0');
            qrModalContent.classList.remove('scale-95');
            qrModalContent.classList.add('scale-100');
        }, 10);
    }
    
    function closeQrModal() {
        qrModal.classList.add('opacity-0');
        qrModalContent.classList.remove('scale-100');
        qrModalContent.classList.add('scale-95');
        setTimeout(() => { qrModal.classList.add('hidden'); }, 300);
    }
</script>

<?php require_once 'includes/footer.php'; ?>
