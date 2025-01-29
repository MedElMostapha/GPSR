<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\On;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DataExport;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortField = 'id';
    public string $sortDirection = 'asc';
    public int $perPage = 10;
    public array $columns = [];
    public array $columnLabels = [];
    public array $booleanColumns = [];
    public Collection $data;
    public array $actions = [];
    public array $filterAttributes = [];
    public array $dateFilters = [];
    public array $enabledFilters = [];
    public array $selectFilters = [];
    public array $selectedYears = [];
    public bool $enableSearch = false;
    public bool $pdfEnabled = false;
    public bool $excelEnabled = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function mount(array $columns, Collection $data, array $actions = [], array $columnLabels = [], array $booleanColumns = [], array $enabledFilters = [], array $selectFilters = []): void
    {
        $this->columns = $columns;
        $this->data = $data;
        $this->actions = $actions;
        $this->columnLabels = $columnLabels;
        $this->booleanColumns = $booleanColumns;
        $this->enabledFilters = $enabledFilters;
        $this->selectFilters = $selectFilters;
    }

    #[On('reload')]
    public function reload(): void
    {
        $this->search = '';
        $this->filterAttributes = [];
        $this->dateFilters = [];
        $this->selectedYears = [];
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function rows(): LengthAwarePaginator
    {
        $filteredData = $this->data
            ->when($this->search, function ($collection) {
                return $collection->filter(function ($item) {
                    return collect($item)->search(function ($value, $key) {
                        if ($key === 'id') {
                            return false;
                        }

                        if (is_array($value) || is_object($value)) {
                            return false;
                        }

                        return stripos((string) $value, $this->search) !== false;
                    }) !== false;
                });
            })
            ->when($this->filterAttributes, function ($collection) {
                return $collection->filter(function ($item) {
                    foreach ($this->filterAttributes as $column => $value) {
                        if (!is_null($value)) {
                            $itemValue = $item->$column;

                            if (is_bool($value)) {
                                if ((bool) $itemValue !== $value) {
                                    return false;
                                }
                            } elseif (is_string($value)) {
                                if (!str_contains(strtolower((string) $itemValue), strtolower($value))) {
                                    return false;
                                }
                            } else {
                                if ($itemValue != $value) {
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                });
            })
            ->when($this->selectedYears, function ($collection) {
                return $collection->filter(function ($item) {
                    $selectedYears = (array) $this->selectedYears;
                    return in_array($item->publication_date, $selectedYears);
                });
            })
            ->map(function ($item) {
                if (isset($item['roles']) && (is_array($item['roles']) || is_object($item['roles']))) {
                    $item['roles'] = is_array($item['roles']) ? implode(', ', $item['roles']) : $item['roles']->pluck('name')->join(', ');
                }
                return $item;
            })
            ->sortBy($this->sortField, SORT_REGULAR, $this->sortDirection === 'desc');

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = $this->perPage;
        $results = $filteredData->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $filteredData->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    public function exportPdf()
    {
        $filteredData = $this->rows()->getCollection();

        $pdf = Pdf::loadView('pdf.export', [
            'data' => $filteredData,
            'columns' => $this->columns,
            'columnLabels' => $this->columnLabels,
            'booleanColumns' => $this->booleanColumns
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'export.pdf');
    }

    public function emitAction(string $action, $id): void
    {
        $this->dispatch($action, id: $id);
    }

    public function getColumnLabel(string $column): string
    {
        return $this->columnLabels[$column] ?? ucfirst($column);
    }

    public function getBooleanDisplayText(string $column, $value): array
    {
        if (isset($this->booleanColumns[$column])) {
            return $value ? $this->booleanColumns[$column]['true'] : $this->booleanColumns[$column]['false'];
        }
        return ['text' => $value, 'class' => ''];
    }

    public function clearFilters(): void
    {
        $this->filterAttributes = [];
        $this->dateFilters = [];
        $this->selectedYears = [];
    }

    public function getUniqueYears(string $column): array
    {
        return $this->data->pluck($column)->unique()->values()->toArray();
    }

    public function getSelectOptions(string $column): array
    {
        return $this->selectFilters[$column]['options'] ?? [];
    }

    public function toggleYear(string $column, string $year): void
    {
        if (!isset($this->selectedYears[$column])) {
            $this->selectedYears[$column] = [];
        }

        if (in_array($year, $this->selectedYears[$column])) {
            $this->selectedYears[$column] = array_diff($this->selectedYears[$column], [$year]);
        } else {
            $this->selectedYears[$column][] = $year;
        }
    }

    public function clearFilter($filter, $value = null): void
    {
        if ($filter === 'selectedYears' && $value !== null) {
            $this->selectedYears = array_diff($this->selectedYears, [$value]);
        } else {
            $this->$filter = ($filter === 'selectedYears') ? [] : '';
        }
    }

    public function exportExcel()
    {
        $filteredData = $this->rows()->getCollection();
        return Excel::download(new DataExport($filteredData, $this->columns, $this->columnLabels, $this->booleanColumns), 'export.xlsx');
    }
};
?>

<div class="p-6 bg-white rounded-lg shadow-md">
    <!-- Other UI elements -->



    <!-- Rest of your UI -->
    @if(!empty($selectedYears))
    @foreach ($selectedYears as $year)
    <div class="flex items-center max-w-fit mb-2 bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
        <span>Année : {{ $year }}</span>
        <button wire:click="clearFilter('selectedYears', '{{ $year }}')"
            class="ml-2 text-blue-600 hover:text-blue-800">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endforeach
    @endif

    @if ($this->enableSearch)

    <!-- Search Input -->
    <div class="mb-6 w-full relative ">
        <div class="flex justify-end">

            <input type="text"
                wire:model.change="search"
                placeholder="Search..."
                class="max-w-[40%] px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10" />

            <!-- Clear Button -->
            @if($search)
            <button wire:click="$set('search', '')"
                class="absolute inset-y-0  right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            @endif
        </div>
    </div>
    @endif

    <!-- Filter Inputs -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($columns as $column)
        @if(in_array($column, $enabledFilters))
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ $this->getColumnLabel($column) }}</label>
            @if(isset($this->booleanColumns[$column]))
            <select wire:model.change="filterAttributes.{{ $column }}"
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All</option>
                @foreach($this->booleanColumns[$column] as $key => $details)
                <option value="{{ $key == 'true' ? 1 : 0 }}">{{ $details['text'] }}</option>
                @endforeach
            </select>

            @elseif(isset($this->selectFilters[$column]))
            <select wire:model.change="filterAttributes.{{ $column }}"
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All</option>
                @foreach($this->getSelectOptions($column) as $option)
                <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
            @elseif(strpos($column, 'date') !== false)

            <div id="dropdown-{{ $column }}"
                x-data="{ open: false }"
                class="flex-1 sm:flex-none">
                <div class="relative mt-1">
                    <!-- Dropdown Toggle -->
                    <button @click="open = !open"
                        class="w-full flex justify-between items-center border border-gray-300 rounded-md shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span>
                            @if(!empty($selectedYears[$column] ?? []))
                            {{ count($selectedYears[$column]) }} année(s) sélectionnée(s)
                            @else
                            Sélectionner des années
                            @endif
                        </span>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                        @click.away="open = false"
                        class="absolute z-10 mt-2 w-full bg-white border border-gray-300 rounded-md shadow-lg">
                        <div class="max-h-60 overflow-y-auto py-1 text-sm text-gray-700">
                            @if(!empty($this->getUniqueYears($column)))
                            @foreach ($this->getUniqueYears($column) as $year)
                            @if (!empty($year))

                            <label class="flex items-center px-4 py-2 hover:bg-gray-100">
                                <input type="checkbox"
                                    wire:model.change="selectedYears"
                                    value="{{ $year }}"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2">{{ $year }}</span>
                            </label>
                            @endif
                            @endforeach
                            @else
                            <div class="px-4 py-2">Aucune année disponible</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @else
            <input type="text"
                wire:model.change="filterAttributes.{{ $column }}"
                placeholder="Filter by {{ $this->getColumnLabel($column) }}"
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            @endif
        </div>
        @endif
        @endforeach
    </div>

    <div class="flex items-center gap-4">

        <div>
            @if(!empty($enabledFilters))
            <!-- Clear Filters Button -->
            <div class="mb-6">
                <button wire:click="clearFilters"
                    class="btn-xs bg-gray-500 text-white rounded-md hover:bg-gray-600">
                    Clear Filters
                </button>
            </div>
            @endif
        </div>

        <div>

            @if ($pdfEnabled)

            <div class="mb-6 ">

                <!-- Add the PDF export button -->
                <button wire:click="exportPdf"
                    class="btn btn-xs bg-blue-500 text-white rounded-md border-none hover:bg-blue-600">
                    <i class="fas fa-file-pdf"></i> Export to PDF
                </button>
            </div>
            @endif

        </div>

        <div>

            @if ($excelEnabled)

            <!-- Excel Export Button -->
            <div class="mb-6">
                <button wire:click="exportExcel"
                    class="btn btn-xs bg-green-500 text-white rounded-md border-none hover:bg-green-600">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
            @endif
        </div>

    </div>




    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $column)
                    <th wire:click="sortBy('{{ $column }}')"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center">
                            {{ $this->getColumnLabel($column) }}
                            <!-- Use custom label -->
                            @if($sortField === $column)
                            @if($sortDirection === 'asc')
                            <svg class="w-4 h-4 ml-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 15l7-7 7 7" />
                            </svg>
                            @else
                            <svg class="w-4 h-4 ml-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                            @endif
                            @endif
                        </div>
                    </th>
                    @endforeach
                    <!-- Actions Column Header -->
                    @if(!empty($actions))
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($this->rows() as $row)
                <tr class="hover:bg-gray-50 transition-colors">
                    @foreach($columns as $column)
                    <td class="px-6 py-4 text-sm text-gray-700">
                        @if(isset($this->booleanColumns[$column]))
                        @php
                        $booleanDisplay = $this->getBooleanDisplayText($column, $row[$column]);
                        @endphp
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $booleanDisplay['class'] }}">
                            {{ $booleanDisplay['text'] }}
                        </span>
                        @else
                        {{ $row[$column] }}
                        @endif
                    </td>
                    @endforeach
                    <!-- Actions Column -->
                    @if(!empty($actions))
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <div class="flex space-x-2">
                            @if(in_array('edit', $actions))
                            <button wire:click="emitAction('edit', {{ $row['id'] }})"
                                class="btn-xs bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endif
                            @if(in_array('delete', $actions))
                            <button wire:click="emitAction('delete', {{ $row['id'] }})"
                                class="btn-xs bg-red-500 text-white rounded-lg hover:bg-red-600">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                            @if(in_array('view', $actions))
                            <button wire:click="emitAction('view', {{ $row['id'] }})"
                                class="btn-xs bg-blue-400 text-white rounded-lg hover:bg-blue-500">
                                <i class="fas fa-eye"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach

                @if(count($this->rows()) === 0)
                <tr>
                    <td colspan="{{ count($columns) + count($actions) }}"
                        class="px-6 py-4 text-sm text-gray-700 text-center">
                        Aucun donnée disponible
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $this->rows()->links() }}
    </div>
</div>