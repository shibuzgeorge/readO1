<template>
    <card v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">Upload text</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)" enctype="multipart/form-data">
            <div class="form-check mt-4">
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
                    <button @click="goBack('')" type="button" class="float-right btn btn-sm btn-warning">Go back X</button>
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
                    <button @click="goBack('')" type="button" class="float-right btn btn-sm btn-warning">Go back X</button>
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

                Upload a text [required] [Format - PDF only]
                <input class="form-control" type="file" id="file"  :class="{ 'is-invalid': form.errors.has('file') }" ref="file" v-on:change="handleFileUpload()"/><br/>
            </div>
            <sweet-modal ref="success" v-on:close="$router.go(-1)" icon="success">
                Successfully Uploaded
            </sweet-modal>
            <v-button class="form-control" :loading="form.busy" type="success">
                Upload
            </v-button>
        </form>
    </card>
    <div v-else>
        <clip-loader color="black"/>
    </div>

</template>

<script>
    import axios from 'axios'
    import Form from 'vform'

    export default {
        middleware: 'admin_plus_module_tutor',
        name: "test",
        data: () => ({
            isLoaded: false,
            form: new Form({
                title: '',
                description: '',
                selected: [],
                file: ''
            }),
            section: '',
            selectedOption: [],
            isModuleLoaded: true,
            extensiveReadingTextbooks: [],
            yearGroup: [],
            modules: [],
            moduleTextbooks: [],
            extensiveReadingCategories: [],


        }),
        created() {
            axios.get('/api/text/create')
                .then(response => {
                    this.yearGroup = response.data.yearGroup;
                    this.extensiveReadingCategories = response.data.extensiveReadingCategories;
                    this.isLoaded = true;
                }).catch(function (response) {
                //handle error
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

                await this.form.submit('post','/api/text',{
                    // Transform form data to FormData
                    transformRequest: [function (data, headers) {
                    return formData
                }]})
                    .then(response => {
                        console.log(response);
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