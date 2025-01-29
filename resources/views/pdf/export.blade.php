<!DOCTYPE html>
<html>

<head>
    <title>PDF Export</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h1>Exported Data</h1>
    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                <th>{{ $columnLabels[$column] ?? ucfirst($column) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                @foreach($columns as $column)
                <td>
                    @if(isset($booleanColumns[$column]))
                    @php
                    $booleanDisplay = $row[$column] ? $booleanColumns[$column]['true'] :
                    $booleanColumns[$column]['false'];
                    @endphp
                    <span class="{{ $booleanDisplay['class'] }}">{{ $booleanDisplay['text'] }}</span>
                    @else
                    {{ $row[$column] }}
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>