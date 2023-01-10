<template>
    <div>
        <b-form @submit.prevent="onSubmit">
            <b-row>
                <b-col>
                    <b-form-group label="Tipo">
                        <b-form-select v-model="form.tipo" :options="tipos" :disabled="load" required></b-form-select>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Fecha">
                        <b-form-datepicker v-model="form.fecha" :disabled="load" required></b-form-datepicker>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Hora">
                        <b-form-timepicker v-model="form.hora" locale="en" :disabled="load" required></b-form-timepicker>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-form-group label="Respuesta">
                        <b-form-select v-model="form.respuesta" :options="respuestas" :disabled="load" required></b-form-select>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Duración">
                        <b-row>
                            <b-col class="text-center">
                                <b-form-input v-model="form.duracion.horas" type="number" :disabled="load" required></b-form-input>
                                <label>horas</label>
                            </b-col>
                            <b-col class="text-center">
                                <b-form-input v-model="form.duracion.minutos" type="number" :disabled="load" required></b-form-input>
                                <label>minutos</label>
                            </b-col>
                            <b-col class="text-center">
                                <b-form-input v-model="form.duracion.segundos" type="number" :disabled="load" required></b-form-input>
                                <label>segundos</label>
                            </b-col>
                        </b-row>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-form-group label="Comentario">
                <b-form-textarea v-model="form.comentario" rows="3" max-rows="6"></b-form-textarea>
            </b-form-group>
            <div class="text-right">
                <b-button type="submit" variant="success" pill class="mt-2">
                    <i class="fa fa-check"></i> Guardar
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
export default {
    props: ['cliente_id'],
    data() {
        return {
            load: false,
            form: {
                cliente_id: null,
                tipo: null,
                fecha: null,
                hora: null,
                duracion: {
                    horas: 0,
                    minutos: 0,
                    segundos: 0 
                },
                respuesta: null,
                comentario: null
            },
            tipos: [
                {value: null, text: 'Selecciona una opción', disabled: true},
                {value: 'recibida', text: 'Recibida'},
                {value: 'realizada', text: 'Realizada'}
            ],
            respuestas: [
                {value: null, text: 'Selecciona una opción', disabled: true},
                {value: 'sin respuesta', text: 'sin respuesta'},
                {value: 'ocupado', text: 'ocupado'},
                {value: 'buzon de voz', text: 'buzón de voz'},
                {value: 'llamar mas tarde', text: 'llamar más tarde'},
                {value: 'numero equivocado', text: 'número equivocado'},
                {value: 'no interesado', text: 'no interesado'},
            ]
        }
    },
    methods: {
        onSubmit(){
            this.load = true;
            this.form.cliente_id = this.cliente_id;
            axios.post('/clientes/save_seguimiento', this.form).then(response => {
                this.load = false;
                this.$emit('addedSeguimiento', response.data);
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>