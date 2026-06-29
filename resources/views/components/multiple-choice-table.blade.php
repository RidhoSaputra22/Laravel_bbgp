@props([
    'id',
    'name',
    'headers' => [],
    'items' => [],
    'selected' => [],
    'description' => null,
    'searchPlaceholder' => 'Cari data...',
    'emptyMessage' => 'Data tidak tersedia.',
    'selectedTitle' => 'Data Terpilih',
    'ajaxUrl' => null,
    'pageSize' => 10,
    'initialSelectedItems' => [],
    'selectionName' => null,
    'remoteBulkSelect' => false,
    'initialSelectionState' => [],
    'initialSearchValue' => '',
])

@php
    $selectedValues = collect($selected)->map(fn ($value) => (string) $value)->all();
    $normalizedInitialSelectedItems = collect($initialSelectedItems)
        ->map(function ($item) {
            return [
                'id' => (string) data_get($item, 'id', ''),
                'label' => (string) data_get($item, 'label', data_get($item, 'text', '')),
                'description' => (string) data_get($item, 'description', ''),
                'cells' => collect(data_get($item, 'cells', []))->values()->all(),
                'payload' => data_get($item, 'payload', []),
            ];
        })
        ->filter(fn ($item) => filled($item['id']))
        ->values()
        ->all();
    $normalizedInitialSelectionState = [
        'mode' => data_get($initialSelectionState, 'mode', 'manual'),
        'scope' => [
            'q' => (string) data_get($initialSelectionState, 'scope.q', ''),
            'filters' => collect(data_get($initialSelectionState, 'scope.filters', []))
                ->mapWithKeys(function ($value, $key) {
                    $normalizedValue = trim((string) $value);

                    return $normalizedValue === '' ? [] : [(string) $key => $normalizedValue];
                })
                ->all(),
        ],
        'excludedIds' => collect(data_get($initialSelectionState, 'excludedIds', []))
            ->map(fn ($value) => (string) $value)
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all(),
        'totalMatched' => max((int) data_get($initialSelectionState, 'totalMatched', 0), 0),
    ];
    $normalizedInitialSearchValue = (string) ($initialSearchValue ?: data_get($normalizedInitialSelectionState, 'scope.q', ''));
@endphp

@once
    @push('styles')
        <style>
            .multiple-choice-table {
                border: 1px solid #e4e6fc;
                border-radius: .2rem;
                padding: 1rem;
                background: #fff;
            }

            .multiple-choice-table__toolbar {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 1rem;
            }

            .multiple-choice-table__search {
                min-width: 260px;
                flex: 1 1 320px;
            }

            .multiple-choice-table__table-wrapper {
                border: 1px solid #e4e6fc;
                border-radius: .2rem;
                overflow-x: auto;
                overflow-y: visible;
                position: relative;
            }

            .multiple-choice-table__toolbar-filters {
                margin-bottom: 1rem;
            }

            .multiple-choice-table__table {
                margin-bottom: 0;
                border-collapse: separate;
                border-spacing: 0;
            }

            .multiple-choice-table .dataTables_wrapper .dataTables_length,
            .multiple-choice-table .dataTables_wrapper .dataTables_info,
            .multiple-choice-table .dataTables_wrapper .dataTables_paginate {
                padding-top: 0.85rem;
            }

            .multiple-choice-table__table thead th {
                position: sticky;
                top: 0;
                z-index: 99;
                background-color: #f8f9fa !important;
                background-clip: padding-box;
                opacity: 1;
                box-shadow: 0 1px 0 #e4e6fc;
            }

            .multiple-choice-table__table tbody tr {
                cursor: pointer;
                transition: background-color 0.2s ease;
            }

            .multiple-choice-table__table tbody tr:hover {
                background: #f8f9ff;
            }

            .multiple-choice-table__table tbody tr.is-selected {
                background: rgba(103, 119, 239, 0.08);
            }

            .multiple-choice-table__empty-search {
                border-top: 1px solid #e4e6fc;
                padding: 1rem;
                text-align: center;
                color: #6c757d;
                background: #fcfcff;
            }

            .multiple-choice-table__selected-summary {
                display: none;
                margin-top: 0.75rem;
                color: #6c757d;
                font-size: 0.9rem;
            }

            .multiple-choice-table__selected-summary.is-visible {
                display: block;
            }

            .multiple-choice-table__footer {
                display: flex;
                gap: 0.75rem;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                margin-top: 1rem;
            }

            .multiple-choice-table__pagination {
                display: flex;
                gap: 0.5rem;
                align-items: center;
                flex-wrap: wrap;
            }

            .multiple-choice-table__page-label {
                min-width: 108px;
                text-align: center;
                color: #6c757d;
                font-size: 0.9rem;
            }

            @media (max-width: 767.98px) {
                .multiple-choice-table {
                    padding: 0.875rem;
                }

                .multiple-choice-table__toolbar {
                    align-items: stretch;
                }

                .multiple-choice-table__toolbar-actions {
                    width: 100%;
                    display: flex;
                    gap: 0.5rem;
                }

                .multiple-choice-table__toolbar-actions .btn {
                    flex: 1 1 auto;
                }

                .multiple-choice-table__footer {
                    align-items: stretch;
                }

                .multiple-choice-table__pagination {
                    width: 100%;
                    justify-content: space-between;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                function escapeHtml(value) {
                    const node = document.createElement('div');
                    node.textContent = value == null ? '' : String(value);

                    return node.innerHTML;
                }

                function parseJson(value, fallback) {
                    try {
                        const parsed = JSON.parse(value || 'null');

                        return parsed ?? fallback;
                    } catch (error) {
                        return fallback;
                    }
                }

                function normalizeItem(rawItem) {
                    const payload = rawItem && typeof rawItem.payload === 'object' && rawItem.payload !== null ? rawItem
                        .payload : {};
                    const cells = Array.isArray(rawItem && rawItem.cells) ? rawItem.cells : [];
                    const id = String(rawItem && rawItem.id != null ? rawItem.id : '');
                    const label = String(rawItem && (rawItem.label ?? rawItem.text ?? id));
                    const description = String(rawItem && rawItem.description != null ? rawItem.description : '');
                    const search = [label, description]
                        .concat(cells)
                        .filter((value) => value != null && String(value).trim() !== '')
                        .join(' ')
                        .toLowerCase();

                    return {
                        id: id,
                        label: label,
                        description: description,
                        cells: cells,
                        payload: payload,
                        search: search,
                    };
                }

                function buildRowMarkup(item, tableId, isSelected, rowIndex, inputName) {
                    const cellsHtml = item.cells.map((cell) => {
                        return '<td class="align-middle">' + escapeHtml(cell == null ? '-' : String(cell)) + '</td>';
                    }).join('');

                    return `
                        <tr
                            data-role="mct-row"
                            data-item-id="${escapeHtml(item.id)}"
                            data-item-label="${escapeHtml(item.label)}"
                            data-item-description="${escapeHtml(item.description)}"
                            data-search="${escapeHtml(item.search)}"
                            data-item-payload='${escapeHtml(JSON.stringify(item.payload || {}))}'
                            class="${isSelected ? 'is-selected' : ''}"
                        >
                            <td class="text-center align-middle">
                                <div class="custom-control custom-checkbox d-inline-block">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="${escapeHtml(tableId)}-item-${rowIndex}"
                                        data-role="mct-checkbox"
                                        data-input-name="${escapeHtml(inputName)}"
                                        ${isSelected ? 'checked' : ''}
                                    >
                                    <label class="custom-control-label" for="${escapeHtml(tableId)}-item-${rowIndex}"></label>
                                </div>
                            </td>
                            ${cellsHtml}
                        </tr>
                    `;
                }

                function buildEmptyRowMarkup(colspan, message) {
                    return `
                        <tr data-role="mct-empty-row">
                            <td colspan="${colspan}" class="text-center text-muted py-4">
                                ${escapeHtml(message)}
                            </td>
                        </tr>
                    `;
                }

                function initMultipleChoiceTable(element) {
                    if (!element || element.dataset.initialized === 'true') {
                        return;
                    }

                    element.dataset.initialized = 'true';

                    const tableId = element.dataset.tableId;
                    const inputName = element.dataset.inputName;
                    const ajaxUrl = element.dataset.ajaxUrl || '';
                    const isRemote = ajaxUrl !== '';
                    const pageSize = Math.max(Number(element.dataset.pageSize || 10), 1);
                    const selectedTitle = element.dataset.selectedTitle || 'Data Terpilih';
                    const emptyMessage = element.dataset.emptyMessage || 'Data tidak tersedia.';
                    const selectionName = String(element.dataset.selectionName || '').trim();
                    const remoteBulkSelectEnabled = isRemote &&
                        ['1', 'true'].includes(String(element.dataset.remoteBulkSelect || '').toLowerCase()) &&
                        selectionName !== '';

                    const searchInput = element.querySelector('[data-role="mct-search"]');
                    const selectAllButton = element.querySelector('[data-action="select-all"]');
                    const clearButton = element.querySelector('[data-action="clear-all"]');
                    const masterCheckbox = element.querySelector('[data-role="mct-master-checkbox"]');
                    const emptySearchState = element.querySelector('[data-role="mct-empty-search"]');
                    const selectedSummaryNode = element.querySelector('[data-role="mct-selected-summary"]');
                    const hiddenInputsNode = element.querySelector('[data-role="mct-hidden-inputs"]');
                    const tbodyNode = element.querySelector('[data-role="mct-body"]');
                    const tableNode = element.querySelector('table');
                    const paginationInfoNode = element.querySelector('[data-role="mct-pagination-info"]');
                    const paginationLabelNode = element.querySelector('[data-role="mct-pagination-label"]');
                    const previousPageButton = element.querySelector('[data-action="prev-page"]');
                    const nextPageButton = element.querySelector('[data-action="next-page"]');
                    const footerNode = element.querySelector('[data-role="mct-footer"]');
                    const columnCount = Number(element.dataset.columnCount || 1);
                    const filterInputs = Array.from(element.querySelectorAll('[data-role="mct-filter"][data-filter-param]'));

                    const selectedMap = new Map();
                    const initialSelectedItems = parseJson(element.dataset.initialSelectedItems, []);
                    const initialSelectionState = parseJson(element.dataset.initialSelectionState, {});
                    const initialSearchValue = String(element.dataset.initialSearchValue || '').trim();
                    let currentPage = 1;
                    let lastPage = 1;
                    let totalItems = 0;
                    let rangeFrom = 0;
                    let rangeTo = 0;
                    let keyword = '';
                    let searchTimer = null;
                    let dataTable = null;
                    let localRows = [];
                    let bulkSelection = {
                        active: false,
                        scope: {
                            q: '',
                            filters: {},
                        },
                        excludedIds: new Set(),
                        totalMatched: 0,
                    };

                    function normalizeScope(rawScope) {
                        const scope = rawScope && typeof rawScope === 'object' ? rawScope : {};
                        const filters = scope.filters && typeof scope.filters === 'object' ? scope.filters : {};
                        const normalizedFilters = {};

                        Object.keys(filters).forEach((filterKey) => {
                            const filterValue = String(filters[filterKey] ?? '').trim();

                            if (filterValue !== '') {
                                normalizedFilters[filterKey] = filterValue;
                            }
                        });

                        return {
                            q: String(scope.q ?? '').trim(),
                            filters: normalizedFilters,
                        };
                    }

                    function getCurrentFilterState() {
                        const filters = {};

                        filterInputs.forEach((input) => {
                            const filterParam = String(input.dataset.filterParam || '').trim();
                            const filterValue = String(input.value || '').trim();

                            if (filterParam !== '' && filterValue !== '') {
                                filters[filterParam] = filterValue;
                            }
                        });

                        return filters;
                    }

                    function getCurrentScope() {
                        return normalizeScope({
                            q: String(searchInput ? searchInput.value : '').trim(),
                            filters: getCurrentFilterState(),
                        });
                    }

                    function getActiveRemoteScope() {
                        if (remoteBulkSelectEnabled && bulkSelection.active) {
                            return bulkSelection.scope;
                        }

                        return getCurrentScope();
                    }

                    function itemMatchesScope(item, scope) {
                        const normalizedScope = normalizeScope(scope);

                        if (normalizedScope.q !== '' && !String(item.search || '').includes(normalizedScope.q.toLowerCase())) {
                            return false;
                        }

                        return Object.entries(normalizedScope.filters).every(([filterKey, filterValue]) => {
                            return String(item.payload?.[filterKey] ?? '').trim() === filterValue;
                        });
                    }

                    function isItemSelected(item) {
                        if (remoteBulkSelectEnabled && bulkSelection.active && itemMatchesScope(item, bulkSelection.scope)) {
                            return !bulkSelection.excludedIds.has(item.id);
                        }

                        return selectedMap.has(item.id);
                    }

                    function getExcludedCount() {
                        return remoteBulkSelectEnabled && bulkSelection.active ? bulkSelection.excludedIds.size : 0;
                    }

                    function getSelectionCount() {
                        if (remoteBulkSelectEnabled && bulkSelection.active) {
                            return Math.max(bulkSelection.totalMatched - getExcludedCount(), 0);
                        }

                        return selectedMap.size;
                    }

                    function setScopeLockState(locked) {
                        if (!remoteBulkSelectEnabled) {
                            return;
                        }

                        if (searchInput) {
                            searchInput.readOnly = locked;
                            searchInput.classList.toggle('bg-light', locked);
                        }

                        filterInputs.forEach((input) => {
                            input.disabled = locked;
                        });

                        if (selectAllButton) {
                            selectAllButton.textContent = locked ? 'Semua Terpilih' : 'Pilih Semua';
                        }
                    }

                    function clearBulkSelection() {
                        bulkSelection = {
                            active: false,
                            scope: getCurrentScope(),
                            excludedIds: new Set(),
                            totalMatched: 0,
                        };

                        setScopeLockState(false);
                    }

                    initialSelectedItems
                        .map(normalizeItem)
                        .filter((item) => item.id !== '')
                        .forEach((item) => {
                            selectedMap.set(item.id, item);
                        });

                    if (remoteBulkSelectEnabled && initialSelectionState.mode === 'select_all') {
                        bulkSelection = {
                            active: true,
                            scope: normalizeScope(initialSelectionState.scope),
                            excludedIds: new Set(
                                Array.isArray(initialSelectionState.excludedIds) ?
                                    initialSelectionState.excludedIds
                                        .map((value) => String(value))
                                        .filter((value) => value !== '') :
                                    []
                            ),
                            totalMatched: Math.max(Number(initialSelectionState.totalMatched || 0), 0),
                        };

                        if (searchInput) {
                            searchInput.value = bulkSelection.scope.q;
                        }

                        keyword = bulkSelection.scope.q;
                        setScopeLockState(true);
                    } else if (searchInput && initialSearchValue !== '') {
                        searchInput.value = initialSearchValue;
                        keyword = initialSearchValue;
                    }

                    function getCheckbox(row) {
                        return row.querySelector('[data-role="mct-checkbox"]');
                    }

                    function hydrateItemFromRow(row) {
                        return normalizeItem({
                            id: row.dataset.itemId || '',
                            label: row.dataset.itemLabel || '',
                            description: row.dataset.itemDescription || '',
                            payload: parseJson(row.dataset.itemPayload, {}),
                            cells: Array.from(row.querySelectorAll('td'))
                                .slice(1)
                                .map((cell) => cell.textContent.trim()),
                        });
                    }

                    function getSelectedItems() {
                        if (remoteBulkSelectEnabled && bulkSelection.active) {
                            return [];
                        }

                        return Array.from(selectedMap.values()).map((item) => ({
                            id: item.id,
                            label: item.label,
                            description: item.description,
                            payload: item.payload,
                        }));
                    }

                    function appendHiddenInput(name, value) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = value;
                        hiddenInputsNode.appendChild(input);
                    }

                    function emitChange() {
                        const selectedItems = getSelectedItems();

                        element.dispatchEvent(new CustomEvent('multiple-choice-table:change', {
                            bubbles: true,
                            detail: {
                                tableId: tableId,
                                inputName: inputName,
                                selectedIds: remoteBulkSelectEnabled && bulkSelection.active ?
                                    [] :
                                    selectedItems.map((item) => item.id),
                                selectedItems: selectedItems,
                                selectedCount: getSelectionCount(),
                                excludedCount: getExcludedCount(),
                                selectionMode: remoteBulkSelectEnabled && bulkSelection.active ? 'select_all' : 'manual',
                                selectAllScope: remoteBulkSelectEnabled && bulkSelection.active ? {
                                    q: bulkSelection.scope.q,
                                    filters: bulkSelection.scope.filters,
                                    excludedIds: Array.from(bulkSelection.excludedIds),
                                    totalMatched: bulkSelection.totalMatched,
                                } : null,
                            },
                        }));
                    }

                    function updateHiddenInputs() {
                        if (!hiddenInputsNode) {
                            return;
                        }

                        hiddenInputsNode.innerHTML = '';

                        if (remoteBulkSelectEnabled) {
                            appendHiddenInput(selectionName + '_selection_mode', bulkSelection.active ? 'select_all' : 'manual');
                        }

                        if (remoteBulkSelectEnabled && bulkSelection.active) {
                            appendHiddenInput(selectionName + '_select_all', '1');

                            if (bulkSelection.scope.q !== '') {
                                appendHiddenInput(selectionName + '_selection_scope[q]', bulkSelection.scope.q);
                            }

                            Object.entries(bulkSelection.scope.filters).forEach(([filterKey, filterValue]) => {
                                appendHiddenInput(
                                    selectionName + '_selection_scope[' + filterKey + ']',
                                    filterValue
                                );
                            });

                            Array.from(bulkSelection.excludedIds).forEach((guruId) => {
                                appendHiddenInput(selectionName + '_excluded_ids[]', guruId);
                            });

                            return;
                        }

                        Array.from(selectedMap.values()).forEach((item) => {
                            appendHiddenInput(inputName + '[]', item.id);
                        });
                    }

                    function updateSelectedSummary() {
                        if (!selectedSummaryNode) {
                            return;
                        }

                        const selectedCount = getSelectionCount();

                        if (selectedCount === 0) {
                            selectedSummaryNode.textContent = '';
                            selectedSummaryNode.classList.remove('is-visible');

                            return;
                        }

                        let summaryText = selectedTitle + ': ';

                        if (remoteBulkSelectEnabled && bulkSelection.active) {
                            summaryText += 'semua ' + selectedCount + ' dipilih';

                            if (bulkSelection.scope.q !== '' || Object.keys(bulkSelection.scope.filters).length > 0) {
                                summaryText += ' sesuai filter aktif';
                            }

                            if (getExcludedCount() > 0) {
                                summaryText += ' (' + getExcludedCount() + ' dikecualikan)';
                            }
                        } else {
                            const selectedItems = Array.from(selectedMap.values());
                            const previewLabels = selectedItems.slice(0, 3).map((item) => item.label);

                            summaryText += selectedItems.length + ' dipilih';

                            if (previewLabels.length > 0) {
                                summaryText += ' (' + previewLabels.join(', ');

                                if (selectedItems.length > previewLabels.length) {
                                    summaryText += ' dan ' + (selectedItems.length - previewLabels.length) + ' lainnya';
                                }

                                summaryText += ')';
                            }
                        }

                        selectedSummaryNode.textContent = summaryText;
                        selectedSummaryNode.classList.add('is-visible');
                    }

                    function getRenderedRows() {
                        return Array.from(element.querySelectorAll('[data-role="mct-row"]'));
                    }

                    function getVisibleRows() {
                        if (isRemote) {
                            return getRenderedRows();
                        }

                        if (dataTable) {
                            return dataTable.rows({
                                search: 'applied',
                                page: 'current',
                            }).nodes().toArray();
                        }

                        return localRows.filter((row) => !row.classList.contains('d-none'));
                    }

                    function updateMasterCheckbox() {
                        if (!masterCheckbox) {
                            return;
                        }

                        const visibleRows = getVisibleRows();
                        const visibleSelectedCount = visibleRows.filter((row) => {
                            const item = hydrateItemFromRow(row);

                            return item.id !== '' && isItemSelected(item);
                        }).length;

                        masterCheckbox.checked = visibleRows.length > 0 && visibleSelectedCount === visibleRows.length;
                        masterCheckbox.indeterminate = visibleSelectedCount > 0 && visibleSelectedCount < visibleRows.length;
                    }

                    function syncRenderedRows() {
                        getRenderedRows().forEach((row) => {
                            const checkbox = getCheckbox(row);
                            const item = hydrateItemFromRow(row);
                            const isSelected = item.id !== '' && isItemSelected(item);

                            if (checkbox) {
                                checkbox.checked = isSelected;
                            }

                            row.classList.toggle('is-selected', isSelected);
                        });

                        updateMasterCheckbox();
                    }

                    function syncState() {
                        updateHiddenInputs();
                        updateSelectedSummary();
                        syncRenderedRows();
                        emitChange();
                    }

                    function setRowsSelection(rows, checked) {
                        rows.forEach((row) => {
                            const item = hydrateItemFromRow(row);

                            if (!item.id) {
                                return;
                            }

                            if (remoteBulkSelectEnabled && bulkSelection.active) {
                                if (checked) {
                                    bulkSelection.excludedIds.delete(item.id);
                                } else {
                                    bulkSelection.excludedIds.add(item.id);
                                }

                                return;
                            }

                            if (checked) {
                                selectedMap.set(item.id, item);
                            } else {
                                selectedMap.delete(item.id);
                            }
                        });

                        syncState();
                    }

                    function bindRowEvents(rows) {
                        rows.forEach((row) => {
                            const checkbox = getCheckbox(row);

                            if (!checkbox || row.dataset.bound === 'true') {
                                return;
                            }

                            row.dataset.bound = 'true';

                            checkbox.addEventListener('change', function() {
                                setRowsSelection([row], checkbox.checked);
                            });

                            row.addEventListener('click', function(event) {
                                if (event.target.closest('input, label, button, a')) {
                                    return;
                                }

                                checkbox.checked = !checkbox.checked;
                                setRowsSelection([row], checkbox.checked);
                            });
                        });
                    }

                    function updatePaginationFooter() {
                        if (!isRemote || !footerNode) {
                            return;
                        }

                        if (paginationInfoNode) {
                            paginationInfoNode.textContent = totalItems === 0 ?
                                'Tidak ada data guru.' :
                                'Menampilkan ' + rangeFrom + ' sampai ' + rangeTo + ' dari ' + totalItems + ' entri';
                        }

                        if (paginationLabelNode) {
                            paginationLabelNode.textContent = 'Halaman ' + currentPage + ' / ' + lastPage;
                        }

                        if (previousPageButton) {
                            previousPageButton.disabled = currentPage <= 1;
                        }

                        if (nextPageButton) {
                            nextPageButton.disabled = currentPage >= lastPage;
                        }
                    }

                    function renderRemoteRows(items, message) {
                        if (!tbodyNode) {
                            return;
                        }

                        if (!Array.isArray(items) || items.length === 0) {
                            tbodyNode.innerHTML = buildEmptyRowMarkup(columnCount, message || 'Data tidak tersedia.');
                            updateMasterCheckbox();

                            return;
                        }

                        tbodyNode.innerHTML = items.map((item, index) => {
                            return buildRowMarkup(
                                item,
                                tableId,
                                isItemSelected(item),
                                ((currentPage - 1) * pageSize) + index + 1,
                                inputName
                            );
                        }).join('');

                        bindRowEvents(getRenderedRows());
                        syncRenderedRows();
                    }

                    function fetchRemoteRows(onComplete = null) {
                        if (!isRemote) {
                            return;
                        }

                        if (tbodyNode) {
                            tbodyNode.innerHTML = buildEmptyRowMarkup(columnCount, 'Memuat data guru...');
                        }

                        const scope = getActiveRemoteScope();
                        const params = new URLSearchParams();
                        params.set('page', String(currentPage));
                        params.set('per_page', String(pageSize));
                        keyword = scope.q;

                        if (scope.q !== '') {
                            params.set('q', scope.q);
                        }

                        Object.entries(scope.filters).forEach(([filterParam, filterValue]) => {
                            params.set(filterParam, filterValue);
                        });

                        $.getJSON(ajaxUrl + '?' + params.toString())
                            .done(function(response) {
                                const items = Array.isArray(response.items) ? response.items : [];
                                const pagination = response.pagination || {};

                                currentPage = Number(pagination.current_page || currentPage || 1);
                                lastPage = Math.max(Number(pagination.last_page || 1), 1);
                                totalItems = Number(pagination.total || items.length || 0);
                                rangeFrom = Number(pagination.from || 0);
                                rangeTo = Number(pagination.to || 0);

                                renderRemoteRows(items.map(normalizeItem), items.length === 0 ? (keyword === '' ?
                                    emptyMessage :
                                    'Tidak ada data yang cocok dengan pencarian.') : '');
                                updatePaginationFooter();

                                if (emptySearchState) {
                                    emptySearchState.classList.add('d-none');
                                }

                                if (typeof onComplete === 'function') {
                                    onComplete({
                                        scope: scope,
                                        totalItems: totalItems,
                                    });
                                }
                            })
                            .fail(function() {
                                totalItems = 0;
                                rangeFrom = 0;
                                rangeTo = 0;
                                lastPage = 1;
                                renderRemoteRows([], 'Gagal memuat data guru. Coba lagi.');
                                updatePaginationFooter();

                                if (typeof onComplete === 'function') {
                                    onComplete(null);
                                }
                            });
                    }

                    function activateRemoteBulkSelection() {
                        if (!remoteBulkSelectEnabled) {
                            return;
                        }

                        if (searchTimer) {
                            window.clearTimeout(searchTimer);
                            searchTimer = null;
                        }

                        currentPage = 1;

                        fetchRemoteRows(function(result) {
                            if (!result || result.totalItems <= 0) {
                                syncState();

                                return;
                            }

                            selectedMap.clear();
                            bulkSelection = {
                                active: true,
                                scope: normalizeScope(result.scope),
                                excludedIds: new Set(),
                                totalMatched: result.totalItems,
                            };

                            setScopeLockState(true);
                            syncState();
                        });
                    }

                    element.addEventListener('multiple-choice-table:refresh', function(event) {
                        if (event && event.detail && event.detail.resetPage !== false) {
                            currentPage = 1;
                        }

                        if (isRemote) {
                            fetchRemoteRows();

                            return;
                        }

                        applySearch();
                    });

                    function initLocalMode() {
                        localRows = Array.from(element.querySelectorAll('[data-role="mct-row"]'));

                        localRows.forEach((row) => {
                            const checkbox = getCheckbox(row);

                            if (checkbox && checkbox.checked) {
                                const item = hydrateItemFromRow(row);

                                if (item.id) {
                                    selectedMap.set(item.id, item);
                                }
                            }
                        });

                        bindRowEvents(localRows);

                        if (tableNode && localRows.length > 0 && window.jQuery && typeof $.fn.DataTable === 'function') {
                            dataTable = $(tableNode).DataTable({
                                order: [],
                                pageLength: 10,
                                autoWidth: false,
                                dom: 'lrtip',
                                columnDefs: [{
                                    targets: [0],
                                    orderable: false,
                                    searchable: false,
                                }],
                                language: {
                                    url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                                },
                            });

                            if (emptySearchState) {
                                emptySearchState.classList.add('d-none');
                            }

                            $(tableNode).on('draw.dt', function() {
                                syncRenderedRows();
                            });
                        }

                        syncState();
                    }

                    function applySearch() {
                        const rawKeyword = ((searchInput ? searchInput.value : '') || '').trim();

                        if (isRemote) {
                            if (remoteBulkSelectEnabled && bulkSelection.active) {
                                return;
                            }

                            keyword = rawKeyword;
                            currentPage = 1;

                            if (searchTimer) {
                                window.clearTimeout(searchTimer);
                            }

                            searchTimer = window.setTimeout(function() {
                                fetchRemoteRows();
                            }, 300);

                            return;
                        }

                        const nextKeyword = rawKeyword.toLowerCase();

                        if (dataTable) {
                            dataTable.search(nextKeyword).draw();
                            return;
                        }

                        let visibleCount = 0;

                        localRows.forEach((row) => {
                            const haystack = (row.dataset.search || '').toLowerCase();
                            const isVisible = nextKeyword === '' || haystack.includes(nextKeyword);

                            row.classList.toggle('d-none', !isVisible);

                            if (isVisible) {
                                visibleCount += 1;
                            }
                        });

                        if (emptySearchState) {
                            emptySearchState.classList.toggle('d-none', visibleCount !== 0);
                        }

                        updateMasterCheckbox();
                    }

                    if (searchInput) {
                        searchInput.addEventListener('input', function() {
                            applySearch();
                        });
                    }

                    if (selectAllButton) {
                        selectAllButton.addEventListener('click', function() {
                            if (remoteBulkSelectEnabled) {
                                activateRemoteBulkSelection();

                                return;
                            }

                            setRowsSelection(getVisibleRows(), true);
                        });
                    }

                    if (clearButton) {
                        clearButton.addEventListener('click', function() {
                            selectedMap.clear();
                            clearBulkSelection();
                            syncState();
                        });
                    }

                    if (masterCheckbox) {
                        masterCheckbox.addEventListener('change', function() {
                            setRowsSelection(getVisibleRows(), masterCheckbox.checked);
                        });
                    }

                    if (previousPageButton) {
                        previousPageButton.addEventListener('click', function() {
                            if (currentPage <= 1) {
                                return;
                            }

                            currentPage -= 1;
                            fetchRemoteRows();
                        });
                    }

                    if (nextPageButton) {
                        nextPageButton.addEventListener('click', function() {
                            if (currentPage >= lastPage) {
                                return;
                            }

                            currentPage += 1;
                            fetchRemoteRows();
                        });
                    }

                    if (isRemote) {
                        syncState();
                        fetchRemoteRows();
                    } else {
                        initLocalMode();
                        applySearch();
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('[data-multiple-choice-table]').forEach((element) => {
                        initMultipleChoiceTable(element);
                    });
                });
            })();
        </script>
    @endpush
@endonce

<div class="multiple-choice-table"
    data-multiple-choice-table
    data-table-id="{{ $id }}"
    data-input-name="{{ $name }}"
    data-selected-title="{{ $selectedTitle }}"
    data-selection-name="{{ $selectionName ?? '' }}"
    data-remote-bulk-select="{{ $remoteBulkSelect ? 'true' : 'false' }}"
    data-ajax-url="{{ $ajaxUrl ?? '' }}"
    data-page-size="{{ $pageSize }}"
    data-column-count="{{ count($headers) + 1 }}"
    data-empty-message="{{ $emptyMessage }}"
    data-initial-selected-items='@json($normalizedInitialSelectedItems)'
    data-initial-selection-state='@json($normalizedInitialSelectionState)'
    data-initial-search-value="{{ $normalizedInitialSearchValue }}">
    <div class="multiple-choice-table__toolbar">
        <div class="multiple-choice-table__search">
            <input type="text" class="form-control" data-role="mct-search" placeholder="{{ $searchPlaceholder }}">
            @if ($description)
                <small class="text-muted d-block mt-2">{{ $description }}</small>
            @endif
        </div>
        <div class="multiple-choice-table__toolbar-actions">
            <button type="button" class="btn btn-outline-primary btn-sm" data-action="select-all">
                Pilih Semua
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-action="clear-all">
                Kosongkan
            </button>
        </div>
    </div>

    @if (isset($toolbarFilters) && $toolbarFilters->isNotEmpty())
        <div class="multiple-choice-table__toolbar-filters">
            {{ $toolbarFilters }}
        </div>
    @endif

    <div data-role="mct-hidden-inputs"></div>

    <div class="multiple-choice-table__table-wrapper table-responsive ">
        <table class="table table-striped table-hover multiple-choice-table__table" id="{{ $id }}-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 56px;">
                        <div class="custom-control custom-checkbox d-inline-block">
                            <input type="checkbox" class="custom-control-input" id="{{ $id }}-master-checkbox"
                                data-role="mct-master-checkbox">
                            <label class="custom-control-label" for="{{ $id }}-master-checkbox"></label>
                        </div>
                    </th>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody data-role="mct-body">
                @forelse ($items as $item)
                    @php
                        $itemId = (string) data_get($item, 'id', '');
                        $itemLabel = (string) data_get($item, 'label', data_get($item, 'text', $itemId));
                        $itemDescription = (string) data_get($item, 'description', '');
                        $itemCells = collect(data_get($item, 'cells', []))->values()->all();
                        $itemPayload = data_get($item, 'payload', []);
                        $searchValue = mb_strtolower(
                            trim(
                                collect([$itemLabel, $itemDescription])
                                    ->merge($itemCells)
                                    ->filter(fn ($value) => filled($value))
                                    ->implode(' '),
                            ),
                        );
                        $isSelected = in_array($itemId, $selectedValues, true);
                    @endphp
                    <tr data-role="mct-row" data-item-id="{{ $itemId }}" data-item-label="{{ $itemLabel }}"
                        data-item-description="{{ $itemDescription }}" data-search="{{ $searchValue }}"
                        data-item-payload='@json($itemPayload)' class="{{ $isSelected ? 'is-selected' : '' }}">
                        <td class="text-center align-middle">
                            <div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input"
                                    id="{{ $id }}-item-{{ $loop->iteration }}" data-role="mct-checkbox"
                                    @checked($isSelected)>
                                <label class="custom-control-label"
                                    for="{{ $id }}-item-{{ $loop->iteration }}"></label>
                            </div>
                        </td>
                        @foreach ($headers as $columnIndex => $header)
                            <td class="align-middle">
                                {{ $itemCells[$columnIndex] ?? '-' }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr data-role="mct-empty-row">
                        <td colspan="{{ count($headers) + 1 }}" class="text-center text-muted py-4">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="multiple-choice-table__empty-search d-none" data-role="mct-empty-search">
            Tidak ada data yang cocok dengan pencarian.
        </div>
    </div>

    <div class="multiple-choice-table__selected-summary" data-role="mct-selected-summary"></div>

    <div class="multiple-choice-table__footer {{ $ajaxUrl ? '' : 'd-none' }}" data-role="mct-footer">
        <small class="text-muted" data-role="mct-pagination-info"></small>
        <div class="multiple-choice-table__pagination">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-action="prev-page">
                Sebelumnya
            </button>
            <span class="multiple-choice-table__page-label" data-role="mct-pagination-label">Halaman 1 / 1</span>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-action="next-page">
                Berikutnya
            </button>
        </div>
    </div>
</div>
