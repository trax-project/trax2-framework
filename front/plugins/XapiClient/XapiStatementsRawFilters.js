import moment from 'moment'
import FormErrors from '../../classes/FormErrors'

export default class XapiStatementsRawFilters {

    constructor(contextFilters) {
        this.contextFilters = contextFilters
        this.errors = new FormErrors()
        this.reset()
    }

    attach(component) {
        this.vm = component
    }

    reset() {
        this.actor = null
        this.verb = null
        this.object = null
        this.context = null
        this.from = null
        this.to = null
        this.chronological = false
        this.contextFilters.reset()
    }

    empty() {
        return !this.actor && !this.verb && !this.object 
            && !this.context && !this.from && !this.to
            && this.contextFilters.empty()
    }

    get(params) {
        this.errors.clearAll()
        params = this.contextFilters.get(params)
        this.addActor(params)
        this.addVerb(params)
        this.addObject(params)
        this.addContext(params)
        this.addDate(params, 'from', 'since')
        this.addDate(params, 'to', 'until')
        this.addSorting(params)
        return this.errors.added() ? false : params
    }

    addActor(params) {
        return this.addAgentOrGroup(params, 'actor', 'actor', true)
    }

    addVerb(params) {

        // Empty field.
        if (!this.verb) {
            return false
        }
        let verb = this.verb.trim()
        
        // Error: a single word.
        if (verb.split(' ').length > 1) {
            this.errors.add('verb', this.vm.$t('xapi.statements.search.verb-error'))
            return false
        }

        // Full IRI vs fulltext search on ID
        if (verb.substring(0, 4) == 'http') {
            params.filters['data->verb->id'] = verb
        } else {
            params.filters['data->verb->id'] = { $text: verb }
        }
        return true
    }

    addObject(params) {

        // Agent or group.
        if (this.addAgentOrGroup(params, 'object', 'object')) {
            return true
        }

        // Empty field.
        if (!this.object) {
            return false
        }
        let object = this.object.trim()

        // Fulltext search on name.
        if (object.substring(0, 5) == 'name:') {
            params.filters['data->object->definition->name'] = { $text: object.substring(5) }
            return true
        } 

        // Fulltext search on type.
        if (object.substring(0, 5) == 'type:') {
            params.filters['data->object->definition->type'] = { $text: object.substring(5) }
            return true
        } 
        
        // Fulltext search on ID.
        params.filters['data->object->id'] = { $text: object }
        return true
    }

    addContext(params) {

        // Instructor / Team.
        if (this.addContextAgentOrGroup(params)) {
            return true
        }

        // Empty field.
        if (!this.context) {
            return false
        }
        let context = this.context.trim()

        // Search in parent activities.
        if (context.substring(0, 7) == 'parent:') {
            params.filters['data->context->contextActivities->parent[*]->id'] = context.substring(7)
            return true
        } 
        
        // Search in grouping activities.
        if (context.substring(0, 9) == 'grouping:') {
            params.filters['data->context->contextActivities->grouping[*]->id'] = context.substring(9)
            return true
        } 
        
        // Search in category activities.
        if (context.substring(0, 9) == 'category:') {
            params.filters['data->context->contextActivities->category[*]->id'] = context.substring(9)
            return true
        } 
        
        // Search in category activities.
        if (context.substring(0, 8) == 'profile:') {
            params.filters['data->context->contextActivities->category[*]->id'] = { $text: context.substring(8) }
            return true
        } 
        
        // Search in all context activities.
        params.filters.$or = {
            'data->context->contextActivities->parent[*]->id': context,
            'data->context->contextActivities->grouping[*]->id': context,
            'data->context->contextActivities->category[*]->id': context,
            'data->context->contextActivities->other[*]->id': context,
        }
        return true
    }

    addAgentOrGroup(params, filter, target, fulltext = false) {

        // Empty field.
        if (!this[filter]) {
            return false
        }
        let value = this[filter].trim()
        
        // Account.
        if (value.substring(0, 8) == 'account:') {
            let account = value.substring(8).split('@')
            params.filters['data->' + target + '->account->name'] = account[0]
            if (account.length > 1) {
                params.filters['data->' + target + '->account->homePage'] = account[1]
            }
            return true
        }
        
        // Mbox.
        if (value.indexOf('@') > -1) {
            params.filters['data->' + target + '->mbox'] = 'mailto:' + value
            return true
        } 

        // Fulltext search on name.
        if (fulltext) {
            params.filters['data->' + target + '->name'] = { $text: value }
            return true
        }
        return false
    }

    addContextAgentOrGroup(params) {

        // Empty field.
        if (!this.context) {
            return false
        }
        let value = this.context.trim()
        
        // Account.
        if (value.substring(0, 8) == 'account:') {
            let account = value.substring(8).split('@')
            if (account.length == 1) {
                params.filters.$or = {
                    'data->context->instructor->account->name': account[0],
                    'data->context->team->account->name': account[0],
                }
            } else {
                params.filters.$or = {
                    "$and": {
                        'data->context->instructor->account->name': account[0],
                        'data->context->instructor->account->homePage': account[1],
                    },
                    "$and": {
                        'data->context->team->account->name': account[0],
                        'data->context->team->account->homePage': account[1],
                    },
                }
            }
            return true
        }
        
        // Mbox.
        if (value.indexOf('@') > -1) {
            params.filters.$or = {
                'data->context->instructor->mbox': 'mailto:' + value,
                'data->context->team->mbox': 'mailto:' + value,
            }
            return true
        } 
        return false
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
}
