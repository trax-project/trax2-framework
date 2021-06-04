import FormErrors from '../../../classes/FormErrors'

export default class XapiFilters {

    constructor(contextFilters) {
        this.contextFilters = contextFilters
        this.errors = new FormErrors()
        this.reset()
    }

    attach(component) {
        this.vm = component
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
        this.addParams(params)
        return this.errors.added() ? false : params
    }
}
