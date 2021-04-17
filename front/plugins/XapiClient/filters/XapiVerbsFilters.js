import XapiFilters from './XapiFilters'

export default class XapiVerbsFilters extends XapiFilters {

    reset() {
        super.reset()
        this.id = null
    }

    empty() {
        return !this.id
            && super.empty()
    }

    addParams(params) {
        this.addId(params)
    }

    addId(params) {
        if (!this.id) {
            return false
        }
        params.filters['magic'] = this.id.trim()
        return true
    }
}
