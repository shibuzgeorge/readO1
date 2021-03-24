<template>
    <div v-if="isLoaded">
    <card>
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">Edit textbook/document</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)" enctype="multipart/form-data">
            <div class="form-check mt-4">
                <label>Title:           </label> <input class="form-control" v-model="form.title" type="text" value="" required/><br/>
                <label>Description:     </label> <input class="form-control" v-model="form.description" type="text" value="" required/><br/>
                <div v-show="section===''">
                    <label>Select a section to upload:</label><br/>

                    <button @click="section='module'" type="button" class="btn btn-sm btn-primary">Module</button>
                    Or <button @click="section='extensiveReading'" type="button" class="btn btn-sm btn-primary">Extensive Reading</button>
                </div>

                <div v-show="section==='module'">Choose module(s) <button @click="section=''" type="button" class="float-right btn btn-sm btn-warning">Change section X</button>
                    <multiselect v-model="form.selected" :options="modules"
                                 track-by="name" label="name"
                                 :multiple="true"
                                 :close-on-select="false">
                    </multiselect>

                </div>
                <div v-show="section==='extensiveReading'">Select a extensive reading category<button @click="section=''" type="button" class="float-right btn btn-sm btn-warning">Change section X</button>
                    <multiselect v-model="form.selected" :options="extensiveReadingCategories"
                                 track-by="name" label="name"
                                 :close-on-select="true">
                    </multiselect>
                </div>
                <br/>
                <label>Update full textbook [optional]: (Format: PDF) - <i>A thumbnail will be generated automatically (Page 1 of PDF)</i></label>
                <input class="form-control" type="file" id="file" ref="file" v-on:change="handleFileUpload()"/><br/>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="customSwitch1" :checked="thumbnailOn" @click="thumbnailOn = !thumbnailOn">
                    <label class="custom-control-label" for="customSwitch1">Turn thumbnail on or not</label>
                </div>
                <img v-if="thumbnailImage!==null" :src="'data:image/png;base64,'+thumbnailImage" width="100" height="100"/><br/>
                <label>Update thumbnail [optional]: (Format: jpg, jpeg, png) - <i>Overrides thumbnail of PDF upload (if uploaded)</i></label>
                <input class="form-control" type="file" id="thumbnail" :disabled="!thumbnailOn" ref="thumbnail" v-on:change="handleThumbnailUpload()"/><br/>

                <div v-if="fileName!==''">
                    <label>Current PDF file uploaded:</label>
                <PDFViewer :fileName="fileName" :path="path" width="200" height="400"/>
                </div>


            </div>
            <sweet-modal ref="success" v-on:close="$router.push({name: 'textbook.show', params: {id: $route.params.id}})" icon="success">
                {{successMessage}}
            </sweet-modal>
            <v-button class="form-control" :loading="form.busy" type="success">
                Edit
            </v-button>
        </form>
    </card>
    </div>
<div v-else>
    <sweet-modal ref="error" v-on:close="$router.go(-1)" icon="error">
        {{errorMessage}}
    </sweet-modal>
    <clip-loader color="black"/>
</div>
</template>

<script>
    import axios from 'axios'
    import Form from 'vform'
    import PDFViewer from '~/components/PDFViewer';

    export default {
        middleware: 'admin_plus_module_tutor',
        components:{
            PDFViewer,
        },
        name: "edit",
        data: () => ({
            isLoaded: false,
            form: new Form({
                title: '',
                description: '',
                selected: [],
            }),
            file: '',
            fileName: '',
            thumbnailImage: '',
            thumbnailOn: true,
            thumbnail: '',
            path: '/lib/pdf/web/viewer.html',
            successMessage: '',
            errorMessage: '',
            modules: [],
            section: '',
            originalSelected: [],
            extensiveReadingCategories: '',
            originalSection: '',

        }),
        watch:{
            section: function(){
                if(this.section===''){
                    this.form.selected = [];
                } else if(this.section==='module' && this.originalSection ==='module'){
                    this.form.selected = this.originalSelected;
                } else if(this.section==='extensiveReading' && this.originalSection ==='extensiveReading'){
                    this.form.selected = this.originalSelected;
                }
            }

        },
        created() {
                axios.get('/api/module/')
                    .then(response => {
                        this.modules = response.data;
                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
            axios.get('/api/extensiveReading/create')
                .then(response => {
                    this.extensiveReadingCategories = response.data;

                }).catch(function (response) {
                //handle error
                console.log(response);
            });
            let self = this

            axios.get(`/api/textbook/${this.$route.params.id}/edit`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else {
                        this.form.title = response.data.title;
                        this.form.description = response.data.description;
                        this.form.selected = response.data.selected;
                        this.originalSelected = response.data.selected;
                        this.section = response.data.section;
                        this.thumbnailImage = response.data.thumbnailImage;
                        if(this.thumbnailImage === null){
                            this.thumbnailOn = false;
                        }else{
                            this.thumbnailOn = true;
                        }
                        this.originalSection = response.data.section;
                        if(response.data.file === true){
                            this.fileName =`/api/textbook/pdf/${this.$route.params.id}`;
                        }
                        this.isLoaded = true;

                    }
                }).catch(function (response) {
                //handle error
                self.$router.push({name: 'home'})

                console.log(response);
            });
        },
        methods: {
            handleFileUpload(){
                this.file = this.$refs.file.files[0];
            },
            handleThumbnailUpload(){
                this.thumbnail = this.$refs.thumbnail.files[0];
            },
            async submit() {
                this.form.busy = true;
                let formData = new FormData();

                /*
                    Add the form data we need to submit
                */
                if(!this.thumbnailOn){
                    this.thumbnail = 'remove';
                }
                formData.append('thumbnail', this.thumbnail);
                formData.append('file', this.file);
                formData.append('title', this.form.title);
                formData.append('description', this.form.description);
                formData.append('selected', JSON.stringify(this.form.selected));
                formData.append('section', this.section);
                formData.append('_method', 'PATCH');
                await axios.post(`/api/textbook/${this.$route.params.id}`,formData,
                    {
                        headers: {'Content-Type': 'multipart/form-data'
                        }
                    })
                    .then(response => {
                        this.form.busy = false;
                        this.successMessage = response.data.Success;
                        this.$refs.success.open();
                    })
                    .catch(error => {
                        console.log(error.response)
                    });
            }
        }

    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>