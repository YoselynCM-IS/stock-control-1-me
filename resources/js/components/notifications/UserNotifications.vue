<template>
    <div>
        <li class="list-inline-item dropdown notif">
            <a class="nav-link dropdown-toggle arrow-none" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i class="fa fa-fw fa-bell-o"></i>
                <span v-if="notifications.length > 0"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ notifications.length }}
                </span>

            </a>

            <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-lg">
                <!-- item-->
                <div class="dropdown-item noti-title">
                    <h6>
                        <b>
                            <span class="label label-danger pull-xs-right">
                                {{ notifications.length }}
                            </span>
                            Notificaciones sin leer
                        </b>
                    </h6>
                </div><hr>

                <!-- All-->
                <button v-for="(notification, index) in notifications"
                    :key="index" style="font-size: 12px;"
                    class="dropdown-item notify-item notify-all" @click="getActividad(notification, index)"
                    v-html="notification.title">
                </button>
            </div>
        </li>
        <b-modal id="modal-detailsActividad" title="Detalles de la actividad" hide-footer size="lg">
            <details-actividad :actividad="actividad" :role_id="null"></details-actividad>
        </b-modal>
    </div>
</template>

<script>
import DetailsActividad from '../actividades/partials/DetailsActividad.vue';
    export default {
  components: { DetailsActividad },
        props: ['user_id', 'noleidos'],
        data () {
            return {
                notifications: [],
                actividad: null
            }
        },
        mounted () {
            this.noleidos.forEach(noleido => {
                this.notifications.push(this.get_notificaciones(noleido, noleido.data.actividad, noleido.data.mensaje));
            });
            Echo.private('App.User.' + this.user_id)
                .notification((notification) => {
                    this.notifications.unshift(this.get_notificaciones(notification, notification.actividad, notification.mensaje));
                    if(notification.type.includes('RecordarActNotification')){
                        swal(notification.actividad.nombre, notification.mensaje, "info")
                    }
                });
        },
        methods: {
            get_notificaciones(datos, actividad, mensaje){
                return {
                    id: actividad.id,
                    title: `${mensaje} <b>${actividad.nombre}</b>`,
                    notification_id: datos.id
                };
            },
            getActividad(notification, index){
                axios.get('/actividades/view_notification', {params: {
                    actividad_id: notification.id, notification_id: notification.notification_id
                }}).then(response => {
                    this.actividad = response.data;
                    this.$bvModal.show('modal-detailsActividad');
                    this.notifications.splice(index,1);
                }).catch(error => {});
            }
        }
    }
</script>

<style scoped>

</style>