<template>
    <card>
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">Create textbook</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)" enctype="multipart/form-data">
            <div class="form-check mt-4">
                <label>Title:           </label> <input class="form-control" v-model="form.title" :class="{ 'is-invalid': form.errors.has('title') }" type="text" value="" />
                <has-error :form="form" field="title" /><br/>
                <label>Description:     </label> <input class="form-control" v-model="form.description" :class="{ 'is-invalid': form.errors.has('description') }" type="text" value="" />
                <has-error :form="form" field="description" /><br/>
                    <div class="form-control"  style="display:none;" :class="{ 'is-invalid': form.errors.has('selected') } " ></div>
                <div v-show="section===''">
                        <label>Select a section to upload:</label><br/>

                            <button @click="section='module'" type="button" class="btn btn-sm btn-primary">Module</button>
                             Or <button @click="section='extensiveReading'" type="button" class="btn btn-sm btn-primary">Extensive Reading</button>
                </div>

                <div v-show="section==='module'">Choose module(s) <button @click="section=''" type="button" class="float-right btn btn-sm btn-warning">Go back X</button>
                    <multiselect v-model="form.selected" :options="modules"
                                 track-by="name" label="name"
                                 :multiple="true"
                                 :close-on-select="false">
                    </multiselect>

                </div>
                <div v-show="section==='extensiveReading'">Select a extensive reading category<button @click="section=''" type="button" class="float-right btn btn-sm btn-warning">Go back X</button>
                    <multiselect v-model="form.selected" :options="extensiveReadingCategories"
                                 track-by="name" label="name"
                                 :close-on-select="true">
                    </multiselect>
                </div>
                <has-error :form="form" field="selected" />
                <br/>
                <label>Upload full textbook [optional]: (Format: PDF) - <i>A thumbnail will be generated automatically (Page 1 of PDF)</i></label>
                <input class="form-control" type="file" id="file" :class="{ 'is-invalid': form.errors.has('file') }" ref="file" v-on:change="handleFileUpload()"/><br/>
                <has-error :form="form" field="file" />
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="customSwitch1" :checked="thumbnailOn" @click="thumbnailOn = !thumbnailOn">
                    <label class="custom-control-label" for="customSwitch1">Turn thumbnail on or not</label>
                </div>
                <label>Upload thumbnail [optional]: (Format: jpg, jpeg, png) - <i>Overrides thumbnail of PDF upload (if uploaded)</i></label>
                <input class="form-control" :disabled="!thumbnailOn" type="file" :class="{ 'is-invalid': form.errors.has('thumbnail') }" id="thumbnail" ref="thumbnail" v-on:change="handleThumbnailUpload()"/><br/>
                <has-error :form="form" field="thumbnail" />
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
                description: '',
                file: '',
                thumbnail: '',
                selected: [],
            }),
            section: '',
            modules: [],
            extensiveReadingCategories: [],
            thumbnailOn: true,
            moduleSelect: false,
            extensiveReadingSelect: false,
        }),
        watch:{
            section: function(){
                if(this.section===''){
                    this.form.selected = [];
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
                    this.form.file = this.$refs.file.files[0];
            },
            handleThumbnailUpload(){
                this.form.thumbnail = this.$refs.thumbnail.files[0];
            },
            async submit() {

                this.form.busy = true;
                let formData = new FormData();

                /*
                    Add the form data we need to submit
                */
                if(!this.thumbnailOn){
                    this.form.thumbnail = 'remove';
                }

                formData.append('thumbnail', this.form.thumbnail);
                formData.append('file', this.form.file);
                formData.append('title', this.form.title);
                formData.append('description', this.form.description);
                formData.append('section', this.section);
                formData.append('selected', JSON.stringify(this.form.selected));

                await this.form.submit('post','/api/textbook', {
                    // Transform form data to FormData
                    transformRequest: [function (data, headers) {
                    return formData
                }]})
                    .then(response => {
                        this.successMessage = response.data.Success;
                        this.$refs.success.open();
                    })
                    .catch(error => {
                        this.form.selected = JSON.parse(this.form.selected)
                        console.log(error.response)
                    });
            },

        }

    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>