<html>
<head>
    <style>
        .pass-table {
            border-collapse: collapse;
        }

        .pass-table th, .pass-table td {
            border: 1px solid black;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
<table class="pass-table">
    <thead>
    <tr>
        <th>Branch Name</th>
        <th>Password</th>
    </tr>
    </thead>
    <tbody>
    @foreach($passwords as $password)
        <tr>
            <td>{{ $password->branch }}</td>
            <td>{{ $password->password }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
