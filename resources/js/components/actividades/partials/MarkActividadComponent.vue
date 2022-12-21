<template>
    <div>
        <b-card class="mb-5" header="Completar actividad"
            border-variant="success" header-bg-variant="success" header-text-variant="white">
            <b-form @submit.prevent="onUpdate()">
                <b-form-group label="¿La actividad se completo con exito?">
                    <b-form-radio v-model="actividad.exitosa" name="exitosa" :value="true">Si</b-form-radio>
                    <b-form-radio v-model="actividad.exitosa" name="exitosa" :value="false">No</b-form-radio>
                </b-form-group>
                <b-form-group label="Observaciones">
                    <b-form-textarea v-model="actividad.observaciones" rows="3" max-rows="6"
                                :disabled="load"></b-form-textarea>
                </b-form-group>
                <div class="mt-2 text-right">
                    <b-button type="submit" :disabled="load" variant="success" pill>
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                </div>
            </b-form>
        </b-card>
    </div>
</template>

<script>
import updEdoAct from '../../../mixins/updEdoAct';
export default {
    mixins: [updEdoAct]
}
</script>

<style>

</style>