<template>
    <card title="Assign students to modules" v-if="isLoaded">
        <form @submit.prevent="assign" @keydown="form.onKeydown($event)">
            Select a module: <select  class="form-control" v-model="selected" v-on:change="getCurrentModuleTutors()">
            <option>Please select a module</option>
            <option v-for="module in modules" :value="module.id" :key="module.id">
                ({{ module.module_code }}) {{ module.name }} - {{ module.module_year }}
            </option>
        </select><br/>
            <div v-if="showModuleTutors">
                <div v-if="selectModuleTutorLoaded">
                    <h4>Select the module tutors:</h4>
                    <div v-for="user in users">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="exampleCheck1" :value="user.id" v-model="checked">
                            <label class="form-check-label" for="exampleCheck1">{{user.name}}</label>
                        </div>
                    </div>
                    <sweet-modal ref="success" icon="success">
                        {{successMessage}}
                    </sweet-modal>
                    <v-button class="form-control" type="success">
                        Update
                    </v-button>
                </div>
                <div v-else>
                    <clip-loader color="black"/>
                </div>
            </div>
        </form>
    </card>
    <div v-else>
        <clip-loader color="black"/>
    </div>
</template>

<script>
    import axios from 'axios'

    export default {
        middleware: 'admin_plus_module_tutor',
        name: "module.assignStudents",
        data: () => ({
            isLoaded: false,
            selectModuleTutorLoaded: false,
            showModuleTutors: false,
            selected: "Please select a module",
            modules: [],
            moduleUsers: [],
            users: [],
            checked: [],
            successMessage: '',

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
            axios.get('/api/module/getAllModuleTutors')
                .then(response => {
                    this.isLoaded = true;
                    this.users = response.data;

                }).catch(function (response) {
                //handle error
                console.log(response);
            });

        },
        methods: {
            getCurrentModuleTutors(){
                this.showModuleTutors = true;
                this.successMessage = '';
                this.selectModuleTutorLoaded = false;
                let self = this;

                axios.get(`/api/module/getModuleTutorsForModule/${this.selected}`)
                    .then(response => {
                        this.selectModuleTutorLoaded = true;
                        this.moduleUsers = response.data;
                        this.users.forEach(function (value, index) {
                            if (response.data.find(x => x.id === value.id)) {
                                self.checked.push(value.id);
                            } else {
                                self.checked.splice(index);
                            }
                        });

                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
            },
            assign(){
                axios.post(`/api/module/assign/${this.selected}`, this.checked)
                    .then(response => {
                        this.successMessage = response.data.Success;
                        this.$refs.success.open();
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