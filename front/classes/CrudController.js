import ModalsController from './ModalsController'
import CrudForm from './CrudForm'
import CrudClient from './CrudClient'

export default class CrudController {

    constructor(args) {
        this.mapData = args.mapData
        this.unmapData = args.unmapData
        this.client = new CrudClient(args.endpoint, args.query)
        this.form = new CrudForm(this.client, this.mapData, this.unmapData)
        this.modals = new ModalsController(['confirmDelete', 'edit'].concat(args.modals))
        this.rows = []
        this.query = args.query
        this.paging = args.paging === undefined ? {} : args.paging
        this.paging.currentPage = 1
        this.callbacks = args.callbacks ? args.callbacks : {}
        this.autolist = args.autolist !== undefined ? args.autolist : true
    }

    init(component, target) {
        this.form.init(component, target + '.form')
        this.modals.init(component, target + '.modals')
        this.vm = component
        this.watch(component, target)
        if (this.autolist) {
            return this.list()
        }
    }

    list() {
        this.client.list(this.paging).then(resp => {          
            // Set data.
            this.rows = []
            resp.data.data.forEach(item => {
                this.rows.push(this.mapData(item))
            });
            // Callback.
            if (this.callbacks.listed) {
                this.callbacks.listed(this.rows)
            }
            // Update paging.
            if (resp.data.paging) {
                this.paging.total = resp.data.paging.count
            }
            this.resolve(resp)
        })
        return new Promise((resolve, reject) => {
            this.resolve = resolve
            this.reject = reject
        })
    }

    filter(filters) {
        this.query.filters = filters
        this.reload()
    }

    filterMore(filters) {
        const legacy = this.query.filters ? this.query.filters : {}
        this.query.filters = { ...legacy, ...filters }
        this.reload()
    }

    reload() {
        if (this.paging.currentPage > 1) {
            this.paging.currentPage = 1
        } else {
            this.list()
        }
    }

    create() {
        this.modals.open('edit')
        this.form.create().then(resp => {
            if (this.callbacks.created) {
                this.callbacks.created(resp)
            }
            this.modals.close('edit')
            if (this.autolist) {
                this.list()
            }
        })
    }
    
    update(id) {
        this.modals.open('edit')
        this.form.update(id).then(resp => {
            if (this.callbacks.updated) {
                this.callbacks.updated(resp)
            }
            this.modals.close('edit')
            if (this.autolist) {
                this.list()
            }
        })
    }

    duplicate(id) {
        this.modals.open('edit')
        this.form.duplicate(id).then(resp => {
            if (this.callbacks.duplicated) {
                this.callbacks.duplicated(resp)
            }
            this.modals.close('edit')
            if (this.autolist) {
                this.list()
            }
        })
    }

    remove(id) {
        // Just an alias because delete can't be used in some contexts (reserved JS keyword).
        this.delete(id)
    }

    delete(id) {
            this.modals.open('confirmDelete').then(resp => {
            if (resp.confirmed) {
                if (this.callbacks.deleteConfirmed) {
                    this.callbacks.deleteConfirmed()
                }
                this.client.delete(id).then(resp => {
                    if (this.callbacks.deleted) {
                        this.callbacks.deleted(resp)
                    }
                    this.paging.total--
                    if (this.paging.total <= (this.paging.currentPage - 1) * this.paging.perPage && this.paging.currentPage > 1) {
                        this.paging.currentPage--
                    } else if (this.autolist) {
                        this.list()
                    }
                })
                .catch(err => {
                    if (err.response.status == 423) {
                        this.vm.$notify({type: 'danger', message: this.vm.$t('errors.423-delete') })
                    } else if (err.response.status == 403) {
                        this.vm.$notify({type: 'danger', message: this.vm.$t('errors.403-delete') })
                    }
                })        
            }
        })
    }
    
    watch(component, target) {
        component.$watch(target + '.paging.currentPage', (data) => {
            this.list()
        })
        component.$watch(target + '.paging.perPage', (data) => {
            this.reload()
        })
    }
}
