export default {
    data(){
        return {
            resultslibros: []
        }
    },
    methods: {
        getLibros(titulo){
            axios.get('/mostrarLibros', {params: {queryTitulo: titulo}}).then(response => {
                this.resultslibros = response.data;
            }).catch(error => { });
        }
    },
}