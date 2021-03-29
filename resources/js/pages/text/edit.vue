<template>
    <div v-if="isLoaded">
        <card>
            <div class="card-header d-flex justify-content-between align-items-center">
            <h5 id="title">Edit text/document</h5>
                <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
            </div>
            <div :class="{ 'row': fileName!=='' }">
                <div class="form-check mt-4" :class="{ 'col-sm-7': fileName!=='' }">
            <form @submit.prevent="submit" @keydown="form.onKeydown($event)" enctype="multipart/form-data">
                    <label>Title:           </label> <input class="form-control" v-model="form.title" type="text" :class="{ 'is-invalid': form.errors.has('title') }" value=""/>
                    <has-error :form="form" field="title" /><br/>
                    <label>Description:     </label> <input class="form-control" v-model="form.description" type="text" value="" :class="{ 'is-invalid': form.errors.has('description') }"/>
                    <has-error :form="form" field="description" /><br/>
                    <div v-show="selectedOption.length!==0">
                        <ul>
                            <li v-for="s in selectedOption">
                                {{s}}
                            </li>
                        </ul>
                    </div>
                    <div class="form-control"  style="display:none;" :class="{ 'is-invalid': form.errors.has('selected') } " ></div>
                    <div v-show="section===''">
                        <label>Select a section to upload:</label><br/>

                        <button @click="section='yearGroup'" type="button" class="btn btn-sm btn-primary">Module</button>
                        Or <button @click="section='extensiveReading'" type="button" class="btn btn-sm btn-primary">Extensive Reading</button>
                    </div>

                    <div v-show="section==='yearGroup'"> Select a year group:
                        <button @click="goBack('')" type="button" class="float-right btn btn-sm btn-warning">Change section X</button>
                        <multiselect v-model="form.selected" @input="selectModule()" :options="yearGroup"
                                     track-by="id" label="name"
                                     :close-on-select="true">
                        </multiselect>
                    </div>

                    <div v-if="isModuleLoaded" v-show="section==='module'">
                        Choose module <button @click="goBack('yearGroup')" type="button" class="float-right btn btn-sm btn-warning">Go back X</button>
                        <multiselect v-model="form.selected" @input="selectModuleTextbook()" :options="modules"
                                     track-by="name" label="name"
                                     :close-on-select="true">
                        </multiselect>
                    </div>
                    <div v-else>
                        <clip-loader color="black"/>
                    </div>

                    <div v-show="section==='moduleTextbook'">
                        Choose textbook <button @click="goBack('module')" type="button" class="float-right btn btn-sm btn-warning">Go back X</button>
                        <multiselect v-model="form.selected" :options="moduleTextbooks"
                                     track-by="title" label="title"
                                     :close-on-select="true">
                        </multiselect>

                    </div>
                    <div v-show="section==='extensiveReading'">Select a extensive reading category
                        <button @click="goBack('')" type="button" class="float-right btn btn-sm btn-warning">Change section X</button>
                        <multiselect v-model="form.selected" @input="selectExtensiveReadingTextbook()" :options="extensiveReadingCategories"
                                     track-by="name" label="name"
                                     :close-on-select="true">
                        </multiselect>
                    </div>

                    <div v-show="section==='extensiveReadingTextbook'">Choose textbook
                        <button @click="goBack('extensiveReading')" type="button" class="float-right btn btn-sm btn-warning">Go back X</button>
                        <multiselect v-model="form.selected" :options="extensiveReadingTextbooks"
                                     track-by="title" label="title"
                                     :close-on-select="true">
                        </multiselect>
                    </div>
                    <has-error :form="form" field="selected" /><br/>

                    Update the text [Format - PDF only]
                    <input class="form-control" type="file" id="file"  :class="{ 'is-invalid': form.errors.has('file') }" ref="file" v-on:change="handleFileUpload()"/>
                    <has-error :form="form" field="file" /><br/>
                <sweet-modal ref="success" v-on:close="$router.go(-1)" icon="success">
                    {{successMessage}}
                </sweet-modal>
                <v-button class="form-control" :loading="form.busy" type="success">
                    Update
                </v-button>
            </form>
                </div>
                <div class="form-check mt-4 col-sm-5" v-if="fileName!==''">
                    <label>Current text uploaded:</label>
                    <PDFViewer :fileName="fileName" :path="path" width="200" height="400"/>
                </div>
            </div>
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
        name: "edit",
        components:{
            PDFViewer,
        },
        data: () => ({
            isLoaded: false,
            fileName: '',
            form: new Form({
                title: '',
                description: '',
                selected: [],
                file: '',
            }),
            watch:{
                section: function(){
                    if(this.section===''){
                        this.form.selected = [];
                    }
                }
            },
            successMessage: '',
            errorMessage: '',

            section: '',
            selectedOption: [],
            isModuleLoaded: true,
            extensiveReadingTextbooks: [],
            path: '/lib/pdf/web/viewer.html',
            yearGroup: [],
            year_group: [],
            modules: [],
            selectedExtensiveReadingCategory: [],
            selectedModule: [],
            moduleTextbooks: [],
            extensiveReadingCategories: [],

        }),

        created() {
            let self = this
            axios.get(`/api/text/${this.$route.params.id}/edit`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else {
                        this.isLoaded = true;
                        this.form.title = response.data.title;
                        this.form.description = response.data.description;
                        this.form.selected = response.data.textbook;
                        this.section = response.data.section;
                        this.yearGroup = response.data.yearGroup;
                        this.extensiveReadingCategories = response.data.extensiveReadingCategories;
                        if(this.section === 'moduleTextbook') {
                            this.year_group = response.data.year_group;
                            axios.get(`/api/yearGroup/getModulesForYearGroup/${this.year_group.id}`)
                                .then(response => {
                                    this.isModuleLoaded = false;
                                    this.modules = response.data;
                                    this.isModuleLoaded = true;
                                }).catch(function (response) {
                                //handle error
                                console.log(response);
                            });
                            this.selectedOption.push('Year: ' + this.year_group.name);
                            this.selectedModule = response.data.selectedModule;
                            this.selectedOption.push('Module: '+ this.selectedModule.name);
                            this.moduleTextbooks = this.selectedModule.textbooks;
                        }else{
                            this.selectedExtensiveReadingCategory = response.data.selectedExtensiveReadingCategory;
                            this.selectedOption.push('Extensive Reading Category: ' + this.selectedExtensiveReadingCategory.name);
                            this.extensiveReadingTextbooks = this.selectedExtensiveReadingCategory.textbooks;
                        }
                            this.fileName =`/api/text/pdf/${this.$route.params.id}`;


                    }
                }).catch(function (response) {
                //handle error
                //self.$router.push({name: 'home'})

                console.log(response);
            });
        },
        methods: {
            selectModule(){
                this.isModuleLoaded = false;
                this.section = 'module';
                axios.get(`/api/yearGroup/getModulesForYearGroup/${this.form.selected.id}`)
                    .then(response => {
                        this.modules = response.data;
                        this.isModuleLoaded = true;

                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
                this.selectedOption.push('Year: ' + this.form.selected.name);
                this.form.selected = [];
            },
            selectModuleTextbook(){
                this.section = 'moduleTextbook';
                this.moduleTextbooks = this.form.selected.textbooks;
                this.selectedOption.push('Module: ' + this.form.selected.name);
                this.form.selected = [];
            },
            selectExtensiveReadingTextbook(){
                this.section = 'extensiveReadingTextbook';
                this.extensiveReadingTextbooks = this.form.selected.textbooks;
                this.selectedOption.push('Extensive Reading Category: '+this.form.selected.name);
                this.form.selected = [];
            },
            goBack(section){
                this.form.selected = [];
                this.section = section;
                this.selectedOption.pop();
            },
            handleFileUpload(){
                this.form.file = this.$refs.file.files[0];
            },
            async submit() {
                this.form.busy = true;
                let formData = new FormData();

                /*
                    Add the form data we need to submit
                */
                formData.append('file', this.form.file);
                formData.append('title', this.form.title);
                formData.append('description', this.form.description);
                formData.append('selected', JSON.stringify(this.form.selected));
                formData.append('section', this.section);
                formData.append('_method', 'PATCH');

                await this.form.submit('post', `/api/text/${this.$route.params.id}`,{
                    // Transform form data to FormData
                    transformRequest: [function (data, headers) {
                        return formData
                    }]
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