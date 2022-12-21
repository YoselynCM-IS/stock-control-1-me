<template>
    <div>
        <b-row class="my-1">
            <b-col align="right">Estado</b-col>
            <div class="col-md-9">
                <b-form-select v-model="form.estado_id" :options="estados" required
                    :disabled="load"
                ></b-form-select>
            </div>
        </b-row>
        <b-row class="my-1">
            <b-col align="right">Correo electrónico</b-col>
            <div class="col-md-9">
                <b-form-input 
                    id="input-email"
                    v-model="form.email"
                    type="email"
                    :disabled="load"
                    required>
                </b-form-input>
                <div v-if="errors && errors.email" class="text-danger">{{ errors.email[0] }}</div>
            </div>
        </b-row>
        <b-row class="my-1">
            <b-col align="right">Teléfono</b-col>
            <div class="col-md-9">
                <b-form-input 
                    id="input-telefono"
                    v-model="form.telefono" 
                    :disabled="load"
                    required>
                </b-form-input>
                <div v-if="errors && errors.telefono" class="text-danger">{{ errors.telefono[0] }}</div>
            </div>
        </b-row>
        <b-row class="my-1">
            <b-col align="right">Responsable del cliente</b-col>
            <div class="col-md-9">
                <b-form-select v-model="form.user_id" :options="usuarios" required
                    :disabled="load"
                ></b-form-select>
            </div>
        </b-row>
    </div>
</template>

<script>
import getUsuarios from '../../../mixins/getUsuarios';
export default {
    props: ['form', 'load', 'errors'],
    mixins: [getUsuarios],
    data(){
        return{
            estados: []
        }
    },
    created: function(){
        this.getEstados();
        this.getUsuarios(6);
    },
    methods: {
        getEstados(){
            axios.get('/clientes/get_estados').then(response => {
                let edos = response.data;
                this.estados.push({ value: null, text: 'Selecciona una opción', disabled: true});
                edos.forEach(e => {
                    this.estados.push({ value: e.id, text: e.estado });
                });
            }).catch(error => { });
        },
    }
}
</script>

<style>

</style>