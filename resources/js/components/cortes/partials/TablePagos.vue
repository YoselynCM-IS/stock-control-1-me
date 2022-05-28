<template>
    <div>
        <b-table v-if="remdepositos.length > 0"
            :items="remdepositos" :fields="fieldsPagos" responsive>
            <template v-slot:cell(pago)="row">
                ${{ row.item.pago | formatNumber }}
            </template>
            <template v-if="showTitle" #thead-top="row">
                <tr>
                    <th colspan="5" class="text-center">Pagos</th>
                </tr>
            </template>
            <template v-if="role_id == 1 || role_id == 2 || role_id == 6" v-slot:cell(foto)="row">
                <b-button v-if="!row.item.foto" pill size="sm" variant="info" 
                    @click="selectImage(row.item)">
                    <i class="fa fa-camera-retro"></i>
                </b-button>
                <a v-else :href="row.item.foto.public_url" target="_blank">
                    Ver foto
                </a>
            </template>
            <template v-if="role_id == 6" v-slot:cell(actions)="row">
                <b-button pill size="sm" variant="primary" @click="movePago(row.item)">
                    <i class="fa fa-exchange"></i>
                </b-button>
                <b-button pill size="sm" variant="warning" @click="editPago(row.item)">
                    <i class="fa fa-pencil"></i>
                </b-button>
                <b-button pill size="sm" variant="danger" @click="deletePago(row.item)">
                    <i class="fa fa-close"></i>
                </b-button>
            </template>
        </b-table>
        <alert-v-component v-else :text="'pagos'"></alert-v-component>

        <!-- MODALAS -->
        <!-- Mover pago de corte -->
        <b-modal ref="show-move-pago" hide-footer size="sm" title="Mover pago">
            <select-corte-pagos-component :form="form" :options="options" :move="true"
                :cortes="cortes" @pagosGuardados="pagosGuardados"></select-corte-pagos-component>
        </b-modal>
        <!-- Editar pago -->
        <b-modal ref="show-editar-pago" hide-footer size="sm" title="Editar pago">
            <edit-pago-component :form="formPago" @updPayment="updPayment"></edit-pago-component>
        </b-modal>
        <!-- Eliminar pago -->
        <b-modal ref="show-eliminar-pago" hide-footer size="sm" title="Eliminar pago">
            <div class="text-center">
                <p>¿Estas seguro de eliminar el pago?</p>
                <b-button pill variant="danger" @click="confirmDelete()">
                    <i class="fa fa-close"></i> Confirmar
                </b-button>
            </div>
        </b-modal>
        <!-- SUBIR PAGO -->
        <b-modal ref="show-upload-pago" hide-footer size="sm" title="Subir foto del pago">
            <form @submit="uploadImage" enctype="multipart/form-data">
                <b-form-group>
                    <input :disabled="load" type="file" id="archivoType" 
                        v-on:change="fileChange" required name="file">
                    <label for="archivoType">
                        <i class="fa fa-upload"></i> Seleccionar foto
                    </label>
                    <p v-if="formImage.file !== null">
                        FOTO: <b>{{ formImage.file.name }}</b>
                    </p>
                    <div v-if="errors && errors.file" class="text-danger">
                        La foto debe tener un tamaño máximo de 3MB y solo formato jpg, png, jpeg
                    </div>
                </b-form-group>
                <div class="text-right mt-3">
                    <b-button pill :disabled="load" variant="success" type="submit">
                        <i class="fa fa-plus-circle"> Subir</i> 
                    </b-button>
                </div>
            </form>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
import AlertVComponent from './AlertVComponent.vue';
import setCortes from '../../../mixins/setCortes';
import toast from '../../../mixins/toast';
import EditPagoComponent from './EditPagoComponent.vue';
export default {
    components: {AlertVComponent, EditPagoComponent},
    props: ['remdepositos', 'cortePagar', 'showTitle', 'cliente_id','role_id'],
    mixins: [formatNumber,setCortes,toast],
    data(){
        return {
            fieldsPagos: [
                {key: 'created_at', label: 'Fecha de registro'},
                'pago',
                {key: 'ingresado_por', label: 'Ingresado por'},
                'nota',
                {key: 'fecha', label: 'Fecha del pago'},
                { key: 'foto', label: '' },
                { key: 'actions', label: '' }
            ],
            form: {
                pago_id: null,
                corte_id: null,
                corte_id_favor: null,
                cliente_id: this.cliente_id,
                total_selected: 0,
            },
            formPago: {
                id: null,
                pago: null,
                fecha: null,
                nota: null,
                corte_pagar: this.cortePagar
            },
            pago_id: null,
            options: [],
            load: false,
            cortes: [],
            formImage: {
                pagoid: null, file: null
            },
            // file: null,
            errors: {}
        }
    },
    methods: {
        // MOVER PAGO
        movePago(pago){
            this.load = true;
            axios.get('/cortes/get_all').then(response => {
                this.form.pago_id = pago.id;
                this.form.corte_id = null;
                this.form.total_selected = pago.pago;
                this.cortes = response.data;
                this.options = this.setCortes(response.data, null);
                this.$refs['show-move-pago'].show();
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        // PAGO MOVIDO
        pagosGuardados(respuesta){
            this.$refs['show-move-pago'].hide();
            if(respuesta) {
                swal("OK", "El pago se movio correctamente.", "success")
                .then((value) => {
                    location.href = `/cortes/details_cliente/${this.form.cliente_id}`;
                });
            } else {
                this.makeToast('warning', 'Vuelve a elegir el pago.');
            }
        },
        // EDITAR PAGO
        editPago(pago) {
            this.formPago.id = pago.id;
            this.formPago.pago = pago.pago;
            this.formPago.fecha = pago.fecha;
            this.formPago.nota = pago.nota;
            this.$refs['show-editar-pago'].show();
        },
        // PAGO ACTUALIZADO
        updPayment(pago){
            this.$refs['show-editar-pago'].hide();
            swal("OK", "El pago se actualizo correctamente.", "success")
                .then((value) => {
                    location.href = `/cortes/details_cliente/${this.cliente_id}`;
                });
        },
        // ELIMINAR PAGO
        deletePago(pago){
            this.pago_id = pago.id;
            this.$refs['show-eliminar-pago'].show();
        },
        // CONFIRMAR PARA BORRAR EL PAGO
        confirmDelete(){
            this.load = true;
            axios.delete('/cortes/delete_payment', {params: {pago_id: this.pago_id}}).then(response => {
                this.$refs['show-eliminar-pago'].hide();
                swal("OK", "El pago se elimino correctamente.", "success")
                    .then((value) => {
                        location.href = `/cortes/details_cliente/${this.cliente_id}`;
                    });
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            }); 
        },
        selectImage(pago){
            this.formImage.pagoid = pago.id;
            this.$refs['show-upload-pago'].show();
        },
        fileChange(e){
            var fileInput = document.getElementById('archivoType');
            var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
            
            if(allowedExtensions.exec(fileInput.value)){
                this.formImage.file = e.target.files[0];
            } else {
                swal("Revisar formato de imagen", "Formato de imagen no permitido, solo puede ser en formato imagen (jpg, jpeg, png)", "warning");
            }
        },
        uploadImage(e){
            e.preventDefault();
            this.load = true;
            let formData = new FormData();
            formData.append('file', this.formImage.file, this.formImage.file.name);
            formData.append('pagoid', this.formImage.pagoid);
            axios.post('/cortes/upload_payment', formData, { 
                headers: { 'content-type': 'multipart/form-data' } }).then(response => {
                swal("OK", "La foto se guardo correctamente.", "success")
                    .then((value) => {
                        location.href = `/cortes/details_cliente/${this.cliente_id}`;
                    });
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>
    input[type="file"]#archivoType {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }
   
    label[for="archivoType"] {
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        background-color: #106BA0;
        display: inline-block;
        transition: all .5s;
        cursor: pointer;
        padding: 15px 40px !important;
        text-transform: uppercase;
        width: fit-content;
        text-align: center;
        border-radius: 20px;
    }
</style>