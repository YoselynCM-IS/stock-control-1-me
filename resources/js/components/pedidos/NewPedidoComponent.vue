<template>
    <div>
        <b-row>
            <b-col sm="6">
                <b-form-group label="PARA:" label-class="font-weight-bold">
                    <b-input v-model="queryCliente" @keyup="mostrarClientes()" autofocus
                        style="text-transform:uppercase;" :disabled="load" required
                        placeholder="BUSCAR CLIENTE">
                    </b-input>
                    <div class="list-group" v-if="clientes.length" id="listP">
                        <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                            v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                            {{ cliente.name }}
                        </a>
                    </div>
                </b-form-group>
            </b-col>
            <b-col></b-col>
            <b-col sm="2">
                <b-button @click="save_pedido()" class="mt-2" variant="success" pill block
                    :disabled="(load || this.form.total_quantity <= 0 || this.form.cliente_id == null)">
                    <i class="fa fa-check-circle"></i> Guardar
                </b-button>
            </b-col>
        </b-row>
        <b-table :items="form.libros" :fields="fields">
            <template v-slot:cell(index)="data">
                {{ data.index + 1 }}
            </template>
            <template v-slot:cell(quantity)="data">
                {{ data.item.quantity | formatNumber }}
            </template>
            <template v-slot:cell(edit)="data">
                <b-button variant="warning" pill @click="edit_register(data.item, data.index)"
                    :disabled="load">
                    <i v-if="!editar2 || data.index !== position" class="fa fa-edit"></i>
                        <i v-if="editar2 && data.index == position" class="fa fa-spinner"> Editando</i>
                </b-button>
                <b-button variant="danger" pill @click="delete_register(data.item, data.index)"
                    :disabled="load">
                    <i class="fa fa-trash"></i>
                </b-button>
            </template>
            <template #thead-top="row">
                <tr>
                    <th><b>{{ !editar2 ? 'Agregar':'Editar' }}</b></th>
                    <th>ISBN</th>
                    <th>Titulo</th>
                    <th>Cantidad</th>
                    <th></th>
                </tr>
                <tr>
                    <th>
                        <b-button variant="secondary" pill block size="sm"
                            :disabled="(load || registro.libro.id == null)"
                            @click="inicializar_registro()">
                            Limpiar
                        </b-button>
                    </th>
                    <th>
                        <b-input
                            v-model="queryISBN" @keyup="buscarISBN()" :disabled="load"
                        ></b-input>
                        <div class="list-group" v-if="resultsISBNs.length" id="listaL">
                            <a class="list-group-item list-group-item-action" 
                                v-for="(libro, i) in resultsISBNs" v-bind:key="i"
                                @click="datosLibro(libro)" href="#" >
                                {{ libro.ISBN }}
                            </a>
                        </div>
                    </th>
                    <th>
                        <b-input style="text-transform:uppercase;"
                            v-model="queryTitulo" :disabled="load"
                            @keyup="getLibros(queryTitulo)"
                        ></b-input>
                        <div class="list-group" v-if="resultslibros.length" id="listaL">
                            <a class="list-group-item list-group-item-action" 
                                v-for="(libro, i) in resultslibros" v-bind:key="i" 
                                @click="datosLibro(libro)" href="#" >
                                {{ libro.titulo }}
                            </a>
                        </div>
                    </th>
                    <th>
                        <b-input required type="number" v-model="registro.quantity" :disabled="load"></b-input>
                    </th>
                    <th>
                        <b-button variant="success" pill size="sm" 
                            :disabled="(load || registro.libro.id == null)" 
                            @click="save_register()">
                            <i class="fa fa-level-down"></i>
                        </b-button>
                    </th>
                </tr>
                <tr class="mt-5">
                    <th colspan="2"></th>
                    <th class="text-right"><b>Total unidades</b></th>
                    <th>
                        <b>{{ form.total_quantity | formatNumber }}</b>
                    </th>
                    <th></th>
                </tr>
            </template>
        </b-table>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
import getLibros from '../../mixins/getLibros';
import searchCliente from '../../mixins/searchCliente';
import toast from '../../mixins/toast';
export default {
    mixins: [searchCliente, formatNumber, getLibros, toast],
    data(){
        return {
            form: {
                cliente_id: null,
                total_quantity: 0,
                libros: []
            },
            load: false,
            fields: [
                {label: 'N.', key: 'index'},
                {label: 'ISBN', key: 'libro.ISBN'},
                {label: 'Titulo', key: 'libro.titulo'},
                {label: 'Cantidad', key: 'quantity'},
                {label: '', key: 'edit'}
            ],
            editar2: false,
            position: null,
            registro: {
                libro: { id: null, ISBN: '', titulo: ''},
                quantity: 0
            },
            queryTitulo: null
        }
    },
    methods: {
        save_pedido(){
            this.load = true;
            axios.post('/pedido/store', this.form).then(response => {
                swal("OK", "El pedido se guardo correctamente.", "success")
                    .then((value) => { location.reload(); });
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        selectCliente(cliente){
            this.form.cliente_id = cliente.id;
            this.queryCliente = cliente.name;
            this.clientes = [];
        },
        edit_register(register, index){
            this.registro.quantity = register.quantity;
            this.assign_datos(register.libro);
            this.position = index;
            this.editar2 = true;
        },
        delete_register(register, index){
            this.form.libros.splice(index, 1);
            this.form.total_quantity = this.form.total_quantity - register.quantity;
            this.inicializar_registro();
        },
        datosLibro(libro){
            this.assign_datos(libro);
            this.resultslibros = [];
            this.resultsISBNs = [];
        },
        assign_datos(libro){
            this.registro.libro.id = libro.id;
            this.registro.libro.ISBN = libro.ISBN;
            this.registro.libro.titulo = libro.titulo;
            this.queryISBN = libro.ISBN;
            this.queryTitulo = libro.titulo;
        },
        save_register(){
            if(this.registro.libro.id != null && this.registro.quantity > 0){
                if(!this.editar2){
                    this.form.libros.push(this.registro);
                } else{
                    this.form.libros[this.position].quantity = this.registro.quantity;
                    this.form.libros[this.position].libro.id = this.registro.libro.id;
                    this.form.libros[this.position].libro.ISBN = this.registro.libro.ISBN;
                    this.form.libros[this.position].libro.titulo = this.registro.libro.titulo;
                }

                this.inicializar_registro();

                this.form.total_quantity = 0;
                this.form.libros.forEach(registro => {
                    this.form.total_quantity += parseInt(registro.quantity);
                });
            } else {
                this.makeToast('warning', 'Las unidades deben ser mayor a 0');
                this.form.total_quantity = 0;
            }
        },
        inicializar_registro(){
            this.registro = { 
                libro: { id: null, ISBN: '', titulo: ''},
                quantity: 0,
            };
            this.queryISBN = null;
            this.queryTitulo = null;
            this.position = null;
            this.editar2 = false;
        }
    }
}
</script>

<style>

</style>