<template>
    <div>
    <h1>Scanning</h1>

        Try these scanning techniques<br/>
        <b>The hand</b>: Move your hand slowly straight down the page as your eyes follow.<br/>
        <b>The card</b>: to move the card to be followed by the brain.<br/>
        <b>The sweep</b>: to use the hand to help draw the eyes across the page.<br/>
        <b>The hop</b>: to lift fingers and make two bounced on each line to catch sections of three or four words and then move on.<br/>
        <b>The zig-zag</b>: to take the hand and cut the text in a diagonal motion for two or the line.<br/>

        <vueper-slides
                class="no-shadow"
                :visible-slides="2"
                slide-multiple
                :gap="3"
                :touchable="true"
                :slide-ratio="1 / 4"
                :dragging-distance="200"
                :breakpoints="{ 800: { visibleSlides: 2, slideMultiple: 2 } }">

            <vueper-slide class="card" key="1" title="Why is scanning useful?">
                <template v-slot:content>
                    <div>
                    ..................
                    </div>
                </template>
            </vueper-slide>
                <vueper-slide class="card" key="2" title="How to effectively scan">
                <template v-slot:content>
                    <div>
                      .................
                    </div>
                </template>
            </vueper-slide>
            <vueper-slide class="card" key="3" title="How to use scanning in the application">
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
        name: "Scanning"
    }
</script>

<style scoped>

</style>