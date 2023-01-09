<template>
    <div>
        <b-row>
            <b-col><h4>Actividades completadas</h4></b-col>
            <b-col sm="2">
                <b-button variant="secondary" pill :href="`/information/actividades/lista`">
                    <i class="fa fa-arrow-left"></i> Regresar
                </b-button>
            </b-col>
        </b-row>
        <hr>
        <b-table v-if="actividades.length > 0" striped 
            :items="actividades" :fields="fields" :tbody-tr-class="rowClass">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template v-slot:cell(exitosa)="row">
                <div v-if="role_id == 1 || role_id == 6">
                    <h4 v-if="row.item.exitosa == 'SI'"><b-badge variant="success"><i class="fa fa-smile-o"></i></b-badge></h4>
                    <h4 v-if="row.item.exitosa == 'NO'"><b-badge variant="warning"><i class="fa fa-frown-o"></i></b-badge></h4>
                    <h4 v-if="row.item.exitosa == 'REGULAR'"><b-badge variant="secondary"><i class="fa fa-meh-o"></i></b-badge></h4>
                </div>
            </template>
            <template v-slot:cell(show_details)="row">
                <b-button variant="dark" pill size="sm" @click="row.toggleDetails">
                    {{ row.detailsShowing ? 'Ocultar' : 'Mostrar'}}
                </b-button>
            </template>
            <template #row-details="row">
                <b-card>
                    <details-actividad :actividad="row.item" :role_id="role_id"></details-actividad>
                </b-card>
            </template>
        </b-table>
        <no-registros-component v-else></no-registros-component>
    </div>
</template>

<script>
import getActsStatus from '../../mixins/getActsStatus';
import NoRegistrosComponent from '../funciones/NoRegistrosComponent.vue';
import DetailsActividad from './partials/DetailsActividad.vue';
export default {
  components: { DetailsActividad, NoRegistrosComponent },
    props: ['status', 'role_id'],
    mixins: [getActsStatus],
    data(){
        return {
            fields: [
                {key: 'exitosa', label: ''},
                {key: 'index', label: 'N.'},
                {key: 'tipo', label: 'Tipo'},
                {key: 'nombre', label: 'Actividad'},
                {key: 'fecha', label: 'Fecha'},
                {key: 'show_details', label: 'Detalles'}
            ],
        }
    },
    created: function(){
        this.actividades_bystatus(this.status);
    },
    methods: {
        rowClass(item, type){
            if(this.role_id == 1 || this.role_id == 6){
                if (item.exitosa == 'REGULAR') return 'table-secondary'
                if (item.exitosa == 'SI') return 'table-success'
                if (item.exitosa == 'NO') return 'table-warning'
            }
            
        }
    }
}
</script>

<style>

</style>