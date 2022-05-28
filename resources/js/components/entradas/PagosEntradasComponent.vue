<template>
    <div>
        <b-row>
            <b-col>
                <h4 style="color: #170057">Proveedores</h4>
            </b-col>
            <b-col sm="3" class="text-right">
                <b-button variant="dark" href="/descargar_gralEdit" pill>
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </b-col>
        </b-row>
        <b-table :items="editoriales" :fields="fieldsEntrada">
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template v-slot:cell(total_pagos)="row">
                ${{ row.item.total_pagos | formatNumber }}
            </template>
            <template v-slot:cell(total_devolucion)="row">
                ${{ row.item.total_devolucion | formatNumber }}
            </template>
            <template v-slot:cell(total_pendiente)="row">
                <!-- ${{ row.item.total - (row.item.total_pagos + row.item.total_devolucion) | formatNumber }} -->
                ${{ row.item.total_pendiente | formatNumber }}
            </template>
            <template v-slot:cell(pagar)="row">
                <b-button v-if="row.item.total > 0 && row.item.total_pendiente > 0 && (role_id === 1 || role_id === 2 || role_id == 6)" 
                    variant="primary" pill @click="registrarMonto(row.item)">
                    Realizar pago
                </b-button>
            </template>
            <template v-slot:cell(pagos)="row">
                <b-button v-if="row.item.entdepositos_count > 0" 
                    variant="info" pill @click="verPagos(row.item)">
                    Ver pagos
                </b-button>
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="1">&nbsp;</th>
                    <th>${{ eTotals.total | formatNumber }}</th>
                    <th>${{ eTotals.total_pagos | formatNumber }}</th>
                    <th>${{ eTotals.total_devolucion | formatNumber }}</th>
                    <th>${{ eTotals.total_pendiente | formatNumber }}</th>
                    <th colspan="2">&nbsp;</th>
                </tr>
            </template>
        </b-table>
        <b-modal ref="modal-registrarPago" :title="`${!edit ? 'Registrar':'Editar'} pago`" 
            hide-footer>
            <new-edit-pago-entrada :form="form" :edit="edit" @savedPago="savedPago"></new-edit-pago-entrada>
        </b-modal>
        <b-modal ref="modal-mostrarPagos" :title="entrada.editorial" hide-footer size="xl">
            <b-tabs content-class="mt-3" fill>
                <b-tab title="PAGOS" active>
                    <b-table :items="entrada.entdepositos" :fields="fieldsDep">
                        <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                        <template v-slot:cell(pago)="row">
                            ${{ row.item.pago | formatNumber }}
                        </template>
                        <template #thead-top="row">
                            <tr>
                                <th colspan="2">&nbsp;</th>
                                <th>${{ entrada.total_pagos | formatNumber }}</th>
                                <th colspan="3">&nbsp;</th>
                            </tr>
                        </template>
                        <template v-if="role_id == 6" v-slot:cell(actions)="row">
                            <b-button pill variant="warning" size="sm" @click="editPago(row.item)">
                                <i class="fa fa-pencil"></i>
                            </b-button>
                            <b-button pill variant="danger" size="sm" @click="deletePago(row.item)">
                                <i class="fa fa-close"></i>
                            </b-button>
                        </template>
                    </b-table>
                </b-tab>
                <b-tab title="DEVOLUCIONES">
                    <b-table :items="entrada.entdevoluciones" :fields="fieldsDev" responsive>
                        <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                            <template v-slot:cell(costo_unitario)="row">
                                ${{ row.item.costo_unitario | formatNumber }}
                            </template>
                            <template v-slot:cell(unidades)="row">
                                {{ row.item.unidades | formatNumber }}
                            </template>
                            <template v-slot:cell(total)="row">
                                ${{ row.item.total | formatNumber }}
                            </template>
                    </b-table>
                </b-tab>
            </b-tabs>
        </b-modal>
        <b-modal ref="modal-deletePago" title="Eliminar pago" hide-footer size="sm">
            <div class="text-center">
                <p>¿Estás seguro de eliminar el pago?</p>
                <b-button variant="danger" pill @click="confirmarEliminar()"
                    :disabled="load">
                    <i class="fa fa-close"></i> Confirmar
                </b-button>
            </div>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
import NewEditPagoEntrada from './NewEditPagoEntrada.vue';
import toast from '../../mixins/toast';
export default {
    props: ['role_id', 'editoriales'],
    components: {NewEditPagoEntrada},
    mixins: [formatNumber,toast],
    data(){
        return {
            fieldsEntrada: [
                'editorial', 'total',
                {key: 'total_pagos', label: 'Pagos'},
                {key: 'total_devolucion', label: 'Devolucion'},
                {key: 'total_pendiente', label: 'Pagar'},
                {key: 'pagar', label: ''},
                {key: 'pagos', label: ''}
            ],
            eTotals: {
                total: 0,
                total_pagos: 0,
                total_devolucion: 0,
                total_pendiente: 0
            },
            form: {
                id: null, 
                enteditoriale_id: null, 
                pago: null,
                fecha: null,
                nota: null,
                editorial: null,
                total_pendiente: null,
            },
            entrada: { 
                editorial: null, 
                total_pagos: null,
                total_pendiente: null,
                entdepositos: [],
                entdevoluciones: []
            },
            fieldsDep: [
                {key: 'index', label: 'N.'},
                {key: 'created_at', label: 'Fecha de registro'},
                'pago',
                {key: 'ingresado_por', label: 'Ingresado por'}, 
                'nota',
                {key: 'fecha', label: 'Fecha del pago'},
                {key: 'actions', label: ''},
            ],
            fieldsDev: [
                {key: 'index', label: 'N.'},
                {key: 'created_at', label: 'Fecha de registro'},
                'folio', {key: 'creado_por', label: 'Ingresado por'}, 
                'ISBN', {key: 'titulo', label: 'Libro'},
                {key: 'costo_unitario', label: 'Costo unitario'},
                'unidades', 'total'
            ],
            edit: false,
            pago_id: null,
            load: false
        }
    },
    created: function(){
        this.getTotales();
    },
    methods: {
        // OBTENER TOTALES
        getTotales(){
            this.editoriales.forEach(editorial => {
                this.eTotals.total += editorial.total;
                this.eTotals.total_pagos += editorial.total_pagos;
                this.eTotals.total_devolucion += editorial.total_devolucion;
                this.eTotals.total_pendiente += editorial.total_pendiente;
            });
        },
        // REGISTRAR PAGO
        registrarMonto(editorial){;
            this.edit = false;
            this.form = {
                id: null, 
                enteditoriale_id: editorial.id, 
                pago: 0,
                fecha: null,
                nota: null,
                editorial: editorial.editorial,
                total_pendiente: editorial.total_pendiente,
            };
            this.$refs['modal-registrarPago'].show();
        },
        // PAGO GUARDADO
        savedPago(pago){
            this.$refs['modal-registrarPago'].hide();
            let msg = "El pago se guardo correctamente";
            if(this.edit) msg = 'El pago se actualizo correctamente.';
            this.showSwal(msg);
        },
        showSwal(msg){
            swal("OK", msg, "success")
                .then((value) => {
                    let ruta = '#';
                    if(this.role_id === 2) ruta = '/oficina/entradas/pagos';
                    if(this.role_id === 6) ruta = '/manager/entradas/pagos';
                    location.href = ruta;
                });
        },
        // MOSTRAR LOS DEPOSITOS DE LA EDITORIAL
        verPagos(editorial){
            axios.get('/entradas/enteditoriale_pagos', {params: {id: editorial.id}}).then(response => {
                this.entrada.editorial = editorial.editorial;
                this.entrada.total_pagos = editorial.total_pagos;
                this.entrada.total_pendiente = editorial.total_pendiente;
                this.entrada.entdepositos = response.data.entdepositos;
                this.entrada.entdevoluciones = response.data.entdevoluciones;
                this.$refs['modal-mostrarPagos'].show();
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        // EDITAR PAGO
        editPago(pago){
            this.edit = true;
            this.form = {
                id: pago.id, 
                enteditoriale_id: pago.enteditoriale_id, 
                pago: pago.pago,
                fecha: pago.fecha,
                nota: pago.nota,
                editorial: this.entrada.editorial,
                total_pendiente: this.entrada.total_pendiente,
            };
            this.$refs['modal-registrarPago'].show();
        },
        // ELIMINAR PAGO
        deletePago(pago){
            this.pago_id = pago.id;
            this.$refs['modal-deletePago'].show();
        },
        // CONFIRMAR ELIMINACION
        confirmarEliminar(){
            this.load = true;
            axios.delete('/entradas/delete_pago', {params: {pago_id: this.pago_id}}).then(response => {
                this.$refs['modal-deletePago'].hide();
                this.showSwal('El pago ha sido eliminado correctamente.');
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