<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Testing</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        html {
            margin: 0;
        }
        body {
            background-color: #FFFFFF;
            font-size: 10px;
            margin: 36pt;
        }
    </style>
</head>
<body>
<p class="mt-5">Total points: {{ $totalPointsCollected }} points</p>
<table class="table table-bordered">
    <thead>
    <tr>
        <th>Question Text</th>
        <th>Points</th>
    </tr>
    </thead>
    <tbody>
    @foreach($quiz['questions'] as $question)
        <tr>
            <td>{{ $question['question_text'] }}</td>
            @foreach($question['options'] as $option)
                <td>{{ $option['option_text'] }}</td>
                <td>{{ $option['points'] }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>