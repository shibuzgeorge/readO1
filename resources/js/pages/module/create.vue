<template>
    <div class="container-fluid" v-if="isLoaded">
        <div class="row">
            <card class="col-6 d-flex flex-column h-100">
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Create Module
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </h5>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Module Name:           </label> <input class="form-control" v-model="form.module_name" type="text" value="" required/><br/>
                <label>Module Code:     </label> <input class="form-control" v-model="form.module_code" type="text" value="" required/><br/>
                <label>Module Year Group:     </label>
                <select class="form-control" v-model="form.module_year">
                    <option>Please select a year group</option>
                    <option v-for="year in year_groups" :value="year.id" :key="year.id">
                        {{ year.name }}
                    </option>
                </select><br/>
                <br/>
            </div>
            <sweet-modal ref="success" v-on:close="$router.push({name: 'module.index' , query: { page: 'last' }})" icon="success">
                {{successMessage}}
            </sweet-modal>
            <v-button class="form-control" :loading="form.busy" type="success">
                Add
            </v-button>
        </form>
    </card>
            <card class="col-5 ml-4 d-flex flex-column h-100">
                <h6 class="card-header d-flex align-items-center">
                    Current Modules:
                </h6>
                <li class="mt-2" style="font-size: small;" v-for="module in modules">
                    {{module.name}} - {{module.module_code}} - {{module.year_group.name}}
                </li>
            </card>
        </div>
    </div>
    <div v-else>
        <clip-loader color="black"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import Form from 'vform'

    export default {
        name: "create",
        middleware: 'admin_plus_module_tutor',
        data: () => ({
            isLoaded: false,
            form: new Form({
                module_name: '',
                module_code: '',
                module_year: 'Please select a year group',

            }),
            successMessage: '',
            year_groups: '',
            modules: '',

        }),
        created(){
            axios.get('/api/module/create')
                .then(response => {
                    this.isLoaded = true;
                    this.modules = response.data.modules;
                    this.year_groups = response.data.year_groups;
                }).catch(function (response) {
                //handle error
                console.log(response);
            });
        },
        methods:{
            async submit() {
                await axios.post('/api/module', {
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