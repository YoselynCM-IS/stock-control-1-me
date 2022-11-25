<template>
    <div>
        <b-row>
            <b-col></b-col>
            <b-col sm="2" class="text-right">
                <b-button variant="success" pill @click="addLibro()" block
                    :disabled="load">
                    <i class="fa fa-plus"></i> Agregar libro
                </b-button>
            </b-col>
        </b-row>
        <!-- AGREGAR LIBRO -->
        <div>
            <div v-if="showAddLibro" class="mt-2 mb-2">
                <b-row>
                    <b-col>
                        <b-input style="text-transform:uppercase;"
                            v-model="queryTitulo" autofocus
                            placeholder="BUSCAR LIBRO" :disabled="edit || load"
                            @keyup="mostrarLibros()"
                        ></b-input>
                        <div class="list-group" v-if="resultslibros.length" id="listaL">
                            <a v-for="(libro, i) in resultslibros"
                                class="list-group-item list-group-item-action" 
                                href="#" v-bind:key="i" 
                                @click="datosLibro(libro)">
                                {{ libro.titulo }}
                            </a>
                        </div>
                    </b-col>
                    <b-col  sm="2">
                        <b-input v-model="form.costo_unitario" :disabled="load"
                            type="number" min="1" max="9999">
                        </b-input>
                    </b-col>
                    <b-col sm="2" class="text-right">
                        <b-button variant="success" pill @click="saveLibro()" block
                            :disabled="load">
                            <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                        </b-button>
                    </b-col>
                </b-row>
            </div>
            <b-alert :show="dismissCountDown" dismissible variant="success"
                @dismissed="dismissCountDown=0" @dismiss-count-down="countDownChanged"
                class="mt-2 mb-2">
                <i class="fa fa-check"></i> El libro se {{ !edit ? 'agrego':'actualizo' }} correctamente.
            </b-alert>
        </div>
        <b-alert v-if="deleteConfirm" show dismissible variant="danger"
            class="text-center mt-2">
            ¿Seguro de eliminar el libro?
            <b-button variant="danger" pill size="sm" @click="confirmDelete()"
                :disabled="load">
                SI
            </b-button>
        </b-alert>
        <div v-if="!load" class="mt-2 mb-2">
            <b-table v-if="libros.length > 0" :items="libros" :fields="fields">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
                <template v-slot:cell(actions)="row">
                    <b-button variant="warning" pill size="sm" class="text-white"
                        @click="editLibro(row.item, row.index)" :disabled="load">
                        <i class="fa fa-pencil"></i>
                    </b-button>
                    <b-button variant="danger" pill size="sm"
                        @click="deleteLibro(row.item, row.index)" :disabled="load">
                        <i class="fa fa-close"></i>
                    </b-button>
                </template>
            </b-table>
            <b-alert v-else show variant="secondary">
                <i class="fa fa-warning"></i> No se encontraron registros.
            </b-alert>
        </div>
        <load-component v-else></load-component>
    </div>
</template>

<script>
import getLibros from '../../mixins/getLibros';
export default {
    props: ['cliente_id'],
    mixins: [getLibros],
    data(){
        return {
            queryTitulo: null,
            form: {
                cliente_id: null,
                libro_id: null,
                costo_unitario: 0
            },
            showAddLibro: false,
            dismissSecs: 3,
            dismissCountDown: 0,
            libros: [],
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'titulo', label: 'Libro'},
                {key: 'pivot.costo_unitario', label: 'Costo unitario'},
                {key: 'actions', label: 'Editar/Eliminar'}
            ],
            edit: false,
            position: null,
            deleteConfirm: false,
            load:false
        }
    },
    created: function(){
        this.get_libros();
    },
    methods: {
        get_libros(){
            this.load = true;
            axios.get('/clientes/get_libros', {params: {cliente_id: this.cliente_id}}).then(response => {
                this.libros = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        countDownChanged(dismissCountDown) {
            this.dismissCountDown = dismissCountDown
        },
        mostrarLibros(){
            if(this.queryTitulo.length > 0){
                this.getLibros(this.queryTitulo);
            } else{
                this.resultslibros = [];
            }
        },
        datosLibro(libro){
            this.form.libro_id = libro.id;
            this.queryTitulo = libro.titulo;
            this.resultslibros = [];
        },
        addLibro(){
            this.showAddLibro = true;
            this.deleteConfirm = false;
            this.form.cliente_id = this.cliente_id;
            this.form.libro_id = null;
            this.form.costo_unitario = 0;
            this.queryTitulo = null;
            this.edit = false;
        },
        saveLibro(){
            this.load = true;
            if(!this.edit){
                axios.post('/clientes/save_libro', this.form).then(response => {
                    this.dismissCountDown = this.dismissSecs;
                    this.showAddLibro = false;
                    this.get_libros();
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            } else {
                axios.put('/clientes/update_libro', this.form).then(response => {
                    this.dismissCountDown = this.dismissSecs;
                    this.showAddLibro = false;
                    this.libros[this.position].pivot.costo_unitario = response.data;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            }
        },
        editLibro(libro, position){
            this.showAddLibro = true;
            this.datos_libro(libro);
            this.queryTitulo = libro.titulo;
            this.edit = true;
            this.position = position;
        },
        datos_libro(libro){
            this.form.cliente_id = this.cliente_id;
            this.form.libro_id = libro.id;
            this.form.costo_unitario = libro.pivot.costo_unitario;
        },
        deleteLibro(libro, position){
            this.datos_libro(libro);
            this.position = position;
            this.deleteConfirm = true;
            this.showAddLibro = false;
        },
        confirmDelete(){
            this.load = true;
            axios.delete('/clientes/delete_libro', {params: {cliente_id: this.form.cliente_id, libro_id: this.form.libro_id}}).then(response => {
                this.libros.splice(this.position, 1);
                this.deleteConfirm = false;
                this.load = false;
            }).catch(error => {
                this.load = false;
            }); 
        }
    }
}
</script>

<style>

</style>