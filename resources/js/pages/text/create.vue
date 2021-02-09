<template>
    <card>
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">Upload text</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)" enctype="multipart/form-data">
            <div class="form-check mt-4">
                <label>Title:           </label> <input class="form-control" v-model="form.title" type="text" value="" required/><br/>
                <label>Description:     </label> <input class="form-control" v-model="form.description" type="text" value="" required/><br/>
                <label>Choose a textbook: </label>
                <select  class="form-control" v-model="selected">
                    <option>Please select a textbook</option>
                    <option v-for="textbook in textbooks" :value="textbook.id" :key="textbook.id">
                        {{ textbook.title }}- {{textbook.id}}
                    </option>
                </select><br/>

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
        name: "test",
        data: () => ({
            isLoaded: false,
            form: new Form({
                title: '',
                description: ''
            }),
            selected: "Please select a textbook",
            textbooks: [],
            file: ''

        }),
        created() {
            axios.get('/api/text/create')
                .then(response => {
                    this.isLoaded = true;
                    this.textbooks = response.data;

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
                formData.append('textbook_id', this.selected);

                await axios.post('/api/text',formData,
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