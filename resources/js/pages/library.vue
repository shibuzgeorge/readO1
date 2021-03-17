<template>

    <card v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">Library</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <div class="search-wrapper"><br/>
            <input type="search" v-model="searchQuery" class="form-control col-lg-4" placeholder="Search by title or description..."
                   aria-label="Search" />
            <select class="form-control col-lg-3 ml-2" v-model="filterByModule">
                <option>Filter by textbook...</option>
                <option v-for="textbook in list_of_textbooks" :value="textbook.title" :key="textbook.title">
                    {{ textbook.title }}
                </option>
            </select>
        </div>
        <div class="wrapper">
            <div class="row">
                <div v-for="textbook in textbooksPaginated" class="card col-sm-3 ml-4 mt-4">
                    <div class="card-body">
                        <router-link :to="{ name: 'textbook.show', params: {id: textbook.id} }">
                            <h5 class="card-title">{{textbook.title}}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">{{textbook.description }}</h6>
                        </router-link>
                        <router-link :to="{ name: 'textbook.edit', params: {id: textbook.id} }">
                            <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
                        </router-link>
                        <a @click="del(textbook.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#" class="card-link">Delete</a><br/>

                    </div>
                </div>
            </div><br/>
            <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate>
        </div>
    </card>
    <div v-else style="text-align: center;">
        <clip-loader :color="loadingColor"/>
    </div>

</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import Swal from 'sweetalert2';
    export default {
        middleware: 'auth',
        data: () => ({
            isLoaded: false,
            textbooks: [],
            textbooksPaginated: [],
            loadingColor: "black",
            searchQuery: null,
            sizePage: 9,
        }),
        computed : {
            ...mapGetters({
                role: 'auth/role'
            }),
            resultQuery() {
                if (this.searchQuery) {
                    return this.textbooks.filter((item) => {
                        return this.searchQuery.toLowerCase().split(' ').every(
                            v => item.title.toLowerCase().includes(v) ||
                                item.description.toLowerCase().includes(v))
                    })
                }else {
                    return this.textbooks;
                }
            }
        },
        created() {

            axios.get('api/textbook/')
                .then(response => {
                    this.isLoaded = true;
                    this.textbooks = response.data

                }).catch(error => {
                console.log(error.response)
            });
        },
        methods: {
            async del(data) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete the textbook?",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then(function (result) {
                    if (result.value) {
                        Swal.fire(
                            'Deleted!',
                            'The textbook has been deleted.',
                            'success'
                        );
                        axios.delete(`/api/textbook/${data}`).then(response => {
                            window.location.reload();
                        })
                    }
                });

            },
            onChangePage(pageOfItems) {
                this.textbooksPaginated = pageOfItems;
            },
        }

    }
</script>
