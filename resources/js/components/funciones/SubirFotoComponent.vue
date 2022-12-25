<template>
    <div>
        <b-form-group>
            <input :disabled="disabled" type="file" id="archivoType" 
                v-on:change="fileChange" required name="file">
            <label for="archivoType">
                <i class="fa fa-upload"></i> {{titulo}}
            </label>
            <p v-if="file.name">
                FOTO: <b>{{ file.name }}</b>
            </p>
            <!-- <div v-if="errors && errors.file" class="text-danger">
                La foto debe tener un tamaño máximo de 3MB y solo formato jpg, png, jpeg
            </div> -->
        </b-form-group>
    </div>
</template>

<script>
export default {
    props: ['disabled', 'titulo'],
    data(){
        return {
            file: {}
        }
    },
    methods: {
        fileChange(e){
            var fileInput = document.getElementById('archivoType');
            var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
            
            if(allowedExtensions.exec(fileInput.value)){
                this.file = e.target.files[0];
                this.$emit('uploadImage', this.file);
            } else {
                swal("Revisar formato de imagen", "Formato de imagen no permitido, solo puede ser en formato imagen (jpg, jpeg, png)", "warning");
            }
        },
    }
}
</script>

<style>

</style>