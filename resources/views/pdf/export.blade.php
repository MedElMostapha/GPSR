<!DOCTYPE html>
<html>

<head>
    <title>PDF Export</title>
    <style>
        /* Center the image in the header */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 200px;
            height: auto;
        }

        /* Table styling */
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
    <!-- Header with centered image -->
    <div class="header">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/images/logo.png')))}}"
            alt="Logo">
    </div>

    <!-- Table -->
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