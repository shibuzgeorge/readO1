<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Testing</title>
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
        .flexbox {
            display: flex;
            justify-content:  space-between;
        }
        .flex-item {
            margin: 10px auto;
        }
    </style>
</head>
<body>
<section class="jumbotron text-center">
        <h1 class="jumbotron-heading">You scored: {{ $totalPointsCollected }} points out of {{$quiz['max_points']}}
            {{number_format($totalPointsCollected/$quiz['max_points'], 3) * 100}}%</h1>
        <div class="flexbox">
            <div class="flex-item"><b>Date: {{Carbon\Carbon::now()->toDateTimeString()}}</b></div>
            <div class="flex-item"><b>Attempt Number: {{$attempt_number}}</b></div>
        </div>
</section>
    <br/>
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
                    @if(in_array($option['option_text'], $selectedOptions))
                                <td bgcolor="yellow">{{ $option['points'] }}</td>
                            @else
                                <td></td>
                            @endif
                        </tr>
            @endforeach
    </tbody>
</table>
    @endforeach
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>