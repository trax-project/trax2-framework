import Auth from "./Auth";

export default {
    install(Vue) {
        // Make auth and its properties reactive.
        let app = new Vue({
            data: {
                auth: new Auth()
            }
        });
        Vue.prototype.$auth = app.auth;
    }
}
  