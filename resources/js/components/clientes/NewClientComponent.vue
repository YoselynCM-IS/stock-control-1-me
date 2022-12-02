<template>
    <div align="center">
        <b-form @submit.prevent="onSubmit()">
            <b-row>
                <b-col>
                    <b-row class="my-1">
                        <b-col align="right">Tipo de cliente</b-col>
                        <div class="col-md-9">
                            <b-form-select v-model="form.tipo" :options="tipos" required
                                :disabled="loaded" autofocus
                            ></b-form-select>
                        </div>
                    </b-row>
                    <datos-parte-1 :form="form" :loaded="loaded" :errors="errors"></datos-parte-1>
                </b-col>
                <b-col>
                    <b-row class="my-1">
                        <b-col align="right">Condiciones de pago</b-col>
                        <div class="col-md-9">
                            <b-form-input 
                                id="input-condiciones_pago"
                                style="text-transform:uppercase;"
                                v-model="form.condiciones_pago" 
                                :disabled="loaded"
                                required>
                            </b-form-input>
                            <div v-if="errors && errors.condiciones_pago" class="text-danger">{{ errors.condiciones_pago[0] }}</div>
                        </div>
                    </b-row>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-row class="my-1">
                        <b-col align="right">Dirección</b-col>
                        <div class="col-md-9">
                            <b-form-input 
                                id="input-direccion"
                                style="text-transform:uppercase;"
                                v-model="form.direccion" 
                                :disabled="loaded"
                                required>
                            </b-form-input>
                            <div v-if="errors && errors.direccion" class="text-danger">{{ errors.direccion[0] }}</div>
                        </div>
                    </b-row>
                    <datos-parte-2 :form="form" :loaded="loaded" :errors="errors"></datos-parte-2>
                </b-col>
                <b-col>
                    <b-row class="my-1">
                        <b-col align="right">Dirección fiscal</b-col>
                        <div class="col-md-9">
                            <b-form-input 
                                id="input-fiscal"
                                style="text-transform:uppercase;"
                                v-model="form.fiscal" 
                                :disabled="loaded"
                                required>
                            </b-form-input>
                            <div v-if="errors && errors.fiscal" class="text-danger">{{ errors.fiscal[0] }}</div>
                        </div>
                    </b-row>
                    <b-row class="my-1">
                        <b-col align="right">RFC</b-col>
                        <div class="col-md-9">
                            <b-form-input 
                                id="input-rfc"
                                style="text-transform:uppercase;"
                                v-model="form.rfc"
                                :disabled="loaded">
                            </b-form-input>
                            <div v-if="errors && errors.rfc" class="text-danger">{{ errors.rfc[0] }}</div>
                        </div>
                    </b-row>
                    <b-row class="my-1">
                        <b-col align="right">Teléfono (oficina)</b-col>
                        <div class="col-md-9">
                            <b-form-input 
                                id="input-telefono"
                                v-model="form.tel_oficina" 
                                :disabled="loaded"
                                required>
                            </b-form-input>
                            <div v-if="errors && errors.tel_oficina" class="text-danger">{{ errors.tel_oficina[0] }}</div>
                        </div>
                    </b-row>
                </b-col>
            </b-row>
            <hr>
            <div align="right">
                <b-button type="submit" :disabled="loaded" variant="success">
                    <i class="fa fa-check"></i> {{ !loaded ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="loaded"></b-spinner>
                </b-button>
            </div>
        </b-form>
        <hr>
        <b-alert v-if="success" show dismissible>
            <i class="fa fa-check"></i>Cliente guardado
        </b-alert>
    </div>
</template>

<script>
import DatosParte1 from './partials/DatosParte1.vue';
import DatosParte2 from './partials/DatosParte2.vue';
    export default {
        components: { DatosParte1, DatosParte2 },
        props: ['form', 'edit'],
        data() {
            return {
                errors: {},
                success: false,
                loaded: false,
                tipos: [
                    { value: null, text: 'Selecciona una opción', disabled: true},
                    { value: 'PLANTEL', text: 'PLANTEL' },
                    { value: 'DISTRIBUIDOR', text: 'DISTRIBUIDOR' }
                ]
            }
        },
        methods: {
            // GUARDAR NUEVO CLIENTE
            onSubmit() {
                this.loaded = true;
                this.errors = {};
                if(!this.edit){
                    axios.post('/clientes/store', this.form).then(response => {
                        this.loaded = false;
                        this.success = true;
                        this.$emit('actualizarClientes', response.data);
                    }).catch(error => {
                        this.catch_error(error);
                    });
                } else {
                    axios.put('/clientes/update', this.form).then(response => {
                        this.loaded = false;
                        this.success = true;
                        this.$emit('actualizarClientes', response.data);
                    }).catch(error => {
                        this.catch_error(error);
                    });
                }
            },
            catch_error(error){
                this.loaded = false;
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    this.$bvToast.toast('Ocurrió un problema. Verifica tu conexión a internet y/o actualiza la página para volver a intentar.', {
                        title: 'Mensaje',
                        variant: 'danger',
                        solid: true
                    })
                }
            }
        }
    }
</script>