/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import 'flatpickr/dist/flatpickr.css';
import flatPickr from 'vue-flatpickr-component';
import vSelect from 'vue-select';
import VTooltip from 'v-tooltip'
import moment from "moment"
import "../../public/Inspinia/js/plugins/select2/select2.full.min.js";
window.Vue = require('vue');
window.$fechaActual = moment().format("YYYY-MM-DD");
window.$fechaStartMhont= moment().startOf('month').format('YYYY-MM-DD');
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))
Vue.component('notify-component', require('./components/layout/notifyUser.vue').default);
Vue.component('v-select', vSelect)
Vue.component("v-picker",flatPickr);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const appNotify = new Vue({
    el: '#appNotify',
});
Vue.use(VTooltip);
Vue.component('v-paginate', require('./components/Pagination.vue').default);
Vue.component('compras-component', require('./pages/compras/IndexCompra.vue').default);
Vue.component('compras-edit-component', require('./pages/compras/partials/edit.vue').default);

const pages = new Vue({
    el: "#content-system"
});