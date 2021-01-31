<template>
    <div v-if="isLoaded">
    <card>
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Edit textbook/document
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </h5>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)" enctype="multipart/form-data">
            <div class="form-check mt-4">
                <label>Title:           </label> <input class="form-control" v-model="form.title" type="text" value="" required/><br/>
                <label>Description:     </label> <input class="form-control" v-model="form.description" type="text" value="" required/><br/>
                <label>Change module? </label>
                <select class="form-control" v-model="form.selected">
                    <option v-for="module in modules" :value="module.id" :key="module.id">
                        ({{ module.module_code }}) {{ module.name }} - {{ module.module_year }}
                    </option>
                </select><br/>
                <input class="form-control" type="file" id="file" ref="file" v-on:change="handleFileUpload()"/><br/>
            </div>
            <sweet-modal ref="success" v-on:close="$router.go(-1)" icon="success">
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

    export default {
        middleware: 'admin_plus_module_tutor',
        name: "edit",
        data: () => ({
            isLoaded: false,
            form: new Form({
                title: '',
                description: '',
                selected: '',
            }),
            file: '',
            module: '',
            successMessage: '',
            errorMessage: '',
            modules: '',
        }),

        created() {
                axios.get('/api/module/')
                    .then(response => {
                        this.modules = response.data;

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
                        this.isLoaded = true;
                        this.form.title = response.data.title;
                        this.form.description = response.data.description;
                        this.module = response.data.module;
                        this.form.selected = this.module.id;
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
            async submit() {
                this.form.busy = true;
                let formData = new FormData();

                /*
                    Add the form data we need to submit
                */
                formData.append('file', this.file);
                formData.append('title', this.form.title);
                formData.append('description', this.form.description);
                formData.append('module_id', this.form.selected);
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

<style scoped>

</style>