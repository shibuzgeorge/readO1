<template>
    <div class="container-fluid" v-if="isLoaded">
        <div class="row">
            <card class="col-6 d-flex flex-column h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 id="title">Create Module</h5>
                    <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
                </div>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Module Name:           </label> <input class="form-control" v-model="form.module_name" :class="{ 'is-invalid': form.errors.has('module_name') }" name="name" type="text" value=""/>
                <has-error :form="form" field="module_name" /><br/>
                <label>Module Code:     </label> <input class="form-control" v-model="form.module_code" :class="{ 'is-invalid': form.errors.has('module_code') }" name="code" type="text" value=""/>
                <has-error :form="form" field="module_code" /><br/>
                <label>Module Year Group:     </label>
                <select class="form-control" name="year_group" :class="{ 'is-invalid': form.errors.has('module_year') }" v-model="form.module_year">
                    <option>Please select a year group</option>
                    <option v-for="year in year_groups" :value="year.id" :key="year.id">
                        {{ year.name }}
                    </option>
                </select>
                <has-error :form="form" field="module_year" /><br/>
                <div v-if="form.unassigned.length > 0">
                    <label>Want to add of these unassigned textbooks?     </label>
                <div>
                    <div v-for="textbook in form.unassigned">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" :value="textbook.id" v-model="form.checked">
                            <label class="form-check-label">{{textbook.title}} - {{textbook.description}}</label>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <sweet-modal ref="success" v-on:close="$router.push({name: 'module.index'})" icon="success">
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
                unassigned: '',
                checked: [],
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
                    this.form.unassigned = response.data.unassigned;
                }).catch(function (response) {
                //handle error
                console.log(response);
            });
        },
        methods:{
            async submit() {
                this.form.busy = true;
                await this.form.post('/api/module')
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