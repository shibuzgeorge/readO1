<template>
    <card>
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Add Year Group
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </h5>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Add Year Group:           </label> <input class="form-control" v-model="form.year_group" type="text" value="" required/><br/>
            </div>
            <sweet-modal ref="success" v-on:close="$router.push({name: 'module.v.index'})" icon="success">
                {{successMessage}}
            </sweet-modal>
            Current Year Groups:
            <li v-for="year in current_year_groups">
                {{year.name}}
            </li>
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
        name: "addYearGroup",
        middleware: 'admin',
        data: () => ({
            isLoaded: false,
            form: new Form({
                year_group: '',

            }),
            successMessage: '',
            current_year_groups: '',
        }),
        created(){
            axios.get('/api/module/v/create')
                .then(response => {
                    this.current_year_groups = response.data.year_groups;
                }).catch(function (response) {
                //handle error
                console.log(response);
            });
        },
        methods:{
            async submit() {
                await axios.post('/api/module/addYearGroup', {
                    year_group: this.form.year_group,
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