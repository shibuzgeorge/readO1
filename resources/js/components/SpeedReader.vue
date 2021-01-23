<template>
    <div>
    <button class="btn btn-primary" v-on:click="start_btn" id="start_btn">Start</button>
    <button class="btn btn-primary" v-on:click="stop_btn" id="stop_btn">Pause</button>
    <span class="speed-controls">Speed (words per minute):
                    <select class="form-control" name="speed" id="speed">
                        <option value="100">100</option>
                        <option value="150">150</option>
                        <option value="200">200</option>
                        <option value="250">250</option>
                        <option value="300">300</option>
                        <option value="350">350</option>
                        <option value="400">400</option>
                        <option value="450">450</option>
                    </select>
                </span>
                <pre>
                <div id="text">

                </div>
                </pre>
    </div>
</template>

<script>
    import 'jquery/dist/jquery.min.js'
    import 'popper.js/dist/umd/popper.min.js'
    import $ from 'jquery'
    export default {
        name: "SpeedReader",
        middleware: 'auth',
        props: {
            text: String,
        },
        data: () => ({
            timer: '',
            isPaused: false,
            index: 0,
        }),
        created(){
            $.fn.lighten = function(test){
                var text = $(this).text();
                text = text.split(" ");
                text[test] = "<mark>" + text[test] + "</mark>";
                $(this).html(text.join(" "));
                this.index = test;
            };
        },
    methods: {
        start: function (element, text) {

            var index = this.index;
            element.text(text);
            var len = element.text().split(" ").length;
            var speed = parseInt($("#speed").val()); //Words per minute
            this.timer = setInterval(function () {
                if (index >= len)
                    clearInterval(timer);
                else
                    element.lighten(index++);

            }, 60000.0 / speed);
        },
        stop: function () {

            clearInterval(this.timer);
        },
        start_btn: function () {
            this.index = 0;
            this.stop();
            this.start($("#text"), this.text);
            $("#stop_btn").text("Pause");
            $("#start_btn").text("Restart");
            this.isPaused = false;
            return false;

        },
        stop_btn: function () {
            this.isPaused = !this.isPaused;
            if (this.isPaused) {
                this.stop();
                $("#stop_btn").text("Resume");
            }
            else {
                var findMark = $("#text").html().split(" ").indexOf("<mark>" + $("mark").text() + "</mark>");
                this.index = findMark;
                this.start($("#text"), this.text);
                $("#stop_btn").text("Pause");
            }
            return false;
        }
    }
    }
</script>

<style scoped>
    pre {
        white-space: -moz-pre-wrap; /* Mozilla, supported since 1999 */
        white-space: -pre-wrap; /* Opera */
        white-space: -o-pre-wrap; /* Opera */
        white-space: pre-wrap; /* CSS3 - Text module (Candidate Recommendation) http://www.w3.org/TR/css3-text/#white-space */
        word-wrap: break-word; /* IE 5.5+ */
    }

    #text
    {
        margin-top: 10px;
        color: #aaa;
    }
    mark
    {
        background-color: blue;
    }
    select.form-control
    {
        display: inline;
        width: 20%;
    }
    .speed-controls
    {
        margin-left: 30px;
    }
    .bs-callout {
        padding: 20px;
        margin: 20px 0;
        border: 1px solid #eee;
        border-left-width: 5px;
        border-radius: 3px;
    }
    .bs-callout h4 {
        margin-top: 0;
        margin-bottom: 5px;
    }
    .bs-callout p:last-child {
        margin-bottom: 0;
    }
    .bs-callout code {
        border-radius: 3px;
    }
    .bs-callout+.bs-callout {
        margin-top: -5px;
    }
    .bs-callout-default {
        border-left-color: #777;
    }
    .bs-callout-default h4 {
        color: #777;
    }
    .bs-callout-primary {
        border-left-color: #428bca;
    }
    .bs-callout-primary h4 {
        color: #428bca;
    }
    .bs-callout-success {
        border-left-color: #5cb85c;
    }
    .bs-callout-success h4 {
        color: #5cb85c;
    }
    .bs-callout-danger {
        border-left-color: #d9534f;
    }
    .bs-callout-danger h4 {
        color: #d9534f;
    }
    .bs-callout-warning {
        border-left-color: #f0ad4e;
    }
    .bs-callout-warning h4 {
        color: #f0ad4e;
    }
    .bs-callout-info {
        border-left-color: #5bc0de;
    }
    .bs-callout-info h4 {
        color: #5bc0de;
    }
</style>