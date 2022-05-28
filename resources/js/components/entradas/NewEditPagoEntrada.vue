<template>
    <div>
        <b-form @submit.prevent="guardarDeposito()">
            <b-row>
                <b-col sm="4"><label>Monto</label></b-col>
                <b-col sm="8">
                    <b-form-input v-model="form.pago" autofocus 
                        :state="state" :disabled="load" required></b-form-input>
                </b-col>
            </b-row>
            <b-row>
                <b-col sm="4"><label>Fecha del pago</label></b-col>
                <b-col sm="8">
                    <b-form-input v-model="form.fecha"
                        type="date" :disabled="load" required>
                    </b-form-input>
                </b-col>
            </b-row>
            <b-row>
                <b-col sm="4"><label>Nota (Opcional)</label></b-col>
                <b-col sm="8">
                    <b-form-textarea v-model="form.nota" 
                        :disabled="load" required rows="6" max-rows="6">
                    </b-form-textarea>
                </b-col>
            </b-row><br>
            <b-row>
                <b-col sm="8">
                    <b-alert show variant="info">
                        <i class="fa fa-exclamation-circle"></i> 
                        Verificar el pago antes de presionar <b>Guardar</b>, ya que después no se podrán realizar cambios.
                    </b-alert>
                </b-col>
                <b-col sm="4" class="text-center">
                    <b-button type="submit" variant="success" :disabled="load">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} 
                        <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                </b-col>
            </b-row>
        </b-form>
    </div>
</template>

<script>
import toast from '../../mixins/toast';
export default {
    mixins: [toast],
    props: ['form', 'edit'],
    data(){
        return {
            load: false,
            state: null
        }
    },
    methods: {
        guardarDeposito(){
            if(this.form.pago > 0){
                if(this.form.pago <= this.form.total_pendiente){
                    this.state = true;
                    this.load = true;
                    if(!this.edit){
                        axios.post('/entradas/save_pago', this.form).then(response => {
                            this.$emit('savedPago', response.data);
                            this.load = false;
                        }).catch(error => {
                            this.load = false;
                            this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                        });
                    } else {
                        axios.put('/entradas/update_pago', this.form).then(response => {
                            this.$emit('savedPago', response.data);
                            this.load = false;
                        }).catch(error => {
                            this.load = false;
                            this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                        });
                    }
                } else {
                    this.state = false;
                    this.makeToast('warning', 'El monto es mayor al total pendiente');
                }
            } else {
                this.state = false;
                this.makeToast('warning', 'El monto tiene que ser mayor a 0');
            }
            // if(this.repayment.pago > 0){
            //     if(this.repayment.pago <= this.entrada.total_pendiente){
            //         this.state = null;
            //         this.load = true;
            //         axios.put('/pago_entrada', this.repayment).then(response => {
            //             this.makeToast('success', 'El pago se guardo correctamente');
            //             this.load = false;
            //             this.repayment = {
            //                 entrada_id: 0,
            //                 pago: null
            //             };
            //             this.$bvModal.hide('modal-registrarPago');
            //             this.entradas[this.posicion].total_pagos = response.data.total_pagos;
            //             this.acumular();

            //         }).catch(error => {
            //             this.load = false;
            //             this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            //         });
            //     }
            //     else{
            //         this.state = false;
            //         this.makeToast('warning', 'El pago es mayor al total pendiente');
            //     }
            // }
            // else{
                // this.state = false;
                // this.makeToast('warning', 'El pago tiene que ser mayor a 0');
            // }
        },
    }
}
</script>

<style>

</style>