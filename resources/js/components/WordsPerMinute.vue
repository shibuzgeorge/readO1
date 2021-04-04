<template>
    <div>
    <h1>Speed reading word per minute</h1>

        Want a challenge?

        Try reading at a set words per minute

        <vueper-slides
                class="no-shadow"
                :visible-slides="2"
                slide-multiple
                :gap="3"
                :touchable="true"
                :slide-ratio="1 / 4"
                :dragging-distance="200"
                :breakpoints="{ 800: { visibleSlides: 2, slideMultiple: 2 } }">

            <vueper-slide class="card" key="1" title="Can you keep up?">
                <template v-slot:content>
                    <div>
                    ..................
                    </div>
                </template>
            </vueper-slide>
                <vueper-slide class="card" key="2" title="Pace and challenge yourself">
                <template v-slot:content>
                    <div>
                      .................
                    </div>
                </template>
            </vueper-slide>
            <vueper-slide class="card" key="3" title="Practise makes perfect">
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
        name: "WordsPerMinute"
    }
</script>

<style scoped>

</style>