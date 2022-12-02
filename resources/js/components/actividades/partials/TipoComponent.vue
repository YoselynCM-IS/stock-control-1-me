<template>
    <div>
        <div v-if="!load">
            <b-row>
                <b-col></b-col>
                <b-col sm="2">
                    <b-button v-if="misActs"
                        variant="success" pill size="sm" block 
                        @click="markCompleted()" class="mb-2"
                        :disabled="(selected.length == 0 || estado == 'completado' || loaded)">
                        <i class="fa fa-check-square-o"></i> Completado
                    </b-button>
                </b-col>
            </b-row>
            <b-table v-if="actividades.length > 0" striped 
                :items="actividades" :fields="fields" 
                :select-mode="selectMode" @row-selected="onRowSelected" 
                :no-select-on-click="(!misActs || estado == 'completado')" ref="selectableTable" selectable
                :tbody-tr-class="rowClass">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
                <template #cell(selected)="{ rowSelected }">
                    <template v-if="rowSelected">
                        <span aria-hidden="true">&check;</span>
                        <span class="sr-only">Selected</span>
                    </template>
                    <template v-else>
                        <span aria-hidden="true">&nbsp;</span>
                        <span class="sr-only">Not selected</span>
                    </template>
                </template>
            </b-table>
            <no-registros-componente v-else></no-registros-componente>  
        </div>
        <load-component v-else></load-component>
    </div>
</template>

<script>
import setTipoAct from '../../../mixins/setTipoAct';
import LoadComponent from '../../cortes/partials/LoadComponent.vue';
import NoRegistrosComponente from '../../funciones/NoRegistrosComponente.vue';
export default {
    props: ['actividades', 'tipo', 'load', 'misActs', 'estado'],
    mixins: [setTipoAct],
    components: {LoadComponent, NoRegistrosComponente},
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'cliente_name', label: 'Cliente'},
                {key: 'descripcion', label: 'Descripción'},
                {key: 'fecha_recordatorio', label: this.label_tipo(this.tipo)},
                {key: 'created_at', label: 'Fecha del registro'},
                {key: 'user_name', label: 'Registrado por'},
            ],
            selectMode: 'multi',
            selected: [],
            loaded: false,
            form: { selected: [] }
        }
    },
    methods: {
        onRowSelected(items) {
            this.selected = items
        },
        markCompleted(){
            this.loaded = true;
            this.form.selected = this.selected;
            axios.put('/actividades/mark_actividades', this.form).then(response => {
                this.form.selected = [];
                this.selected = [];
                this.$emit('updatedActEstado', true);
                this.loaded = false;
            }).catch(error => {
                this.loaded = false;
            });
        },
        // DISTINGUIR DE OTRO COLOR LAS ACTIVIDADES COMPLETADAS
        rowClass(item, type) {
            if (!item) return
            if (item.estado == 'completado') return 'table-success'
        },
    }
}
</script>

<style>

</style>