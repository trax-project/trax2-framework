import XapiFilters from './XapiFilters'

export default class XapiAgentProfilesFilters extends XapiFilters {

    reset() {
        super.reset()
        this.agentId = null
        this.profileId = null
    }

    empty() {
        return !this.agentId && !this.profileId
            && super.empty()
    }

    addParams(params) {
        this.addAgentId(params)
        this.addProfileId(params)
    }

    addAgentId(params) {
        if (!this.agentId) {
            return false
        }
        params.filters['uiAgent'] = this.agentId.trim()
        return true
    }

    addProfileId(params) {
        if (!this.profileId) {
            return false
        }
        params.filters['uiProfile'] = this.profileId.trim()
        return true
    }
}
