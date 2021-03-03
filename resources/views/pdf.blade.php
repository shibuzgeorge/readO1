<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Testing</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUIyJ" crossorigin="anonymous">
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
<div class="container-fluid">
<p class="mt-5">
    <h2><b>You scored: {{ $totalPointsCollected }} points out of {{$quiz['max_points']}}
            - {{number_format($totalPointsCollected/$quiz['max_points'], 3) * 100}}%</b></h2>
    </p>
<b>Date: {{Carbon\Carbon::now()->toDateTimeString()}}</b><br/>
    @foreach($quiz['questions'] as $question)
        <div class="justify-content-between align-items-center">
        <h6><b>Question:</b> {{ $question['question_text'] }}</h6>
            <b>Maximum points available: {{$question['max_points']}} </b>
        </div><br/>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Options</th>
                <th>Points</th>
            </tr>
            </thead>
            <tbody>
            @foreach($question['options'] as $option)
                        <tr>
                <td @if(in_array($option['option_text'], $selectedOptions)) bgcolor="yellow" @endif>{{ $option['option_text'] }}</td>
                <td @if(in_array($option['option_text'], $selectedOptions)) bgcolor="yellow" @endif>{{ $option['points'] }}</td>
                        </tr>
            @endforeach
    </tbody>
</table>
    @endforeach
</div>
</body>
</html>