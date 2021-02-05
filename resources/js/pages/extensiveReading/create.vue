<template>
    <card>
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 id="title">Create a category</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Name:           </label> <input class="form-control" :class="{ 'is-invalid': form.errors.has('name') }" v-model="form.name" type="text" name="name" />
                <has-error :form="form" field="name" /><br/>
                <label>Description:     </label> <input class="form-control" :class="{ 'is-invalid': form.errors.has('description') }" v-model="form.description" type="text" name="description" />
                <has-error :form="form" field="description" />
                <br/>
            </div>
            <sweet-modal ref="success" v-on:close="$router.go(-1)" icon="success">
                Successfully Created a category
            </sweet-modal>
            <v-button class="form-control" :loading="form.busy" type="success">
                Create
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
                name: '',
                description: '',
            }),
            categories: '',

        }),
        created() {
            axios.get('/api/extensiveReading/create')
                .then(response => {
                    this.isLoaded = true;
                    this.categories = response.data;

                }).catch(function (response) {
                //handle error
                console.log(response);
            });

        },
        methods: {
            async submit() {
                this.form.busy = true;

                await this.form.post('/api/extensiveReading')
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