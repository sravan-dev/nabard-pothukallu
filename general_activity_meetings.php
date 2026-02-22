<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "General Activity Meetings";
$role = $_SESSION['role'];
$username = $_SESSION['username'];

require_once 'includes/header.php';
?>

<div class="flex h-screen overflow-hidden bg-slate-900">
    <?php require_once 'includes/nav.php'; ?>

    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once 'includes/topbar.php'; ?>

        <main class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-white">General Activity Meetings</h2>
                    <p class="text-slate-400 text-sm">Manage meetings with details, grants and photos</p>
                </div>
                <button onclick="openModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Meeting
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="meetingsTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Name Of Activity / Meeting</th>
                            <th class="px-6 py-4">Participants</th>
                            <th class="px-6 py-4">Guest</th>
                            <th class="px-6 py-4">Nabard Grant</th>
                            <th class="px-6 py-4">Other Grant</th>
                            <th class="px-6 py-4">Sum</th>
                            <th class="px-6 py-4">Photos</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="dataBody" class="divide-y divide-white/5"></tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<div id="dataModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div id="modalContent" class="bg-slate-900 rounded-xl shadow-2xl w-full max-w-4xl border border-white/10 transform scale-95 transition-transform duration-300 max-h-[92vh] flex flex-col">
        <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center bg-slate-800 rounded-t-xl">
            <h3 class="text-xl font-bold text-white" id="modalTitle">General Activity Meetings</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <form id="dataForm" method="POST" action="api/general_activity_meetings_action.php" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="id" id="dataId">
                <input type="hidden" name="action" id="dataAction" value="create">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date</label>
                        <input type="date" name="meeting_date" id="meeting_date" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">No. Of Participants</label>
                        <input type="number" name="participants" id="participants" min="0" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="No. of Participants">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Name Of Activity / Meeting</label>
                    <input type="text" name="activity_name" id="activity_name" required class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Name of Activity / Meeting">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Location</label>
                    <textarea name="location" id="location" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Location"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Guest</label>
                        <input type="text" name="guest" id="guest" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Guest">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Brief Description</label>
                        <input type="text" name="brief_description" id="brief_description" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Brief Description">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nabard Grant</label>
                        <input type="number" step="0.01" name="nabard_grant" id="nabard_grant" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 grant-input" placeholder="Nabard Grant">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Other Grant</label>
                        <input type="number" step="0.01" name="other_grant" id="other_grant" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 grant-input" placeholder="Other Grant">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Sum</label>
                        <input type="number" step="0.01" name="total_sum" id="total_sum" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Sum">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Activity Photo 1</label>
                        <input type="file" name="photo1" id="photo1" accept="image/*" class="w-full text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200">
                        <div id="previewWrapPhoto1" class="mt-2 hidden">
                            <img id="previewPhoto1" src="" alt="Photo 1" class="w-full max-w-xs h-28 object-cover rounded border border-slate-700">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Activity Photo 2</label>
                        <input type="file" name="photo2" id="photo2" accept="image/*" class="w-full text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200">
                        <div id="previewWrapPhoto2" class="mt-2 hidden">
                            <img id="previewPhoto2" src="" alt="Photo 2" class="w-full max-w-xs h-28 object-cover rounded border border-slate-700">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-white/10 bg-slate-800 rounded-b-xl flex justify-end gap-3">
            <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded transition-colors">Cancel</button>
            <button type="button" id="saveBtn" onclick="submitForm()" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded transition-colors shadow">Save</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        setupPhotoPreview('photo1', 'Photo1');
        setupPhotoPreview('photo2', 'Photo2');
        bindGrantAutoSum();
        loadData();
    });

    function safe(value) {
        const raw = String(value ?? '');
        return raw
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function normalizePath(path) {
        const value = String(path || '').trim();
        if (!value) return '';
        if (/^https?:\/\//i.test(value) || value.charAt(0) === '/') return value;
        return value.replace(/\\/g, '/');
    }

    function setPreview(previewKey, src) {
        const wrap = document.getElementById('previewWrap' + previewKey);
        const img = document.getElementById('preview' + previewKey);
        if (!wrap || !img) return;

        const normalized = normalizePath(src);
        if (normalized) {
            img.src = normalized;
            wrap.classList.remove('hidden');
        } else {
            img.src = '';
            wrap.classList.add('hidden');
        }
    }

    function clearPreviews() {
        setPreview('Photo1', '');
        setPreview('Photo2', '');
    }

    function setupPhotoPreview(inputId, previewKey) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('change', () => {
            if (input.files && input.files.length > 0) {
                setPreview(previewKey, URL.createObjectURL(input.files[0]));
            } else {
                setPreview(previewKey, '');
            }
        });
    }

    function clearFileInputs() {
        const p1 = document.getElementById('photo1');
        const p2 = document.getElementById('photo2');
        if (p1) p1.value = '';
        if (p2) p2.value = '';
    }

    function bindGrantAutoSum() {
        document.querySelectorAll('.grant-input').forEach(input => {
            input.addEventListener('input', calculateSum);
        });
    }

    function calculateSum() {
        const nabard = parseFloat(document.getElementById('nabard_grant')?.value || '0');
        const other = parseFloat(document.getElementById('other_grant')?.value || '0');
        const total = (isNaN(nabard) ? 0 : nabard) + (isNaN(other) ? 0 : other);
        document.getElementById('total_sum').value = total ? total.toFixed(2) : '';
    }

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        const response = await fetch('api/general_activity_meetings_action.php', { method: 'POST', body: formData });
        const result = await response.json();

        const tbody = document.getElementById('dataBody');
        tbody.innerHTML = '';
        if (result.status !== 'success') return;

        const rows = result.data || [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-8 text-center italic text-slate-500">No General Activity Meetings found.</td></tr>';
        } else {
            rows.forEach(row => {
                const id = Number(row.id) || 0;
                const photo1 = normalizePath(row.photo1);
                const photo2 = normalizePath(row.photo2);
                const photoLinks = `
                    ${photo1 ? `<a href="${safe(photo1)}" target="_blank" class="text-blue-400 hover:text-blue-300">Photo1</a>` : '<span class="text-slate-500">-</span>'}
                    ${photo2 ? `<a href="${safe(photo2)}" target="_blank" class="text-blue-400 hover:text-blue-300 ml-2">Photo2</a>` : ''}
                `;

                tbody.innerHTML += `
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">${safe(row.meeting_date) || '-'}</td>
                        <td class="px-6 py-4 text-white">${safe(row.activity_name)}</td>
                        <td class="px-6 py-4">${safe(row.participants) || '-'}</td>
                        <td class="px-6 py-4">${safe(row.guest) || '-'}</td>
                        <td class="px-6 py-4 text-emerald-300 font-mono">${safe(row.nabard_grant) || '0.00'}</td>
                        <td class="px-6 py-4 text-emerald-300 font-mono">${safe(row.other_grant) || '0.00'}</td>
                        <td class="px-6 py-4 text-emerald-300 font-mono">${safe(row.total_sum) || '0.00'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${photoLinks}</td>
                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                            <button onclick="editData(${id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                            <button onclick="deleteData(${id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        initializeDataTable('#meetingsTable');
    }

    const modal = document.getElementById('dataModal');
    const modalContent = document.getElementById('modalContent');

    function openModal(isEdit = false) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);

        clearFileInputs();
        if (!isEdit) {
            document.getElementById('dataForm').reset();
            document.getElementById('dataId').value = '';
            document.getElementById('dataAction').value = 'create';
            document.getElementById('modalTitle').innerText = 'Add General Activity Meeting';
            clearPreviews();
        } else {
            document.getElementById('modalTitle').innerText = 'Edit General Activity Meeting';
        }
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    function submitForm() {
        const form = document.getElementById('dataForm');
        if (!form) return;
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        }
    }

    document.getElementById('dataForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const saveBtn = document.getElementById('saveBtn');
        const originalLabel = saveBtn ? saveBtn.textContent : 'Save';

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            saveBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }

        $.ajax({
            url: 'api/general_activity_meetings_action.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: (result) => {
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
                        text: result.message || 'Save failed',
                        background: '#1e293b',
                        color: '#fff'
                    });
                }
            },
            error: (xhr) => {
                let message = 'Save failed.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    background: '#1e293b',
                    color: '#fff'
                });
            },
            complete: () => {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalLabel;
                    saveBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                }
            }
        });
    });

    async function editData(id) {
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        const response = await fetch('api/general_activity_meetings_action.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.status !== 'success' || !result.data) return;

        const row = result.data;
        openModal(true);

        const setVal = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.value = value ?? '';
        };

        setVal('dataId', row.id);
        setVal('dataAction', 'update');
        setVal('meeting_date', row.meeting_date);
        setVal('activity_name', row.activity_name);
        setVal('participants', row.participants);
        setVal('location', row.location);
        setVal('guest', row.guest);
        setVal('brief_description', row.brief_description);
        setVal('nabard_grant', row.nabard_grant);
        setVal('other_grant', row.other_grant);
        setVal('total_sum', row.total_sum);

        setPreview('Photo1', row.photo1);
        setPreview('Photo2', row.photo2);
    }

    function deleteData(id) {
        Swal.fire({
            title: 'Delete this record?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete',
            background: '#1e293b',
            color: '#fff'
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            const response = await fetch('api/general_activity_meetings_action.php', { method: 'POST', body: formData });
            const res = await response.json();
            if (res.status === 'success') {
                loadData();
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted',
                    text: res.message,
                    background: '#1e293b',
                    color: '#fff',
                    timer: 1200,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message || 'Delete failed',
                    background: '#1e293b',
                    color: '#fff'
                });
            }
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>

