<?php

use Livewire\Volt\Component;
use Carbon\Carbon;
use App\Models\Publication;

new class extends Component {
    public $publicationStats = [];
    public $months = [];
    public $values = [];
    public $selectedYear;
    public $years = [];
    public $isMounted = false; // Flag to track initial mount

    public function mount()
    {
        $this->generateYears();
        $this->selectedYear = '2024'; // Default to current year
        $this->publicationStat($this->selectedYear);
        $this->isMounted = true; // Set the flag after mount
    }
    

    public function generateYears()
    {
        $this->years = range(2020, 2040);
    }

    public function publicationStat($year)
    {
        $this->selectedYear = $year;
        $this->publicationStats = Publication::getMonthlyPublicationStatistics($year)->toArray();
        $this->months = array_keys($this->publicationStats);
        $this->values = array_values($this->publicationStats);

        // Dispatch event only if the component is not being mounted
        if ($this->isMounted) {
            $this->dispatch('statsUpdated', [
                'months' => $this->months,
                'values' => $this->values
            ]);
        }
    }
};
?>


<div class="p-4 bg-white rounded-lg shadow-md w-full"
    x-data="{'months': @entangle('months'), 'values': @entangle('values')}">
    <h2 class="text-lg font-semibold mb-2 text-gray-800 text-center">
        {{ __('Publications par mois') }}
    </h2>

    <!-- Year Selection Dropdown -->
    <div class="mb-4 relative">
        <select wire:model="selectedYear"
            wire:change="publicationStat($event.target.value)"
            class="w-32 px-3 py-1.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out appearance-none"
            size="1">
            @foreach($years as $year)
            <option value="{{ $year }}"
                @selected($year==$selectedYear)>
                {{ $year }}
            </option>
            @endforeach
        </select>
    </div>

    <!-- Chart Canvas -->
    <div wire:ignore>
        <div class="w-full h-96 p-4">
            <canvas id="publicationChart"
                class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Include Chart.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('publicationChart').getContext('2d');
            const months = @json($months);
            const values = @json($values);

            console.log('Months:', months);
            // console.log('Values:', values);

            let chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Publications',
                        data: values,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.7)' }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                },
                                autoSkip: true,
                                maxTicksLimit: 12,
                                maxRotation: 45,
                                minRotation: 45,
                                padding: 10
                            },
                            offset: true
                        },
                        y: {
                            grid: { color: 'rgba(200, 200, 200, 0.3)' },
                            ticks: {
                                font: { size: 10 },
                                precision: 0
                            }
                        }
                    }
                }
            });

            Livewire.on('statsUpdated', (data) => {

                if (Array.isArray(data) && data.length > 0) {
                    let newMonths = data[0].months;
                    let newValues = data[0].values;

                    const uniqueMonths = [];
                    const uniqueValues = [];
                    const seen = new Set();

                    newMonths.forEach((month, index) => {
                        if (!seen.has(month)) {
                            seen.add(month);
                            uniqueMonths.push(month);
                            uniqueValues.push(newValues[index]);
                        }
                    });



                    chart.data.labels = uniqueMonths;
                    chart.data.datasets[0].data = uniqueValues;
                    chart.update();
                    chart.resize();
                    console.log('Livewire event received and chart updated');
                } else {
                    console.error('Invalid data format received:', data);
                }
            });
        });
    </script>
</div>