<template>
    <card title="Assign students to modules" v-if="isLoaded">
        <form @submit.prevent="assign" @keydown="form.onKeydown($event)">
    Select a module: <select  class="form-control" v-model="selected" v-on:change="getCurrentUser()">
        <option>Please select a module</option>
        <option v-for="module in modules" :value="module.id" :key="module.id">
            ({{ module.module_code }}) {{ module.name }} - {{ module.module_year }}
        </option>
    </select><br/>
        Select the students:
        <div v-for="user in users">
            <div class="form-check">
            <input type="checkbox" class="form-check-input" id="exampleCheck1" :value="user.id" v-model="checked">
            <label class="form-check-label" for="exampleCheck1">{{user.name}}</label>
            </div>
        </div>
        <v-button class="form-control" :loading="form.busy" type="success">
            Assign
        </v-button>
        </form>
    </card>
    <div v-else>
        <clip-loader :color="loadingColor"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import Form from 'vform'
    import ClipLoader from "vue-spinner/src/ClipLoader";
    export default {
        middleware: 'admin_plus_module_tutor',
        components: {ClipLoader},
        name: "module.assignStudents",
        data: () => ({
            isLoaded: false,
            loadingColor: "black",
            form: new Form({

            }),

            selected: "Please select a module",
            modules: [],
            moduleUsers: [],
            users: [],
            checked: [],

        }),
        created() {
            axios.get('/api/module/allModules')
                .then(response => {
                    this.isLoaded = true;
                    this.modules = response.data;

                }).catch(function (response) {
                //handle error
                console.log(response);
            });
            axios.get('/api/module/getAllStudents')
                .then(response => {
                    this.isLoaded = true;
                    this.users = response.data;

                }).catch(function (response) {
                //handle error
                console.log(response);
            });

        },
        methods: {
            getCurrentUser(){
                let self = this;
                axios.get(`/api/module/getStudentsForModule/${this.selected}`)
                    .then(response => {
                        this.moduleUsers = response.data;
                        this.users.forEach(function (value,index)
                        {
                            if(response.data.find(x=>x.id === value.id)){
                                self.checked.push(value.id);
                            }else{
                                self.checked.splice(index);
                            }
                        });



                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
            },
            assign(){
                console.log(this.checked);
                axios.post(`/api/module/assign/${this.selected}/${this.checked}`)
                    .then(response => {

                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
            }
        }
    }
</script>

<style scoped>

</style>