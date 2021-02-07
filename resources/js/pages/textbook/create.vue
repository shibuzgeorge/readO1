<template>
    <card>
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">Create textbook</h5>
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

                <div v-show="section==='module'">Choose module(s) <button @click="section=''" type="button" class="float-right btn btn-sm btn-warning">Go back X</button>
                    <multiselect v-model="selected" :options="modules"
                                 track-by="name" label="name"
                                 :multiple="true"
                                 :close-on-select="false">
                    </multiselect>

                </div>
                <div v-show="section==='extensiveReading'">Select a extensive reading category<button @click="section=''" type="button" class="float-right btn btn-sm btn-warning">Go back X</button>
                    <multiselect v-model="selected" :options="extensiveReadingCategories"
                                 track-by="name" label="name"
                                 :close-on-select="true">
                    </multiselect>
                </div>
                <br/>
                <label>Upload full textbook: </label>
                <input class="form-control" type="file" id="file" ref="file" v-on:change="handleFileUpload()"/><br/>
            </div>
            <sweet-modal ref="success" v-on:close="$router.go(-1)" icon="success">
                Successfully Uploaded
            </sweet-modal>
            <v-button class="form-control" :loading="form.busy" type="success">
                 Upload
            </v-button>
        </form>
    </card>


</template>

<script>
    import axios from 'axios'
    import Form from 'vform'

    export default {
        middleware: 'admin_plus_module_tutor',
        name: "create",
        data: () => ({
            isLoaded: false,
            form: new Form({
                title: '',
                description: ''
            }),
            selected: [],
            modules: [],
            extensiveReadingCategories: [],
            file: '',
            moduleSelect: false,
            extensiveReadingSelect: false,
            section: '',

        }),
        watch:{
            section: function(){
                if(this.section===''){
                    this.selected = [];
                }
            }

        },
    created() {
        axios.get('/api/textbook/create')
            .then(response => {
                this.modules = response.data.modules;
                this.extensiveReadingCategories = response.data.extensiveReadingCategories;
                this.isLoaded = true;
            }).catch(function (response) {
            //handle error
            console.log(response);
        });

    },
        methods: {
            handleFileUpload(){
                    this.file = this.$refs.file.files[0];
            },
            async submit() {
                this.form.busy = true;
                let formData = new FormData();

                /*
                    Add the form data we need to submit
                */
                formData.append('file', this.file);
                formData.append('title', this.form.title);
                formData.append('description', this.form.description);
                formData.append('section', this.section);
                formData.append('selected', JSON.stringify(this.selected));

                await axios.post('/api/textbook',formData,
                {
                    headers: {
                        'Content-Type'
                    :
                        'multipart/form-data'
                    }
                })
                    .then(response => {
                        console.log(response);
                        this.$refs.success.open();
                    })
                    .catch(error => {
                        console.log(error.response)
                    });
            },

        }

    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>