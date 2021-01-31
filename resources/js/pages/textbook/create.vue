<template>
    <card>
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Create textbook
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </h5>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)" enctype="multipart/form-data">
            <div class="form-check mt-4">
                <label>Title:           </label> <input class="form-control" v-model="form.title" type="text" value="" required/><br/>
                <label>Description:     </label> <input class="form-control" v-model="form.description" type="text" value="" required/><br/>
                <label>Choose a module: </label>
                <select  class="form-control" v-model="selected">
                    <option>Please select a module</option>
                    <option v-for="module in modules" :value="module.id" :key="module.id">
                        ({{ module.module_code }}) {{ module.name }} - {{ module.year_group.name }}
                    </option>
                </select><br/>
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
            selected: "Please select a module",
            modules: [],
            file: ''

        }),
    created() {
        axios.get('/api/textbook/create')
            .then(response => {
                this.isLoaded = true;
                this.modules = response.data;

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
                formData.append('module_id', this.selected);

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


            }
        }

    }
</script>

<style scoped>

</style>