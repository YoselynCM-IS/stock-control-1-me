<template>
    <div>
        <div v-if="!openCostos">
            <b-row>
                <b-col sm="4">
                    <h5><b>Pedido</b> {{ pedido.identifier }}</h5>
                </b-col>
                <b-col sm="2">
                    <estado-order :id="pedido.id" :status="pedido.status" :observations="pedido.observations"></estado-order>
                </b-col>
                <b-col sm="2">
                    <b-button v-if="pedido.status == 'espera' && (role_id === 1 || role_id == 2 || role_id == 6)" variant="danger"
                        pill :disabled="load" @click="openCancelar = true">
                        <i class="fa fa-close"></i> Cancelar
                    </b-button>
                </b-col>
                <b-col sm="2">
                    <b-button v-if="(pedido.total_bill == 0 && pedido.status == 'espera') && (role_id === 1 || role_id == 2 || role_id == 6)" 
                        @click="addCostos()" pill variant="success" block>
                        <i class="fa fa-dollar"></i> Agregar costos
                    </b-button>
                </b-col>
                <b-col sm="2">
                    <div v-if="(pedido.total_bill > 0)">
                        <b-button v-if="pedido.status == 'espera' && (role_id == 3 || role_id == 6)" variant="primary" 
                            pill @click="act_status()" :disabled="load">
                            <i class="fa fa-refresh"></i> Actualizar
                        </b-button>
                    </div>
                </b-col>
            </b-row>
            <datos-order :order="pedido"></datos-order>
            <b-table :items="pedido.elements" :fields="fieldsRegistros">
                <template v-slot:cell(index)="data">
                    {{ data.index + 1 }}
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
        <div v-if="openCostos">
            <b-row>
                <b-col><h5><b>Pedido</b> {{ pedido.identifier }}</h5></b-col>
                <b-col sm="2">
                    <b-button variant="secondary" @click="(openCostos = false)"
                        pill block :disabled="load">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </b-button>
                </b-col>
            </b-row>
            <add-costos-order-component :order="pedido"></add-costos-order-component>
        </div>
    </div>
</template>

<script>
import toast from '../../mixins/toast';
import formatNumber from '../../mixins/formatNumber';
import moment from '../../mixins/moment';
import DatosOrder from './partials/DatosOrder.vue';
import EstadoOrder from './partials/EstadoOrder.vue';
export default {
    components: { DatosOrder, EstadoOrder },
    props: ['pedido', 'role_id'],
    mixins: [formatNumber, moment, toast],
    data(){
        return {
            load: false,
            openCostos: false,
            openCancelar: false,
            fieldsRegistros: [
                {label: 'N.', key: 'index'},
                {label: 'ISBN', key: 'libro.ISBN'},
                {label: 'Titulo', key: 'libro.titulo'},
                {label: 'Cantidad', key: 'quantity'},
                {label: 'Precio unitario', key: 'unit_price'},
                {label: 'Total', key: 'total'},
                {label: '', key: 'edit'}
            ],
            openStatus: false,
            pedidoStatus: {
                pedido_id: null, status: null, observations: '', elements: []
            },
            estados: [
                { value: null, text: 'Selecciona una opción', disabled: true },
                { value: 'rechazado', text: 'Rechazado' },
                { value: 'completo', text: 'Recibido (Completo)' },
                { value: 'incompleto', text: 'Recibido (Incompleto)' }
            ],
        }
    },
    methods: {
        addCostos(){
            this.openCostos = true;
        },
        cancelar_pedido(){
            this.load = true;
            axios.put('/order/cancelar', this.pedido).then(response => {
                swal("OK", "El pedido ha sido cancelado", "warning")
                        .then((value) => { location.reload(); });
                this.load = false;
            }).catch(error => {
                this.load = false;
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
            axios.put('/order/change_status', this.pedidoStatus).then(response => {
                swal("OK", "El estado del pedido se actualizo correctamente", "success")
                        .then((value) => { location.reload(); });
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
    }
}
</script>

<style>

</style>