<template>
    <div>
        <iframe height="100%" width=100% :src="this.path +'?file=' + this.fileName" ></iframe>
    </div>
</template>

<script>
    export default {
        name: 'PDFViewer',
        props: {
            fileName: String,
            path:String
        },
    }
</script>
<style scoped>
    div {
        width: 100%;
        height: 79vh;
        min-width: 400px;
    }
</style>