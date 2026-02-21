<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Report {{ $month }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        th { background: #efefef; }
    </style>
</head>
<body>
    <h2>Monthly Payroll Report ({{ $month }})</h2>
    <table>
        <thead>
            <tr>
                <th>Driver</th>
                <th>Status</th>
                <th>Completed Trips</th>
                <th>Safety Score</th>
                <th>License Expiry</th>
                <th>Monthly Salary</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row[0] }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $row[1])) }}</td>
                    <td>{{ $row[2] }}</td>
                    <td>{{ $row[3] }}</td>
                    <td>{{ $row[4] }}</td>
                    <td>{{ $row[5] }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
