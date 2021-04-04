<template>
        <div>
            <h1>Quiz Guide</h1>

            <vueper-slides
                    class="no-shadow"
                    :visible-slides="2"
                    slide-multiple
                    :gap="3"
                    :touchable="true"
                    :slide-ratio="1 / 4"
                    :dragging-distance="200"
                    :breakpoints="{ 800: { visibleSlides: 2, slideMultiple: 2 } }">

                <vueper-slide class="card" key="1" title="Test yourself">
                    <template v-slot:content>
                        <div>
                            ..................
                        </div>
                    </template>
                </vueper-slide>
                <vueper-slide class="card" key="2" title="Multiple choice question">
                    <template v-slot:content>
                        <div>
                            .................
                        </div>
                    </template>
                </vueper-slide>
                <vueper-slide class="card" key="3" title="Feedback given instantly and emailed to you">
                    <template v-slot:content>
                        <div>
                            .................
                        </div>
                    </template>
                </vueper-slide>
            </vueper-slides>
        </div>
</template>


<script>
    export default {
        name: "QuizGuide"
    }
</script>

<style scoped>

</style>