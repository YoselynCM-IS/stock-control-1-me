<template>
    <div>
        <h4>Actividades completadas</h4>
        <hr>
        <b-table v-if="actividades.length > 0" striped 
            :items="actividades" :fields="fields" 
            :tbody-tr-class="rowClass">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template v-slot:cell(exitosa)="row">
                <h4 v-if="row.item.exitosa == 'SI'"><b-badge variant="success"><i class="fa fa-smile-o"></i></b-badge></h4>
                <h4 v-if="row.item.exitosa == 'NO'"><b-badge variant="warning"><i class="fa fa-frown-o"></i></b-badge></h4>
                <h4 v-if="row.item.exitosa == 'REGULAR'"><b-badge variant="secondary"><i class="fa fa-meh-o"></i></b-badge></h4>
            </template>
            <template v-slot:cell(show_details)="row">
                <b-button variant="info" pill size="sm" @click="row.toggleDetails">
                    {{ row.detailsShowing ? 'Ocultar' : 'Mostrar'}}
                </b-button>
            </template>
            <template #row-details="row">
                <details-actividad :actividad="row.item"></details-actividad>
            </template>
        </b-table>
    </div>
</template>

<script>
import getActsStatus from '../../mixins/getActsStatus';
import DetailsActividad from './partials/DetailsActividad.vue';
export default {
  components: { DetailsActividad },
    props: ['status'],
    mixins: [getActsStatus],
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'exitosa', label: 'Resultado'},
                {key: 'tipo', label: 'Tipo'},
                {key: 'nombre', label: 'Actividad'},
                {key: 'fecha', label: 'Fecha'},
                {key: 'cliente.name', label: 'Cliente'},
                {key: 'show_details', label: 'Detalles'}
            ],
        }
    },
    created: function(){
        this.actividades_bystatus(this.status);
    },
    methods: {
        rowClass(item, type){
            if (!item) return
            if (item.exitosa == 'REGULAR') return 'table-secondary'
            if (item.exitosa == 'SI') return 'table-success'
            if (item.exitosa == 'NO') return 'table-warning'
        }
    }
}
</script>

<style>

</style>