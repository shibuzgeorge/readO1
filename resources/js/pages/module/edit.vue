<template>
    <card v-if="isLoaded">
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Edit Module
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </h5>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Module Name:           </label> <input class="form-control" v-model="form.module_name" type="text" value="" required/><br/>
                <label>Module Code:     </label> <input class="form-control" v-model="form.module_code" type="text" value="" required/><br/>
                <label>Module Year Group:     </label>
                <select class="form-control" v-model="form.module_year">
                    <option v-for="year in year_groups" :value="year.id" :key="year.id">
                        {{ year.name }}
                    </option>
                </select><br/>
                <br/>
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