import XapiContextFilters from "./XapiContextFilters";
import XapiStatementsFilters from "./XapiStatementsFilters";
import XapiStatementsLoader from "./XapiStatementsLoader";

export default {
    install(Vue) {
        const contextFilters = new XapiContextFilters()
        const filters = {
            context: contextFilters,
            statements: new XapiStatementsFilters(contextFilters),
        }
        const statementsLoader = new XapiStatementsLoader('/trax/api/front/xapi/ext/statements', filters)

        Vue.prototype.$xapi = {
            filters,
            loaders: {
                statements: statementsLoader,
            }
        }
    }
}
