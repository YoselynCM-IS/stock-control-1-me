export default {
    props: ['actividad'],
    data(){
        return {
            load: false
        }
    },
    methods: {
        onUpdate(){
            this.load = true;
            axios.put('/actividades/update_estado', this.actividad).then(response => {
                this.$emit('updatedActEstado', true);
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    },
}