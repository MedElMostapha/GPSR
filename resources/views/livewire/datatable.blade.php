<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortField = 'id';
    public string $sortDirection = 'asc';
    public int $perPage = 10;
    public array $columns = [];
    public array $columnLabels = []; // Custom labels for columns
    public array $booleanColumns = []; // Define boolean columns and their display text
    public Collection $data;
    public array $actions = []; // Define actions (e.g., 'edit', 'delete')

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function mount(array $columns, Collection $data, array $actions = [], array $columnLabels = [], array $booleanColumns = []): void
    {
        $this->columns = $columns;
        $this->data = $data;
        $this->actions = $actions; // Assign actions
        $this->columnLabels = $columnLabels; // Assign custom column labels
        $this->booleanColumns = $booleanColumns; // Assign boolean columns
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
                        // Skip the 'id' field from the search
                        if ($key === 'id') {
                            return false; // Skip the 'id' field
                        }

                        // Handle non-string values
                        if (is_array($value) || is_object($value)) {
                            return false; // Skip arrays and objects
                        }

                        // Convert value to string for comparison
                        return stripos((string) $value, $this->search) !== false;
                    }) !== false;
                });
            })
            ->map(function ($item) {
                // Ensure roles are displayed as a string
                if (isset($item['roles']) && (is_array($item['roles']) || is_object($item['roles']))) {
                    $item['roles'] = is_array($item['roles']) ? implode(', ', $item['roles']) : $item['roles']->pluck('name')->join(', ');
                }
                return $item;
            })
            ->sortBy($this->sortField, SORT_REGULAR, $this->sortDirection === 'desc');

        // Manually paginate the collection
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

    // Emit events for actions
    public function emitAction(string $action, $id): void
    {
        $this->dispatch($action, id: $id); // Emit the action event
    }

    // Get the display label for a column
    public function getColumnLabel(string $column): string
    {
        return $this->columnLabels[$column] ?? ucfirst($column); // Fallback to column name
    }

    // Get the display text and class for a boolean value
    public function getBooleanDisplayText(string $column, $value): array
    {
        if (isset($this->booleanColumns[$column])) {
            return $value ? $this->booleanColumns[$column]['true'] : $this->booleanColumns[$column]['false'];
        }
        return ['text' => $value, 'class' => '']; // Fallback to raw value with no class
    }
};
?>

<div class="p-6 bg-white rounded-lg shadow-md">
    <!-- Search Input -->
    <div class="mb-6 relative">
        <input type="text"
            wire:model.change="search"
            placeholder="Search..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10" />

        <!-- Clear Button -->
        @if($search)
        <button wire:click="$set('search', '')"
            class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
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
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $this->rows()->links() }}
    </div>
</div>