<template>
    <div>
        <check-connection-component></check-connection-component>
        <b-row>
            <b-col sm="7">
                <!-- BUSCAR CLIENTE POR NOMBRE -->
                <b-row>
                    <b-col class="text-right" sm="2">Cliente</b-col>
                    <b-col sm="10">
                        <b-input style="text-transform:uppercase;" v-model="queryCliente" 
                            @keyup="http_byname()"></b-input>
                    </b-col>
                </b-row>
            </b-col>
            <b-col class="text-right" sm="2">
                <b-button href="/descargar_clientes" variant="dark"><i class="fa fa-download"></i> Lista</b-button>
            </b-col>
            <!-- AGREGAR NUEVO CLIENTE -->
            <b-col class="text-right" sm="3">
                <b-button v-if="(role_id === 1 || role_id === 2 || role_id == 6)" variant="success" v-b-modal.modal-nuevoCliente><i class="fa fa-plus"></i> Agregar cliente</b-button>
            </b-col>
        </b-row>
        <hr>
        <!-- PAGINACIÓN -->
        <pagination size="default" :limit="1" :data="clientesData" 
            @pagination-change-page="getResults">
            <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
            <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
        </pagination>
        <div v-if="!loadRegisters">
            <!-- LISTADO DE CLIENTES -->
            <b-table v-if="clientes.length > 0"
                responsive hover :items="clientes" :fields="fields">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
                <template v-slot:cell(editar)="row">
                    <b-button 
                        v-if="role_id === 1 || role_id === 2 || role_id == 6" 
                        v-b-modal.modal-editarCliente 
                        variant="warning" 
                        style="color: white;"
                        @click="editarCliente(row.item, row.index)">
                        <i class="fa fa-pencil"></i>
                    </b-button>
                </template>
                <template v-slot:cell(detalles)="row">
                    <b-button variant="info" v-b-modal.modal-detalles @click="showDetails(row.item)">Detalles</b-button>
                </template>
            </b-table>
            <b-alert v-else show variant="secondary">
                <i class="fa fa-warning"></i> No se encontraron registros.
            </b-alert>
        </div>
        <load-component v-else></load-component>
        <!-- MODALS -->
        <!-- MODAL PARA MOSTRAR LOS DETALLES DEL CLIENTE -->
        <b-modal id="modal-detalles" :title="`${form.name ? form.name:''}`" hide-footer>
            <div v-if="!loadDetails">
                <label><b>Contacto:</b> {{ form.contacto }}</label><br>
                <label><b>Dirección:</b> {{ form.direccion }}</label><br>
                <label><b>RFC:</b> {{ form.rfc }}</label><br>
                <label><b>Dirección fiscal:</b> {{ form.fiscal }}</label><br>
                <label><b>Correo:</b> {{ form.email }}</label><br>
                <label><b>Telefono:</b> {{ form.telefono }}</label><br>
                <label><b>Condiciones de pago:</b> {{ form.condiciones_pago }}</label>
            </div>
            <load-component v-else></load-component>
        </b-modal>
        <!-- MODAL PARA AGREGAR CLIENTE -->
        <b-modal id="modal-editarCliente" title="Editar cliente">
            <b-form @submit.prevent="onUpdate()">
                <b-row class="my-1">
                    <b-col align="right">Nombre</b-col>
                    <div class="col-md-7">
                        <b-form-input 
                            id="input-name"
                            style="text-transform:uppercase;"
                            v-model="form.name"
                            :disabled="loaded"
                            required>
                        </b-form-input>
                        <div v-if="errors && errors.name" class="text-danger">{{ errors.name[0] }}</div>
                    </div>
                </b-row>
                <b-row class="my-1">
                    <b-col align="right">Contacto</b-col>
                    <div class="col-md-7">
                        <b-form-input 
                            id="input-name"
                            style="text-transform:uppercase;"
                            v-model="form.contacto"
                            :disabled="loaded">
                        </b-form-input>
                        <div v-if="errors && errors.contacto" class="text-danger">{{ errors.contacto[0] }}</div>
                    </div>
                </b-row>
                <b-row class="my-1">
                    <b-col align="right">Dirección</b-col>
                    <div class="col-md-7">
                        <b-form-input 
                            id="input-direccion"
                            style="text-transform:uppercase;"
                            v-model="form.direccion" 
                            :disabled="loaded"
                            required>
                        </b-form-input>
                        <div v-if="errors && errors.direccion" class="text-danger">{{ errors.direccion[0] }}</div>
                    </div>
                </b-row>
                <b-row class="my-1">
                <b-col align="right">RFC</b-col>
                    <div class="col-md-7">
                        <b-form-input 
                            id="input-rfc"
                            style="text-transform:uppercase;"
                            v-model="form.rfc"
                            :disabled="loaded">
                        </b-form-input>
                        <div v-if="errors && errors.rfc" class="text-danger">{{ errors.rfc[0] }}</div>
                    </div>
                </b-row>
                <b-row class="my-1">
                    <b-col align="right">Dirección fiscal</b-col>
                    <div class="col-md-7">
                        <b-form-input 
                            id="input-fiscal"
                            style="text-transform:uppercase;"
                            v-model="form.fiscal" 
                            :disabled="loaded"
                            required>
                        </b-form-input>
                        <div v-if="errors && errors.fiscal" class="text-danger">{{ errors.fiscal[0] }}</div>
                    </div>
                </b-row>
                <b-row class="my-1">
                    <b-col align="right">Correo electrónico</b-col>
                    <div class="col-md-7">
                        <b-form-input 
                            id="input-email"
                            v-model="form.email"
                            type="email"
                            :disabled="loaded"
                            required>
                        </b-form-input>
                        <div v-if="errors && errors.email" class="text-danger">{{ errors.email[0] }}</div>
                    </div>
                </b-row>
                <b-row class="my-1">
                    <b-col align="right">Teléfono</b-col>
                    <div class="col-md-7">
                        <b-form-input 
                            id="input-telefono"
                            v-model="form.telefono" 
                            :disabled="loaded"
                            required>
                        </b-form-input>
                        <div v-if="errors && errors.telefono" class="text-danger">{{ errors.telefono[0] }}</div>
                    </div>
                </b-row>
                <b-row class="my-1">
                    <b-col align="right">Condiciones de pago</b-col>
                    <div class="col-md-7">
                        <b-form-input 
                            id="input-condiciones_pago"
                            style="text-transform:uppercase;"
                            v-model="form.condiciones_pago" 
                            :disabled="loaded"
                            required>
                        </b-form-input>
                        <div v-if="errors && errors.condiciones_pago" class="text-danger">{{ errors.condiciones_pago[0] }}</div>
                    </div>
                </b-row>
                <hr>
                <div align="right">
                    <b-button type="submit" :disabled="loaded" variant="success">
                        <i class="fa fa-check"></i> {{ !loaded ? 'Actualizar' : 'Actualizando' }} <b-spinner small v-if="loaded"></b-spinner>
                    </b-button>
                </div>
            </b-form>
            <div slot="modal-footer"></div>
        </b-modal>
        <!-- MODAL PARA AGREGAR UN CLIENTE -->
        <b-modal id="modal-nuevoCliente" title="Agregar cliente" hide-footer>
            <new-client-component @actualizarClientes="actClientes"></new-client-component>
        </b-modal>
    </div>
</template>

<script>
    export default {
        props: ['role_id'],
        data() {
            return {
                clientesData: {},
                clientes: [],
                fields: [
                    {key: 'index', label: 'N.'},
                    {key: 'name', label: 'Nombre'},
                    {key: 'email', label: 'Correo'},
                    {key: 'telefono', label: 'Teléfono'},
                    'contacto',
                    {key: 'detalles', label: ''},
                    {key: 'editar', label: ''}
                ],
                form: {
                    id: 0,
                    name: '',
                    contacto: null,
                    email: '',
                    telefono: 0,
                    direccion: '',
                    condiciones_pago: ''

                },
                loaded: false,
                errors: {},
                posicion: null,
                queryCliente: '',
                loadRegisters: false,
                sTName: false,
                loadDetails: false
            }
        },
        mounted: function(){
            this.getResults();
        },
        methods: {
            // OBTENER TODOS LOS CLIENTES
            getResults(page = 1){
                if(!this.sTName)
                    this.http_clientes(page);
                else 
                    this.http_byname(page);
            },
            http_clientes(page = 1){
                this.loadRegisters = true;
                axios.get(`/clientes/index?page=${page}`).then(response => {
                    this.clientesData = response.data;
                    this.clientes = response.data.data;
                    this.loadRegisters = false;
                }).catch(error => {
                    this.loadRegisters = true;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR TODOS LOS CLIENTES
            http_byname(page = 1){
                this.loadRegisters = true;
                axios.get(`/clientes/by_name?page=${page}`, {params: {cliente: this.queryCliente}}).then(response => {
                    this.clientesData = response.data;
                    this.clientes = response.data.data;
                    this.sTName = true;
                    this.loadRegisters = false;
                }).catch(error => {
                    this.loadRegisters = true;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                }); 
            },
            showDetails(cliente){
                this.loadDetails = true;
                this.form = {};
                axios.get('/clientes/show', {params: {cliente_id: cliente.id}}).then(response => {
                    this.assign_datos(response.data);
                    this.loadDetails = false;
                }).catch(error => {
                    this.loadDetails = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // INICIALIZAR PARA EDITAR CLIENTE
            editarCliente(cliente, i){
                this.posicion = i;
                this.form = {};
                this.assign_datos(cliente);
            },
            assign_datos(cliente){
                this.form.id = cliente.id;
                this.form.name = cliente.name;
                this.form.contacto = cliente.contacto;
                this.form.email = cliente.email;
                this.form.telefono = cliente.telefono;
                this.form.direccion = cliente.direccion;
                this.form.condiciones_pago = cliente.condiciones_pago;
                this.form.rfc = cliente.rfc;
                this.form.fiscal = cliente.fiscal;
            },
            // AGREGAR CLIENTE A LA LISTA (EVENTO)
            actClientes(cliente){
                this.clientes.unshift(cliente);
                this.$bvModal.hide('modal-nuevoCliente');
                this.makeToast('success', 'El cliente se agrego correctamente.');
            },
            // ACTUALIZAR DATOS DE CLIENTE
            onUpdate(){
                this.loaded = true;
                axios.put('/editar_cliente', this.form).then(response => {
                    this.loaded = false;
                    this.clientes[this.posicion].name = response.data.name;
                    this.clientes[this.posicion].contacto = response.data.contacto;
                    this.clientes[this.posicion].email = response.data.email;
                    this.clientes[this.posicion].telefono = response.data.telefono;
                    this.$bvModal.hide('modal-editarCliente');
                    this.makeToast('success', 'Cliente actualizado correctamente.');
                })
                .catch(error => {
                    this.loaded = false;
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                    else{
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    }
                });
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            }
        }
    }
</script>