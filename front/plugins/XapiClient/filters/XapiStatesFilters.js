import XapiFilters from './XapiFilters'

export default class XapiStatesFilters extends XapiFilters {

    reset() {
        super.reset()
        this.agentId = null
        this.activityId = null
        this.stateId = null
    }

    empty() {
        return !this.agentId && !this.activityId && !this.stateId
            && super.empty()
    }

    addParams(params) {
        this.addActivityId(params)
        this.addAgentId(params)
        this.addStateId(params)
    }

    addActivityId(params) {
        if (!this.activityId) {
            return false
        }
        params.filters['magicActivity'] = this.activityId.trim()
        return true
    }

    addAgentId(params) {
        if (!this.agentId) {
            return false
        }
        params.filters['magicAgent'] = this.agentId.trim()
        return true
    }

    addStateId(params) {
        if (!this.stateId) {
            return false
        }
        params.filters['magicState'] = this.stateId.trim()
        return true
    }
}
