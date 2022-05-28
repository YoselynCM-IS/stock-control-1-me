<template>
    <div>
        <!-- ENCABEZADO -->
        <b-row>
            <b-col sm="4"><h5><b>Remisión No. {{ remision.id }}</b></h5></b-col>
            <b-col sm="2" class="text-right">
                <b-button v-if="(role_id === 1 || role_id == 2 || role_id == 6) && remision.total_pagar === remision.total && remision.estado != 'Cancelado'"
                    variant="dark" v-b-modal.modal-cancelar>
                    <i class="fa fa-close"></i> Cancelar
                </b-button>
                <b-badge variant="danger" v-if="remision.estado == 'Cancelado'">Remisión cancelada</b-badge>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button v-b-modal.my-comentarios @click="ini_comment()" variant="dark" v-if="role_id !== 4">
                    <i class="fa fa-comment"></i> Comentarios
                </b-button>
            </b-col>
            <b-col sm="4" class="text-right">
                <!-- <a class="btn btn-dark" v-if="role_id !== 2" :href="'/imprimirSalida/' + remision.id">
                    <i class="fa fa-download"></i> Descargar
                </a> -->
                <b-button v-if="role_id === 1 || role_id === 2 || role_id == 6" :href="`/download_remision/${remision.id}`" variant="dark">
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </b-col>
        </b-row>
        <hr>
        <datos-remision :remision="remision" :cliente_name="remision.cliente.name"></datos-remision>
        <!-- DATOS DE LA REMISION -->
        <b-table :items="remision.datos" :fields="fieldsDatos" responsive>
            <template v-slot:cell(costo_unitario)="row">
                ${{ row.item.costo_unitario | formatNumber }}
            </template>
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="4"></th>
                    <th><h5>${{ remision.total | formatNumber }}</h5></th>
                </tr>
            </template>
            <template #cell(codes)="row">
                <b-button v-if="row.item.codes.length > 0" 
                    size="sm" @click="row.toggleDetails" pill variant="info">
                    {{ row.detailsShowing ? 'Ocultar' : 'Mostrar'}}
                </b-button>
            </template>
            <template #row-details="row">
                <b-row>
                    <b-col sm="3"></b-col>
                    <b-col sm="6">
                        <b-table :items="row.item.codes" :fields="fieldsCodes">
                            <template v-slot:cell(index)="data">
                                {{ data.index + 1 }}
                            </template>
                            <template v-slot:cell(devolucion)="data">
                                <b-badge v-if="!data.item.pivot.devolucion" variant="light">No</b-badge>
                                <b-badge v-else variant="warning">Si</b-badge>
                            </template>
                        </b-table>
                    </b-col>
                    <b-col sm="3"></b-col>
                </b-row>
            </template>
        </b-table>
        <br>
        <!-- PAGOS DE LA REMISION -->
        <div>
            <b-button v-if="remision.depositos.length > 0"
                variant="link" :class="mostrarPagos ? 'collapsed' : null"
                :aria-expanded="mostrarPagos ? 'true' : 'false'"
                aria-controls="collapse-3" @click="mostrarPagos = !mostrarPagos">
                <h4><b>Pagos</b></h4>
            </b-button>
            <b-collapse id="collapse-3" v-model="mostrarPagos" class="mt-2">
                <depositos-remision :depositos="remision.depositos"></depositos-remision>>
            </b-collapse>
        </div>
        <br><br>
        <!-- DEVOLUCIONES DE LA REMISION -->
        <div>
            <b-button v-if="remision.total_devolucion > 0" variant="link" 
                :class="mostrarDevolucion ? 'collapsed' : null"
                :aria-expanded="mostrarDevolucion ? 'true' : 'false'"
                aria-controls="collapse-2"
                @click="mostrarDevolucion = !mostrarDevolucion">
                <h4><b>Devolución</b></h4>
            </b-button>
            <b-collapse id="collapse-2" v-model="mostrarDevolucion" class="mt-2">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <td></td><td></td>
                            <td></td><td></td>
                            <td><h5>${{ remision.total_devolucion | formatNumber }}</h5></td>
                        </tr>
                        <tr>
                            <th scope="col">ISBN</th>
                            <th scope="col">Libro</th>
                            <th scope="col">Costo unitario</th>
                            <th scope="col">Unidades</th>
                            <th scope="col">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(devolucion, i) in remision.devoluciones" v-bind:key="i">
                            <td>{{ devolucion.libro.ISBN }}</td>
                            <td>{{ devolucion.libro.titulo }}</td>
                            <td>${{ devolucion.dato.costo_unitario | formatNumber }}</td>
                            <td>{{ devolucion.unidades }}</td>
                            <td>${{ devolucion.total | formatNumber }}</td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <h5><b>Detalles de la devolución</b></h5>
                <b-table hover :items="remision.fechas" :fields="fieldsFechas">
                    <template  v-slot:cell(isbn)="data">
                        {{ data.item.libro.ISBN}}
                    </template>
                    <template  v-slot:cell(titulo)="data">
                        {{ data.item.libro.titulo}}
                    </template>
                    <template  v-slot:cell(unidades)="data">
                        {{ data.item.unidades | formatNumber }}
                    </template>
                    <template  v-slot:cell(total)="data">
                        ${{ data.item.total | formatNumber }}
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="6"></th>
                            <th><h5>${{ remision.total_devolucion | formatNumber }}</h5></th>
                        </tr>
                    </template>
                </b-table>
            </b-collapse>
        </div>
        <!-- MODALS -->
        <!-- CANCELAR REMISIÓN -->
        <b-modal id="modal-cancelar" title="Cancelar remisión">
            <p><b><i class="fa fa-exclamation-triangle"></i> ¿Estas seguro de cancelar la remisión?</b></p>
            <b-alert show variant="warning">
                <i class="fa fa-exclamation-circle"></i> Una vez presionado <b>OK</b> no se podrán realizar cambios.
            </b-alert>
            <div slot="modal-footer">
                <b-button variant="danger" :disabled="load" @click="cambiarEstado()">OK</b-button>
            </div>
        </b-modal>
        <!-- MODAL PARA HACER COMENTARIOS -->
        <b-modal id="my-comentarios" size="lg" title="Comentarios de la remisión">
            <div v-if="!newComment">
                <div class="text-right">
                    <b-button v-if="!newComment && remision.estado != 'Cancelado'" 
                        variant="success" @click="newComment = true">
                        <i class="fa fa-plus"></i> Agregar comentario
                    </b-button>
                </div>
                <hr>
                <b-table v-if="remision.comentarios.length" 
                    :items="remision.comentarios" :fields="fieldsComen">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(user_id)="row">{{ row.item.user.name }}</template>
                    <template v-slot:cell(created_at)="row">{{ row.item.created_at | moment }}</template> 
                </b-table>
                <b-alert v-else show variant="secondary">La remisión no tiene comentarios</b-alert>
            </div>
            <div v-else>
                <b-form @submit.prevent="guardarComentario()">
                    <label><b>Escribir comentario</b></label>
                    <b-form-input type="text" v-model="form.comentario" required></b-form-input><br>
                    <div class="text-right">
                        <b-button type="submit" :disabled="load" variant="success">
                            <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                        </b-button>
                    </div>
                </b-form>
            </div>
            <div slot="modal-footer"></div>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
import toast from '../../mixins/toast';
import moment from '../../mixins/moment';
import DatosRemision from './partials/DatosRemision.vue';
import DepositosRemision from './partials/DepositosRemision.vue';
export default {
    props: ['remision', 'role_id'],
    components: {DatosRemision, DepositosRemision},
    mixins: [formatNumber,toast,moment],
    data(){
        return {
            mostrarPagos: false,
            mostrarDevolucion: false,
            load: false,
            newComment: false,
            fieldsDatos: [
                { key: 'libro.ISBN', label: 'ISBN' },
                { key: 'libro.titulo', label: 'Libro' },
                { key: 'costo_unitario', label: 'Costo unitario' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'total', label: 'Subtotal' },
                { key: 'codes', label: '' }
            ],
            fieldsCodes: [
                {key:'index', label:'N.'},
                {key:'codigo', label:'Código'},
                {key:'devolucion', label:'Devolución'},
            ],
            fieldsFechas: [
                { key: 'creado_por', label: 'Ingresado por' },
                { key: 'fecha_devolucion', label: 'Fecha' },
                { key: 'entregado_por', label: 'Entregada por' },
                { key: 'isbn', label: 'ISBN' },
                { key: 'titulo', label: 'Libro' },
                'unidades',
                { key: 'total', label: 'Subtotal' },
            ],
            fieldsComen: [
                {key: 'index', label: 'N.'},
                'comentario',
                {key: 'user_id', label: 'Usuario'},
                {key: 'created_at', label: 'Fecha'},
            ],
            form: {
                remision_id: null,
                comentario: ''
            }
            
        }
    },
    methods: {
        ini_comment(){
            this.newComment = false;
            this.form = {
                remision_id: null,
                comentario: ''
            }
        },
        cambiarEstado(){
            this.load = true;
            axios.put('/remisiones/cancel', this.remision).then(response => {
                this.remision.estado = response.data.estado;
                this.$bvModal.hide('modal-cancelar');
                this.load = false;
                this.makeToast('secondary', 'Remisión cancelada');
            })
            .catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        guardarComentario(){
            this.form.remision_id = this.remision.id;
            this.load = true;
            axios.post('/guardar_comentario', this.form).then(response => {
                this.load = false;
                this.makeToast('success', 'El comentario se guardo correctamente');
                this.remision.comentarios.push(response.data);
                this.ini_comment();
            })
            .catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        }
    }
}
</script>

<style>

</style>