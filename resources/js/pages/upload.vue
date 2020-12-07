<template>
    <card title="Upload textbook/document">
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check">
                <label>Title:           </label> <input class="form-control" v-model="form.title" type="text" value=""/><br/>
                <label>Description:     </label> <input class="form-control" v-model="form.description" type="text" value=""/><br/>
                <label>Choose a module: </label>
                <select  class="form-control" v-model="selected">
                    <option>Please select a module</option>
                    <option v-for="module in modules" :value="module.name" :key="module.id">
                        ({{ module.module_code }}) {{ module.name }} - {{ module.module_year }}
                    </option>
                </select><br/>
                <input class="form-control" type="file" id="file" ref="file" v-on:change="handleFileUpload()"/><br/>
            </div>

            <v-button class="form-control" :loading="form.busy" type="success">
                {{ $t('Upload') }}
            </v-button>
        </form>
    </card>

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
                description: ''
            }),
            selected: "Please select a module",
            modules: []

        }),
    created() {
        axios.get('/api/upload')
            .then(response => {
                this.isLoaded = true;
                this.modules = response.data;

            }).catch(function (response) {
            //handle error
            console.log(response);
        });

    },
        methods: {
            async submit() {
                // Submit the form.

            }
        }

    }
</script>

<style scoped>

</style>