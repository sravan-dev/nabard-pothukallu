<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Individual Distribution Details";
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
                    <h2 class="text-3xl font-bold text-white">Individual Distribution Details</h2>
                    <p class="text-slate-400 text-sm">Manage distribution records with photos and component mapping</p>
                </div>
                <button onclick="openModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Record
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <table id="distributionTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Net Plan</th>
                            <th class="px-6 py-4">Settlement</th>
                            <th class="px-6 py-4">Beneficiary</th>
                            <th class="px-6 py-4">Component</th>
                            <th class="px-6 py-4">Component Type</th>
                            <th class="px-6 py-4">Qty</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Total Amount</th>
                            <th class="px-6 py-4">Photos</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5" id="dataBody"></tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<div id="dataModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-900 rounded-xl shadow-2xl w-full max-w-5xl border border-white/10 transform scale-95 transition-transform duration-300 max-h-[92vh] flex flex-col" id="modalContent">
        <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center bg-slate-800 rounded-t-xl">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Individual Distribution Details</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <form id="dataForm" method="POST" action="api/individual_distribution_action.php" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="id" id="dataId">
                <input type="hidden" name="action" id="dataAction" value="create">
                <input type="hidden" name="family_id" id="family_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Net Plan Number</label>
                        <input type="text" name="netplannumber" id="netplannumber" list="netplannumber_list" required placeholder="Select or type Net Plan Number" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                        <datalist id="netplannumber_list"></datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Name of Settlement</label>
                        <input type="text" name="nameofsettlement" id="nameofsettlement" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Name of Family Head</label>
                        <input type="text" name="family_head" id="family_head" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Name of Beneficiary</label>
                        <input type="text" name="name_of_Beneficiary" id="name_of_Beneficiary" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Insurance No</label>
                        <input type="text" name="InsuranceNo" id="InsuranceNo" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Insurance Date</label>
                        <input type="date" name="InsuranceDate" id="InsuranceDate" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Insurance Amount</label>
                        <input type="number" step="0.01" name="InsuranceAmount" id="InsuranceAmount" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Damage/Death Date</label>
                        <input type="date" name="DamageDeathDate" id="DamageDeathDate" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Insurance Claimed</label>
                        <input type="text" name="InsuranceClamed" id="InsuranceClamed" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Insurance Claimed Date</label>
                        <input type="date" name="InsuranceClamedDate" id="InsuranceClamedDate" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Substitute Purchased Date</label>
                        <input type="date" name="SubstitutePuchacedDate" id="SubstitutePuchacedDate" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Component / Item</label>
                        <select name="mis_comp_item" id="mis_comp_item" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                            <option value="">Please Select</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Component Type</label>
                        <select name="component_type_id" id="component_type_id" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                            <option value="">Please Select</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nos. / Quantity</label>
                        <input type="text" name="mis_qty" id="mis_qty" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date of Distribution</label>
                        <input type="date" name="mis_date_of_distribution" id="mis_date_of_distribution" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Name of Bank</label>
                        <input type="text" name="name_of_bank" id="name_of_bank" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Account No</label>
                        <input type="text" name="Account_no" id="Account_no" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">IFSC</label>
                        <input type="text" name="ifsc" id="ifsc" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Photo 1</label>
                        <input type="file" name="photo1" id="photo1" accept="image/*" class="w-full text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200">
                        <div id="previewWrapPhoto1" class="mt-2 hidden">
                            <img id="previewPhoto1" src="" alt="Photo 1" class="w-full max-w-xs h-28 object-cover rounded border border-slate-700">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Photo 2</label>
                        <input type="file" name="photo2" id="photo2" accept="image/*" class="w-full text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200">
                        <div id="previewWrapPhoto2" class="mt-2 hidden">
                            <img id="previewPhoto2" src="" alt="Photo 2" class="w-full max-w-xs h-28 object-cover rounded border border-slate-700">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nabard Share</label>
                        <input type="number" step="0.01" name="Nabard_Share" id="Nabard_Share" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 share-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Beneficiary Share</label>
                        <input type="number" step="0.01" name="Beneficiary_Share" id="Beneficiary_Share" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 share-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Convergence</label>
                        <input type="number" step="0.01" name="Convergence" id="Convergence" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 share-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Agency Share</label>
                        <input type="number" step="0.01" name="Agency_Share" id="Agency_Share" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 share-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Total Amount</label>
                        <input type="number" step="0.01" name="Total_Amount" id="Total_Amount" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100">
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
    const netPlanMap = {};
    let componentsCache = [];
    let netPlanSearchTimer = null;
    let netPlanSearchToken = 0;

    document.addEventListener('DOMContentLoaded', async () => {
        setupPhotoPreview('photo1', 'Photo1');
        setupPhotoPreview('photo2', 'Photo2');
        bindShareAutoTotal();
        bindNetPlanAutoFill();
        bindComponentTypeFilter();
        await Promise.all([loadNetPlans(), loadComponents(), loadData()]);
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

    function bindShareAutoTotal() {
        document.querySelectorAll('.share-input').forEach(input => {
            input.addEventListener('input', calculateTotalAmount);
        });
    }

    function calculateTotalAmount() {
        const fields = ['Nabard_Share', 'Beneficiary_Share', 'Convergence', 'Agency_Share'];
        let sum = 0;
        fields.forEach(id => {
            const value = parseFloat(document.getElementById(id)?.value || '0');
            if (!isNaN(value)) sum += value;
        });
        document.getElementById('Total_Amount').value = sum ? sum.toFixed(2) : '';
    }

    function bindNetPlanAutoFill() {
        const netPlanInput = document.getElementById('netplannumber');
        if (!netPlanInput) return;

        const syncNetPlan = () => {
            const key = String(netPlanInput.value || '');
            const selected = netPlanMap[key];
            document.getElementById('family_id').value = selected ? selected.family_id : '';
            if (!selected) return;

            document.getElementById('nameofsettlement').value = selected.settlement_name || '';

            if (selected && !document.getElementById('family_head').value) {
                document.getElementById('family_head').value = selected.beneficiary_name || '';
            }
            if (selected && !document.getElementById('name_of_Beneficiary').value) {
                document.getElementById('name_of_Beneficiary').value = selected.beneficiary_name || '';
            }
        };

        const queueSearch = () => {
            const query = String(netPlanInput.value || '').trim();
            clearTimeout(netPlanSearchTimer);
            netPlanSearchTimer = setTimeout(() => {
                searchNetPlans(query);
            }, 250);
            syncNetPlan();
        };

        netPlanInput.addEventListener('input', queueSearch);
        netPlanInput.addEventListener('change', queueSearch);
        netPlanInput.addEventListener('focus', () => {
            searchNetPlans(String(netPlanInput.value || '').trim());
        });
    }

    function bindComponentTypeFilter() {
        const componentSelect = document.getElementById('mis_comp_item');
        if (!componentSelect) return;
        componentSelect.addEventListener('change', () => {
            populateComponentTypes(componentSelect.value, '');
        });
    }

    function populateComponentTypes(componentId, selectedTypeId) {
        const select = document.getElementById('component_type_id');
        if (!select) return;
        select.innerHTML = '<option value="">Please Select</option>';

        const component = componentsCache.find(c => String(c.id) === String(componentId));
        if (!component || !Array.isArray(component.sub_components)) return;

        component.sub_components.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.textContent = sub.name;
            if (String(sub.id) === String(selectedTypeId)) {
                option.selected = true;
            }
            select.appendChild(option);
        });
    }

    function applyNetPlanRows(rows) {
        const list = document.getElementById('netplannumber_list');
        if (list) list.innerHTML = '';
        Object.keys(netPlanMap).forEach(key => delete netPlanMap[key]);

        (rows || []).forEach(row => {
            const key = String(row.net_plan_number);
            netPlanMap[key] = row;
            if (list) {
                const option = document.createElement('option');
                option.value = key;
                option.label = [row.beneficiary_name || '', row.settlement_name || ''].filter(Boolean).join(' - ');
                list.appendChild(option);
            }
        });
    }

    async function searchNetPlans(query = '') {
        const token = ++netPlanSearchToken;
        const formData = new FormData();
        formData.append('action', 'search_net_plans');
        formData.append('query', query);

        try {
            const response = await fetch('api/individual_distribution_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (token !== netPlanSearchToken) return;
            if (result.status !== 'success') return;

            applyNetPlanRows(result.data || []);
        } catch (error) {
            console.error(error);
        }
    }

    async function loadNetPlans() {
        await searchNetPlans('');
    }

    async function loadComponents() {
        const formData = new FormData();
        formData.append('action', 'fetch_components');
        const response = await fetch('api/individual_distribution_action.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.status !== 'success') return;

        componentsCache = result.data || [];
        const select = document.getElementById('mis_comp_item');
        select.innerHTML = '<option value="">Please Select</option>';

        componentsCache.forEach(component => {
            const option = document.createElement('option');
            option.value = component.id;
            option.textContent = component.name;
            select.appendChild(option);
        });
    }

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        const response = await fetch('api/individual_distribution_action.php', { method: 'POST', body: formData });
        const result = await response.json();

        const tbody = document.getElementById('dataBody');
        tbody.innerHTML = '';
        if (result.status !== 'success') return;

        const rows = result.data || [];
        rows.forEach(row => {
            const netPlan = safe(row.net_plan_number);
            const settlement = safe(row.settlement_name);
            const beneficiary = safe(row.beneficiary_name);
            const component = safe(row.component_name);
            const componentType = safe(row.component_type_name);
            const quantity = safe(row.quantity);
            const distDate = safe(row.distribution_date);
            const totalAmount = safe(row.total_amount);

            const photoCount = (row.photo1 ? 1 : 0) + (row.photo2 ? 1 : 0);
            const photoBadge = photoCount > 0
                ? `<span class="px-2 py-1 bg-emerald-500/10 text-emerald-400 text-xs rounded">${photoCount} file(s)</span>`
                : '<span class="text-slate-500 text-xs">None</span>';

            const id = Number(row.id) || 0;
            tbody.innerHTML += `
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-6 py-4 font-mono text-indigo-300">${netPlan}</td>
                    <td class="px-6 py-4 text-white">${settlement}</td>
                    <td class="px-6 py-4">${beneficiary}</td>
                    <td class="px-6 py-4">${component}</td>
                    <td class="px-6 py-4">${componentType}</td>
                    <td class="px-6 py-4">${quantity}</td>
                    <td class="px-6 py-4">${distDate || '-'}</td>
                    <td class="px-6 py-4 text-emerald-300 font-mono">${totalAmount || '-'}</td>
                    <td class="px-6 py-4">${photoBadge}</td>
                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                        <button onclick="editData(${id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                        <button onclick="deleteData(${id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                    </td>
                </tr>
            `;
        });

        initializeDataTable('#distributionTable');
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
            document.getElementById('modalTitle').innerText = 'Add Individual Distribution Details';
            document.getElementById('component_type_id').innerHTML = '<option value="">Please Select</option>';
            clearPreviews();
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Individual Distribution Details';
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
            url: 'api/individual_distribution_action.php',
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
        if (!componentsCache.length) {
            await loadComponents();
        }
        if (!Object.keys(netPlanMap).length) {
            await loadNetPlans();
        }

        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        const response = await fetch('api/individual_distribution_action.php', { method: 'POST', body: formData });
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
        setVal('family_id', row.family_id);
        setVal('netplannumber', row.net_plan_number);
        setVal('nameofsettlement', row.settlement_name);
        setVal('family_head', row.family_head);
        setVal('name_of_Beneficiary', row.beneficiary_name);
        setVal('InsuranceNo', row.insurance_no);
        setVal('InsuranceDate', row.insurance_date);
        setVal('InsuranceAmount', row.insurance_amount);
        setVal('DamageDeathDate', row.damage_death_date);
        setVal('InsuranceClamed', row.insurance_claimed);
        setVal('InsuranceClamedDate', row.insurance_claimed_date);
        setVal('SubstitutePuchacedDate', row.substitute_purchased_date);
        setVal('mis_comp_item', row.component_id);
        populateComponentTypes(row.component_id, row.component_type_id);
        setVal('mis_qty', row.quantity);
        setVal('mis_date_of_distribution', row.distribution_date);
        setVal('name_of_bank', row.bank_name);
        setVal('Account_no', row.account_no);
        setVal('ifsc', row.ifsc);
        setVal('Nabard_Share', row.nabard_share);
        setVal('Beneficiary_Share', row.beneficiary_share);
        setVal('Convergence', row.convergence_share);
        setVal('Agency_Share', row.agency_share);
        setVal('Total_Amount', row.total_amount);

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

            const response = await fetch('api/individual_distribution_action.php', { method: 'POST', body: formData });
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
