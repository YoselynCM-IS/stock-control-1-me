export default {
    methods: {
        label_tipo(tipo){
            if(tipo == 'cita') return 'Fecha de la cita';
            if(tipo == 'recordatorio') return 'Fecha del recordatorio';
            if(tipo == 'anotacion') return 'Fecha de ultima llamada';
            return 'Fecha';
        },
    }
}