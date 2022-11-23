<template>
    <div align="center">
        <b-form @submit.prevent="onSubmit()">
            <b-row>
                <b-col>
                    <b-row class="my-1">
                        <b-col align="right">Tipo de cliente</b-col>
                        <div class="col-md-7">
                            <b-form-select v-model="form.tipo" :options="tipos" required
                                :disabled="loaded" autofocus
                            ></b-form-select>
                        </div>
                    </b-row>
                    <b-row class="my-1">
                        <b-col align="right">{{(form.tipo == null || form.tipo == 'PLANTEL') ? 'Plantel':'Distribuidor'}}</b-col>
                        <div class="col-md-7">
                            <b-form-input 
                                id="input-name"
                                style="text-transform:uppercase;"
                                v-model="form.name"
                                :disabled="loaded"
                                required>
                            </b-form-input>
                            <div v-if="errors && errors.name" class="text-danger">{{ errors.name[0] }}</div>
                        </div>
                    </b-row>
                    <b-row class="my-1">
                        <b-col align="right">{{(form.tipo == null || form.tipo == 'PLANTEL') ? 'Coordinador':'Comunicarse con'}}</b-col>
                        <div class="col-md-7">
                            <b-form-input 
                                id="input-name"
                                style="text-transform:uppercase;"
                                v-model="form.contacto"
                                :disabled="loaded">
                            </b-form-input>
                            <div v-if="errors && errors.contacto" class="text-danger">{{ errors.contacto[0] }}</div>
                        </div>
                    </b-row>
                </b-col>
                <b-col>
                    <b-row class="my-1">
                        <b-col align="right">Responsable del cliente</b-col>
                        <div class="col-md-7">
                            <b-form-select v-model="form.user_id" :options="usuarios" required
                                :disabled="loaded"
                            ></b-form-select>
                        </div>
                    </b-row>
                    <b-row class="my-1">
                        <b-col align="right">Condiciones de pago</b-col>
                        <div class="col-md-7">
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
                        <div class="col-md-7">
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
                    <b-row class="my-1">
                        <b-col align="right">Estado</b-col>
                        <div class="col-md-7">
                            <b-form-select v-model="form.estado_id" :options="estados" required
                                :disabled="loaded"
                            ></b-form-select>
                        </div>
                    </b-row>
                    <b-row class="my-1">
                        <b-col align="right">Teléfono</b-col>
                        <div class="col-md-7">
                            <b-form-input 
                                id="input-telefono"
                                v-model="form.telefono" 
                                :disabled="loaded"
                                required>
                            </b-form-input>
                            <div v-if="errors && errors.telefono" class="text-danger">{{ errors.telefono[0] }}</div>
                        </div>
                    </b-row>
                    <b-row class="my-1">
                        <b-col align="right">Teléfono (oficina)</b-col>
                        <div class="col-md-7">
                            <b-form-input 
                                id="input-telefono"
                                v-model="form.tel_oficina" 
                                :disabled="loaded"
                                required>
                            </b-form-input>
                            <div v-if="errors && errors.tel_oficina" class="text-danger">{{ errors.tel_oficina[0] }}</div>
                        </div>
                    </b-row>
                    <b-row class="my-1">
                        <b-col align="right">Correo electrónico</b-col>
                        <div class="col-md-7">
                            <b-form-input 
                                id="input-email"
                                v-model="form.email"
                                type="email"
                                :disabled="loaded"
                                required>
                            </b-form-input>
                            <div v-if="errors && errors.email" class="text-danger">{{ errors.email[0] }}</div>
                        </div>
                    </b-row>
                </b-col>
                <b-col>
                    <b-row class="my-1">
                        <b-col align="right">Dirección fiscal</b-col>
                        <div class="col-md-7">
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
                        <div class="col-md-7">
                            <b-form-input 
                                id="input-rfc"
                                style="text-transform:uppercase;"
                                v-model="form.rfc"
                                :disabled="loaded">
                            </b-form-input>
                            <div v-if="errors && errors.rfc" class="text-danger">{{ errors.rfc[0] }}</div>
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
    export default {
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
                ],
                estados: [],
                usuarios: []
            }
        },
        created: function(){
            this.getEstados();
            this.getUsuarios();
        },
        methods: {
            getEstados(){
                this.loaded = true;
                axios.get('/clientes/get_estados').then(response => {
                    let edos = response.data;
                    this.estados.push({ value: null, text: 'Selecciona una opción', disabled: true});
                    edos.forEach(e => {
                        this.estados.push({ value: e.id, text: e.estado });
                    });
                    this.loaded = false;
                }).catch(error => {
                    this.loaded = true;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            getUsuarios(){
                this.loaded = true;
                axios.get('/clientes/get_usuarios').then(response => {
                    let users = response.data;
                    this.usuarios.push({ value: null, text: 'Selecciona una opción', disabled: true});
                    users.forEach(u => {
                        this.usuarios.push({ value: u.id, text: u.name });
                    });
                    this.loaded = false;
                }).catch(error => {
                    this.loaded = true;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
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