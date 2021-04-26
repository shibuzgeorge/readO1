<template>
    <div class="main-layout">


        <card :title="$t('about')">
            <h4>How it works:</h4>
            ReadO(1) is a unique single page web application designed for computer science students to improve their knowledge and exam performance using speed reading techniques.
            Students can have access to modules, which are assigned to them, and all extensive reading categories to select a textbook of their choice.
            Inside each textbook, there are chapters (texts) which is a small subset of the full textbook.
            Students can decide to read a chapter one by one. Once inside a text, a timer will appear,
            and you have 3 different options to speed read: PDF View, Text View or Speed-Reading View.
            You can select any and start reading your text. Once you’ve finished reading, click the finished reading button
            and you will be presented with a quiz based on the text you’ve read (if available).
            You’ll have options to select for each question and once you’ve completed a quiz, a score will be calculated based on points.
            In the dashboard you can see your score for each text you’ve completed, the time taken and the number of attempts on that text.<p>

            <h4>Library:</h4>
            The library contains all the textbooks which contains all the extensive reading material and all the module textbook you’re assigned to. You can search through and select a textbook of your choice.
            <p> <h4>Modules:</h4>
            Students can be assigned to modules and inside each module has textbooks in which they can choose from.
            <p><h4>Extensive Reading:</h4>
            The extensive reading section has many categories for you to choose. Based on your interest you can choice a textbook and start speed reading.

            <p><h4>The speed reading strategies:</h4>
                <skimming></skimming><br/>
                <scanning></scanning><br/>
            <words-per-minute></words-per-minute><br/>

            <h3>Test yourself</h3>
            <quiz-guide></quiz-guide><br/>

        </card>
    </div>
</template>

<script>

    import Skimming from "../components/Skimming";
    import Scanning from "../components/Scanning";
    import QuizGuide from "../components/QuizGuide";
    import WordsPerMinute from "../components/WordsPerMinute";
    export default {
        name: 'MainLayout',

        components: {
            WordsPerMinute,
            QuizGuide,
            Scanning,
            Skimming

        }
    }
</script>
