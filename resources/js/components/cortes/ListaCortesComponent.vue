<template>
    <div>
        <div v-if="!showClasRems && !showDetails && !showClasPagos">
            <!-- FUNCIONES (ENCABEZADO) -->
            <b-row class="mb-3">
                <b-col>
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="cortes" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <!-- <b-col sm="4" class="text-center"> -->
                    <!-- REMISIONES -->
                    <!-- <b-button variant="primary" pill @click="classify(1)">
                        <i class="fa fa-exchange"></i> Remisiones
                    </b-button> -->
                    <!-- PAGOS -->
                    <!-- <b-button variant="primary" pill @click="classify(2)">
                        <i class="fa fa-exchange"></i> Pagos
                    </b-button> -->
                <!-- </b-col> -->
                <b-col sm="2" class="text-center">
                    <!-- AGREGAR CORTE -->
                    <b-button id="show-necorte" @click="showNewCorte"
                        pill variant="success">
                        <i class="fa fa-plus-circle"></i> Agregar corte
                    </b-button>
                </b-col>
            </b-row>
            <!-- TABLA DE CORTES -->
            <div v-if="!load">
                <b-table responsive hover 
                            :items="cortes.data" :fields="fieldsCortes">
                    <template v-slot:cell(details)="row">
                        <!-- DETALLES -->
                        <b-button variant="info" pill @click="getDetails(row.item)">
                            <i class="fa fa-info-circle"></i>
                        </b-button>
                        <!-- EDITAR CORTE -->
                        <b-button variant="warning" pill @click="showEditCorte(row.item)">
                            <i class="fa fa-edit"></i>
                        </b-button>
                    </template>
                </b-table>
            </div>
            <load-component v-else></load-component>
        </div>
        <div v-if="showClasRems">
            <!-- CLASIFICAR REMISIONES -->
            <classify-cortes-component :cortes="options"></classify-cortes-component>
        </div>
        <div v-if="showClasPagos">
            <classify-pagos-component :cortes="options"></classify-pagos-component>
        </div>
        <div v-if="showDetails">
            <!-- DETALLES DEL CORTE -->
            <div class="text-right mb-2">
                <b-button variant="secondary" pill @click="showDetails = !showDetails">
                    <i class="fa fa-arrow-left"></i> Regresar
                </b-button>
            </div>
            <details-corte-component :corte="corte"></details-corte-component>
        </div>
        <!-- MODALS -->
        <!-- Crear/Editar cortes -->
        <b-modal ref="show-necorte" hide-footer size="sm"
            :title="`${!edit ? 'Agregar':'Editar'} corte`">
            <new-edit-corte-component :edit="edit" :form="form"
                @saveCorte="saveCorte"></new-edit-corte-component>
        </b-modal>
    </div>
</template>

<script>
import toast from '../../mixins/toast';
import LoadComponent from './partials/LoadComponent.vue';
export default {
  components: { LoadComponent },
    mixins: [toast],
    data(){
        return {
            edit: false,
            form: {
                id: null,
                tipo: null,
                inicio: null,
                final: null
            },
            cortes: {},
            fieldsCortes: [
                { key: 'tipo', label: 'Temporada' },
                { key: 'inicio', label: 'Fecha de inicio' },
                { key: 'final', label: 'Fecha de termino' },
                { key: 'created_at', label: 'Creado el:' },
                { key: 'details', label: '' }
            ],
            load: false,
            showClasRems: false,
            showDetails: false,
            corte: null,
            showClasPagos: false,
            options: []
        }
    },
    mounted: function(){
        this.getResults();
    },
    methods: {
        // OBTENER CORTES
        getResults(page = 1){
            this.load = true;
            axios.get(`/cortes/index?page=${page}`).then(response => {
                this.cortes = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        // AGREGAR CORTE
        showNewCorte(){
            this.form = {
                id: null,
                tipo: null,
                inicio: null,
                final: null
            };
            this.$refs['show-necorte'].show();
        },
        // EDITAR CORTE
        showEditCorte(corte){
            this.form = {
                id: corte.id,
                tipo: corte.tipo,
                inicio: corte.inicio,
                final: corte.final
            };
            this.edit = true;
            this.$refs['show-necorte'].show();
        },
        // CORTE GUARDADO
        saveCorte(){
            this.getResults();
            this.makeToast('success', `El corte se ${!this.edit ? 'guardo':'actualizo'} correctamente.`);
            this.$refs['show-necorte'].hide();
        },
        // OBTENER DETALLES DEL CORTE
        getDetails(corte){
            this.corte = corte;
            this.showDetails = true;
        },
        // ABRIR, PARA CLASIFICAR 
        classify(tipo){
            this.load = true;
            axios.get('/cortes/get_all').then(response => {
                this.options = response.data;
                if(tipo == 1) this.showClasRems = !this.showClasRems;
                if(tipo == 2) this.showClasPagos = !this.showClasPagos;
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        }
    }
}
</script>

<style>

</style>