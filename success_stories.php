<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

check_auth();

$pageTitle = "Success Stories";
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
                    <h2 class="text-3xl font-bold text-white">Success Stories</h2>
                    <p class="text-slate-400 text-sm">Documenting Achievements</p>
                </div>
                
                <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg flex items-center shadow-lg transition-transform transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Story
                </button>
            </div>

            <div class="glass rounded-xl border border-white/5 overflow-hidden p-6 text-sm">
                <table id="storiesTable" class="w-full text-left text-sm text-slate-400">
                    <thead class="bg-white/5 text-xs uppercase font-medium text-slate-300">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">About the Person</th>
                            <th class="px-6 py-4">Photo</th>
                             <th class="px-6 py-4">Videos</th>
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
    <div class="bg-slate-800 rounded-xl shadow-2xl w-full max-w-2xl border border-slate-700 transform scale-95 transition-transform duration-300 overflow-y-auto max-h-[90vh]" id="modalContent">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800 z-10 sticky top-0">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Add Success Story</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6">
            <form id="dataForm" class="space-y-6">
                <input type="hidden" name="id" id="dataId">
                <input type="hidden" name="action" id="dataAction" value="create">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date</label>
                        <input type="date" name="story_date" id="story_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none scheme-dark max-w-xs">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">About the Person / Story</label>
                        <textarea name="description" id="description" rows="3" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Main Photo</label>
                        <input type="file" name="photo" id="photo" accept="image/*" class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
                        <input type="hidden" name="old_photo" id="old_photo">
                         <div id="photoPreview" class="mt-2 hidden">
                             <img src="" class="h-20 w-32 rounded-lg object-cover border border-slate-600">
                         </div>
                    </div>

                    <!-- Video Section -->
                    <div class="md:col-span-2 border-t border-slate-700 pt-4">
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-medium text-white">Videos with Remarks</label>
                            <button type="button" onclick="addVideoRow()" class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white px-2 py-1 rounded transition-colors">+ Add Video</button>
                        </div>
                        
                        <!-- Existing Videos List (for Delete) -->
                        <div id="existingVideos" class="space-y-2 mb-3"></div>

                        <!-- New Videos Container -->
                        <div id="newVideosContainer" class="space-y-3"></div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors">Save Story</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 z-[60] hidden bg-black/90 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300" onclick="closeLightbox()">
    <img id="lightboxImg" src="" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl transform scale-90 transition-transform duration-300 border border-slate-700">
    <button class="absolute top-4 right-4 text-white/50 hover:text-white transition-colors">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

<!-- Video Modal -->
<div id="videoModal" class="fixed inset-0 z-[70] hidden bg-black/90 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="relative w-full max-w-4xl">
        <button onclick="closeVideoModal()" class="absolute -top-10 right-0 text-white hover:text-slate-300">
             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <video id="videoPlayer" controls class="w-full rounded-lg shadow-2xl border border-slate-700 bg-black"></video>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', loadData);

    async function loadData() {
        const formData = new FormData();
        formData.append('action', 'fetch_all');
        try {
            const response = await fetch('api/success_stories_action.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const tbody = document.getElementById('dataBody');
                tbody.innerHTML = '';
                result.data.forEach(row => {
                    const photoHtml = row.photo 
                        ? `<img src="uploads/success_stories/photos/${row.photo}" onclick="openLightbox('uploads/success_stories/photos/${row.photo}')" class="h-16 w-16 object-cover rounded-lg cursor-pointer border border-slate-600 hover:border-blue-400">`
                        : '<span class="text-xs text-slate-600">No Photo</span>';

                    tbody.innerHTML += `
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-white align-top whitespace-nowrap">${row.story_date}</td>
                            <td class="px-6 py-4 text-slate-300 align-top">${row.description}</td>
                            <td class="px-6 py-4 align-top">${photoHtml}</td>
                            <td class="px-6 py-4 align-top">
                                <span class="bg-indigo-500/10 text-indigo-400 px-2 py-1 rounded text-xs font-bold">${row.video_count} Video(s)</span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 align-top">
                                <button onclick="editData(${row.id})" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                                <button onclick="deleteData(${row.id})" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                initializeDataTable('#storiesTable');
            }
        } catch (e) { console.error(e); }
    }

    function addVideoRow() {
        const container = document.getElementById('newVideosContainer');
        const div = document.createElement('div');
        div.className = "flex items-start space-x-2 bg-slate-700/30 p-2 rounded border border-slate-700/50";
        div.innerHTML = `
            <div class="flex-1 space-y-2">
                <input type="file" name="video_files[]" accept="video/*" class="block w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-slate-600 file:text-white hover:file:bg-slate-500">
                <input type="text" name="video_remarks[]" placeholder="Video Remarks..." class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-xs text-white focus:outline-none focus:border-indigo-500">
            </div>
            <button type="button" onclick="this.closest('div').remove()" class="text-red-400 hover:text-red-300 p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
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
            document.getElementById('modalTitle').innerText = 'Add Success Story';
            document.getElementById('photoPreview').classList.add('hidden');
            document.getElementById('existingVideos').innerHTML = '';
            document.getElementById('newVideosContainer').innerHTML = '';
            addVideoRow(); // Add one default row
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Success Story';
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
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        try {
            const response = await fetch('api/success_stories_action.php', { method: 'POST', body: formData });
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
        finally {
            submitBtn.disabled = false;
            submitBtn.innerText = originalText;
        }
    });

    async function editData(id) {
        const formData = new FormData();
        formData.append('action', 'fetch_single');
        formData.append('id', id);

        try {
            const response = await fetch('api/success_stories_action.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                const videos = result.videos || [];

                document.getElementById('dataId').value = data.id;
                document.getElementById('story_date').value = data.story_date;
                document.getElementById('description').value = data.description;
                
                // Photo
                document.getElementById('old_photo').value = data.photo || '';
                const preview = document.getElementById('photoPreview');
                if (data.photo) {
                    preview.querySelector('img').src = 'uploads/success_stories/photos/' + data.photo;
                    preview.classList.remove('hidden');
                } else {
                    preview.classList.add('hidden');
                }
                
                // Existing Videos
                const existingContainer = document.getElementById('existingVideos');
                existingContainer.innerHTML = '';
                videos.forEach(v => {
                    const div = document.createElement('div');
                    div.className = "flex items-center justify-between bg-slate-900 p-2 rounded border border-slate-700";
                    div.innerHTML = `
                         <div class="flex items-center space-x-3 overflow-hidden">
                            <button type="button" onclick="playVideo('uploads/success_stories/videos/${v.video_path}')" class="text-indigo-400 hover:text-white flex items-center space-x-1">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="text-xs font-bold">Play</span>
                            </button>
                            <span class="text-xs text-slate-300 truncate">${v.remarks || 'No remarks'}</span>
                        </div>
                        <button type="button" onclick="deleteVideo(${v.id}, this)" class="text-red-400 hover:text-red-300 text-xs px-2 py-1">Remove</button>
                    `;
                    existingContainer.appendChild(div);
                });

                document.getElementById('newVideosContainer').innerHTML = ''; // Reset new
                document.getElementById('dataAction').value = 'update';
                openModal(true);
            }
        } catch (error) { console.error('Error:', error); }
    }
    
    async function deleteVideo(id, btn) {
        if(!confirm('Delete this video?')) return;
         const formData = new FormData();
        formData.append('action', 'delete_video');
        formData.append('video_id', id); 
        try {
            const response = await fetch('api/success_stories_action.php', { method: 'POST', body: formData });
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
                    const response = await fetch('api/success_stories_action.php', { method: 'POST', body: formData });
                    const res = await response.json();
                    if (res.status === 'success') {
                        loadData();
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, background: '#1e293b', color: '#fff', timer: 1500, showConfirmButton: false });
                    }
                } catch (error) {}
            }
        })
    }

    // Video Player
    const videoModal = document.getElementById('videoModal');
    const videoPlayer = document.getElementById('videoPlayer');
    
    function playVideo(src) {
        videoPlayer.src = src;
        videoModal.classList.remove('hidden');
         setTimeout(() => { videoModal.classList.remove('opacity-0'); }, 10);
        videoPlayer.play();
    }
    
    function closeVideoModal() {
        videoPlayer.pause();
        videoPlayer.src = "";
        videoModal.classList.add('opacity-0');
        setTimeout(() => { videoModal.classList.add('hidden'); }, 300);
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
