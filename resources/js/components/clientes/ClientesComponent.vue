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
                <b-button href="/descargar_clientes" variant="dark" pill><i class="fa fa-download"></i> Lista</b-button>
            </b-col>
            <!-- AGREGAR NUEVO CLIENTE -->
            <b-col class="text-right" sm="3">
                <b-button v-if="(role_id === 1 || role_id === 2 || role_id == 6)" 
                            variant="success" @click="newCliente()" pill>
                    <i class="fa fa-plus"></i> Agregar cliente
                </b-button>
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
                    <b-button v-if="role_id === 1 || role_id === 2 || role_id == 6" 
                        v-b-modal.modal-editarCliente variant="warning" 
                        style="color: white;" pill
                        @click="editarCliente(row.item, row.index)">
                        <i class="fa fa-pencil"></i>
                    </b-button>
                </template>
                <template v-slot:cell(detalles)="row">
                    <b-button variant="info" v-b-modal.modal-detalles pill
                        @click="showDetails(row.item)">Detalles</b-button>
                </template>
            </b-table>
            <b-alert v-else show variant="secondary">
                <i class="fa fa-warning"></i> No se encontraron registros.
            </b-alert>
        </div>
        <load-component v-else></load-component>
        <!-- MODALS -->
        <!-- MODAL PARA MOSTRAR LOS DETALLES DEL CLIENTE -->
        <b-modal id="modal-detalles" title="Información del cliente" hide-footer size="xl">
            <div v-if="!loadDetails" class="mb-5">
                <b-row>
                    <b-col>
                        <b-row class="my-1">
                            <b-col align="right"><b>Tipo de cliente:</b></b-col>
                            <div class="col-md-7">{{datosCliente.tipo}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right">
                                <b>{{(datosCliente.tipo == null || datosCliente.tipo == 'PLANTEL') ? 'Plantel':'Distribuidor'}}:</b>
                            </b-col>
                            <div class="col-md-7">{{datosCliente.name}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right">
                                <b>{{(datosCliente.tipo == null || datosCliente.tipo == 'PLANTEL') ? 'Coordinador':'Comunicarse con'}}:</b>
                            </b-col>
                            <div class="col-md-7">{{datosCliente.contacto}}</div>
                        </b-row>
                    </b-col>
                    <b-col>
                        <b-row class="my-1">
                            <b-col align="right"><b>Responsable del cliente:</b></b-col>
                            <div class="col-md-7">{{datosCliente.user ? datosCliente.user.name:''}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Condiciones de pago:</b></b-col>
                            <div class="col-md-7">{{datosCliente.condiciones_pago}}</div>
                        </b-row>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col>
                        <b-row class="my-1">
                            <b-col align="right"><b>Dirección:</b></b-col>
                            <div class="col-md-7">{{datosCliente.direccion}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Estado:</b></b-col>
                            <div class="col-md-7">{{datosCliente.estado ? datosCliente.estado.estado:''}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Teléfono:</b></b-col>
                            <div class="col-md-7">{{datosCliente.telefono}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Teléfono (oficina):</b></b-col>
                            <div class="col-md-7">{{datosCliente.tel_oficina}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Correo electrónico:</b></b-col>
                            <div class="col-md-7">{{datosCliente.email}}</div>
                        </b-row>
                    </b-col>
                    <b-col>
                        <b-row class="my-1">
                            <b-col align="right"><b>Dirección fiscal:</b></b-col>
                            <div class="col-md-7">{{datosCliente.fiscal}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>RFC:</b></b-col>
                            <div class="col-md-7">{{datosCliente.rfc}}</div>
                        </b-row>
                    </b-col>
                </b-row>
            </div>
            <load-component v-else></load-component>
        </b-modal>
        <!-- MODAL PARA AGREGAR CLIENTE -->
        <b-modal id="modal-editarCliente" title="Editar cliente" hide-footer size="xl">
            <new-client-component :form="form" :edit="edit" @actualizarClientes="actClientes"></new-client-component>
        </b-modal>
        <!-- MODAL PARA AGREGAR UN CLIENTE -->
        <b-modal id="modal-nuevoCliente" title="Nuevo cliente" hide-footer size="xl">
            <new-client-component :form="form" :edit="edit" @actualizarClientes="actClientes"></new-client-component>
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
                    {key: 'tipo', label: 'Tipo'},
                    {key: 'estado.estado', label: 'Estado'},
                    {key: 'name', label: 'Cliente'},
                    {key: 'email', label: 'Correo'},
                    {key: 'telefono', label: 'Teléfono'},
                    {key: 'user.name', label: 'Responsable'},
                    {key: 'detalles', label: ''},
                    {key: 'editar', label: ''}
                ],
                form: {
                    tipo: null,
                    name: null,
                    contacto: null,
                    user_id: null,
                    condiciones_pago: null,
                    direccion: null,
                    estado_id: null,
                    telefono: null,
                    tel_oficina: null,
                    email: null,
                    fiscal: null,
                    rfc: null
                },
                datosCliente: {},
                loaded: false,
                errors: {},
                posicion: null,
                queryCliente: '',
                loadRegisters: false,
                sTName: false,
                loadDetails: false,
                edit: false
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
                axios.get('/clientes/show', {params: {cliente_id: cliente.id}}).then(response => {
                    this.datosCliente = response.data;
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
                this.edit = true;
                console.log(cliente.tipo);
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
                this.form.tipo = cliente.tipo;
                this.form.user_id = cliente.user_id;
                this.form.estado_id = cliente.estado_id;
                this.form.tel_oficina = cliente.tel_oficina;
            },
            // AGREGAR CLIENTE A LA LISTA (EVENTO)
            actClientes(cliente){
                if(!this.edit){
                    this.$bvModal.hide('modal-nuevoCliente');
                    swal("OK", "El cliente se guardo correctamente.", "success")
                        .then((value) => { location.reload(); });
                } else {
                    this.$bvModal.hide('modal-editarCliente');
                    swal("OK", "El cliente se actualizo correctamente.", "success")
                        .then((value) => { location.reload(); });
                }
            },
            newCliente(){
                this.edit = false;
                this.form = {
                    tipo: null,
                    name: null,
                    contacto: null,
                    user_id: null,
                    condiciones_pago: null,
                    direccion: null,
                    estado_id: null,
                    telefono: null,
                    tel_oficina: null,
                    email: null,
                    fiscal: null,
                    rfc: null
                };
                this.$bvModal.show('modal-editarCliente');
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