<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "JLG/Group Details";
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
                    <h2 class="text-3xl font-bold text-white">JLG/Group Details</h2>
                    <p class="text-slate-400 text-sm">Manage JLG / Group entries with loan and share details</p>
                </div>
                <button onclick="openModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add JLG/Group
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="jlgTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Group Name</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Members</th>
                            <th class="px-6 py-4">Net Plan Nos</th>
                            <th class="px-6 py-4">Loans Availed From</th>
                            <th class="px-6 py-4">Loan Amount</th>
                            <th class="px-6 py-4">Date of Sanction</th>
                            <th class="px-6 py-4">Total</th>
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
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add JLG/Group Details</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <form id="dataForm" method="POST" action="api/jlg_group_action.php" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="id" id="dataId">
                <input type="hidden" name="action" id="dataAction" value="create">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Name Of Group</label>
                        <input type="text" name="group_name" id="group_name" required class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Name Of Group">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Type</label>
                        <select name="group_type" id="group_type" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                            <option value="JLG">JLG</option>
                            <option value="Group">Group</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">No. Of Members</label>
                        <input type="number" name="no_of_members" id="no_of_members" min="0" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="No. of Members">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Net Plan Nos</label>
                        <input type="text" name="net_plan_nos" id="net_plan_nos" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Type Net Plan Nos">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Member Names</label>
                        <textarea name="member_names" id="member_names" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Type Family Member Name"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Activity Involved</label>
                        <textarea name="activity_involved" id="activity_involved" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Activity Involved"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Name Of Account</label>
                        <input type="text" name="account_name" id="account_name" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Name Of Beneficiary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Name Of Bank</label>
                        <input type="text" name="bank_name" id="bank_name" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Name Of Bank">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Account No</label>
                        <input type="text" name="account_no" id="account_no" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Account No">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">IFSC</label>
                        <input type="text" name="ifsc" id="ifsc" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="IFSC">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Loans Availed From</label>
                        <input type="text" name="loans_availed_from" id="loans_availed_from" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Loans Availed From">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Loan Amount</label>
                        <input type="number" step="0.01" name="loan_amount" id="loan_amount" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Loan Amount">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date Of Sanction</label>
                        <input type="date" name="date_of_sanction" id="date_of_sanction" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Group Photo</label>
                        <input type="file" name="group_photo" id="group_photo" accept="image/*" class="w-full text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200">
                        <div id="previewWrapGroupPhoto" class="mt-2 hidden">
                            <img id="previewGroupPhoto" src="" alt="Group Photo" class="w-full max-w-xs h-28 object-cover rounded border border-slate-700">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Activity Photo</label>
                        <input type="file" name="activity_photo" id="activity_photo" accept="image/*" class="w-full text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200">
                        <div id="previewWrapActivityPhoto" class="mt-2 hidden">
                            <img id="previewActivityPhoto" src="" alt="Activity Photo" class="w-full max-w-xs h-28 object-cover rounded border border-slate-700">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Total Amount</label>
                        <input type="number" step="0.01" name="total_amount" id="total_amount" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Total Amount">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nabard Share</label>
                        <input type="number" step="0.01" name="nabard_share" id="nabard_share" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 share-input" placeholder="Nabard Share">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Beneficiary Share</label>
                        <input type="number" step="0.01" name="beneficiary_share" id="beneficiary_share" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 share-input" placeholder="Beneficiary Share">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Convergence</label>
                        <input type="number" step="0.01" name="convergence" id="convergence" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 share-input" placeholder="Convergence">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Agency Share</label>
                        <input type="number" step="0.01" name="agency_share" id="agency_share" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 share-input" placeholder="Agency Share">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Total</label>
                        <input type="number" step="0.01" name="total" id="total" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100" placeholder="Total">
                    </div>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-white/10 bg-slate-800 rounded-b-xl flex justify-end gap-3">
            <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded transition-colors">Cancel</button>
            <button type="button" id="saveBtn" onclick="submitForm()" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded transition-colors shadow">Save Data</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        setupPhotoPreview('group_photo', 'GroupPhoto');
        setupPhotoPreview('activity_photo', 'ActivityPhoto');
        bindShareAutoTotal();
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
        setPreview('GroupPhoto', '');
        setPreview('ActivityPhoto', '');
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
        const groupInput = document.getElementById('group_photo');
        const activityInput = document.getElementById('activity_photo');
        if (groupInput) groupInput.value = '';
        if (activityInput) activityInput.value = '';
    }

    function bindShareAutoTotal() {
        document.querySelectorAll('.share-input').forEach(input => {
            input.addEventListener('input', calculateTotal);
        });
    }

    function calculateTotal() {
        const fields = ['nabard_share', 'beneficiary_share', 'convergence', 'agency_share'];
        let sum = 0;
        fields.forEach(id => {
            const value = parseFloat(document.getElementById(id)?.value || '0');
            if (!isNaN(value)) sum += value;
        });
        const totalInput = document.getElementById('total');
        if (totalInput) {
            totalInput.value = sum ? sum.toFixed(2) : '';
        }
    }

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');

        const response = await fetch('api/jlg_group_action.php', { method: 'POST', body: formData });
        const result = await response.json();

        const tbody = document.getElementById('dataBody');
        tbody.innerHTML = '';
        if (result.status !== 'success') return;

        const rows = result.data || [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="px-6 py-8 text-center italic text-slate-500">No JLG/Group details found.</td></tr>';
        } else {
            rows.forEach(row => {
                const id = Number(row.id) || 0;
                const groupPhoto = normalizePath(row.group_photo);
                const activityPhoto = normalizePath(row.activity_photo);
                const groupPhotoLink = groupPhoto ? `<a href="${safe(groupPhoto)}" target="_blank" class="text-blue-400 hover:text-blue-300">Group</a>` : '<span class="text-slate-500">-</span>';
                const activityPhotoLink = activityPhoto ? `<a href="${safe(activityPhoto)}" target="_blank" class="text-blue-400 hover:text-blue-300">Activity</a>` : '<span class="text-slate-500">-</span>';

                tbody.innerHTML += `
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 text-white">${safe(row.group_name)}</td>
                        <td class="px-6 py-4">${safe(row.group_type) || '-'}</td>
                        <td class="px-6 py-4">${safe(row.no_of_members) || '-'}</td>
                        <td class="px-6 py-4">${safe(row.net_plan_nos) || '-'}</td>
                        <td class="px-6 py-4">${safe(row.loans_availed_from) || '-'}</td>
                        <td class="px-6 py-4 text-emerald-300 font-mono">${safe(row.loan_amount) || '0.00'}</td>
                        <td class="px-6 py-4">${safe(row.date_of_sanction) || '-'}</td>
                        <td class="px-6 py-4 text-emerald-300 font-mono">${safe(row.total) || '0.00'}</td>
                        <td class="px-6 py-4 space-x-2 whitespace-nowrap">${groupPhotoLink} ${activityPhotoLink}</td>
                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                            <button onclick="editData(${id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                            <button onclick="deleteData(${id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        initializeDataTable('#jlgTable');
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
            document.getElementById('modalTitle').innerText = 'Add JLG/Group Details';
            document.getElementById('group_type').value = 'JLG';
            clearPreviews();
        } else {
            document.getElementById('modalTitle').innerText = 'Edit JLG/Group Details';
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
        const originalLabel = saveBtn ? saveBtn.textContent : 'Save Data';

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            saveBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }

        $.ajax({
            url: 'api/jlg_group_action.php',
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

        const response = await fetch('api/jlg_group_action.php', { method: 'POST', body: formData });
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
        setVal('group_name', row.group_name);
        setVal('group_type', row.group_type || 'JLG');
        setVal('no_of_members', row.no_of_members);
        setVal('net_plan_nos', row.net_plan_nos);
        setVal('member_names', row.member_names);
        setVal('activity_involved', row.activity_involved);
        setVal('account_name', row.account_name);
        setVal('bank_name', row.bank_name);
        setVal('account_no', row.account_no);
        setVal('ifsc', row.ifsc);
        setVal('total_amount', row.total_amount);
        setVal('nabard_share', row.nabard_share);
        setVal('beneficiary_share', row.beneficiary_share);
        setVal('convergence', row.convergence);
        setVal('agency_share', row.agency_share);
        setVal('total', row.total);
        setVal('loans_availed_from', row.loans_availed_from);
        setVal('loan_amount', row.loan_amount);
        setVal('date_of_sanction', row.date_of_sanction);

        setPreview('GroupPhoto', row.group_photo);
        setPreview('ActivityPhoto', row.activity_photo);
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

            const response = await fetch('api/jlg_group_action.php', { method: 'POST', body: formData });
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

