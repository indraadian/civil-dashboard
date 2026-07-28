/**
 * Reusable DataTable Alpine.js Engine
 *
 * Provides all state and methods for search, filter, sort, paginate,
 * row selection, bulk actions, and column visibility.
 *
 * Usage: x-data="dataTableEngine({ ...config })"
 */
export function registerDataTableEngine(Alpine) {
    Alpine.data('dataTableEngine', (config) => ({
        // --- Config (from PHP) ---
        config: config,

        // --- State ---
        search: '',
        rows: [],
        selectedRows: [],
        selectAll: false,
        currentPage: 1,
        totalPages: 1,
        perPage: config.defaultPerPage || 10,
        totalData: 0,
        sortField: config.defaultSortField || 'updated_at',
        sortDirection: config.defaultSortDirection || 'desc',
        loading: false,
        visibleColumns: [],
        showColumnToggle: false,

        // Active filters: [{field, value}]
        activeFilters: [],

        // --- Init ---
        init() {
            // Initialize visible columns from config
            this.visibleColumns = this.config.columns
                .filter(c => c.visible !== false)
                .map(c => c.field);

            this.getData();

            // Watch for search changes with debounce
            this.$watch('search', () => {
                this.currentPage = 1;
                this.getData();
            });
        },

        // --- Data Fetching ---
        getData() {
            this.loading = true;

            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: this.search,
                sort_field: this.sortField,
                sort_direction: this.sortDirection,
            });

            // Add filters
            this.activeFilters.forEach((filter, index) => {
                if (filter.field && filter.value !== '') {
                    params.append(`filters[${index}][field]`, filter.field);
                    params.append(`filters[${index}][value]`, filter.value);
                }
            });

            fetch(`${this.config.dataUrl}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(response => {
                if (response.status === 419 || response.status === 401) {
                    window.location.href = '/login';
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
                this.rows = data.data;
                this.currentPage = data.meta.current_page;
                this.totalPages = data.meta.last_page;
                this.totalData = data.meta.total;
                this.loading = false;
            })
            .catch(err => {
                console.error('DataTable fetch error:', err);
                this.loading = false;
            });
        },

        // --- Sorting ---
        toggleSort(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            this.currentPage = 1;
            this.getData();
        },

        getSortIcon(field) {
            if (this.sortField !== field) return 'none';
            return this.sortDirection;
        },

        // --- Pagination ---
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.getData();
            }
        },

        prevPage() {
            if (this.currentPage > 1) this.goToPage(this.currentPage - 1);
        },

        nextPage() {
            if (this.currentPage < this.totalPages) this.goToPage(this.currentPage + 1);
        },

        changePerPage(value) {
            this.perPage = parseInt(value);
            this.currentPage = 1;
            this.getData();
        },

        get displayedPages() {
            const pages = [];
            for (let i = 1; i <= this.totalPages; i++) {
                if (
                    i === 1 ||
                    i === this.totalPages ||
                    (i >= this.currentPage - 1 && i <= this.currentPage + 1)
                ) {
                    pages.push(i);
                } else if (pages[pages.length - 1] !== '...') {
                    pages.push('...');
                }
            }
            return pages;
        },

        // --- Row Selection ---
        handleSelectAll() {
            this.selectAll = !this.selectAll;
            this.selectedRows = this.selectAll
                ? this.rows.map(row => row.id)
                : [];
        },

        handleRowSelect(id) {
            if (this.selectedRows.includes(id)) {
                this.selectedRows = this.selectedRows.filter(rowId => rowId !== id);
            } else {
                this.selectedRows.push(id);
            }
            this.selectAll = this.selectedRows.length === this.rows.length && this.rows.length > 0;
        },

        isSelected(id) {
            return this.selectedRows.includes(id);
        },

        // --- Column Visibility ---
        isColumnVisible(field) {
            return this.visibleColumns.includes(field);
        },

        toggleColumn(field) {
            if (this.visibleColumns.includes(field)) {
                this.visibleColumns = this.visibleColumns.filter(f => f !== field);
            } else {
                this.visibleColumns.push(field);
            }
        },

        // --- Filters ---
        addFilter() {
            const firstFilter = this.config.filters[0];
            if (firstFilter) {
                this.activeFilters.push({ field: firstFilter.field, value: '' });
            }
        },

        removeFilter(index) {
            this.activeFilters.splice(index, 1);
            this.currentPage = 1;
            this.getData();
        },

        resetFilters() {
            this.activeFilters = [];
            this.currentPage = 1;
            this.getData();
        },

        applyFilter() {
            this.currentPage = 1;
            this.getData();
        },

        getFilterOptions(field) {
            const filter = this.config.filters.find(f => f.field === field);
            return filter?.options || [];
        },

        getFilterType(field) {
            const filter = this.config.filters.find(f => f.field === field);
            return filter?.type || 'text';
        },

        // --- Row Actions ---
        executeRowAction(action, row) {
            if (action.confirmMessage && !confirm(action.confirmMessage)) {
                return;
            }

            if (action.emitEvent) {
                // For edit-type events: fetch full data then emit
                if (action.name === 'edit') {
                    const editUrl = action.url
                        ? action.url.replace(':id', row.id)
                        : `${this.config.baseUrl}/${row.id}/edit`;

                    fetch(editUrl, {
                        headers: { 'Accept': 'application/json' },
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.$dispatch(action.emitEvent, { data: data });
                    })
                    .catch(err => console.error('Error:', err));
                } else {
                    this.$dispatch(action.emitEvent, { data: row });
                }
                return;
            }

            if (action.method === 'DELETE') {
                const url = action.url
                    ? action.url.replace(':id', row.id)
                    : `${this.config.baseUrl}/${row.id}`;

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                    },
                })
                .then(res => res.json())
                .then(() => {
                    this.rows = this.rows.filter(r => r.id !== row.id);
                    this.selectedRows = this.selectedRows.filter(id => id !== row.id);
                    this.totalData--;
                    if (this.rows.length === 0 && this.currentPage > 1) {
                        this.goToPage(this.currentPage - 1);
                    }
                })
                .catch(err => console.error('Error:', err));
            }
        },

        // --- Bulk Actions ---
        executeBulkAction(action) {
            if (this.selectedRows.length === 0) return;

            const message = action.confirmMessage
                ? action.confirmMessage.replace(':count', this.selectedRows.length)
                : `Yakin ingin ${action.label.toLowerCase()} ${this.selectedRows.length} data?`;

            if (!confirm(message)) return;

            fetch(action.endpoint, {
                method: action.method || 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                body: JSON.stringify({ ids: this.selectedRows }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' || data.message) {
                    this.rows = this.rows.filter(row => !this.selectedRows.includes(row.id));
                    this.selectedRows = [];
                    this.selectAll = false;
                    if (this.rows.length === 0) {
                        this.getData();
                    }
                }
            })
            .catch(err => console.error('Bulk action error:', err));
        },

        // --- Helpers ---
        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        getColumnValue(row, column) {
            return row[column.field] ?? '-';
        },

        getBadgeInfo(column, value) {
            if (column.mapping && column.mapping[value]) {
                return column.mapping[value];
            }
            return { label: value || '-', color: 'light' };
        },

        formatDate(value, column) {
            if (!value) return '-';
            try {
                const date = new Date(value);
                return date.toLocaleDateString(column.locale || 'id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                });
            } catch (e) {
                return value;
            }
        },
    }));
}
