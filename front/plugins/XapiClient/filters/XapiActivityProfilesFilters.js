import XapiFilters from './XapiFilters'

export default class XapiActivityProfilesFilters extends XapiFilters {

    reset() {
        super.reset()
        this.activityId = null
        this.profileId = null
    }

    empty() {
        return !this.activityId && !this.profileId
            && super.empty()
    }

    addParams(params) {
        this.addActivityId(params)
        this.addProfileId(params)
    }

    addActivityId(params) {
        if (!this.activityId) {
            return false
        }
        params.filters['magicActivity'] = this.activityId.trim()
        return true
    }

    addProfileId(params) {
        if (!this.profileId) {
            return false
        }
        params.filters['magicProfile'] = this.profileId.trim()
        return true
    }
}
