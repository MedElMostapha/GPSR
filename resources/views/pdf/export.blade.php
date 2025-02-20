<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Export PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .total {
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }

        .logo img {
            width: 150px;
            height: auto;
        }

        .logo {
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="logo">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/images/logo.png')))}}"
            alt="Logo">
    </div>

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

    @if($total > 0)
    <div class="total">
        Total: {{ $total }}
    </div>
    @endif
</body>

</html>