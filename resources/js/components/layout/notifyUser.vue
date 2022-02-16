<template>

    <li class="dropdown">
        <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
            <i class="fa fa-bell"></i>  <span class="label label-danger" v-if="notifyCount > 0">{{ notifyCount }}</span>
        </a>
        <ul class="dropdown-menu dropdown-alerts list-alerts">
            <li v-if="lstNotifyEnvio.length > 0">
                <a href="#" class="dropdown-item" @click="locationEnvio()">
                    <div  class="content-alert text-center">
                        <b>{{ lstNotifyEnvio.length }} {{ lstNotifyEnvio.length > 1 ? "notificaciones" : "notificacion" }} de envío</b>
                    </div>
                </a>
            </li>
            <li v-for="(notify) in lstNotifyEnvio" :key="notify.id">
                <a href="#" class="dropdown-item" @click="locationEnvio()">
                    <div class="content-alert">
                        <i class="fa fa-envelope fa-fw text-danger"></i> Documento no enviado: {{ notify.data.body.serie + ' - ' + notify.data.body.correlativo }}
                        <span class="float-right text-muted small">{{ notify.data.time }}</span>
                    </div>
                </a>
            </li>
            <li class="dropdown-divider" v-if="lstNotifyRegularize.length > 0 && lstNotifyEnvio.length > 0"></li>
            <li v-if="lstNotifyRegularize.length > 0">
                <a href="#" class="dropdown-item" @click="locationRegularize()">
                    <div  class="content-alert text-center">
                        <b>{{ lstNotifyRegularize.length }} {{ lstNotifyRegularize.length > 1 ? "notificaciones" : "notificacion"}} de regularizar</b>
                    </div>
                </a>
            </li>
            <li v-for="(notify) in lstNotifyRegularize" :key="notify.id">
                <a href="#" class="dropdown-item" @click="locationRegularize()">
                    <div  class="content-alert">
                        <i class="fa fa-envelope fa-fw text-danger"></i> Documento por regularizar: {{ notify.data.body.serie + ' - ' + notify.data.body.correlativo }}
                        <span class="float-right text-muted small">{{ notify.data.time }}</span>
                    </div>
                </a>
            </li>
            <li v-if="notifyCount == 0">
                <a href="#" class="dropdown-item">
                    <div  class="content-alert">
                        <i class="fa fa-exclamation"></i> No tiene notificatciones
                        <span class="float-right text-muted small">-</span>
                    </div>
                </a>
            </li>
        </ul>
    </li>
</template>
<script>
import Axios from "axios";
import "datatables.net-bs4";
import "datatables.net-buttons-bs4";
export default {
    data() {
        return {
            notifyCount: 0,
            lstNotifyEnvio: [],
            lstNotifyRegularize: [],
        };
    },
    mounted() {
        let $this = this;
        $this.loadNotifications();
        Echo.channel("notifySunat").listen("NotifySunatEvent", e => {
            $this.loadNotifications();
        });
    },
    methods: {
        loadNotifications: function()
        {
            axios.get(route('getNotifications')).then((value) => {
                let data = value.data;
                this.notifyCount = data.notifications.length;
                this.lstNotifyEnvio = data.notifications.filter(notify => notify.data.head == 'envio');
                this.lstNotifyRegularize = data.notifications.filter(notify => notify.data.head == 'regularize');
            })
        },
        locationEnvio: function()
        {
            window.location = route('consultas.ventas.alerta.envio');
        },
        locationRegularize: function()
        {
            window.location = route('consultas.ventas.alerta.regularize');
        }
    },
    updated() {
        this.$nextTick(function () {

        });
    }
};
</script>
