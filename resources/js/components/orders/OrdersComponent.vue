<template>
    <div>
        <div v-if="!openPedido">
            <b-row class="mb-2">
                <b-col>
                    <pagination size="default" :limit="1" :data="pedidosData" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col sm="4">
                    <label><b>Buscar por proveedor</b></label>
                    <b-form-select v-model="provider" :options="options" 
                        @change="change_provider()" :disabled="load">
                    </b-form-select>
                </b-col>
                <b-col sm="2">
                    <b-button v-if="role_id == 1 || role_id == 2 || role_id == 6"
                        variant="success" pill block @click="addPedido()">
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
                        <b-button variant="info" pill :disabled="load"
                            :href="`/order/show/${data.item.id}`" target="blank">
                            <i class="fa fa-exclamation-circle"></i>
                        </b-button>
                    </template>
                    <template v-slot:cell(status)="data">
                        <estado-order :id="data.item.id" :status="data.item.status" :observations="data.item.observations"></estado-order>
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
        <div v-else>
            <b-row>
                <b-col sm="10">
                    <h4><b>Nuevo pedido</b></h4>
                </b-col>
                <b-col>
                    <b-button pill block variant="secondary" @click="openPedido = false" 
                        :disabled="load">
                        <i class="fa fa-reply"></i> Volver
                    </b-button>
                </b-col>
            </b-row><hr>
            <new-order-component></new-order-component>
        </div>
    </div>
</template>

<script>
import EstadoOrder from './partials/EstadoOrder.vue';
import getEditoriales from '../../mixins/getEditoriales';
import formatNumber from '../../mixins/formatNumber';
import moment from '../../mixins/moment';
import NewOrderComponent from './NewOrderComponent.vue';
    export default {
        components: { EstadoOrder, NewOrderComponent },
        mixins: [formatNumber, moment, getEditoriales],
        props: ['role_id'],
        data(){
            return {
                provider: null,
                pedidos: [],
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
                load: false,
                pedidosData: {},
                searchProveedor: false,
                openPedido: false
            }
        },
        created: function(){
            this.getResults();
            this.get_editoriales();
        },
        methods: {
            addPedido(){
                this.openPedido = true;
            },
            getResults(page = 1){
                if(!this.searchProveedor)
                    this.http_pedidos(page);
                if(this.searchProveedor)
                    this.change_provider(page);
            },
            http_pedidos(page){
                this.load = true;
                axios.get(`/order/index?page=${page}`).then(response => {
                    this.pedidosData = response.data; 
                    this.pedidos = response.data.data;
                    this.searchProveedor = false;
                    this.load = false;   
                }).catch(error => {
                    this.load = false;
                });
            },
            change_provider(page = 1){
                this.load = true;
                axios.get(`/pedido/by_provider?page=${page}`, {params: {provider: this.provider}}).then(response => {
                    this.pedidos = response.data.data;
                    this.pedidosData = response.data;
                    this.searchProveedor = true;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            
        }
    }
</script>

<style>
    #listaL{
        position: absolute;
        z-index: 100
    }
</style>