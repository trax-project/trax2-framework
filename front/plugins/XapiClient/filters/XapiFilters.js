import FormErrors from '../../../classes/FormErrors'

export default class XapiFilters {

    constructor(contextFilters) {
        this.contextFilters = contextFilters
        this.errors = new FormErrors()
        this.reset()
    }

    attach(component, scope) {
        this.vm = component
        this.scope = scope
    }
    
    reset() {
        this.contextFilters.reset()
    }

    empty() {
        return this.contextFilters.empty()
    }

    addParams(params) {
    }

    get(params) {
        this.errors.clearAll()
        params = this.contextFilters.get(params)
        params.sort = ['id']    // Default value. Needed!
        if (this.scope) {
            params.scope = this.scope
        }
        this.addParams(params)
        return this.errors.added() ? false : params
    }
}
