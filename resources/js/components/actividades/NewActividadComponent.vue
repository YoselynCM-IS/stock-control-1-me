<template>
    <div>
        <b-form @submit.prevent="onSubmit()">
            <div>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Nombre de la actividad</label></b-col>
                    <b-col>
                        <b-form-input v-model="form.nombre" required :disabled="loaded" autofocus></b-form-input>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Tipo</label></b-col>
                    <b-col>
                        <b-form-select v-model="form.tipo" :options="tipos" required
                            :disabled="loaded" @change="initializeValues()"></b-form-select>
                    </b-col>
                </b-row>
                <b-row class="mb-2" v-if="form.tipo == 'reunion' || form.tipo == 'videoconferencia'">
                    <b-col sm="3" class="text-right"><label>{{setTitulo(form.tipo)}}</label></b-col>
                    <b-col>
                        <b-form-input v-model="form.lugar" required :disabled="loaded"></b-form-input>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Fecha</label></b-col>
                    <b-col>
                        <b-form-datepicker v-model="form.fecha" :disabled="loaded" required></b-form-datepicker>
                    </b-col>
                    <b-col v-if="form.tipo != 'nota'" sm="1" class="text-right"><label>Hora</label></b-col>
                    <b-col v-if="form.tipo != 'nota'">
                        <b-form-timepicker v-model="form.hora" locale="en" :disabled="loaded" required></b-form-timepicker>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Descripción</label></b-col>
                    <b-col>
                        <b-form-textarea v-model="form.descripcion" rows="3" max-rows="6"
                            :disabled="loaded" required></b-form-textarea>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Cliente relacionado</label></b-col>
                    <b-col>
                        <b-input v-model="queryCliente" @keyup="mostrarClientes()"
                            style="text-transform:uppercase;" :disabled="loaded">
                        </b-input>
                        <div class="list-group" v-if="clientes.length" id="listP">
                            <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                                v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                                {{ cliente.name }}
                            </a>
                        </div>
                    </b-col>
                </b-row>
            </div>
            <b-row class="mb-2">
                <b-col></b-col>
                <b-col sm="9">
                    <b-button type="submit" :disabled="loaded" variant="success"
                        pill block>
                        <i class="fa fa-check"></i> {{ !loaded ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="loaded"></b-spinner>
                    </b-button>
                </b-col>
            </b-row>
        </b-form>
    </div>
</template>

<script>
import searchCliente from '../../mixins/searchCliente';
import setTitulo from '../../mixins/setTitulo';
export default {
    mixins: [searchCliente, setTitulo],
    data(){
        return {
            loaded: false, 
            form: {
                cliente_id: null,
                tipo: null,
                lugar: null,
                fecha: null,
                hora: null,
                descripcion: null
            },
            errors: {},
            tipos: [
                { value: null, text: 'Selecciona una opción', disabled: true},
                { value: 'reunion', text: 'Reunión' },
                { value: 'videoconferencia', text: 'Video conferencia' },
                { value: 'llamar', text: 'Llamar' },
                { value: 'enviarcorreo', text: 'Enviar correo' },
                { value: 'nota', text: 'Nota' }
            ],
        }
    },
    created: function(){
        
    },
    methods: {
        initializeValues(){
            this.form.lugar = null;
            if(this.form.tipo == 'nota') this.form.hora = null;
        },
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