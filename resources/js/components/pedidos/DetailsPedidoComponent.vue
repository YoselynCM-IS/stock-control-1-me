<template>
    <div>
        <b-row>
            <b-col>
                <h5><b>DETALLES DEL PEDIDO</b></h5>
            </b-col>
            <b-col sm="2">
                <estado-pedido :id="pedido.id" :comentarios="pedido.comentarios" :estado="pedido.estado"></estado-pedido>
            </b-col>
            <b-col sm="2">
                <b-button v-if="(role_id == 6 || role_id == 7) && pedido.estado == 'proceso'" 
                    :disabled="load"
                    variant="danger" pill block @click="cancelarPedido()">
                    <i class="fa fa-close"></i> Cancelar
                </b-button>
            </b-col>
            <b-col sm="2">
                <b-button v-if="(role_id == 2 || role_id == 6) && pedido.estado == 'proceso'"
                    :href="`/pedido/preparar/${pedido.id}`" 
                    variant="dark" pill block :disabled="load">
                    Preparar pedido
                </b-button>
            </b-col>
        </b-row>
        <hr>
        <datos-pedido :cliente_name="pedido.cliente.name" 
                    :user_name="pedido.user.name" 
                    :created_at="pedido.created_at">
        </datos-pedido>
        <b-table :items="pedido.peticiones" :fields="fields">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="3"></th>
                    <th>{{ pedido.total_quantity }}</th>
                </tr>
            </template>
        </b-table>
    </div>
</template>

<script>
import DatosPedido from './partials/DatosPedido.vue'
import EstadoPedido from './partials/EstadoPedido.vue';
export default {
    components: { DatosPedido, EstadoPedido },
    props: ['pedido', 'role_id'],
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'libro.ISBN', label: 'ISBN'},
                {key: 'libro.titulo', label: 'Titulo'},
                {key: 'quantity', label: 'Unidades'}
            ],
            load: false
        }
    },
    methods: {
        cancelarPedido(){
            this.load = true;
            let form = {pedido_id: this.pedido.id };
            axios.put('/pedido/cancelar', form).then(response => {
                swal("OK", "El pedido se ha cancelado.", "warning")
                .then((value) => { location.reload(); });
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