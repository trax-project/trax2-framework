import XapiFilters from './XapiFilters'

export default class XapiLogsFilters extends XapiFilters {

    constructor(contextFilters) {
        super(contextFilters)
        this.options = {
            api: { 
                'statement': 'Statement API',
                'activity': 'Activity API',
                'agent': 'Agent API',
                'activity_profile': 'Activity Profile API',
                'agent_profile': 'Agent Profile API',
                'state': 'State API',
            },
            method: { 
                'POST': 'POST',
                'PUT': 'PUT',
                'GET': 'GET',
                'DELETE': 'DELETE',
            },
            status: { 
                0: 'Success',
                1: 'Error',
            },
        }
    }

    reset() {
        super.reset()
        this.api = null
        this.method = null
        this.status = null
    }

    empty() {
        return !this.api && !this.method && !this.status 
            && super.empty()
    }

    addParams(params) {
        params.sort = ['-id']
        this.addApi(params)
        this.addMethod(params)
        this.addStatus(params)
    }

    addApi(params) {
        if (!this.api) {
            return false
        }
        params.filters['api'] = this.api
        return true
    }

    addMethod(params) {
        if (!this.method) {
            return false
        }
        params.filters['method'] = this.method
        return true
    }

    addStatus(params) {
        if (!this.status) {
            return false
        }
        params.filters['error'] = this.status
        return true
    }
}
