export default class XapiContextFilters {

    constructor() {
        this.clients = []
        this.options = {
            entities: {},
            clients: {},
            accesses: {},
        }
        this.reset()
    }

    loadOptions() {
        let filters = Vue.prototype.$auth.owner
            ? { owner_id: Vue.prototype.$auth.owner.id }
            : {}

        axios.get('/trax/api/front/clients', { params: {
            relations: ['accesses'],
            filters: filters
        }}).then(respClients => {
            axios.get('/trax/api/front/entities', { params: {
                filters: filters
            }}).then(respEntities => {
                this.optionsLoaded(respClients.data.data, respEntities.data.data)
                this.resolve()
            }).catch(error => {
                this.reject(error)
            })
        }).catch(error => {
            this.reject(error)
        })

        return new Promise((resolve, reject) => {
            this.resolve = resolve
            this.reject = reject
        })
    }

    reset() {
        this.entity_id = null
        this.client_id = null
        this.access_id = null
    }

    empty() {
        return !this.entity_id && !this.client_id && !this.access_id 
    }

    get(params = {}) {
        if (!params.filters) {
            params.filters = {}
        }
        this.addEntity(params)
        this.addClient(params)
        this.addAccess(params)
        this.addOwner(params)
        return params
    }

    addEntity(params) {
        if (this.entity_id && !this.client_id) {
            params.filters['entity_id'] = this.entity_id
        }
    }

    addClient(params) {
        if (this.client_id && !this.access_id) {
            params.filters['client_id'] = this.client_id
        }
    }

    addAccess(params) {
        if (this.access_id) {
            params.filters['access_id'] = this.access_id
        }
    }
    
    addOwner(params) {
        if (Vue.prototype.$auth.owner) {
            params.filters['owner_id'] = Vue.prototype.$auth.owner.id
        }
    }
    
    optionsLoaded(clients, entities) {
        // Refresh entities.
        this.setEntities(entities)
        if (this.entity_id && this.options.entities[this.entity_id] === undefined) {
            this.entity_id = null
            this.client_id = null
            this.access_id = null
        }

        // Refresh clients.
        this.clients = clients
        this.setEntityClients(this.entity_id)
        if (this.client_id && this.options.clients[this.client_id] === undefined) {
            this.client_id = null
            this.access_id = null
        }

        // Refresh accesses.
        this.setClientAccesses(this.client_id)
        if (this.access_id && this.options.accesses[this.access_id] === undefined) {
            this.access_id = null
        }
    }

    entityChanged(entityId) {
        this.client_id = null
        this.access_id = null
        this.setEntityClients(entityId)   
        this.options.accesses = {}
    }

    clientChanged(clientId) {
        this.access_id = null
        this.setClientAccesses(clientId)
    }

    setEntities(entities) {
        this.options.entities = {}
        entities.forEach(entity => {
            this.options.entities[entity.id] = entity.name
        })
    }

    setEntityClients(entityId) {
        this.options.clients = {}
        this.clients.forEach(client => {
            if (client.entity_id == entityId || (entityId == '' && !client.entity_id)) {
                this.options.clients[client.id] = client.name
            }
        })
    }

    setClientAccesses(clientId) {
        this.options.accesses = {}
        if (clientId) {
            let accesses = this.clients.find( client => client.id == clientId).accesses
            accesses.forEach(access => {
                this.options.accesses[access.id] = access.name
            })
        }        
    }

    watch(component, target) {

        component.$watch(target + '.entity_id', (data) => {
            this.entityChanged(data)
        })

        component.$watch(target + '.client_id', (data) => {
            this.clientChanged(data)
        })

        component.$watch(target + '.access_id', (data) => {
        })
    }
}
