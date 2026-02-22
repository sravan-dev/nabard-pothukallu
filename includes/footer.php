<!-- DataTables JS and Buttons -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    // Global CSRF Protection for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Global CSRF Protection for Fetch
    const { fetch: originalFetch } = window;
    window.fetch = async (...args) => {
        let [resource, config] = args;
        if (config && config.method && config.method.toUpperCase() === 'POST') {
            config.headers = {
                ...config.headers,
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            };
        }
        return originalFetch(resource, config);
    };

    // Mobile Sidebar Toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('mainSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            // Open
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        } else {
            // Close
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        }
    }

    // Global Search Logic
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('globalSearchResults');
    let debounceTimer;

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(async () => {
                const formData = new FormData();
                formData.append('action', 'search_net_plan');
                formData.append('query', query);

                try {
                    const response = await fetch('api/family_action.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        searchResults.innerHTML = '';
                        if (result.data.length > 0) {
                            searchResults.classList.remove('hidden');
                            result.data.forEach(item => {
                                searchResults.innerHTML += `
                                    <a href="family_details.php?open_id=${item.id}" class="block px-4 py-3 hover:bg-slate-700 border-b border-slate-700 last:border-0 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="text-white font-medium text-sm">${item.beneficiary_name}</div>
                                                <div class="text-indigo-400 text-xs text-mono">Net Plan: #${item.net_plan_number}</div>
                                            </div>
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </div>
                                    </a>
                                `;
                            });
                        } else {
                            searchResults.innerHTML = '<div class="px-4 py-3 text-slate-400 text-sm text-center">No results found</div>';
                            searchResults.classList.remove('hidden');
                        }
                    }
                } catch (e) {
                    console.error(e);
                }
            }, 300);
        });

        // Close search when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    }

    // Global DataTable Initialization Helper
    function initializeDataTable(tableId) {
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
        }
        
        return $(tableId).DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', className: 'dt-button' },
                { extend: 'csv', className: 'dt-button' },
                { extend: 'excel', className: 'dt-button' },
                { extend: 'pdf', className: 'dt-button' },
                { extend: 'print', className: 'dt-button' }
            ],
            stateSave: true,
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
</script>
</body>
</html>
