<template>
    <div align="center">
        <b-form @submit.prevent="onSubmit()">
            <b-row class="my-1">
                <b-col align="right">Nombre</b-col>
                <div class="col-md-7">
                    <b-form-input 
                        id="input-name"
                        style="text-transform:uppercase;"
                        autofocus
                        v-model="form.name"
                        :disabled="loaded"
                        required>
                    </b-form-input>
                    <div v-if="errors && errors.name" class="text-danger">{{ errors.name[0] }}</div>
                </div>
            </b-row>
            <b-row class="my-1">
                <b-col align="right">Contacto</b-col>
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
        data() {
            return {
                form: {},
                errors: {},
                success: false,
                loaded: false
            }
        },
        methods: {
            // GUARDAR NUEVO CLIENTE
            onSubmit() {
                this.loaded = true;
                this.errors = {};
                axios.post('/new_client', this.form).then(response => {
                    this.form = {};
                    this.loaded = false;
                    this.success = true;
                    this.$emit('actualizarClientes', response.data);
                })
                .catch(error => {
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
                });
            },
        }
    }
</script>