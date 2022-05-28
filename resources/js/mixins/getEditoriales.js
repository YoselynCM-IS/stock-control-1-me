export default {
    data(){
        return {
            options: []
        }
    },
    methods: {
        get_editoriales(){
            axios.get('/libro/get_editoriales').then(response => {
                let editoriales = response.data;
                this.options.push({
                    value: null, text: 'Seleccionar una opción', disabled: true
                });
                editoriales.forEach(editorial => {
                    this.options.push({
                        value: editorial.editorial,
                        text: editorial.editorial
                    });
                });
            }).catch(error => { });
        }
    },
}