import VueRouter from "vue-router";

export default class RouterFactory {

    static make(routes, activeClass = 'active') {

        let segments = String(window.baseURL).split('/');
        segments.splice(0, 3);
        let baseRoute = '/' + segments.join('/');
    
        return new VueRouter({
            mode: 'history',
            base: baseRoute,
            routes,
            linkActiveClass: 'active',
            linkExactActiveClass: "active",
            scrollBehavior: (to, from, savedPosition) => {
                if (savedPosition) {
                    return savedPosition
                } else if (to.hash) {
                    return {selector: to.hash}
                } else {
                    return { x: 0, y: 0 }
                }
            }
        });
    }
}
