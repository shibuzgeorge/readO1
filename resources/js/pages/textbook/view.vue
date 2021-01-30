<template>
        <div class="container-fluid" v-if="isLoaded">
            <div class="row" v-if="file">
                <card class="col-5 ml-4 d-flex flex-column h-100">
                    <PDFViewer :fileName="fileName" :path="path"/>
                </card>
                <card class="col-6 d-flex flex-column h-100">
                    <h5 class="card-header d-flex justify-content-between align-items-center">
                        Title: {{title}} - Description: {{description}}
                        <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
                    </h5>

                    <div class="wrapper mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="col-xs-3">
                                <input type="search" id="form1" class="form-control" placeholder="Search..."
                                       aria-label="Search" />
                            </div>
                            <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'text.create' }">
                                <button class="btn btn-success" v-if="role!=='Student'">+ Add text</button>
                            </router-link>
                        </div>
                        <h1 style="text-align: center;" class="mt-4" v-if="!texts || !texts.length">
                            This textbook has no texts!
                        </h1>
                    <br/>
                            <ul class="list-group" v-for="text in texts">
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'text.show', params: {id: text.id} }">
                                        {{text.title}} - {{text.description }}
                                    </router-link>
                                    <a style="float: right;" @click="del(text.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#"><button type="button" class="btn btn-warning btn-sm">Delete</button></a>
                                    <router-link :to="{ name: 'text.edit', params: {id: text.id} }">
                                        <a style="float: right;" v-show="role==='Admin' || role==='Module Tutor'" href="edit"><button type="button" class="btn btn-primary btn-sm">Edit</button></a>
                                    </router-link>
                                </li>
                        </ul>
                    </div>
                </card>
            </div>
            <div v-else>
                <card>
                    <h5 class="card-header d-flex justify-content-between align-items-center">
                        Title: {{title}} - Description: {{description}}
                        <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
                    </h5>

                    <div class="wrapper mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="col-xs-3">
                                <input type="search" id="form1" class="form-control" placeholder="Search..."
                                       aria-label="Search" />
                            </div>
                            <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'text.create' }">
                                <button class="btn btn-success" v-if="role!=='Student'">+ Add text</button>
                            </router-link>
                        </div>
                        <h1 style="text-align: center;" class="mt-4" v-if="!texts || !texts.length">
                            This textbook has no texts!
                        </h1>
                        <br/>
                        <ul class="list-group" v-for="text in texts">
                            <li class="list-group-item">
                                <router-link :to="{ name: 'text.show', params: {id: text.id} }">
                                    {{text.title}} - {{text.description }}
                                </router-link>
                                <a style="float: right;" @click="del(text.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#"><button type="button" class="btn btn-warning btn-sm">Delete</button></a>
                                <router-link :to="{ name: 'text.edit', params: {id: text.id} }">
                                    <a style="float: right;" v-show="role==='Admin' || role==='Module Tutor'" href="edit"><button type="button" class="btn btn-primary btn-sm">Edit</button></a>
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </card>
            </div>
        </div>
    <div v-else style="text-align: center;">
        <sweet-modal ref="error" v-on:close="$router.go(-1)" icon="error">
            {{errorMessage}}
        </sweet-modal>
        <clip-loader color="black"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import Swal from 'sweetalert2'
    import PDFViewer from '~/components/PDFViewer';
    export default {
        middleware: 'auth',
        computed: mapGetters({
            role: 'auth/role'
        }),
        components:{
            PDFViewer,
        },
        data: () => ({
            isLoaded: false,
            title: '',
            description: '',
            texts: [],
            errorMessage: '',
            file: true,
            fileName: '',
            path: '/lib/pdf/web/viewer.html',

        }),
        created() {
            this.fileName =`/api/textbook/view/${this.$route.params.id}/pdf`
            axios.get(`/api/textbook/view/${this.$route.params.id}`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else {
                        this.isLoaded = true;
                        this.title = response.data.title;
                        this.description = response.data.description;
                        this.texts = response.data.texts;
                        this.file = response.data.file;
                    }

                }).catch(error => {
                this.$router.go(-1)
            });
        },
        methods: {
            async del(data) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete the text?",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then(function (result) {
                    if (result.value) {
                        Swal.fire(
                            'Deleted!',
                            'The text has been deleted.',
                            'success'
                        );
                        axios.delete(`/api/text/${data}`).then(response => {
                            window.location.reload();
                        })
                    }
                });

            }
        },
        metaInfo () {
            return {title: this.$t('home')}
        }

    }
</script>

<style scoped>

</style>