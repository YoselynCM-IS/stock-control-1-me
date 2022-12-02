<template>
    <div>
        <b-form @submit.prevent="onSubmit()">
            <div>
                <div v-if="!addProspecto">
                    <b-form-group label-cols-sm="9" label-cols-lg="3" label-align-sm="right"
                        label="Cliente">
                        <!-- BUSCAR CLIENTE -->
                        <b-input v-model="queryCliente" @keyup="mostrarClientes()" autofocus
                            style="text-transform:uppercase;" :disabled="loaded" required>
                        </b-input>
                        <div class="list-group" v-if="clientes.length" id="listP">
                            <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                                v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                                {{ cliente.name }}
                            </a>
                        </div>
                        <b-button class="mt-1" variant="secondary" pill size="sm" block
                            @click="addProspecto = true" :disabled="form.cliente_id != null">
                            Cliente no registrado
                        </b-button>
                    </b-form-group>
                </div>
                <div v-else class="mb-5">
                    <h6><b>Datos del cliente</b></h6>
                    <datos-parte-1 :form="form.prospecto" :loaded="loaded" :errors="errors"></datos-parte-1>
                    <datos-parte-2 :form="form.prospecto" :loaded="loaded" :errors="errors"></datos-parte-2>
                </div>
            </div>
            <div>
                <h6><b>Datos de la actividad</b></h6>
                <b-form-group label-cols-sm="9" label-cols-lg="3" label-align-sm="right"
                    label="Tipo de actividad">
                    <b-form-select v-model="form.tipo" :options="tipos" required
                        :disabled="loaded"></b-form-select>
                </b-form-group>
                <b-form-group label-cols-sm="9" label-cols-lg="3" label-align-sm="right"
                    label="Descripción">
                    <b-form-textarea v-model="form.descripcion" rows="3" max-rows="6"
                        :disabled="loaded" required></b-form-textarea>
                </b-form-group>
                <b-form-group label-cols-sm="9" label-cols-lg="3" label-align-sm="right"
                    :label="label_tipo(form.tipo)" >
                    <b-form-datepicker v-model="form.fecha_recordatorio" :disabled="loaded"
                        required></b-form-datepicker>
                </b-form-group>
            </div>
            <b-row>
                <b-col></b-col>
                <b-col sm="9">
                    <b-button type="submit" :disabled="loaded" variant="success"
                        pill block class="mt-2">
                        <i class="fa fa-check"></i> {{ !loaded ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="loaded"></b-spinner>
                    </b-button>
                </b-col>
            </b-row>
        </b-form>
    </div>
</template>

<script>
import searchCliente from '../../mixins/searchCliente';
import DatosParte1 from '../clientes/partials/DatosParte1.vue';
import DatosParte2 from '../clientes/partials/DatosParte2.vue';
import setTipoAct from '../../mixins/setTipoAct';
export default {
  components: { DatosParte1, DatosParte2 },
    mixins: [searchCliente, setTipoAct],
    data(){
        return {
            addProspecto: false,
            loaded: false, 
            form: {
                cliente_id: null,
                tipo: null,
                descripcion: null,
                fecha_recordatorio: null,
                prospecto: {
                    name: null,
                    contacto: null,
                    estado_id: null,
                    email: null,
                    telefono: null,
                    user_id: null
                }
            },
            errors: {},
            tipos: [
                { value: null, text: 'Selecciona una opción', disabled: true},
                { value: 'cita', text: 'Cita' },
                { value: 'recordatorio', text: 'Recordatorio' },
                { value: 'anotacion', text: 'Anotación' }
            ],
        }
    },
    created: function(){
        
    },
    methods: {
        selectCliente(cliente){
            this.form.cliente_id = cliente.id;
            this.queryCliente = cliente.name;
            this.clientes = [];
        },
        onSubmit(){
            this.loaded = true;
            axios.post('/actividades/store', this.form).then(response => {
                this.$emit('actividadSaved', response.data);
                this.loaded = false;
            }).catch(error => {
                this.loaded = false;
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        }
    }
}
</script>

<style>

</style>