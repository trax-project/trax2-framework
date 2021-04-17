import XapiFilters from './XapiFilters'

export default class XapiAgentsFilters extends XapiFilters {

    constructor(contextFilters) {
        super(contextFilters)
        this.options = {
            objectType: { 'Agent': 'Agent', 'Group': 'Group' }
        }
    }

    reset() {
        super.reset()
        this.id = null
        this.objectType = null
        this.name = null
    }

    empty() {
        return !this.id && !this.objectType && !this.name 
            && super.empty()
    }

    addParams(params) {
        this.addId(params)
        this.addOjectType(params)
        this.addName(params)
    }

    addId(params) {
        if (!this.id) {
            return false
        }
        params.filters['magic'] = this.id.trim()
        return true
    }

    addOjectType(params) {
        if (!this.objectType) {
            return false
        }
        params.filters['xapiObjectType'] = this.objectType
        return true
    }

    addName(params) {
        if (!this.name) {
            return false
        }
        params.filters['xapiName'] = this.name.trim()
        return true
    }
}
