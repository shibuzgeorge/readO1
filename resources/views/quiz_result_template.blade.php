<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Result</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style type="text/css">
        html {
            margin: 0;
        }
        body {
            margin: 36pt;
        }
    </style>
</head>
<body>
<div class="jumbotron jumbotron-fluid align-items-center">
    <div class="text-center">
                <h1 class="jumbotron-heading">You scored: {{ $totalPointsCollected }} out of {{$quiz['max_points']}}</h1>
                <h1>{{number_format($totalPointsCollected/$quiz['max_points'], 3) * 100}}%</h1>
                <b>@if($section === 'Extensive Reading') Extensive Reading Category: {{$area['category']}} @else Module: {{$area['module']}} @endif </b><br/>
                <b>Textbook: {{$area['textbook']}} </b><br/>
                <b>Text: {{$area['text']}} </b><br/><br/>
                <b>Date: {{Carbon\Carbon::now()->timezone('Europe/London')->format('l jS \\of F Y H:i:s')}}</b><br/>
                <b>Attempt Number: {{$attempt_number}}</b>
    </div>
</div>
    @foreach($quiz['questions'] as $question)
        <div class="justify-content-between align-items-center">
        <h5><b>Question: {{ $question['question_text'] }}</b></h5>
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
<br/><hr>
<div class="text-center">
Thank you for using ReadO(1) &copy; <?php echo date("Y"); ?>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>