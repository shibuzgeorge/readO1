<template>
    <div>
    <h1>Skimming</h1>

        Try these skimming techniques:<br/>

        1) Read the title<br/>
        2) Read the introduction or the first paragraph<br/>
        3) Read the first sentence of every other paragraph<br/>
        4) Read any headings and sub-headings<br/>
        5) Notice any pictures, charts, or graphs<br/>
        6) Notice any italicized or bold face word or phrases<br/>
        7) Read the summary or last paragraph.<br/>

        <vueper-slides
                class="no-shadow"
                :visible-slides="2"
                slide-multiple
                :gap="3"
                :touchable="true"
                :slide-ratio="1 / 4"
                :dragging-distance="200"
                :breakpoints="{ 800: { visibleSlides: 2, slideMultiple: 2 } }">

            <vueper-slide class="card" key="1" title="Why is skimming useful?">
                <template v-slot:content>
                    <div>
                    ..................
                    </div>
                </template>
            </vueper-slide>
                <vueper-slide class="card" key="2" title="How to effectively skim">
                <template v-slot:content>
                    <div>
                      .................
                    </div>
                </template>
            </vueper-slide>
            <vueper-slide class="card" key="3" title="How to use skimming in the application">
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
        name: "Skimming"
    }
</script>

<style scoped>

</style>