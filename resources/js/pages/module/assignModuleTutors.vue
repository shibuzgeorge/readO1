<template>
    <card v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 id="title">Assign module tutors to modules</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <form @submit.prevent="assign" @keydown="form.onKeydown($event)" class="mt-4">
            <div> Select a year group:
                <multiselect v-model="yearSelected" @input="selectModule()" :options="yearGroup"
                             track-by="id" label="name" :allow-empty="false" deselect-label="Can't remove this value"
                             :close-on-select="true">
                </multiselect>
            </div>

            <div v-if="isModuleLoaded">
                Select a module:
                <multiselect v-model="moduleSelected" @input="getCurrentModuleTutors()" :options="modules"
                             track-by="id" label="name" :allow-empty="false" deselect-label="Can't remove this value" :custom-label="moduleWithCode"
                             :close-on-select="true">
                </multiselect>
            </div>
            <div v-if="!isModuleLoaded && yearSelected !== ''">
                <clip-loader color="black"/>
            </div>
           <br/>
            <div v-if="showModuleTutors">
                <div v-if="selectModuleTutorLoaded">
                    <h4>Select the module tutors:</h4>
                    <br/><input type="search" v-model="searchQuery" class="form-control" placeholder="Search by name"
                           aria-label="Search" /><br/>
                    <div v-for="user in usersPaginated">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="exampleCheck1" :value="user.id" v-model="checked">
                            <label class="form-check-label" for="exampleCheck1">{{user.name}}</label>
                        </div>
                    </div>
                    <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate><br/>
                    <sweet-modal ref="success" icon="success">
                        {{successMessage}}
                    </sweet-modal>
                    <v-button :loading="form.busy" class="form-control" type="success">
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
    import Form from 'vform'
    export default {
        middleware: 'admin',
        name: "module.assignStudents",
        computed: {
            resultQuery() {
                if (this.searchQuery) {
                    return this.users.filter((item) => {
                        return this.searchQuery.toLowerCase().split(' ').every(
                            v => item.name.toLowerCase().includes(v))
                    })
                }else {
                    return this.users;
                }
            }
        },
        data: () => ({
            isLoaded: false,
            selectModuleTutorLoaded: false,
            showModuleTutors: false,
            yearSelected: '',
            moduleSelected: '',
            modules: [],
            moduleUsers: [],
            form: new Form({
                busy: false
            }),
            users: [],
            usersPaginated: [],
            yearGroup: [],
            checked: [],
            successMessage: '',
            isModuleLoaded: false,
            searchQuery: null,
            sizePage: 9,

        }),
        created() {
            axios.get('/api/yearGroup')
                .then(response => {
                    this.yearGroup = response.data;

                }).catch(function (response) {
                //handle error
                console.log(response);
            });

            axios.get('/api/module/getAllModuleTutors')
                .then(response => {
                    this.users = response.data;
                    this.isLoaded = true;
                }).catch(function (response) {
                //handle error
                console.log(response);
            });

        },
        methods: {
            selectModule(){
                this.showModuleTutors = false;
                this.moduleSelected = [];
                this.isModuleLoaded = false;
                axios.get(`/api/yearGroup/getAllModulesForYearGroup/${this.yearSelected.id}`)
                    .then(response => {
                        this.modules = response.data;
                        this.isModuleLoaded = true;

                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });

            },
            getCurrentModuleTutors(){
                this.showModuleTutors = true;
                this.successMessage = '';
                this.selectModuleTutorLoaded = false;
                let self = this;
                self.checked = [];

                axios.get(`/api/module/getUsersForModule/${this.moduleSelected.id}`)
                    .then(response => {
                        this.selectModuleTutorLoaded = true;
                        this.moduleUsers = response.data;
                        this.users.forEach(function (value, index) {
                            if (self.moduleUsers.find(x => x.id === value.id)) {
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
            async assign(){
                this.form.busy = true;
                await axios.post(`/api/module/assignModuleTutors/${this.moduleSelected.id}`, this.checked)
                    .then(response => {
                        this.successMessage = response.data.Success;
                        this.$refs.success.open();
                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
                this.form.busy = false;
            },
            moduleWithCode ({ name, module_code }) {
                return `(${module_code}) ${name}`
            },
            onChangePage(pageOfItems) {
                this.usersPaginated = pageOfItems;
            },
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>