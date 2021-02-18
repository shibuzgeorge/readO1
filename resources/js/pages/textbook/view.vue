<template>
        <div class="container-fluid" v-if="isLoaded">
            <div class="row" v-if="file">
                <card class="col-lg-6 col-md-6 col-sm-12">
                    <h5 class="card-header d-flex justify-content-between align-items-center">
                        Title: {{title}}
                        Description: {{description}}
                        <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
                    </h5>

                    <div class="wrapper mt-4">
                        <div>
                            <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'textbook.edit', params: {id: $route.params.id} }">
                                <button class="btn btn-sm btn-primary" v-if="role!=='Student'">Edit</button>
                            </router-link>
                            <div style="float: right;" class="ml-2">
                            <a @click="manageQuiz()"><button class="btn btn-sm btn-success" v-if="role!=='Student'">+ Manage Quizzes</button></a>
                            <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'text.create' }">
                                <button class="btn btn-sm btn-success" v-if="role!=='Student'">+ Add text</button>
                            </router-link>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                                <input type="search" v-model="searchQuery" class="form-control" placeholder="Search..."
                                       aria-label="Search" />
                        </div>
                        <h1 style="text-align: center;" class="mt-4" v-if="!texts || !texts.length">
                            This textbook has no texts!
                        </h1>
                        <br/>
                        <ul class="list-group" v-for="text in textsPaginated">
                            <li class="list-group-item">
                                <router-link :to="{ name: 'text.show', params: {id: text.id} }">
                                    {{text.title}} - {{text.description }}
                                </router-link>
                                <a style="float: right;" @click="del(text.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#"><button type="button" class="btn btn-warning btn-sm">Delete</button></a>
                                <router-link :to="{ name: 'text.edit', params: {id: text.id} }">
                                    <a style="float: right;" v-show="role==='Admin' || role==='Module Tutor'" href="edit"><button type="button" class="btn btn-primary btn-sm">Edit</button></a>
                                </router-link>
                            </li>
                        </ul><br/>
                        <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate>
                    </div>
                </card>
                <card class="col-lg-5 col-md-5 col-sm-12 ml-2">
                    <PDFViewer :fileName="fileName" :path="path"/>
                </card>

            </div>
            <div v-else>
                <card>
                    <h5 class="card-header d-flex justify-content-between align-items-center">
                        Title: {{title}} - Description: {{description}}
                        <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
                    </h5>

                    <div class="wrapper mt-4">
                        <div>
                            <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'textbook.edit', params: {id: $route.params.id} }">
                                <button class="btn btn-sm btn-primary" v-if="role!=='Student'">Edit</button>
                            </router-link>
                            <div style="float: right;" class="ml-2">
                                <a @click="manageQuiz()"><button class="btn btn-sm btn-success" v-if="role!=='Student'">+ Manage Quizzes</button></a>
                                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'text.create' }">
                                    <button class="btn btn-sm btn-success" v-if="role!=='Student'">+ Add text</button>
                                </router-link>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                                <input type="search" v-model="searchQuery" class="form-control" placeholder="Search..."
                                       aria-label="Search" />
                        </div>
                        <h1 style="text-align: center;" class="mt-4" v-if="!texts || !texts.length">
                            This textbook has no texts!
                        </h1>
                        <br/>
                        <ul class="list-group" v-for="text in textsPaginated">
                            <li class="list-group-item">
                                <router-link :to="{ name: 'text.show', params: {id: text.id} }">
                                    {{text.title}} - {{text.description }}
                                </router-link>
                                <a style="float: right;" @click="del(text.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#"><button type="button" class="btn btn-warning btn-sm">Delete</button></a>
                                <router-link :to="{ name: 'text.edit', params: {id: text.id} }">
                                    <a style="float: right;" v-show="role==='Admin' || role==='Module Tutor'" href="edit"><button type="button" class="btn btn-primary btn-sm">Edit</button></a>
                                </router-link>
                            </li>
                        </ul> <br/>
                        <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate>
                    </div>
                </card>
            </div>
            <sweet-modal ref="success" icon="success">
                {{successMessage}}
            </sweet-modal>
            <sweet-modal ref="manageQuiz">
                <select class="form-control" v-model="quizTextSelected">
                    <option>Please a text...</option>
                    <option v-for="text in texts" :value="text.id" :key="text.id">
                        {{ text.title }}
                    </option>
                </select><br/>
                <manage-quiz :text_id="quizTextSelected" :parent="2"></manage-quiz>
            </sweet-modal>
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
    import ManageQuiz from "../../components/ManageQuiz";
    export default {
        middleware: 'auth',
        data: () => ({
            isLoaded: false,
            title: '',
            description: '',
            texts: [],
            textsPaginated: [],
            errorMessage: '',
            successMessage: '',
            file: true,
            fileName: '',
            path: '/lib/pdf/web/viewer.html',
            searchQuery: null,
            sizePage: 9,
            quizTextSelected: 'Please a text...',
        }),
        computed : {
            ...mapGetters({
                role: 'auth/role'
            }),
            resultQuery() {
                if (this.searchQuery) {
                    return this.texts.filter((item) => {
                        return this.searchQuery.toLowerCase().split(' ').every(
                            v => item.title.toLowerCase().includes(v) ||
                                item.description.toLowerCase().includes(v))
                    })
                }else {
                    return this.texts;
                }
            }
        },
        components:{
            ManageQuiz,
            PDFViewer,
        },
        created() {
            this.fileName =`/api/textbook/pdf/${this.$route.params.id}`
            axios.get(`/api/textbook/${this.$route.params.id}`)
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
            },
            onChangePage(pageOfItems) {
                this.textsPaginated = pageOfItems;
            },
            manageQuiz(){
                this.$refs.manageQuiz.open();
            },
        },
        metaInfo () {
            return {title: this.$t('home')}
        }

    }
</script>

<style scoped>

</style>