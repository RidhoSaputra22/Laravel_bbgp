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
])

@php
    $selectedValues = collect($selected)->map(fn($value) => (string) $value)->all();
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
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                function escapeHtml(value) {
                    const node = document.createElement('div');
                    node.textContent = value || '';

                    return node.innerHTML;
                }

                function initMultipleChoiceTable(element) {
                    if (!element || element.dataset.initialized === 'true') {
                        return;
                    }

                    element.dataset.initialized = 'true';

                    const tableId = element.dataset.tableId;
                    const inputName = element.dataset.inputName;
                    const searchInput = element.querySelector('[data-role="mct-search"]');
                    const selectAllButton = element.querySelector('[data-action="select-all"]');
                    const clearButton = element.querySelector('[data-action="clear-all"]');
                    const masterCheckbox = element.querySelector('[data-role="mct-master-checkbox"]');
                    const emptySearchState = element.querySelector('[data-role="mct-empty-search"]');
                    const selectedCountNode = element.querySelector('[data-role="mct-selected-count"]');
                    const selectedListNode = element.querySelector('[data-role="mct-selected-list"]');
                    const selectedEmptyNode = element.querySelector('[data-role="mct-selected-empty"]');
                    const rows = Array.from(element.querySelectorAll('[data-role="mct-row"]'));
                    const tableNode = element.querySelector('table');
                    let dataTable = null;

                    if (tableNode && rows.length > 0 && window.jQuery && typeof $.fn.DataTable === 'function') {
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
                            updateMasterCheckbox();
                        });
                    }

                    function getCheckbox(row) {
                        return row.querySelector('[data-role="mct-checkbox"]');
                    }

                    function getVisibleRows() {
                        if (dataTable) {
                            return dataTable.rows({
                                search: 'applied'
                            }).nodes().toArray();
                        }

                        return rows.filter((row) => !row.classList.contains('d-none'));
                    }

                    function getSelectedRows() {
                        return rows.filter((row) => {
                            const checkbox = getCheckbox(row);

                            return checkbox ? checkbox.checked : false;
                        });
                    }

                    function updateMasterCheckbox() {
                        if (!masterCheckbox) {
                            return;
                        }

                        const visibleRows = getVisibleRows();
                        const visibleSelectedCount = visibleRows.filter((row) => {
                            const checkbox = getCheckbox(row);

                            return checkbox ? checkbox.checked : false;
                        }).length;

                        masterCheckbox.checked = visibleRows.length > 0 && visibleSelectedCount === visibleRows.length;
                        masterCheckbox.indeterminate = visibleSelectedCount > 0 && visibleSelectedCount < visibleRows
                            .length;
                    }



                    function emitChange() {
                        const selectedItems = getSelectedRows().map((row) => {
                            let payload = {};

                            try {
                                payload = JSON.parse(row.dataset.itemPayload || '{}');
                            } catch (error) {
                                payload = {};
                            }

                            return {
                                id: row.dataset.itemId,
                                label: row.dataset.itemLabel || '',
                                description: row.dataset.itemDescription || '',
                                payload: payload,
                            };
                        });

                        element.dispatchEvent(new CustomEvent('multiple-choice-table:change', {
                            bubbles: true,
                            detail: {
                                tableId: tableId,
                                inputName: inputName,
                                selectedIds: selectedItems.map((item) => item.id),
                                selectedItems: selectedItems,
                            },
                        }));
                    }

                    function syncState() {
                        rows.forEach((row) => {
                            const checkbox = getCheckbox(row);

                            row.classList.toggle('is-selected', checkbox ? checkbox.checked : false);
                        });

                        updateMasterCheckbox();

                        emitChange();
                    }

                    function applySearch() {
                        const keyword = ((searchInput ? searchInput.value : '') || '').trim().toLowerCase();

                        if (dataTable) {
                            dataTable.search(keyword).draw();
                            return;
                        }

                        let visibleCount = 0;

                        rows.forEach((row) => {
                            const haystack = (row.dataset.search || '').toLowerCase();
                            const isVisible = keyword === '' || haystack.includes(keyword);

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

                    rows.forEach((row) => {
                        const checkbox = getCheckbox(row);

                        if (!checkbox) {
                            return;
                        }

                        checkbox.addEventListener('change', function() {
                            syncState();
                        });

                        row.addEventListener('click', function(event) {
                            if (event.target.closest('input, label, button, a')) {
                                return;
                            }

                            checkbox.checked = !checkbox.checked;
                            syncState();
                        });
                    });

                    if (searchInput) {
                        searchInput.addEventListener('input', function() {
                            applySearch();
                        });
                    }

                    if (selectAllButton) {
                        selectAllButton.addEventListener('click', function() {
                            getVisibleRows().forEach((row) => {
                                const checkbox = getCheckbox(row);

                                if (checkbox) {
                                    checkbox.checked = true;
                                }
                            });

                            syncState();
                        });
                    }

                    if (clearButton) {
                        clearButton.addEventListener('click', function() {
                            rows.forEach((row) => {
                                const checkbox = getCheckbox(row);

                                if (checkbox) {
                                    checkbox.checked = false;
                                }
                            });

                            syncState();
                        });
                    }

                    if (masterCheckbox) {
                        masterCheckbox.addEventListener('change', function() {
                            getVisibleRows().forEach((row) => {
                                const checkbox = getCheckbox(row);

                                if (checkbox) {
                                    checkbox.checked = masterCheckbox.checked;
                                }
                            });

                            syncState();
                        });
                    }

                    applySearch();
                    syncState();
                }

                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('[data-multiple-choice-table]').forEach((element) => {
                        initMultipleChoiceTable(element);
                    });
                });
            })
            ();
        </script>
    @endpush
@endonce

<div class="multiple-choice-table" data-multiple-choice-table data-table-id="{{ $id }}"
    data-input-name="{{ $name }}">
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

    <div class="multiple-choice-table__table-wrapper table-responsive">
        <table class="table table-striped table-hover multiple-choice-table__table" id="{{ $id }}-table">
            <thead class="">
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
            <tbody>
                @forelse ($items as $item)
                    @php
                        $itemId = (string) data_get($item, 'id', '');
                        $itemLabel = (string) data_get($item, 'label', $itemId);
                        $itemDescription = (string) data_get($item, 'description', '');
                        $itemCells = collect(data_get($item, 'cells', []))->values()->all();
                        $itemPayload = data_get($item, 'payload', []);
                        $searchValue = mb_strtolower(
                            trim(
                                collect([$itemLabel, $itemDescription])
                                    ->merge($itemCells)
                                    ->filter(fn($value) => filled($value))
                                    ->implode(' '),
                            ),
                        );
                        $isSelected = in_array($itemId, $selectedValues, true);
                    @endphp
                    <tr data-role="mct-row" data-item-id="{{ $itemId }}" data-item-label="{{ $itemLabel }}"
                        data-item-description="{{ $itemDescription }}" data-search="{{ $searchValue }}"
                        data-item-payload='@json($itemPayload)'
                        class="{{ $isSelected ? 'is-selected' : '' }}">
                        <td class="text-center align-middle">
                            <div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input"
                                    id="{{ $id }}-item-{{ $loop->iteration }}" data-role="mct-checkbox"
                                    name="{{ $name }}[]" value="{{ $itemId }}"
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
                    <tr>
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


</div>
