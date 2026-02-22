<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Social Auditing";
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
                    <h2 class="text-3xl font-bold text-white">Social Auditing</h2>
                    <p class="text-slate-400 text-sm">Add and manage social auditing records</p>
                </div>
                <button onclick="openModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Social Audit
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="socialAuditTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Settlement</th>
                            <th class="px-6 py-4">Participants</th>
                            <th class="px-6 py-4">Households Covered</th>
                            <th class="px-6 py-4">Major Findings</th>
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
    <div id="modalContent" class="bg-slate-900 rounded-xl shadow-2xl w-full max-w-5xl border border-white/10 transform scale-95 transition-transform duration-300 max-h-[92vh] flex flex-col">
        <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center bg-slate-800 rounded-t-xl">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Social Auditing</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <form id="dataForm" method="POST" action="api/social_auditing_action.php" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="id" id="dataId">
                <input type="hidden" name="action" id="dataAction" value="create">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date</label>
                        <input type="date" name="audit_date" id="audit_date" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">No. Of Households Covered</label>
                        <input type="number" name="households_covered" id="households_covered" min="0" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="No. of Households covered">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Name Of Settlement</label>
                    <input type="text" name="settlement_name" id="settlement_name" required class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Name of Settlement">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Name Of Participants</label>
                    <textarea name="participants" id="participants" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Name of Participants"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Major Findings</label>
                    <textarea name="major_findings" id="major_findings" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Major findings"></textarea>
                </div>

                <div id="existingPhotosSection" class="hidden">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Existing Photos (check to remove)</label>
                    <div id="existingPhotosList" class="grid grid-cols-2 md:grid-cols-4 gap-3"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Photos</label>
                    <div id="photoInputsContainer" class="space-y-2"></div>
                    <p class="text-xs text-slate-500 mt-2">Use <span class="font-semibold text-emerald-400">+</span> to add multiple photos.</p>
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
        resetPhotoInputs();
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

    function createPhotoInputRow() {
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';

        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'photos[]';
        input.accept = 'image/*';
        input.className = 'flex-1 text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200';

        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'h-9 w-9 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-xl leading-none';
        addBtn.textContent = '+';
        addBtn.title = 'Add another photo';
        addBtn.onclick = () => addPhotoInput();

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'h-9 w-9 rounded bg-slate-700 hover:bg-red-600 text-white text-lg leading-none';
        removeBtn.textContent = '-';
        removeBtn.title = 'Remove this field';
        removeBtn.onclick = () => {
            row.remove();
            ensureAtLeastOnePhotoInput();
        };

        row.appendChild(input);
        row.appendChild(addBtn);
        row.appendChild(removeBtn);
        return row;
    }

    function ensureAtLeastOnePhotoInput() {
        const container = document.getElementById('photoInputsContainer');
        if (!container.querySelector('input[name="photos[]"]')) {
            container.appendChild(createPhotoInputRow());
        }
    }

    function resetPhotoInputs() {
        const container = document.getElementById('photoInputsContainer');
        container.innerHTML = '';
        container.appendChild(createPhotoInputRow());
    }

    function addPhotoInput() {
        const container = document.getElementById('photoInputsContainer');
        container.appendChild(createPhotoInputRow());
    }

    function hideExistingPhotos() {
        const section = document.getElementById('existingPhotosSection');
        const list = document.getElementById('existingPhotosList');
        section.classList.add('hidden');
        list.innerHTML = '';
    }

    function renderExistingPhotos(photos) {
        const section = document.getElementById('existingPhotosSection');
        const list = document.getElementById('existingPhotosList');
        list.innerHTML = '';

        if (!Array.isArray(photos) || photos.length === 0) {
            section.classList.add('hidden');
            return;
        }

        photos.forEach(photo => {
            const wrap = document.createElement('label');
            wrap.className = 'border border-slate-700 rounded p-2 bg-slate-800/70 flex flex-col gap-2 cursor-pointer';

            const img = document.createElement('img');
            img.src = normalizePath(photo.photo_path);
            img.alt = 'Photo';
            img.className = 'w-full h-20 object-cover rounded border border-slate-700';

            const checkboxWrap = document.createElement('div');
            checkboxWrap.className = 'flex items-center gap-2 text-xs text-slate-300';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'remove_photo_ids[]';
            checkbox.value = photo.id;
            checkbox.className = 'accent-red-500';

            const text = document.createElement('span');
            text.textContent = 'Remove';

            checkboxWrap.appendChild(checkbox);
            checkboxWrap.appendChild(text);

            wrap.appendChild(img);
            wrap.appendChild(checkboxWrap);
            list.appendChild(wrap);
        });

        section.classList.remove('hidden');
    }

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        const response = await fetch('api/social_auditing_action.php', { method: 'POST', body: formData });
        const result = await response.json();

        const tbody = document.getElementById('dataBody');
        tbody.innerHTML = '';
        if (result.status !== 'success') return;

        const rows = result.data || [];
        if (rows.length) {
            rows.forEach(row => {
                const id = Number(row.id) || 0;
                const photoCount = Number(row.photo_count || 0);
                const photoBadge = photoCount > 0
                    ? `<span class="px-2 py-1 bg-emerald-500/10 text-emerald-400 text-xs rounded">${photoCount} photo(s)</span>`
                    : '<span class="text-slate-500 text-xs">None</span>';

                tbody.innerHTML += `
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">${safe(row.audit_date) || '-'}</td>
                        <td class="px-6 py-4 text-white">${safe(row.settlement_name)}</td>
                        <td class="px-6 py-4">${safe(row.participants) || '-'}</td>
                        <td class="px-6 py-4">${safe(row.households_covered) || '-'}</td>
                        <td class="px-6 py-4">${safe(row.major_findings) || '-'}</td>
                        <td class="px-6 py-4">${photoBadge}</td>
                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                            <button onclick="editData(${id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                            <button onclick="deleteData(${id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        initializeDataTable('#socialAuditTable');
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

        if (!isEdit) {
            document.getElementById('dataForm').reset();
            document.getElementById('dataId').value = '';
            document.getElementById('dataAction').value = 'create';
            document.getElementById('modalTitle').innerText = 'Add Social Auditing';
            hideExistingPhotos();
            resetPhotoInputs();
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Social Auditing';
            resetPhotoInputs();
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
            url: 'api/social_auditing_action.php',
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

        const response = await fetch('api/social_auditing_action.php', { method: 'POST', body: formData });
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
        setVal('audit_date', row.audit_date);
        setVal('settlement_name', row.settlement_name);
        setVal('participants', row.participants);
        setVal('households_covered', row.households_covered);
        setVal('major_findings', row.major_findings);

        renderExistingPhotos(row.photos || []);
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

            const response = await fetch('api/social_auditing_action.php', { method: 'POST', body: formData });
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
