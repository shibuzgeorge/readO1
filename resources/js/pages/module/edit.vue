<template>
    <card v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">Edit Module</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Module Name:          </label> <input class="form-control" v-model="form.module_name" :class="{ 'is-invalid': form.errors.has('module_name') }" name="name" type="text" value=""/>
                <has-error :form="form" field="module_name" /><br/>
                <label>Module Code:     </label> <input class="form-control" v-model="form.module_code" :class="{ 'is-invalid': form.errors.has('module_code') }" name="code" type="text" value=""/>
                <has-error :form="form" field="module_code" /><br/>
                <label>Module Year Group:     </label>
                <select class="form-control" v-model="form.module_year" :class="{ 'is-invalid': form.errors.has('module_year') }">
                    <option v-for="year in year_groups" :value="year.id" :key="year.id">
                        {{ year.name }}
                    </option>
                </select>
                <has-error :form="form" field="module_year" /><br/>
                <div v-if="form.textbooks.length > 0">
                <label>Current Textbooks:     </label>
                <ul>
                    <li v-for="textbook in form.textbooks">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="exampleCheck1" :value="textbook.id" v-model="form.checked">
                            <label class="form-check-label" for="exampleCheck1">{{textbook.title}} - {{textbook.description}}</label>
                        </div>
                    </li>
                </ul>
                </div>
                <div v-if="form.unassigned.length > 0">
                <label>Add any of these unassigned Textbooks?     </label>
                <ul>
                    <li v-for="textbook in form.unassigned">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" :value="textbook.id" v-model="form.checked">
                            <label class="form-check-label" for="exampleCheck1">{{textbook.title}} - {{textbook.description}}</label>
                        </div>
                    </li>
                </ul>
                </div>
            </div>
            <sweet-modal ref="success" v-on:close="$router.go(-1)" icon="success">
                {{successMessage}}
            </sweet-modal>
            <v-button class="form-control" :loading="form.busy" type="success">
                Edit
            </v-button>
        </form>
    </card>
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
        name: "edit",
        middleware: 'admin_plus_module_tutor',
        data: () => ({
            isLoaded: false,
            form: new Form({
                module_name: '',
                module_code: '',
                module_year: '',
                textbooks: '',
                checked: [],
                unassigned: '',
            }),
            errorMessage: '',
            successMessage: '',
            year_groups: '',

        }),
        created() {
                axios.get('/api/yearGroup/')
                    .then(response => {
                        this.year_groups = response.data;
                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });

            let self = this
            axios.get(`/api/module/${this.$route.params.id}/edit`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else {
                        this.isLoaded = true;
                        this.form.module_name = response.data.name;
                        this.form.module_code = response.data.code;
                        this.form.module_year = response.data.year.id;
                        this.form.textbooks = response.data.textbooks;
                        this.form.unassigned = response.data.unassigned;
                        this.form.textbooks.forEach(function (textbook) {
                            self.form.checked.push(textbook.id);
                        });

                    }
                }).catch(function (response) {
                //handle error
                self.$router.push({name: 'module.index'})

                console.log(response);
            });
        },
        methods:{
            async submit(){
                await this.form.patch(`/api/module/${this.$route.params.id}`)
                    .then(response => {
                        this.successMessage = response.data.Success;
                        this.$refs.success.open();
                    }).catch(function (response) {
                        //handle error
                        self.$router.push({name: 'module.index'})

                        console.log(response);
                    });

            }
        }


    }
</script>

<style scoped>

</style>