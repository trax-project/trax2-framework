import moment from 'moment'
import XapiFilters from './XapiFilters'

export default class XapiStatementsFilters extends XapiFilters {

    reset() {
        super.reset()
        this.actor = null
        this.verb = null
        this.object = null
        this.context = null
        this.from = null
        this.to = null
        this.chronological = false
        this.reveal = false
    }

    empty() {
        return !this.actor && !this.verb && !this.object 
            && !this.context && !this.from && !this.to
            && super.empty()
    }

    addParams(params) {
        params.options.reorder = true
        this.addActor(params)
        this.addVerb(params)
        this.addObject(params)
        this.addContext(params)
        this.addDate(params, 'from', 'since')
        this.addDate(params, 'to', 'until')
        this.addSorting(params)
        this.addReveal(params)
    }

    addActor(params) {
        if (!this.actor) {
            return false
        }
        params.filters['magicActor'] = this.actor.trim()
        return true
    }

    addVerb(params) {
        if (!this.verb) {
            return false
        }
        params.filters['magicVerb'] = this.verb.trim()
        return true
    }

    addObject(params) {
        if (!this.object) {
            return false
        }
        params.filters['magicObject'] = this.object.trim()
        return true
    }

    addContext(params) {
        if (!this.context) {
            return false
        }
        params.filters['magicContext'] = this.context.trim()
        return true
    }

    addDate(params, localFilter, serverFilter) {

        // Empty field.
        if (!this[localFilter]) {
            return false
        }
        let value = this[localFilter].trim()

        // Invalid value.
        if (!moment(value, 'YYYY-MM-DD HH:mm:ss').isValid()) {
            this.errors.add(localFilter, this.vm.$t('xapi.statements.search.date-error'))
            return false
        }

        // ISO date with since/until standard filters.
        params.filters[serverFilter] = moment(value, 'YYYY-MM-DD HH:mm:ss').format()
        return true
    }

    addSorting(params, localFilter, serverFilter) {
        params.sort = this.chronological ? ['id'] : ['-id']
        return true
    }

    addReveal(params, localFilter, serverFilter) {
        params.options.reveal = this.reveal
        return true
    }
}
