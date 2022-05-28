<template>
    <div>
        <b-row>
            <b-col align="right"><b>{{ form.editorial == 'MAJESTIC EDUCATION' ? 'Unidades (CDMX)':'Unidades' }}:</b></b-col>
            <b-col align="left">{{ form.unidades | formatNumber }}</b-col>
        </b-row>
        <div v-if="form.editorial == 'MAJESTIC EDUCATION'">
            <b-row>
                <b-col align="right"><b>Unidades (QUE):</b></b-col>
                <b-col align="left">{{ total_unidades_que | formatNumber }}</b-col>
            </b-row>
            <b-row>
                <b-col align="right"><b>Total:</b></b-col>
                <b-col align="left">{{ total_unidades | formatNumber }}</b-col>
            </b-row>
        </div>
    </div>
</template>

<script>
import formatNumber from './../../../mixins/formatNumber';
export default {
    props: ['form', 'total_unidades_que', 'total_unidades'],
    mixins: [formatNumber]
}
</script>

<style>

</style>