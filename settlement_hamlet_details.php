<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();
 
$pageTitle = "Settlement/Hamlet Details";
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
                    <h2 class="text-3xl font-bold text-white">Settlement/Hamlet Details</h2>
                    <p class="text-slate-400 text-sm">Settlement & Hamlet Details</p>
                </div>
                
                <button onclick="openModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Settlement/Hamlet
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6">
                <div class="overflow-x-auto">
                    <table id="hamletsTable" class="w-full min-w-[3200px] text-left text-sm text-slate-400">
                        <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                            <tr>
                                <th class="px-4 py-4">ID</th>
                                <th class="px-4 py-4">Block</th>
                                <th class="px-4 py-4">Panchayat</th>
                                <th class="px-4 py-4">Ward</th>
                                <th class="px-4 py-4">Ward Number</th>
                                <th class="px-4 py-4">Name Of Settlement</th>
                                <th class="px-4 py-4">Total Area Of Settlement In Acre</th>
                                <th class="px-4 py-4">Number Of Families</th>
                                <th class="px-4 py-4">Number Of Households</th>
                                <th class="px-4 py-4">Tribal Category</th>
                                <th class="px-4 py-4">Total Population</th>
                                <th class="px-4 py-4">Total Population Male</th>
                                <th class="px-4 py-4">Total Population Female</th>
                                <th class="px-4 py-4">Public Facilities</th>
                                <th class="px-4 py-4">Date Of Formation Of OVS</th>
                                <th class="px-4 py-4">Charge Officer Name</th>
                                <th class="px-4 py-4">Name Of Animator</th>
                                <th class="px-4 py-4">Mobile Of Animator</th>
                                <th class="px-4 py-4">Road Access</th>
                                <th class="px-4 py-4">Map Link</th>
                                <th class="px-4 py-4">Settlement Photo 1</th>
                                <th class="px-4 py-4">Settlement Photo 2</th>
                                <th class="px-4 py-4">Settlement Photo 3</th>
                                <th class="px-4 py-4">Major Crops</th>
                                <th class="px-4 py-4">Major Issues</th>
                                <th class="px-4 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5" id="dataBody">
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal for Data Entry -->
<div id="dataModal" class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-slate-900 rounded-xl shadow-2xl w-full max-w-4xl border border-white/10 transform scale-95 transition-transform duration-300 max-h-[90vh] flex flex-col" id="modalContent">
        
        <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center bg-slate-800 rounded-t-xl">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Settlement/Hamlet Details</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 bg-slate-900">
            <form id="dataForm" method="POST" action="api/settlement_hamlet_action.php" enctype="multipart/form-data" class="max-w-2xl mx-auto space-y-4">
                <input type="hidden" name="id" id="dataId">
                <input type="hidden" name="action" id="dataAction" value="create">

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Name Of Block</label>
                    <input type="text" name="block" id="block" list="blockList" placeholder="Select Block" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <datalist id="blockList"></datalist>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Name Of Panchayat</label>
                    <input type="text" name="panchayat" id="panchayat" list="panchayatList" placeholder="Select Panchayath" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <datalist id="panchayatList"></datalist>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Ward Name And Number</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="ward" id="ward" placeholder="Enter Ward Name" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <input type="text" name="ward_number" id="ward_number" placeholder="Ward Number" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Name Of Settlement *</label>
                    <input type="text" name="settlement_name" id="settlement_name" list="settlementList" required placeholder="Select Settlement" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <datalist id="settlementList"></datalist>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Total Area Of Settlement In Acre</label>
                    <input type="text" name="total_area" id="total_area" placeholder="Total Area Of Settlement In Acre" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Number Of Families</label>
                    <input type="number" name="total_families" id="total_families" placeholder="Enter Number of Families" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Number Of Households</label>
                    <input type="number" name="households" id="households" placeholder="Number of Households" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Tribal Category</label>
                    <div class="relative" id="tribalCategoryWrapper">
                        <input type="hidden" name="tribal_category" id="tribal_category">
                        <button type="button" id="tribalCategoryToggle" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-left flex items-center justify-between">
                            <span id="tribalCategoryLabel" class="text-slate-400">Please Select</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="tribalCategoryDropdown" class="hidden absolute z-40 mt-1 w-full bg-slate-900 border border-slate-700 rounded shadow-xl">
                            <div class="p-2 border-b border-slate-700">
                                <input type="text" id="tribalCategorySearch" placeholder="Search tribal category..." autocomplete="off" class="w-full bg-slate-800 border border-slate-600 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <ul id="tribalCategoryOptions" class="max-h-56 overflow-y-auto py-1"></ul>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Total Population</label>
                    <input type="number" name="population_total" id="population_total" placeholder="Total Population" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 mb-2">
                    <div class="grid grid-cols-3 gap-2">
                        <input type="number" name="population_male" id="population_male" placeholder="Male" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <input type="number" name="population_female" id="population_female" placeholder="Female" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <input type="number" id="population_others" placeholder="Others" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Public Facilities</label>
                    <textarea name="public_facilities" id="public_facilities" rows="2" placeholder="Click To Select Public Facilities" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">OVS Formation Date</label>
                    <input type="date" name="nvs_formation_date" id="nvs_formation_date" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Name Of NVS President</label>
                    <input type="text" name="nvs_president" id="nvs_president" placeholder="NVS PRESIDENT NAME" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Name Of NVS Secretary</label>
                    <input type="text" name="nvs_secretary" id="nvs_secretary" placeholder="NVS SECRETARY NAME" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Name Of Animator</label>
                    <input type="text" name="animator_name" id="animator_name" placeholder="NAME OF ANIMATOR" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Mobile Number</label>
                    <input type="text" name="animator_mobile" id="animator_mobile" placeholder="MOBILE NUMBER" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Road Access</label>
                    <div class="flex items-center gap-4 text-sm text-slate-300">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="road_access" id="road_access_yes" value="Yes">
                            Yes
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="road_access" id="road_access_no" value="No">
                            No
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Google Map link</label>
                    <input type="url" name="map_link" id="map_link" placeholder="Map Link" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Map of the Settlement</label>
                    <input type="file" name="photo1" id="photo1_input" accept="image/*" class="w-full text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200">
                    <div id="previewWrapPhoto1" class="mt-2 hidden">
                        <img id="previewPhoto1" src="" alt="Map preview" class="w-full max-w-xs h-32 object-cover rounded border border-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Photo 1</label>
                    <input type="file" name="photo2" id="photo2_input" accept="image/*" class="w-full text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200">
                    <div id="previewWrapPhoto2" class="mt-2 hidden">
                        <img id="previewPhoto2" src="" alt="Photo 1 preview" class="w-full max-w-xs h-32 object-cover rounded border border-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Photo 2</label>
                    <input type="file" name="photo3" id="photo3_input" accept="image/*" class="w-full text-sm text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-slate-600 file:bg-slate-800 file:text-slate-200">
                    <div id="previewWrapPhoto3" class="mt-2 hidden">
                        <img id="previewPhoto3" src="" alt="Photo 2 preview" class="w-full max-w-xs h-32 object-cover rounded border border-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Major Crops Or Livelihood</label>
                    <textarea name="major_crops" id="major_crops" rows="2" placeholder="Major Crops Or Livelihood" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Major Issues</label>
                    <textarea name="major_issues" id="major_issues" rows="2" placeholder="Major Issues" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-white/10 bg-slate-800 rounded-b-xl flex justify-end gap-3">
            <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded transition-colors">Cancel</button>
            <button type="button" id="saveDataBtn" onclick="submitForm()" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded transition-colors shadow">Save Data</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let tribalCategoryItems = [];

    document.addEventListener('DOMContentLoaded', () => {
        setupTribalCategoryDropdown();
        setupPhotoPreviews();
        loadData();
    });

    function getUniqueSortedCategories(items) {
        const normalized = new Map();
        items.forEach(item => {
            const label = String(item || '').trim();
            if (!label) return;
            const key = label.toLowerCase();
            if (!normalized.has(key)) {
                normalized.set(key, label);
            }
        });
        return Array.from(normalized.values());
    }

    function setTribalCategory(value) {
        const hidden = document.getElementById('tribal_category');
        const label = document.getElementById('tribalCategoryLabel');
        if (!hidden || !label) return;

        const current = String(value || '').trim();
        hidden.value = current;
        if (current) {
            label.textContent = current;
            label.classList.remove('text-slate-400');
            label.classList.add('text-slate-100');
        } else {
            label.textContent = 'Please Select';
            label.classList.remove('text-slate-100');
            label.classList.add('text-slate-400');
        }
    }

    function closeTribalCategoryDropdown() {
        const dropdown = document.getElementById('tribalCategoryDropdown');
        if (dropdown) dropdown.classList.add('hidden');
    }

    function renderTribalCategoryOptions(query = '') {
        const list = document.getElementById('tribalCategoryOptions');
        const hidden = document.getElementById('tribal_category');
        if (!list || !hidden) return;

        const keyword = String(query || '').trim().toLowerCase();
        const selected = String(hidden.value || '').trim().toLowerCase();
        const filtered = tribalCategoryItems.filter(item => item.toLowerCase().includes(keyword));

        list.innerHTML = '';

        const addOption = (label, value) => {
            const li = document.createElement('li');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full text-left px-3 py-2 text-sm transition-colors text-slate-200 hover:bg-emerald-500/20 hover:text-emerald-200';
            if (selected === String(value).toLowerCase()) {
                btn.className = 'w-full text-left px-3 py-2 text-sm transition-colors bg-emerald-600 text-white';
            }
            btn.textContent = label;
            btn.addEventListener('click', () => {
                setTribalCategory(value);
                closeTribalCategoryDropdown();
            });
            li.appendChild(btn);
            list.appendChild(li);
        };

        addOption('Please Select', '');

        if (filtered.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'px-3 py-2 text-sm text-slate-400';
            empty.textContent = 'No matching categories';
            list.appendChild(empty);
            return;
        }

        filtered.forEach(item => addOption(item, item));
    }

    function setupTribalCategoryDropdown() {
        const wrapper = document.getElementById('tribalCategoryWrapper');
        const toggle = document.getElementById('tribalCategoryToggle');
        const dropdown = document.getElementById('tribalCategoryDropdown');
        const search = document.getElementById('tribalCategorySearch');
        if (!wrapper || !toggle || !dropdown || !search) return;

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const isHidden = dropdown.classList.contains('hidden');
            if (isHidden) {
                dropdown.classList.remove('hidden');
                search.value = '';
                renderTribalCategoryOptions('');
                setTimeout(() => search.focus(), 0);
            } else {
                closeTribalCategoryDropdown();
            }
        });

        search.addEventListener('input', () => renderTribalCategoryOptions(search.value));
        search.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeTribalCategoryDropdown();
                toggle.focus();
            }
        });

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) {
                closeTribalCategoryDropdown();
            }
        });

        renderTribalCategoryOptions('');
    }

    function populateSimpleDatalist(id, values) {
        const datalist = document.getElementById(id);
        if (!datalist) return;

        const uniqueValues = [];
        const seen = new Set();
        values.forEach(item => {
            const label = String(item || '').trim();
            if (!label) return;
            const key = label.toLowerCase();
            if (!seen.has(key)) {
                seen.add(key);
                uniqueValues.push(label);
            }
        });

        uniqueValues.sort((a, b) => a.localeCompare(b));
        datalist.innerHTML = '';
        uniqueValues.forEach(value => {
            const option = document.createElement('option');
            option.value = value;
            datalist.appendChild(option);
        });
    }

    function populateLocationLists(rows) {
        populateSimpleDatalist('blockList', rows.map(row => row.block));
        populateSimpleDatalist('panchayatList', rows.map(row => row.panchayat));
        populateSimpleDatalist('settlementList', rows.map(row => row.settlement_name));
    }

    function normalizePreviewPath(path) {
        const value = String(path || '').trim();
        if (!value) return '';
        if (/^https?:\/\//i.test(value) || value.charAt(0) === '/') return value;
        return value.replace(/\\/g, '/');
    }

    function setPreviewImage(previewKey, src) {
        const wrap = document.getElementById('previewWrap' + previewKey);
        const img = document.getElementById('preview' + previewKey);
        if (!wrap || !img) return;

        const finalSrc = normalizePreviewPath(src);
        if (finalSrc) {
            img.src = finalSrc;
            wrap.classList.remove('hidden');
        } else {
            img.src = '';
            wrap.classList.add('hidden');
        }
    }

    function clearPhotoPreviews() {
        setPreviewImage('Photo1', '');
        setPreviewImage('Photo2', '');
        setPreviewImage('Photo3', '');
    }

    function clearPhotoFileInputs() {
        const input1 = document.getElementById('photo1_input');
        const input2 = document.getElementById('photo2_input');
        const input3 = document.getElementById('photo3_input');
        if (input1) input1.value = '';
        if (input2) input2.value = '';
        if (input3) input3.value = '';
    }

    function setExistingPhotoPreviews(data) {
        setPreviewImage('Photo1', data.photo1);
        setPreviewImage('Photo2', data.photo2);
        setPreviewImage('Photo3', data.photo3);
    }

    function bindPhotoPreview(inputId, previewKey) {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('change', () => {
            if (input.files && input.files.length > 0) {
                setPreviewImage(previewKey, URL.createObjectURL(input.files[0]));
            } else {
                setPreviewImage(previewKey, '');
            }
        });
    }

    function setupPhotoPreviews() {
        bindPhotoPreview('photo1_input', 'Photo1');
        bindPhotoPreview('photo2_input', 'Photo2');
        bindPhotoPreview('photo3_input', 'Photo3');
    }

    function setRoadAccess(value) {
        const normalized = String(value || '').trim().toLowerCase();
        const yes = document.getElementById('road_access_yes');
        const no = document.getElementById('road_access_no');
        if (!yes || !no) return;

        yes.checked = normalized === 'yes';
        no.checked = normalized === 'no';
    }

    function hasDetailedSettlementData(row) {
        const hasText = (value) => String(value ?? '').trim() !== '';
        const hasNumber = (value) => Number(value ?? 0) > 0;

        // Hide legacy/basic seed rows by default and show only detailed records.
        return (
            hasText(row.block) ||
            hasText(row.panchayat) ||
            hasText(row.ward) ||
            hasText(row.ward_number) ||
            hasText(row.total_area) ||
            hasText(row.tribal_category) ||
            hasNumber(row.total_families) ||
            hasNumber(row.population_total) ||
            hasNumber(row.population_male) ||
            hasNumber(row.population_female) ||
            hasText(row.public_facilities) ||
            hasText(row.road_access) ||
            hasText(row.major_crops) ||
            hasText(row.major_issues) ||
            hasText(row.nvs_formation_date) ||
            hasText(row.nvs_president) ||
            hasText(row.nvs_secretary) ||
            hasText(row.animator_name) ||
            hasText(row.animator_mobile) ||
            hasText(row.map_link) ||
            hasText(row.photo1) ||
            hasText(row.photo2) ||
            hasText(row.photo3)
        );
    }

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        
        try {
            const response = await fetch('api/settlement_hamlet_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                const detailedRows = (result.data || []).filter(hasDetailedSettlementData);

                populateLocationLists(detailedRows);
                tribalCategoryItems = getUniqueSortedCategories(detailedRows.map(row => row.tribal_category));
                const searchInput = document.getElementById('tribalCategorySearch');
                renderTribalCategoryOptions(searchInput ? searchInput.value : '');

                const txt = (value) => {
                    const raw = String(value ?? '').trim();
                    if (!raw) return '-';
                    return raw
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                };
                const mapLinkCell = (value) => {
                    const raw = String(value ?? '').trim();
                    if (!raw) return '-';
                    const safe = txt(raw);
                    return `<a href="${safe}" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300 underline">Open</a>`;
                };
                const photoCell = (path, label) => {
                    const normalized = normalizePreviewPath(path);
                    if (!normalized) return '-';
                    const safeSrc = txt(normalized);
                    const safeLabel = txt(label);
                    return `
                        <a href="${safeSrc}" target="_blank" rel="noopener noreferrer" class="inline-flex flex-col items-start gap-1">
                            <img src="${safeSrc}" alt="${safeLabel}" class="h-12 w-16 object-cover rounded border border-slate-700">
                        </a>
                    `;
                };

                detailedRows.forEach(row => {
                    const rowId = Number(row.id) || 0;
                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-4">${txt(row.id)}</td>
                            <td class="px-4 py-4">${txt(row.block)}</td>
                            <td class="px-4 py-4">${txt(row.panchayat)}</td>
                            <td class="px-4 py-4">${txt(row.ward)}</td>
                            <td class="px-4 py-4">${txt(row.ward_number)}</td>
                            <td class="px-4 py-4 font-medium text-white">${txt(row.settlement_name)}</td>
                            <td class="px-4 py-4">${txt(row.total_area)}</td>
                            <td class="px-4 py-4">${txt(row.total_families)}</td>
                            <td class="px-4 py-4">${txt(row.households)}</td>
                            <td class="px-4 py-4">${txt(row.tribal_category)}</td>
                            <td class="px-4 py-4 text-emerald-300 font-mono">${txt(row.population_total)}</td>
                            <td class="px-4 py-4">${txt(row.population_male)}</td>
                            <td class="px-4 py-4">${txt(row.population_female)}</td>
                            <td class="px-4 py-4 max-w-[220px] break-words">${txt(row.public_facilities)}</td>
                            <td class="px-4 py-4">${txt(row.nvs_formation_date)}</td>
                            <td class="px-4 py-4">${txt(row.charge_officer_name)}</td>
                            <td class="px-4 py-4">${txt(row.animator_name)}</td>
                            <td class="px-4 py-4">${txt(row.animator_mobile)}</td>
                            <td class="px-4 py-4">${txt(row.road_access)}</td>
                            <td class="px-4 py-4">${mapLinkCell(row.map_link)}</td>
                            <td class="px-4 py-4">${photoCell(row.photo1, 'Settlement Photo 1')}</td>
                            <td class="px-4 py-4">${photoCell(row.photo2, 'Settlement Photo 2')}</td>
                            <td class="px-4 py-4">${photoCell(row.photo3, 'Settlement Photo 3')}</td>
                            <td class="px-4 py-4 max-w-[220px] break-words">${txt(row.major_crops)}</td>
                            <td class="px-4 py-4 max-w-[220px] break-words">${txt(row.major_issues)}</td>
                            <td class="px-4 py-4 text-right space-x-2 whitespace-nowrap">
                                <button onclick="editData(${rowId})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteData(${rowId})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                initializeDataTable('#hamletsTable');
            }
        } catch (error) { console.error('Error:', error); }
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
        clearPhotoFileInputs();

        if (!isEdit) {
            document.getElementById('dataForm').reset();
            document.getElementById('dataId').value = '';
            document.getElementById('dataAction').value = 'create';
            document.getElementById('modalTitle').innerText = 'Add Settlement/Hamlet Details';
            setTribalCategory('');
            setRoadAccess('');
            clearPhotoPreviews();
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Settlement/Hamlet Details';
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
            return;
        }
        const evt = new Event('submit', { bubbles: true, cancelable: true });
        form.dispatchEvent(evt);
    }

    // Form Submit (AJAX multipart, supports photo upload/update)
    document.getElementById('dataForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const saveBtn = document.getElementById('saveDataBtn');
        const originalBtnText = saveBtn ? saveBtn.textContent : 'Save Data';

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            saveBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }

        const onSuccess = (result) => {
            if (result && result.status === 'success') {
                closeModal();
                loadData();
                const uploaded = Array.isArray(result.uploaded) && result.uploaded.length > 0
                    ? ` (Uploaded: ${result.uploaded.join(', ')})`
                    : '';
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: (result.message || 'Saved successfully') + uploaded,
                    background: '#1e293b',
                    color: '#fff',
                    timer: 1800,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: (result && result.message) ? result.message : 'Save failed',
                    background: '#1e293b',
                    color: '#fff'
                });
            }
        };

        const onError = (message) => {
            Swal.fire({
                icon: 'error',
                title: 'Upload Error',
                text: message || 'Save failed. Please try again.',
                background: '#1e293b',
                color: '#fff'
            });
        };

        const onComplete = () => {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = originalBtnText;
                saveBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        };

        if (window.jQuery && typeof $.ajax === 'function') {
            $.ajax({
                url: 'api/settlement_hamlet_action.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: onSuccess,
                error: (xhr) => {
                    let message = 'Save failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    onError(message);
                },
                complete: onComplete
            });
            return;
        }

        fetch('api/settlement_hamlet_action.php', { method: 'POST', body: formData })
            .then(async (response) => {
                const result = await response.json();
                onSuccess(result);
            })
            .catch(() => onError('Save failed. Please try again.'))
            .finally(onComplete);
    });

    // Edit
    async function editData(id) {
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        try {
            const response = await fetch('api/settlement_hamlet_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                const setVal = (id, val) => { if(document.getElementById(id)) document.getElementById(id).value = val || ''; }

                document.getElementById('dataId').value = data.id;
                document.getElementById('dataAction').value = 'update';

                // Populate Fields
                setVal('settlement_name', data.settlement_name);
                setVal('block', data.block);
                setVal('panchayat', data.panchayat);
                setVal('ward', data.ward);
                setVal('ward_number', data.ward_number);
                setVal('total_area', data.total_area);
                
                setVal('total_families', data.total_families);
                setVal('households', data.households);
                setTribalCategory(data.tribal_category);
                setVal('population_total', data.population_total);
                setVal('population_male', data.population_male);
                setVal('population_female', data.population_female);

                setVal('nvs_formation_date', data.nvs_formation_date);
                setVal('nvs_president', data.nvs_president);
                setVal('nvs_secretary', data.nvs_secretary);
                setVal('animator_name', data.animator_name);
                setVal('animator_mobile', data.animator_mobile);
                
                setVal('public_facilities', data.public_facilities);
                setVal('major_crops', data.major_crops);
                setVal('major_issues', data.major_issues);
                
                setVal('map_link', data.map_link);
                setRoadAccess(data.road_access);

                openModal(true);
                setExistingPhotoPreviews(data);
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
                    const response = await fetch('api/settlement_hamlet_action.php', { method: 'POST', body: formData });
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
