<template>
    <div>
        <b-row class="mb-3">
            <b-col>
                <!-- PAGINACIÓN -->
                <pagination size="default" :limit="1" :data="dataRemisiones" 
                    @pagination-change-page="getRemisiones">
                    <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                    <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                </pagination>
            </b-col>
            <b-col sm="2">
                <b-button variant="success" :disabled="load" target="blank" 
                    :href="`/historial/crear_remision`" pill>
                    <i class="fa fa-plus"></i> Crear remisión
                </b-button>
            </b-col>
        </b-row>
        <b-table :items="dataRemisiones.data" :fields="fields">
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template v-slot:cell(pagos)="row">
                ${{ row.item.pagos | formatNumber }}
            </template>
            <template v-slot:cell(total_devolucion)="row">
                ${{ row.item.total_devolucion | formatNumber }}
            </template>
            <template v-slot:cell(total_pagar)="row">
                ${{ row.item.total_pagar | formatNumber }}
            </template>
            <template v-slot:cell(detalles)="row">
                <b-button :href="`/remisiones/details/${row.item.id}`" 
                    target="blank" variant="info" pill :disabled="load">
                    <i class="fa fa-info-circle"></i>
                </b-button>
            </template>
            <template v-slot:cell(devolucion)="row">
                <b-button :href="`/historial/registrar_devolucion/${row.item.id}`" 
                    target="blank" variant="dark" pill 
                    :disabled="load || row.item.total_pagar <= 0">
                    <i class="fa fa-edit"></i>
                </b-button>
            </template>
        </b-table>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
export default {
    props: ['corte_id'],
    mixins: [formatNumber],
    data(){
        return {
            load: false,
            dataRemisiones: {},
            fields: [
                { key: 'id', label: 'Folio' },
                { key: 'fecha_creacion', label: 'Fecha de creación' },
                { key: 'cliente.name', label: 'Cliente' },
                { key: 'total', label: 'Salida' },
                { key: 'pagos', label: 'Pagos' },
                { key: 'total_devolucion', label: 'Devolución' },
                { key: 'total_pagar', label: 'Pagar' },
                { key: 'detalles', label: 'Detalles' },
                { key: 'devolucion', label: 'Devolución' }
            ]
        }
    },
    created: function(){
        this.getRemisiones();
    },
    methods: {
        getRemisiones(page = 1){
            axios.get(`/historial/remisiones_byperiodo?page=${page}`, 
                        {params: {corte_id: this.corte_id}}).then(response => {
                this.dataRemisiones = response.data;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>