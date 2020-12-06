<template>
    <card :title="'Upload textbook/document'">
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check">
                <label>Title:           </label> <input class="form-control" v-model="form.title" type="text" value=""/><br/>
                <label>Description:     </label> <input class="form-control" v-model="form.description" type="text" value=""/><br/>
                <label>Choose a module: </label>
                <select  class="form-control" v-model="selected">
                    <option v-for="module in modules" :value="module.val" :key="module.id">
                        {{ module.val }}
                    </option>
                </select><br/>
                <input class="form-control" type="file" id="file" ref="file" v-on:change="handleFileUpload()"/><br/>
            </div>

            <v-button class="form-control" :loading="form.busy" type="success">
                {{ $t('Upload') }}
            </v-button>
        </form>
    </card>

</template>

<script>
    import axios from 'axios'
    import Form from 'vform'

    export default {
        middleware: 'admin',
        name: "edit",
        data: () => ({
            form: new Form({
                title: '',
                description: ''
            }),
            selected: 'item1',
            modules: {
                1: {id: 1, val: 'CS3160'},
                2: {id: 2, val: 'CS2010'},
                3: {id: 3, val: 'CS1020'},
            }

        }),
        methods: {
            async submit() {
                // Submit the form.

            }
        }

    }
</script>

<style scoped>

</style>