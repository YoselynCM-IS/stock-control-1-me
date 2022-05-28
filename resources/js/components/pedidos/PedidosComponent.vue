<template>
    <div>
        <div v-if="!openPedido && !openDetails">
            <b-row class="mb-2">
                <b-col>
                    <pagination size="default" :limit="1" :data="pedidosData" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col sm="3">
                    <label><b>Buscar por fecha</b></label>
                    <input class="form-control" type="date" v-model="date" 
                       @change="change_date()" :disabled="load">
                </b-col>
                <b-col sm="3">
                    <label><b>Buscar por proveedor</b></label>
                    <b-form-select v-model="provider" :options="proveedores" 
                        @change="change_provider()" :disabled="load">
                    </b-form-select>
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button variant="success" pill @click="newPedido()" 
                    :disabled="load" class="mt-4" v-if="role_id === 1 || role_id == 2 || role_id == 6">
                        <i class="fa fa-plus-circle"></i> Nuevo pedido
                    </b-button>
                </b-col>
            </b-row>
            <div v-if="!load">
                <b-table v-if="pedidos.length > 0" :items="pedidos" :fields="fieldsPedidos"
                    :busy="load">
                    <template v-slot:cell(index)="data">
                        {{ data.index + 1 }}
                    </template>
                    <template v-slot:cell(total_bill)="data">
                        ${{ data.item.total_bill | formatNumber }}
                    </template>
                    <template v-slot:cell(date)="data">
                        {{ data.item.date | moment }}
                    </template>
                    <template v-slot:cell(details)="data" pill>
                        <b-button variant="info" pill @click="details_pedido(data.item, data.index)" :disabled="load">
                            <i class="fa fa-exclamation-circle"></i>
                        </b-button>
                    </template>
                    <template v-slot:cell(status)="data">
                        <b-badge v-if="data.item.status === 'espera'" variant="secondary">en espera</b-badge>
                        <b-badge v-if="data.item.status === 'cancelado'" variant="danger">{{ data.item.status }}</b-badge>
                        <b-badge v-if="data.item.status === 'rechazado'" variant="dark">{{ data.item.status }}</b-badge>
                        <b-badge v-if="data.item.status === 'completo'" variant="success">recibido<br>(completo)</b-badge>
                        <b-badge v-if="data.item.status === 'incompleto'" variant="warning">recibido<br>(incompleto)</b-badge>
                        <i v-if="data.item.observations" class="fa fa-exclamation-circle" :id="`popover-obs${data.item.id}`"></i>
                        <b-popover
                            :target="`popover-obs${data.item.id}`"
                            placement="right"
                            title="Observaciones"
                            triggers="hover focus"
                            :content="data.item.observations"
                        ></b-popover>
                    </template>
                    <template #table-busy>
                        <div class="text-center text-danger my-2">
                        <b-spinner class="align-middle"></b-spinner>
                        <strong>Cargando...</strong>
                        </div>
                    </template>
                </b-table>
                <b-alert v-else show variant="secondary">
                    <i class="fa fa-exclamation-circle"></i> No se han agregado pedidos
                </b-alert>
            </div>
            <div v-else class="text-center text-info my-2 mt-3">
                <b-spinner class="align-middle"></b-spinner>
                <strong>Cargando...</strong>
            </div>
        </div>
        <div v-if="openPedido">
            <b-row>
                <b-col sm="10">
                    <h4><b>{{ !editar ? 'Nuevo':'Editar' }} pedido</b></h4>
                </b-col>
                <b-col>
                    <b-button pill variant="secondary" @click="openPedido = false" :disabled="load">
                        <i class="fa fa-reply"></i> Volver
                    </b-button>
                </b-col>
            </b-row>
            <div class="mb-5">
                <label><b>Identificador del pedido:</b></label>
                <b-input v-model="form.identifier" style="text-transform:uppercase;" :disabled="load"></b-input>
            </div>
            <b-row class="mb-3">
                <b-col sm="2">
                    <label><b>Fecha</b></label>
                    <input class="form-control" type="date" v-model="form.date" :disabled="load">
                </b-col>
                <b-col sm="3">
                    <label><b>Proveedor</b></label>
                    <b-form-select v-model="form.provider" :options="proveedores" :disabled="load"></b-form-select>
                </b-col>
                <b-col>
                    <label><b>Para:</b></label>
                    <b-input style="text-transform:uppercase;" v-model="form.destination" :disabled="load"></b-input>
                </b-col>
                <b-col sm="2">
                    <b-button class="mt-2" :disabled="state_guardar() || load" variant="success" 
                        @click="save_pedido()" pill>
                        <i class="fa fa-check-circle"></i> Guardar
                    </b-button>
                </b-col>
            </b-row>

            <b-table :items="form.registros" :fields="fieldsRegistros">
                <template v-slot:cell(index)="data">
                    {{ data.index + 1 }}
                </template>
                <template v-slot:cell(isbn)="data">
                    {{ data.item.libro.isbn }}
                </template>
                <template v-slot:cell(title)="data">
                    {{ data.item.libro.title }}
                </template>
                <template v-slot:cell(quantity)="data">
                    {{ data.item.quantity | formatNumber }}
                </template>
                <template v-slot:cell(unit_price)="data">
                    ${{ data.item.unit_price | formatNumber }}
                </template>
                <template v-slot:cell(total)="data">
                    ${{ data.item.total | formatNumber }}
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
                        <th>Precio unitario</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>
                            <b-input
                                v-model="registro.libro.isbn" @keyup="buscarISBN()" :disabled="load"
                            ></b-input>
                            <div class="list-group" v-if="resultISBNs.length" id="listaL">
                                <a 
                                    class="list-group-item list-group-item-action" 
                                    href="#" 
                                    v-bind:key="i" 
                                    v-for="(libro, i) in resultISBNs" 
                                    @click="datosLibro(libro)">
                                    {{ libro.ISBN }}
                                </a>
                            </div>
                        </th>
                        <th>
                            <b-input
                                style="text-transform:uppercase;"
                                v-model="registro.libro.title" :disabled="load"
                                @keyup="buscarTitle()"
                            ></b-input>
                            <div class="list-group" v-if="resultTitles.length" id="listaL">
                                <a 
                                    class="list-group-item list-group-item-action" 
                                    href="#" 
                                    v-bind:key="i" 
                                    v-for="(libro, i) in resultTitles" 
                                    @click="datosLibro(libro)">
                                    {{ libro.titulo }}
                                </a>
                            </div>
                        </th>
                        <th>
                            <b-input required type="number" v-model="registro.quantity"
                                @keyup="get_total()" :disabled="load"></b-input>
                        </th>
                        <th>
                            <b-input required type="number" v-model="registro.unit_price"
                                @keyup="get_total()" :disabled="load">
                            </b-input>
                        </th>
                        <th>
                            ${{ registro.total | formatNumber }}
                        </th>
                        <th>
                            <b-button variant="success" :disabled="state_save() || load" pill @click="save_register()">
                                <i class="fa fa-level-down"></i>
                            </b-button>
                        </th>
                    </tr>
                    <tr class="mt-5">
                        <th colspan="4"></th>
                        <th class="text-right"><b>Total Factura</b></th>
                        <th>
                            <b>${{ form.total_bill | formatNumber }}</b>
                        </th>
                        <th></th>
                    </tr>
                </template>
            </b-table>
        </div>
        <div v-if="openDetails">
            <b-row class="mb-5">
                <b-col sm="4">
                    <h4><b>Detalles del pedido</b></h4>
                </b-col>
                <b-col>
                    <b-button v-if="pedido.status == 'espera' && (role_id === 1 || role_id == 2 || role_id == 6)" variant="danger"
                        pill :disabled="load" @click="openCancelar = true">
                        <i class="fa fa-close"></i> Cancelar
                    </b-button>
                    <b-button v-if="pedido.status == 'espera' && (role_id === 1 || role_id == 2 || role_id == 3 || role_id == 6)" variant="primary" 
                        pill @click="act_status()" :disabled="load">
                        <i class="fa fa-refresh"></i> Actualizar estado
                    </b-button>
                    <div v-if="pedido.status != 'espera'">
                        <b-badge v-if="pedido.status === 'cancelado'" variant="danger">{{ pedido.status }}</b-badge>
                        <b-badge v-if="pedido.status === 'rechazado'" variant="dark">{{ pedido.status }}</b-badge>
                        <b-badge v-if="pedido.status === 'completo'" variant="success">recibido<br>(completo)</b-badge>
                        <b-badge v-if="pedido.status === 'incompleto'" variant="warning">recibido<br>(incompleto)</b-badge> <br>
                        <label v-if="pedido.observations">
                            <b><i class="fa fa-exclamation-circle"></i> Observaciones:</b><br>{{ pedido.observations }} 
                        </label>
                    </div>
                </b-col>
                <b-col sm="2">
                    <b-button pill variant="secondary" @click="openDetails = false" :disabled="load">
                        <i class="fa fa-reply"></i> Volver
                    </b-button>
                </b-col>
            </b-row>
            <b-row class="mt-3">
                <b-col>
                    <label><b>Identificador del pedido:</b> {{ pedido.identifier }}</label>
                </b-col>
                <b-col sm="4">
                    <label v-if="pedido.creado_por !== null"><b>Creado por:</b> {{ pedido.creado_por }}</label>
                </b-col>
            </b-row>
            <b-row class="mb-3">
                <b-col><b>Fecha:</b> {{ pedido.date | moment }}</b-col>
                <b-col><b>Proveedor:</b> {{ pedido.provider }}</b-col>
                <b-col><b>Para:</b> {{ pedido.destination }}</b-col>
            </b-row>
            <b-table :items="pedido.elements" 
                :fields="fieldsRegistros">
                <template v-slot:cell(index)="data">
                    {{ data.index + 1 }}
                </template>
                <template v-slot:cell(isbn)="data">
                    {{ data.item.libro.ISBN }}
                </template>
                <template v-slot:cell(title)="data">
                    {{ data.item.libro.titulo }}
                </template>
                <template v-slot:cell(quantity)="data">
                    {{ data.item.quantity | formatNumber }}
                </template>
                <template v-slot:cell(actual_quantity)="data">
                    {{ data.item.actual_quantity | formatNumber }}
                </template>
                <template v-slot:cell(unit_price)="data">
                    ${{ data.item.unit_price | formatNumber }}
                </template>
                <template v-slot:cell(total)="data">
                    ${{ data.item.total | formatNumber }}
                </template>
                <template v-slot:cell(actual_total)="data">
                    ${{ data.item.actual_total | formatNumber }}
                </template>
                <template #thead-top="row">
                    <tr class="mt-5">
                        <th colspan="4"></th>
                        <th class="text-right"><b>Total Factura</b></th>
                        <th>
                            <b>${{ pedido.total_bill | formatNumber }}</b>
                        </th>
                        <th></th>
                    </tr>
                </template>
            </b-table>
            <b-modal v-model="openStatus" title="Actualizar estado del pedido"
                hide-footer size="lg">
                <label><b>Estado del pedido</b></label>
                <b-form-select v-model="pedidoStatus.status" :options="estados" :disabled="load"></b-form-select>
                <label><b>Observaciones</b></label>
                <b-form-textarea v-model="pedidoStatus.observations"
                    rows="3" max-rows="6" :disabled="load" placeholder="Opcional"
                ></b-form-textarea>
                <!-- <div v-if="pedidoStatus.status == 'incompleto'" class="mt-2">
                    <label><b>Introducir número de piezas que se recibieron</b></label>
                    <b-table :items="pedidoStatus.elements" :fields="fieldsRegistros">
                        <template v-slot:cell(index)="data">
                            {{ data.index + 1 }}
                        </template>
                        <template v-slot:cell(isbn)="data">
                            {{ data.item.libro.ISBN }}
                        </template>
                        <template v-slot:cell(title)="data">
                            {{ data.item.libro.titulo }}
                        </template>
                        <template v-slot:cell(quantity)="data">
                            <b-input required type="number" v-model="data.item.actual_quantity"
                                @keyup="get_actual_total(data.item, data.index)" :disabled="load"></b-input>
                        </template>
                        <template v-slot:cell(unit_price)="data">
                            ${{ data.item.unit_price | formatNumber }}
                        </template>
                        <template v-slot:cell(total)="data">
                            ${{ data.item.actual_total | formatNumber }}
                        </template>
                    </b-table>
                </div> -->
                
                <div class="text-right mt-2">
                    <b-button :disabled="pedidoStatus.status == null || load" variant="success" 
                        @click="change_status()" pill>
                        <i class="fa fa-check-circle"></i> Actualizar estado
                    </b-button>
                </div>
            </b-modal>
            <b-modal v-model="openCancelar" title="Cancelar pedido" hide-footer>
                <b-alert show variant="danger">
                    <i class="fa fa-exclamation-triangle"></i> 
                    ¿Estás seguro de cancelar el pedido?, una vez realizada esta acción no se podrá deshacer.
                </b-alert>
                <div class="text-right">
                    <b-button pill variant="dark" @click="cancelar_pedido()">Confimar</b-button>
                </div>
            </b-modal>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['role_id'],
        data(){
            return {
                provider: null,
                pedidos: [],
                perPage: 20,
                currentPage: 1,
                editar: false,
                editar2: false, 
                fieldsPedidos: [
                    {label: 'N.', key: 'index'},
                    {label: 'Fecha', key: 'date'},
                    {label: 'Identificador', key: 'identifier'},
                    {label: 'Proveedor', key: 'provider'},
                    {label: 'Para:', key: 'destination'},
                    {label: 'Total Factura', key: 'total_bill'},
                    {label: 'Detalles', key: 'details'},
                    {label: 'Estado', key: 'status'}
                ],
                fieldsRegistros: [
                    {label: 'N.', key: 'index'},
                    {label: 'ISBN', key: 'isbn'},
                    {label: 'Titulo', key: 'title'},
                    {label: 'Cantidad', key: 'quantity'},
                    {label: 'Precio unitario', key: 'unit_price'},
                    {label: 'Total', key: 'total'},
                    {label: '', key: 'edit'}
                ],
                fieldsRegistros2: [
                    {label: 'N.', key: 'index'},
                    {label: 'ISBN', key: 'isbn'},
                    {label: 'Titulo', key: 'title'},
                    {label: 'Cantidad', key: 'quantity'},
                    {label: 'Cantidad recibida', key: 'actual_quantity', variant: 'info'},
                    {label: 'Precio unitario', key: 'unit_price'},
                    {label: 'Total', key: 'total'},
                    {label: 'Total recibido', key: 'actual_total', variant: 'info'},
                    {label: '', key: 'edit'}
                ],
                form: {
                    identifier: null,
                    date: null,
                    provider: null,
                    destination: null,
                    total_bill: 0,
                    registros: []
                },
                registro: {
                    quantity: 0, unit_price: 0, total: 0,
                    libro: { id: null, isbn: '', title: ''}
                },
                resultISBNs: [],
                resultTitles: [],
                proveedores: [],
                openPedido: false,
                position: null,
                openDetails: false,
                pedido: {},
                estados: [
                    { value: null, text: 'Selecciona una opción', disabled: true },
                    { value: 'rechazado', text: 'Rechazado' },
                    { value: 'completo', text: 'Recibido (Completo)' },
                    { value: 'incompleto', text: 'Recibido (Incompleto)' }
                ],
                pedidoStatus: {
                    pedido_id: null, status: null, observations: '', elements: []
                },
                openStatus: false,
                load: false,
                openCancelar: false,
                date: null,
                pedidosData: {},
                searchFecha: false,
                searchProveedor: false
            }
        },
        filters: {
            moment: function (date) {
                return moment(date).format('DD-MM-YYYY');
            },
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        created: function(){
            this.getResults();
            axios.get('/show_editoriales').then(response => {
                this.proveedores = [];
                let ps = response.data;

                this.proveedores.push({
                    value: null,
                    text: 'Selecciona una opción',
                    disabled: true
                });
                ps.forEach(proveedor => {
                    this.proveedores.push({
                        value: proveedor.editorial,
                        text: proveedor.editorial
                    });
                });
            });
        },
        methods: {
            getResults(page = 1){
                if(!this.searchFecha && !this.searchProveedor)
                    this.http_pedidos(page);
                if(this.searchFecha)
                    this.change_date(page);
                if(this.searchProveedor)
                    this.change_provider(page);
            },
            http_pedidos(page){
                this.load = true;
                axios.get(`/pedido/index?page=${page}`).then(response => {
                    this.pedidosData = response.data; 
                    this.pedidos = response.data.data;
                    this.set_search(false, false);
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            set_search(searchFecha, searchProveedor){
                this.searchFecha = searchFecha;
                this.searchProveedor = searchProveedor;
            },
            buscarISBN(){
                if(this.registro.libro.isbn.length > 0){
                    axios.get('/buscarISBN', {params: {isbn: this.registro.libro.isbn}}).then(response => {
                        if(response.data.length > 0) this.resultISBNs = response.data;
                        else {
                            this.ini_libro();
                            this.toast('b-toaster-top-center', 'No se encontro el ISBN', '', 'danger');
                            this.resultISBNs = [];
                        }
                    });
                } else{
                    this.resultISBNs = [];
                }
            },
            buscarTitle() {
                if(this.registro.libro.title.length > 0) {
                    axios.get('/mostrarLibros', {params: {queryTitulo: this.registro.libro.title}}).then(response => {
                        if(response.data.length > 0) this.resultTitles = response.data;
                        else {
                            this.ini_libro();
                            this.toast('b-toaster-top-center', 'No se encontro el titulo', '', 'danger');
                            this.resultTitles = [];
                        }
                    });
                } else {
                    this.resultTitles = [];
                }
                
            },
            datosLibro(libro) {
                this.registro.libro.id = libro.id;
                this.registro.libro.isbn = libro.ISBN;
                this.registro.libro.title = libro.titulo;
                this.resultISBNs = [];
                this.resultTitles = [];
            }, 
            newPedido() {
                this.form = {
                    identifier: null, date: null, provider: null, destination: null,
                    total_bill: 0, registros: []
                };
                this.registro = {
                    quantity: 0, unit_price: 0, total: 0,
                    libro: { id: null, isbn: '', title: ''}
                };

                this.editar = false;
                this.openPedido = true;
            },
            ini_libro(){
                this.registro.libro.id = null;
                this.registro.libro.isbn = '';
                this.registro.libro.title = '';
            },
            toast(toaster, message, title, variant, append = false) {
                this.$bvToast.toast(message, {
                    title: title,
                    toaster: toaster,
                    solid: true,
                    appendToast: append,
                    variant: variant,
                });
            },
            save_register() {
                if(!this.editar2){
                    this.form.registros.push(this.registro);
                } else{
                    this.form.registros[this.position].quantity = this.registro.quantity;
                    this.form.registros[this.position].unit_price = this.registro.unit_price;
                    this.form.registros[this.position].total = this.registro.total;
                    this.form.registros[this.position].libro.id = this.registro.libro.id;
                    this.form.registros[this.position].libro.isbn = this.registro.libro.isbn;
                    this.form.registros[this.position].libro.title = this.registro.libro.title;
                    this.position = null;
                    this.editar2 = false;
                }

                this.registro = {
                    quantity: 0, unit_price: 0, total: 0,
                    libro: { id: null, isbn: '', title: ''}
                };

                this.form.total_bill = 0;
                this.form.registros.forEach(registro => {
                    this.form.total_bill += registro.total;
                });
            },
            get_total() {
                this.registro.total = parseInt(this.registro.quantity) * parseFloat(this.registro.unit_price);
            },
            delete_register(register, index){
                this.form.registros.splice(index, 1);
                this.form.total_bill = this.form.total_bill - register.total;
            },
            edit_register(register, index){
                this.registro.quantity = register.quantity;
                this.registro.unit_price = register.unit_price;
                this.registro.total = register.total;
                this.registro.libro.id = register.libro.id;
                this.registro.libro.isbn = register.libro.isbn;
                this.registro.libro.title = register.libro.title;

                this.position = index;
                this.editar2 = true;
            },
            state_save() {
                let i = this.registro.libro.id;
                let t = this.registro.total;
                let q = this.registro.quantity;
                let u = this.registro.unit_price;
                if(i == null || q == '' || u == '' || q < 0 || u < 0 || t <= 0) return true;
                if(i > 0 && t > 0) return false;
            },
            state_guardar() {
                let rs = this.form.registros;
                let i = this.form.identifier;
                let f = this.form.date;
                let p = this.form.provider;
                let d = this.form.destination;
                let t = this.form.total_bill;

                if(rs.length == 0 || f == null || p == null || d == null || p == '' || d == '' || t == 0 || i == null || i == '') return true;
                if(rs.length > 0 && f !== null && p.length > 0 && d.length > 0 && t > 0 && i.length > 0) return false;
            },
            save_pedido() {
                this.load = true;
                axios.post('/pedido/guardar', this.form).then(response => {
                    this.pedidos.unshift(response.data);
                    this.openPedido = false;
                    this.toast('b-toaster-top-right', 'El pedido se guardo correctamente', '', 'success');
                    this.load = false;
                })
                .catch(error => {
                    this.toast('b-toaster-top-right', 'Ocurrió un error al guardar el pedido. Vuelve a intentarlo.', 'Error', 'danger');
                    this.load = false;
                });
            },
            details_pedido(pedido, i){
                axios.get('/pedido/detalles', {params: {pedido_id: pedido.id}}).then(response => {
                    this.openDetails = true;
                    this.position = i;
                    this.pedido = response.data;
                });
            },
            act_status(){
                this.openStatus = true;
                this.pedidoStatus = { pedido_id: null, status: null, observations: '', elements: [] };
                this.pedidoStatus.pedido_id = this.pedido.id;
                this.pedidoStatus.elements = this.pedido.elements;
            },
            change_status(){
                this.load = true;
                axios.put('/pedido/change_status', this.pedidoStatus).then(response => {
                    this.pedido.status = response.data.status;
                    this.pedidos[this.position].status = response.data.status;
                    this.openStatus = false;
                    this.toast('b-toaster-top-right', 'El estado del pedido se actualizo correctamente', '', 'success');
                    this.position = null;
                    this.load = false;
                }).catch(error => {
                    this.toast('b-toaster-top-right', 'Ocurrió un error al actualizar el estado del pedido. Vuelve a intentarlo.', 'Error', 'danger');
                    this.load = false;
                });
            },
            get_actual_total(register, i){
                let up = parseFloat(this.pedidoStatus.elements[i].unit_price);
                let aq = parseInt(this.pedidoStatus.elements[i].actual_quantity);
                this.pedidoStatus.elements[i].actual_total = aq * up;
            },
            cancelar_pedido(){
                axios.put('/pedido/cancelar_pedido', this.pedido).then(response => {
                    this.pedido.status = response.data.status;
                    this.pedidos[this.position].status = response.data.status;
                    this.openCancelar = false;
                    this.toast('b-toaster-top-right', 'El pedido ha sido cancelado', '', 'success');
                    this.position = null;
                    this.load = false;
                })
                .catch(error => {
                    this.toast('b-toaster-top-right', 'Ocurrió un error al actualizar el estado del pedido. Vuelve a intentarlo.', 'Error', 'danger');
                    this.load = false;
                });
            },
            change_provider(page = 1){
                this.load = true;
                axios.get(`/pedido/get_provider?page=${page}`, {params: {provider: this.provider}}).then(response => {
                    this.pedidos = response.data.data;
                    this.pedidosData = response.data;
                    this.set_search(false, true);
                    this.load = false;
                }).catch(error => {
                    this.toast('b-toaster-top-right', 'Ocurrió un error al actualizar el estado del pedido. Vuelve a intentarlo.', 'Error', 'danger');
                    this.load = false;
                });
            },
            change_date(page = 1){
                this.load = true;
                axios.get(`/pedido/get_date?page=${page}`, {params: {date: this.date}}).then(response => {
                    this.pedidos = response.data.data;
                    this.pedidosData = response.data;
                    this.set_search(true, false);
                    this.load = false;
                }).catch(error => {
                    this.toast('b-toaster-top-right', 'Ocurrió un error al actualizar el estado del pedido. Vuelve a intentarlo.', 'Error', 'danger');
                    this.load = false;
                });
            }
        }
    }
</script>

<style>
    #listaL{
        position: absolute;
        z-index: 100
    }
</style>