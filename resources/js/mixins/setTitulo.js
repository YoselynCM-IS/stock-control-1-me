export default {
    methods: {
        setTitulo(tipo){
            if(tipo == 'reunion') return 'Lugar';
            if(tipo == 'videoconferencia') return 'Atraves de';
            return;
        },
        // DISTINGUIR DE OTRO COLOR LAS ACTIVIDADES COMPLETADAS
        rowClass(item, type) {
            if (!item) return
            if (item.estado == 'completado') return 'table-success'
            if (item.estado == 'vencido') return 'table-warning'
            if (item.estado == 'cancelado') return 'table-danger'
            if (item.estado == 'proximo') return 'table-primary'
        },
    },
}