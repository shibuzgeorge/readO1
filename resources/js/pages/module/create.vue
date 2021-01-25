<template>
    <card>
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Create Module
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </h5>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Module Name:           </label> <input class="form-control" v-model="form.module_name" type="text" value="" required/><br/>
                <label>Module Code:     </label> <input class="form-control" v-model="form.module_code" type="text" value="" required/><br/>
                <label>Module Year Group:     </label> <input class="form-control" v-model="form.module_year" type="text" value="" required/><br/>
                <br/>
            </div>
            <sweet-modal ref="success" v-on:close="$router.push({name: 'module.v.index'})" icon="success">
                {{successMessage}}
            </sweet-modal>
            <v-button class="form-control" :loading="form.busy" type="success">
                Add
            </v-button>
        </form>
    </card>
</template>

<script>
    import axios from 'axios'
    import Form from 'vform'
    import Swal from 'sweetalert2'
    export default {
        name: "create",
        middleware: 'admin_plus_module_tutor',
        data: () => ({
            isLoaded: false,
            form: new Form({
                module_name: '',
                module_code: '',
                module_year: ''
            }),
            successMessage: '',

        }),
        methods:{
            async submit() {
                await axios.post('/api/module/v', {
                    module_name: this.form.module_name,
                    module_code: this.form.module_code,
                    module_year: this.form.module_year,
                })
                    .then(response => {
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